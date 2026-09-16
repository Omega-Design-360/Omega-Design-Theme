<?php
/**
 * Theme licensing.
 *
 * Checks a license key against the same Supabase Edge Function ("My Login
 * Form"'s license-activation function) that already issues and tracks keys
 * for this product line. This theme only ever calls 'activate', 'validate'
 * and 'deactivate' - 'generate'/'renew' are store-only actions gated by a
 * secret header this theme never holds - so nothing here can mint or
 * extend a license, only check whether one is valid for this domain.
 *
 * Design choice worth being explicit about: a failed API *request*
 * (network error, timeout, the function being briefly unreachable) never
 * changes the stored license status on its own - only an explicit
 * valid:true/valid:false *response* from the API does. An outage on the
 * licensing backend must never be able to lock every customer's live site
 * out at once; at worst it can only prevent a NEW activation from
 * succeeding until the backend is back.
 *
 * The whole theme is gated on this: template_redirect() replaces the
 * front end with a short "not licensed" notice whenever the stored status
 * isn't 'valid'. wp-admin itself is deliberately never locked, so an admin
 * can always reach Settings > License to fix it - locking that too would
 * trap them with no way to enter a working key.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class license {

    const API_URL     = 'https://wpbeudynppqqizghovbj.supabase.co/functions/v1/license-activation';
    const OPTION_KEY   = 'omega_design_license';
    const CRON_HOOK    = 'omega_design_license_validate';
    const NONCE_ACTION = 'omega_license_action';

    // Sent as 'product' on every activate/validate call - the Edge Function
    // now serves more than one product line (My Login Form, Omega Design)
    // off the same licenses table, and rejects a key that was sold for a
    // different product than the one asking. Without this, a key bought
    // for either product would silently unlock the other too.
    const PRODUCT = 'omega-design';

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_notices', [$this, 'admin_notice']);
        add_action('admin_notices', [$this, 'maybe_show_renewal_nag']);
        add_action('admin_notices', [$this, 'activation_result_notice']);
        add_action('admin_footer', [$this, 'render_activation_modal']);
        add_action('admin_post_omega_design_activate_license', [$this, 'handle_activate_request']);
        add_action('admin_post_omega_design_deactivate_license', [$this, 'handle_deactivate_request']);
        add_action(self::CRON_HOOK, [$this, 'validate']);
        add_action('template_redirect', [$this, 'maybe_lock_front_end']);
        add_action('switch_theme', [$this, 'clear_scheduled_check']);

        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK);
        }
    }

    public function init() {}

    public function clear_scheduled_check() {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
    }

    /**
     * Strip protocol, "www.", trailing slash; lowercase. Mirrors the Edge
     * Function's own normalizeDomain() exactly - normalizing identically
     * on both ends means a difference in normalization logic can never
     * itself cause a false "different site" mismatch.
     */
    public static function normalize_domain($input) {
        $domain = strtolower(trim((string) $input));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = preg_replace('#^www\.#', '', $domain);
        $domain = rtrim($domain, '/');
        return $domain;
    }

    public static function site_domain() {
        return self::normalize_domain(home_url());
    }

    public function get_state() {
        $defaults = [
            'key'          => '',
            'status'       => 'unactivated',
            'plan'         => null,
            'expires_at'   => null,
            'domain'       => '',
            'last_checked' => 0,
            'last_error'   => '',
        ];

        $stored = get_option(self::OPTION_KEY, []);
        return is_array($stored) ? array_merge($defaults, $stored) : $defaults;
    }

    private function save_state($state) {
        update_option(self::OPTION_KEY, $state, false);
    }

    public function is_valid() {
        return 'valid' === $this->get_state()['status'];
    }

    /**
     * Days remaining until expiry, for the renewal-nag admin notice
     * (30/14/7 days out - see admin_notice()). Null for a lifetime license
     * (expires_at never set) or when there's no active license at all.
     */
    public function days_until_expiry() {
        $expires_at = $this->get_state()['expires_at'];
        if (empty($expires_at)) {
            return null;
        }
        $seconds = strtotime($expires_at) - time();
        return (int) ceil($seconds / DAY_IN_SECONDS);
    }

    /**
     * POSTs {action, license_key, domain, ...$extra} as JSON. Returns
     * ['transport_ok' => bool, 'body' => array|null] - transport_ok is
     * false only for a genuine network-layer failure (DNS, timeout,
     * connection refused), never for an HTTP error status or a
     * valid:false JSON body, since both of those ARE the API successfully
     * answering "no."
     */
    private function api_request($action, $extra = []) {
        $response = wp_remote_post(self::API_URL, [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode(array_merge(['action' => $action], $extra)),
        ]);

        if (is_wp_error($response)) {
            return ['transport_ok' => false, 'body' => null, 'error' => $response->get_error_message()];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        return ['transport_ok' => true, 'body' => is_array($body) ? $body : null];
    }

    /**
     * A deliberate, user-initiated action (unlike validate()'s background
     * check) - a network failure here is reported straight to the admin
     * who just clicked "Activate" rather than silently swallowed, but
     * still never overwrites whatever license state already existed.
     */
    public function activate($key) {
        $key = trim((string) $key);
        if ('' === $key) {
            return ['ok' => false, 'message' => __('Enter a license key.', 'omega-design')];
        }

        $domain = self::site_domain();
        $result = $this->api_request('activate', ['license_key' => $key, 'domain' => $domain, 'product' => self::PRODUCT]);

        if (!$result['transport_ok']) {
            return ['ok' => false, 'message' => sprintf(
                /* translators: %s: underlying network error message */
                __('Could not reach the license server: %s. Please try again.', 'omega-design'),
                $result['error']
            )];
        }

        $body = $result['body'];
        if (empty($body['valid'])) {
            $this->save_state([
                'key'          => $key,
                'status'       => 'invalid',
                'plan'         => null,
                'expires_at'   => null,
                'domain'       => $domain,
                'last_checked' => time(),
                'last_error'   => $body['error'] ?? __('This license key could not be activated.', 'omega-design'),
            ]);
            return ['ok' => false, 'message' => $body['error'] ?? __('This license key could not be activated.', 'omega-design')];
        }

        $this->save_state([
            'key'          => $key,
            'status'       => 'valid',
            'plan'         => $body['plan'] ?? null,
            'expires_at'   => $body['expires_at'] ?? null,
            'domain'       => $domain,
            'last_checked' => time(),
            'last_error'   => '',
        ]);

        return ['ok' => true, 'message' => __('License activated.', 'omega-design')];
    }

    /**
     * Background daily re-check (WP-Cron). A network failure here only
     * updates last_checked/last_error, leaving 'status' exactly as it was
     * - see the class docblock for why that matters.
     */
    public function validate() {
        $state = $this->get_state();
        if ('' === $state['key']) {
            return;
        }

        $result = $this->api_request('validate', [
            'license_key' => $state['key'],
            'domain'      => self::site_domain(),
            'product'     => self::PRODUCT,
        ]);

        if (!$result['transport_ok']) {
            $state['last_checked'] = time();
            $state['last_error'] = $result['error'];
            $this->save_state($state);
            return;
        }

        $body = $result['body'];

        if (!empty($body['valid'])) {
            $state['status'] = 'valid';
            $state['plan'] = $body['plan'] ?? $state['plan'];
            $state['expires_at'] = $body['expires_at'] ?? $state['expires_at'];
            $state['last_error'] = '';
        } else {
            // This IS a definitive answer from the API (not a transport
            // failure), so - unlike the branch above - it's allowed to
            // flip status away from 'valid'.
            $state['status'] = 'invalid';
            $state['last_error'] = $body['error'] ?? __('This license is no longer valid.', 'omega-design');
        }

        $state['last_checked'] = time();
        $this->save_state($state);
    }

    /**
     * Best-effort on the API side (frees the activation slot for reuse
     * elsewhere), but always clears local state regardless of whether
     * that call succeeds - an admin choosing to remove a key from this
     * site must never be trapped here by an unreachable API.
     */
    public function deactivate() {
        $state = $this->get_state();
        if ('' !== $state['key']) {
            $this->api_request('deactivate', [
                'license_key' => $state['key'],
                'domain'      => self::site_domain(),
            ]);
        }

        delete_option(self::OPTION_KEY);
    }

    /**
     * Where an activate/deactivate submission lands back on - the modal
     * form posts along whatever admin page it was opened from (the popup
     * shows on every wp-admin screen now, not just one dedicated Settings
     * tab), falling back to the theme dashboard if that field is somehow
     * missing. wp_safe_redirect() itself still refuses anything off-site.
     */
    private function redirect_target() {
        if (!empty($_POST['omega_license_redirect'])) {
            return esc_url_raw(wp_unslash($_POST['omega_license_redirect']));
        }
        return admin_url('admin.php?page=omega-dashboard');
    }

    public function handle_activate_request() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this.', 'omega-design'));
        }
        check_admin_referer(self::NONCE_ACTION, 'omega_nonce_license');

        $key = isset($_POST['omega_license_key']) ? sanitize_text_field(wp_unslash($_POST['omega_license_key'])) : '';
        $result = $this->activate($key);

        $redirect = add_query_arg([
            'omega_license'      => $result['ok'] ? 'activated' : 'error',
            'omega_license_msg'  => rawurlencode($result['message']),
        ], $this->redirect_target());

        wp_safe_redirect($redirect);
        exit;
    }

    public function handle_deactivate_request() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this.', 'omega-design'));
        }
        check_admin_referer(self::NONCE_ACTION, 'omega_nonce_license');

        $this->deactivate();

        $redirect = add_query_arg(['omega_license' => 'deactivated'], $this->redirect_target());

        wp_safe_redirect($redirect);
        exit;
    }

    /**
     * A persistent, impossible-to-miss (but not blocking) nag on every
     * wp-admin screen while unlicensed - deliberately not dismissible,
     * since "dismiss and forget" is exactly how a site ends up running
     * unlicensed indefinitely without anyone noticing why the front end
     * is down.
     */
    public function admin_notice() {
        if ($this->is_valid() || !current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="notice notice-error">
            <p>
                <strong><?php esc_html_e('Omega Design is not licensed.', 'omega-design'); ?></strong>
                <?php esc_html_e('The front end of this site is showing a "not licensed" notice to visitors until a valid license key is activated.', 'omega-design'); ?>
                <a href="#" data-omega-license-open><?php esc_html_e('Activate your license', 'omega-design'); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * Renewal nag for a license that's still valid but running out - most
     * people aren't refusing to renew, they just forgot, so this warns
     * (never blocks) starting 30 days out, getting more urgent at the 7-day
     * mark. Mirrors My Login Form's own Includes/Licensing/Notices.php
     * exactly, for a consistent experience across both products.
     *
     * Deliberately separate from admin_notice() above: that one only ever
     * fires once status is already invalid (maybe_lock_front_end() has
     * already kicked in by then) - this one is specifically for the
     * still-valid, still-working window before that happens.
     */
    public function maybe_show_renewal_nag() {
        if (!$this->is_valid() || !current_user_can('manage_options')) {
            return;
        }

        $days = $this->days_until_expiry();
        if (null === $days || $days < 0 || $days > 30) {
            return; // lifetime license, or outside the 30-day nag window
        }

        $dashboard_url = admin_url('admin.php?page=omega-dashboard');
        $urgency = $days <= 7 ? 'notice-error' : 'notice-warning';

        $message = sprintf(
            /* translators: %d: days remaining */
            _n(
                'Your Omega Design license expires in %d day.',
                'Your Omega Design license expires in %d days.',
                max($days, 1),
                'omega-design'
            ),
            $days
        );

        printf(
            '<div class="notice %1$s"><p>%2$s <a href="%3$s">%4$s</a></p></div>',
            esc_attr($urgency),
            esc_html($message),
            esc_url($dashboard_url),
            esc_html__('Renew now to keep receiving updates and support.', 'omega-design')
        );
    }

    /**
     * Reflects the activate/deactivate result back to whichever admin page
     * the popup's form happened to be submitted from (see redirect_target()
     * - it's no longer always the Settings screen now that the popup can
     * appear anywhere), the same way render_license_form() used to on the
     * old dedicated License tab.
     */
    public function activation_result_notice() {
        if (!current_user_can('manage_options') || !isset($_GET['omega_license'])) {
            return;
        }

        $type = sanitize_key(wp_unslash($_GET['omega_license']));
        $msg  = isset($_GET['omega_license_msg']) ? sanitize_text_field(rawurldecode(wp_unslash($_GET['omega_license_msg']))) : '';

        if ('activated' === $type) {
            $notice = ['success', __('License activated.', 'omega-design')];
        } elseif ('deactivated' === $type) {
            $notice = ['success', __('License deactivated on this site.', 'omega-design')];
        } elseif ('error' === $type) {
            $notice = ['error', $msg ?: __('Could not activate this license key.', 'omega-design')];
        } else {
            return;
        }

        printf(
            '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
            esc_attr($notice[0]),
            esc_html($notice[1])
        );
    }

    /**
     * Activation popup shown on Omega Design's own Dashboard and Settings
     * screens while unlicensed. Self-contained
     * (inline CSS/JS, like maybe_lock_front_end()'s own markup below) so it
     * works identically on any admin screen without depending on
     * admin-pages.css, which only loads on this theme's own pages.
     *
     * Auto-opens on load; closing it (X button or Esc) only hides it for
     * the current page view - nothing is persisted, so it reliably
     * reappears on the next navigation instead of being dismissed once and
     * forgotten. Same reasoning as admin_notice()'s own non-dismissible nag
     * just above. Clicking the overlay deliberately does NOT close it -
     * unlike a typical dismiss-anywhere modal, this one only closes via an
     * explicit action, so it can't be accidentally lost with a stray click.
     *
     * Only ever shown on Omega Design's own admin.php?page=omega-dashboard
     * and omega-settings screens - not on core wp-admin (Dashboard/Home,
     * Posts, Plugins, Users, ...) and not on the other "Omega Design" menu
     * items either, since most of those (Site Builder, Appearance, Menus,
     * Mega Menus, Widgets) just point straight at core screens
     * (site-editor.php, customize.php, nav-menus.php, widgets.php) rather
     * than a page this theme renders itself. admin_notice()'s persistent
     * nag still shows everywhere, so an unlicensed site is never silently
     * un-nagged elsewhere - it's only the popup that's scoped this
     * narrowly.
     */
    public function render_activation_modal() {
        if ($this->is_valid() || !current_user_can('manage_options')) {
            return;
        }

        global $pagenow;
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        if ('admin.php' !== $pagenow || !in_array($page, ['omega-dashboard', 'omega-settings'], true)) {
            return;
        }

        $state = $this->get_state();
        $redirect_to = home_url(add_query_arg(null, null));
        ?>
        <div id="omega-license-modal" class="omega-license-modal" role="dialog" aria-modal="true" aria-labelledby="omega-license-modal-title">
            <div class="omega-license-modal__overlay"></div>
            <div class="omega-license-modal__box">
                <button type="button" class="omega-license-modal__close" data-omega-license-close aria-label="<?php esc_attr_e('Close', 'omega-design'); ?>">&times;</button>

                <h2 id="omega-license-modal-title"><?php esc_html_e('Activate Omega Design', 'omega-design'); ?></h2>
                <p><?php esc_html_e('This site is running an unlicensed copy of its theme. The front end shows visitors a "not licensed" notice until a valid license key is activated.', 'omega-design'); ?></p>

                <?php if (!empty($state['last_error']) && 'unactivated' !== $state['status']) : ?>
                    <div class="notice notice-error inline"><p><?php echo esc_html($state['last_error']); ?></p></div>
                <?php endif; ?>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="omega_design_activate_license" />
                    <input type="hidden" name="omega_license_redirect" value="<?php echo esc_url($redirect_to); ?>" />
                    <?php wp_nonce_field(self::NONCE_ACTION, 'omega_nonce_license'); ?>

                    <label for="omega_license_key_modal"><?php esc_html_e('License Key', 'omega-design'); ?></label>
                    <input type="text" id="omega_license_key_modal" name="omega_license_key" value="<?php echo esc_attr($state['key']); ?>" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX" autocomplete="off" spellcheck="false" />

                    <?php submit_button(__('Activate', 'omega-design'), 'primary', 'omega_submit_activate_license', false); ?>
                </form>
            </div>
        </div>
        <style>
            .omega-license-modal { position: fixed; inset: 0; z-index: 159900; display: flex; align-items: center; justify-content: center; }
            .omega-license-modal[hidden] { display: none; }
            .omega-license-modal__overlay { position: absolute; inset: 0; background: rgba(20, 20, 20, 0.6); }
            .omega-license-modal__box { position: relative; background: #fff; color: #1c1c1c; width: 90%; max-width: 420px; border-radius: 8px; padding: 28px; box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25); box-sizing: border-box; }
            .omega-license-modal__box h2 { margin: 0 0 10px; font-size: 1.2rem; }
            .omega-license-modal__box p { margin: 0 0 14px; line-height: 1.5; }
            .omega-license-modal__box label { display: block; font-weight: 600; margin-bottom: 6px; }
            .omega-license-modal__box input[type="text"] { width: 100%; box-sizing: border-box; padding: 8px 10px; margin-bottom: 14px; border: 1px solid #ccc; border-radius: 4px; }
            .omega-license-modal__close { position: absolute; top: 10px; right: 12px; background: none; border: none; font-size: 1.4rem; line-height: 1; cursor: pointer; color: #666; padding: 4px; }
            .omega-license-modal__close:hover { color: #000; }
        </style>
        <script>
            (function () {
                var modal = document.getElementById('omega-license-modal');
                if (!modal) {
                    return;
                }
                function close() {
                    modal.hidden = true;
                }
                function open() {
                    modal.hidden = false;
                }
                modal.querySelectorAll('[data-omega-license-close]').forEach(function (el) {
                    el.addEventListener('click', close);
                });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && !modal.hidden) {
                        close();
                    }
                });
                document.querySelectorAll('[data-omega-license-open]').forEach(function (el) {
                    el.addEventListener('click', function (e) {
                        e.preventDefault();
                        open();
                    });
                });
            })();
        </script>
        <?php
    }

    /**
     * The License panel on the Settings screen (Settings > License, after
     * WooCommerce) - a permanent place to check status/renewal/deactivate
     * that doesn't depend on the popup being open, alongside
     * render_activation_modal()'s own on-every-page nudge. Same
     * activate/deactivate forms, just laid out as a normal settings card
     * instead of a fixed-position dialog; both post to the same
     * admin-post.php handlers and redirect back to $redirect_to via
     * redirect_target(), and activation_result_notice() reflects the
     * result back here exactly like it does everywhere else.
     */
    public function render_settings_panel($redirect_to) {
        $state = $this->get_state();
        ?>
        <div class="omega-card">
            <div class="omega-card__head">
                <span class="dashicons dashicons-admin-network"></span>
                <div>
                    <h2><?php esc_html_e('License', 'omega-design'); ?></h2>
                </div>
            </div>

            <?php if ('valid' === $state['status']) : ?>
                <p>
                    <span class="omega-badge omega-badge--success"><?php esc_html_e('Active', 'omega-design'); ?></span>
                    <?php if (!empty($state['plan'])) : ?>
                        <?php echo esc_html(ucfirst(str_replace('-', ' ', $state['plan']))); ?>
                    <?php endif; ?>
                </p>
                <p class="description">
                    <?php echo esc_html($state['key']); ?><br />
                    <?php if (!empty($state['expires_at'])) : ?>
                        <?php printf(esc_html__('Renews/expires %s', 'omega-design'), esc_html(date_i18n(get_option('date_format'), strtotime($state['expires_at'])))); ?>
                    <?php else : ?>
                        <?php esc_html_e('Lifetime license - never expires.', 'omega-design'); ?>
                    <?php endif; ?>
                </p>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="omega_design_deactivate_license" />
                    <input type="hidden" name="omega_license_redirect" value="<?php echo esc_url($redirect_to); ?>" />
                    <?php wp_nonce_field(self::NONCE_ACTION, 'omega_nonce_license'); ?>
                    <?php submit_button(__('Deactivate on this site', 'omega-design'), 'omega-btn', 'omega_submit_deactivate_license', false); ?>
                </form>
            <?php else : ?>
                <p class="description">
                    <?php esc_html_e('This site is running unlicensed - the front end shows a "not licensed" notice to visitors until a valid key is activated here.', 'omega-design'); ?>
                </p>
                <?php if (!empty($state['last_error']) && 'unactivated' !== $state['status']) : ?>
                    <div class="notice notice-error inline"><p><?php echo esc_html($state['last_error']); ?></p></div>
                <?php endif; ?>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="omega_design_activate_license" />
                    <input type="hidden" name="omega_license_redirect" value="<?php echo esc_url($redirect_to); ?>" />
                    <?php wp_nonce_field(self::NONCE_ACTION, 'omega_nonce_license'); ?>

                    <div class="omega-field">
                        <label for="omega_license_key"><?php esc_html_e('License Key', 'omega-design'); ?></label>
                        <input type="text" id="omega_license_key" name="omega_license_key" value="<?php echo esc_attr($state['key']); ?>" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX" autocomplete="off" spellcheck="false" />
                    </div>

                    <?php submit_button(__('Activate', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_activate_license', false); ?>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Front-end gate. wp-admin, wp-login.php, and REST/cron requests are
     * untouched (this only ever runs on template_redirect, which doesn't
     * fire for any of those) so the site owner can always still log in
     * and fix the license even while the public front end is locked.
     */
    public function maybe_lock_front_end() {
        if ($this->is_valid() || is_admin()) {
            return;
        }

        status_header(503);
        nocache_headers();
        ?>
        <!doctype html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <title><?php esc_html_e('Site temporarily unavailable', 'omega-design'); ?></title>
            <style>
                body { display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #F5F7F2; color: #1C1C1C; padding: 24px; box-sizing: border-box; }
                .omega-lockout { max-width: 420px; text-align: center; }
                .omega-lockout h1 { font-size: 1.25rem; margin: 0 0 8px; }
                .omega-lockout p { color: #555; line-height: 1.6; margin: 0; }
            </style>
        </head>
        <body>
            <div class="omega-lockout">
                <h1><?php esc_html_e('Site temporarily unavailable', 'omega-design'); ?></h1>
                <p><?php esc_html_e('This site is running an unlicensed copy of its theme. Please contact the site administrator.', 'omega-design'); ?></p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
