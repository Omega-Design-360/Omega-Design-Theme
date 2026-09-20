<?php
/**
 * GitHub-based theme updater.
 *
 * This theme isn't distributed through WordPress.org, so WP never checks
 * anywhere for new versions on its own. This polls the theme's GitHub repo
 * (https://github.com/Omega-Design-360/OMEGA-DESIGN-THEME) instead, using
 * the same 'pre_set_site_transient_update_themes' filter WP.org-hosted
 * themes are checked through - so a newer version shows up as a normal
 * "Update available" row in Appearance > Themes and on the Updates screen,
 * with a working one-click "Update Now" that pulls straight from GitHub.
 *
 * "Automatically available after a push" has two layers here:
 *  - Polling: the check result is cached for CACHE_TTL, so it's re-read
 *    from GitHub at most once per that window - same model WP.org updates
 *    use. Visiting any wp-admin page (or clicking "Check again" on the
 *    Updates screen) after that window has passed is enough to see it.
 *  - Instant refresh: register_webhook_endpoint() exposes a REST route a
 *    GitHub webhook (Settings > Webhooks on the repo) can hit on every
 *    push, which just clears the cache - so the very next admin page load
 *    re-checks GitHub instead of waiting out the cache window. Wiring the
 *    actual webhook up on GitHub's side (payload URL + secret) still needs
 *    doing once the site is live at a public URL; see README.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class github_updater {

    const REPO      = 'Omega-Design-360/Omega-Design-Theme';
    const BRANCH    = 'main';
    const CACHE_KEY = 'omega_design_github_update';
    const CACHE_TTL = 6 * HOUR_IN_SECONDS;
    const REST_NS   = 'omega-design/v1';

    private static $instance = null;
    private $slug;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->slug = basename(get_template_directory());

        add_filter('pre_set_site_transient_update_themes', [$this, 'check_for_update']);
        add_filter('upgrader_source_selection', [$this, 'fix_source_folder'], 10, 4);
        add_action('admin_init', [$this, 'maybe_bust_cache_on_manual_check']);
        add_action('rest_api_init', [$this, 'register_webhook_endpoint']);
        add_action('upgrader_process_complete', [$this, 'clear_cache_after_update'], 10, 2);
        add_filter('http_request_args', [$this, 'authorize_github_requests'], 10, 2);
    }

    /**
     * The repo this theme ships from is private, so every request that
     * touches it - our own check_for_update() calls, and WP core's own
     * download_url() call during "Update Now" (which we have no other hook
     * into to add headers) - needs an Authorization header or GitHub
     * returns 404 (not 403 - GitHub hides private repos from unauthorized
     * requests rather than confirming they exist). http_request_args fires
     * for every wp_remote_* call in WP, including ones triggered deep
     * inside core, so this one filter covers both cases at once.
     */
    public function authorize_github_requests($args, $url) {
        if (!defined('OMEGA_GITHUB_TOKEN') || '' === OMEGA_GITHUB_TOKEN) {
            return $args;
        }

        $github_hosts = [
            'https://api.github.com/repos/' . self::REPO,
            'https://raw.githubusercontent.com/' . self::REPO,
            'https://github.com/' . self::REPO . '/archive/',
            'https://codeload.github.com/' . self::REPO,
        ];

        foreach ($github_hosts as $prefix) {
            if (0 === strpos($url, $prefix)) {
                $args['headers']['Authorization'] = 'Bearer ' . OMEGA_GITHUB_TOKEN;
                break;
            }
        }

        return $args;
    }

    public function init() {}

    /**
     * WP's own "Check again" button on the Updates screen sets force-check=1
     * and would otherwise still show our cached (possibly stale) result,
     * since that button only forces WP.org's own update_themes check to
     * bypass its transient - it has no way to know about our separate cache.
     */
    public function maybe_bust_cache_on_manual_check() {
        if (!empty($_GET['force-check']) && current_user_can('update_themes')) {
            delete_site_transient(self::CACHE_KEY);
        }
    }

    public function clear_cache_after_update($upgrader, $hook_extra) {
        if (isset($hook_extra['action'], $hook_extra['type']) && 'update' === $hook_extra['action'] && 'theme' === $hook_extra['type']) {
            delete_site_transient(self::CACHE_KEY);
        }
    }

    /**
     * A GitHub webhook (content type application/json, secret shared with
     * OMEGA_GITHUB_WEBHOOK_SECRET) can POST here on every push to main so
     * the next admin page load re-checks GitHub immediately instead of
     * waiting out CACHE_TTL. Only clears the cache - it never triggers the
     * actual update, which still needs an admin to click "Update Now" (or
     * WP's own auto-update system, if enabled for this theme).
     */
    public function register_webhook_endpoint() {
        register_rest_route(self::REST_NS, '/deploy-webhook', [
            'methods'             => ['GET', 'POST'],
            'callback'            => [$this, 'handle_webhook'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function handle_webhook(\WP_REST_Request $request) {
        if (!defined('OMEGA_GITHUB_WEBHOOK_SECRET') || '' === OMEGA_GITHUB_WEBHOOK_SECRET) {
            return new \WP_REST_Response(['error' => 'Webhook secret not configured on this site.'], 403);
        }

        $signature = $request->get_header('x-hub-signature-256');
        $token     = $request->get_param('token');

        $authorized = false;

        if ($signature) {
            // GitHub's own webhook signing: HMAC-SHA256 of the raw body,
            // keyed with the same secret entered in the repo's webhook
            // settings - verified this way instead of a bare token so the
            // payload itself can't be forged even if the URL leaks.
            $expected = 'sha256=' . hash_hmac('sha256', $request->get_body(), OMEGA_GITHUB_WEBHOOK_SECRET);
            $authorized = hash_equals($expected, $signature);
        } elseif ($token) {
            // Fallback for a manual/non-GitHub trigger (e.g. a CI step that
            // just curls this URL with ?token=... after deploying).
            $authorized = hash_equals(OMEGA_GITHUB_WEBHOOK_SECRET, (string) $token);
        }

        if (!$authorized) {
            return new \WP_REST_Response(['error' => 'Invalid signature or token.'], 403);
        }

        delete_site_transient(self::CACHE_KEY);

        return new \WP_REST_Response(['cleared' => true], 200);
    }

    /**
     * Fetches the latest commit on BRANCH, reads that exact commit's
     * style.css for its Version header, and builds a zip download URL
     * pinned to that same commit SHA - so the version WP shows and the zip
     * it actually installs can never drift apart even if main moves on
     * between the check and the eventual "Update Now" click.
     */
    private function get_remote_info() {
        $cached = get_site_transient(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        $commit_url = sprintf('https://api.github.com/repos/%s/commits/%s', self::REPO, self::BRANCH);
        $commit_response = wp_remote_get($commit_url, [
            'headers' => [
                'Accept'     => 'application/vnd.github+json',
                'User-Agent' => 'OmegaDesign-Theme-Updater',
            ],
            'timeout' => 10,
        ]);

        if (is_wp_error($commit_response) || 200 !== wp_remote_retrieve_response_code($commit_response)) {
            return null;
        }

        $commit = json_decode(wp_remote_retrieve_body($commit_response), true);
        $sha = $commit['sha'] ?? null;
        if (!$sha) {
            return null;
        }

        $style_url = sprintf('https://raw.githubusercontent.com/%s/%s/style.css', self::REPO, $sha);
        $style_response = wp_remote_get($style_url, [
            'headers' => ['User-Agent' => 'OmegaDesign-Theme-Updater'],
            'timeout' => 10,
        ]);

        if (is_wp_error($style_response) || 200 !== wp_remote_retrieve_response_code($style_response)) {
            return null;
        }

        $version = null;
        if (preg_match('/^\s*Version:\s*(.+)$/mi', wp_remote_retrieve_body($style_response), $m)) {
            $version = trim($m[1]);
        }

        if (!$version) {
            return null;
        }

        $info = [
            'version' => $version,
            'sha'     => $sha,
            // Not https://github.com/{repo}/archive/{sha}.zip - for a
            // private repo that 302s to codeload.github.com, and WP's HTTP
            // client (like most, including plain curl) strips the
            // Authorization header on a cross-host redirect, so the
            // follow-up request 404s even with a valid token. The REST
            // zipball endpoint instead redirects to a codeload URL with a
            // short-lived signed token already in its query string, so the
            // follow-up needs no header at all.
            'package' => sprintf('https://api.github.com/repos/%s/zipball/%s', self::REPO, $sha),
        ];

        set_site_transient(self::CACHE_KEY, $info, self::CACHE_TTL);

        return $info;
    }

    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote = $this->get_remote_info();
        if (!$remote) {
            return $transient;
        }

        $current_version = wp_get_theme($this->slug)->get('Version');

        if (version_compare($remote['version'], $current_version, '>')) {
            $transient->response[$this->slug] = [
                'theme'       => $this->slug,
                'new_version' => $remote['version'],
                'url'         => 'https://github.com/' . self::REPO,
                'package'     => $remote['package'],
            ];
        } else {
            unset($transient->response[$this->slug]);
        }

        return $transient;
    }

    /**
     * GitHub's archive zips extract to a "{repo}-{sha}" folder, not the
     * theme's own slug - left alone, WP would install that as a brand new,
     * separate theme instead of overwriting this one in place. Renaming the
     * extracted folder to match our slug is what makes the update actually
     * land on top of the existing theme.
     */
    public function fix_source_folder($source, $remote_source, $upgrader, $args = []) {
        global $wp_filesystem;

        if (empty($args['theme']) || $args['theme'] !== $this->slug || !$wp_filesystem) {
            return $source;
        }

        $corrected = trailingslashit($remote_source) . $this->slug . '/';

        if (trailingslashit($source) === $corrected) {
            return $source;
        }

        if ($wp_filesystem->move($source, $corrected, true)) {
            return $corrected;
        }

        return $source;
    }
}
