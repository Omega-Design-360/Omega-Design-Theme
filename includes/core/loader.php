<?php
/**
 * Theme Loader Class
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class loader {

    use singleton;

    private $modules = [];
    private $configs = [];

    private function __construct() {
        $this->load_configs();
    }

    public function init() {
        $this->load_modules();

        if (is_admin()) {
            $this->init_admin();
        } else {
            $this->init_public();
        }

        do_action('omega_design_loader_initialized', $this);

        return $this;
    }

    private function init_admin() {
        $this->boot_singleton('OmegaDesign\\customizer\\menus');
    }

    private function init_public() {
        $this->boot_singleton('OmegaDesign\\public\\public');
    }

    private function boot_singleton($class) {
        if (class_exists($class) && method_exists($class, 'get_instance')) {
            $class::get_instance();
        }
    }

    /**
     * One module config entry. Modules are loaded in ascending priority
     * order; a module is skipped when any of its deps hasn't loaded.
     */
    private static function module($class, $priority = 20, $deps = ['hooks'], $required = false) {
        return [
            'class'    => $class,
            'priority' => $priority,
            'required' => $required,
            'deps'     => $deps,
            'enabled'  => true,
        ];
    }

    private function load_configs() {
        $core       = 'OmegaDesign\\core\\';
        $customizer = 'OmegaDesign\\customizer\\';

        $this->configs = [
            'hooks'                     => self::module($core . 'hooks', 10, [], true),
            'top_bar_menu'              => self::module($customizer . 'menus'),
            'sidebar'                   => self::module($customizer . 'sidebar'),
            'megamenu'                  => self::module($customizer . 'megamenu', 30),
            'header_visibility'         => self::module($customizer . 'header_visibility'),
            'announcement_bar'          => self::module($customizer . 'announcement_bar'),
            'classic_header'            => self::module($customizer . 'classic_header', 31, ['hooks', 'megamenu', 'header_visibility']),
            'woocommerce_header'        => self::module($customizer . 'woocommerce_header', 31, ['hooks', 'classic_header']),
            'product_page'              => self::module($customizer . 'product_page', 31),
            'shop_layouts'              => self::module($customizer . 'shop_layouts', 31),
            'footer_visibility'         => self::module($customizer . 'footer_visibility'),
            'featured_image_visibility' => self::module($customizer . 'featured_image_visibility'),
            'classic_footer'            => self::module($customizer . 'classic_footer', 31, ['hooks', 'footer_visibility']),
            'patterns'                  => self::module($customizer . 'patterns', 40),
            'title_visibility'          => self::module($customizer . 'title'),
            'content_width'             => self::module($customizer . 'content_width', 21, ['hooks', 'title_visibility']),
            'background_color'          => self::module($customizer . 'background_color', 22, ['hooks', 'content_width']),
            'color_mode'                => self::module($customizer . 'color_mode'),
            'visitor_language'          => self::module($core . 'visitor_language'),
            // Site-wide palette picker (Customize > Omega Design > Color
            // Scheme). Its own key - it used to share 'color_scheme' with
            // the per-page scheme module below, which silently replaced it,
            // so the picked palette never reached the front end.
            'site_color_scheme'         => self::module($customizer . 'color_scheme', 19),
            'color_scheme'              => self::module($core . 'color_scheme'),
            'typography'                => self::module($customizer . 'typography'),
            'buttons'                   => self::module($customizer . 'buttons', 21, ['hooks', 'top_bar_menu']),
            'responsive_styles'         => self::module($core . 'responsive_styles'),
            'icons'                     => self::module($core . 'icons'),
            'patterns_cache'            => self::module($core . 'patterns_cache'),
            'page_cache'                => self::module($core . 'page_cache'),
            'blocks'                    => self::module($core . 'blocks'),
            'leads'                     => self::module($core . 'leads'),
            'scroll_animations'         => self::module($core . 'scroll_animations'),
            'svg_upload'                => self::module($core . 'svg_upload'),
            'block_style_variations'    => self::module($core . 'block_style_variations'),
            'loading_bar'               => self::module($core . 'loading_bar'),
            'lazy_images'               => self::module($core . 'lazy_images'),
            'github_updater'            => self::module($core . 'github_updater'),
            'license'                   => self::module($core . 'license', 5, []),
            'theme_setup'               => self::module($core . 'theme_setup', 10, []),
            'elementor'                 => self::module('OmegaDesign\\compat\\elementor'),
        ];
    }

    private function load_modules() {
        uasort($this->configs, function($a, $b) {
            return ($a['priority'] ?? 100) <=> ($b['priority'] ?? 100);
        });

        foreach ($this->configs as $id => $config) {
            if (!$config['enabled']) {
                continue;
            }

            if (!$this->check_dependencies($config['deps'])) {
                if ($config['required']) {
                    $this->log_error("Required module {$id} dependencies missing.");
                    return;
                }
                continue;
            }

            $this->initialize_module($id, $config);
        }
    }

    private function initialize_module($id, $config) {
        $class = $config['class'];

        if (!class_exists($class)) {
            if ($config['required']) {
                $this->log_error("Required class not found: {$class}");
            }
            return;
        }

        try {
            $module = method_exists($class, 'get_instance') ? $class::get_instance() : new $class();
            $this->modules[$id] = $module;

            if (method_exists($module, 'init')) {
                $module->init();
            }
        } catch (\Throwable $e) {
            $this->log_error("Module {$id} failed: " . $e->getMessage());
        }
    }

    private function check_dependencies($deps) {
        foreach ($deps as $dep) {
            if (!isset($this->modules[$dep])) {
                return false;
            }
        }
        return true;
    }

    public function get_module($id) {
        return $this->modules[$id] ?? null;
    }

    public function is_loaded($id) {
        return isset($this->modules[$id]);
    }

    public function get_loaded_modules() {
        return $this->modules;
    }

    private function log_error($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[OmegaDesign] ' . $message);
        }
    }
}
