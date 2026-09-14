<?php
/**
 * Theme Loader Class
 * 
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class loader {

    private static $instance = null;
    private $modules = [];
    private $configs = [];

    private function __construct() {
        $this->load_configs();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
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
        $admin_class = 'OmegaDesign\\customizer\\menus';
        if (class_exists($admin_class) && method_exists($admin_class, 'get_instance')) {
            $admin_class::get_instance();
        }
    }

    private function init_public() {
        $public_class = 'OmegaDesign\\public\\public';
        if (class_exists($public_class) && method_exists($public_class, 'get_instance')) {
            $public_class::get_instance();
        }
    }

    private function load_configs() {
    $this->configs = [
        'hooks' => [
            'class'    => 'OmegaDesign\\core\\hooks',
            'priority' => 10,
            'required' => true,
            'deps'     => [],
            'enabled'  => true,
        ],
        'top_bar_menu' => [
            'class'    => 'OmegaDesign\\customizer\\menus',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'sidebar' => [
            'class'    => 'OmegaDesign\\customizer\\sidebar',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'megamenu' => [
            'class'    => 'OmegaDesign\\customizer\\megamenu',
            'priority' => 30,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'header_visibility' => [
            'class'    => 'OmegaDesign\\customizer\\header_visibility',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'announcement_bar' => [
            'class'    => 'OmegaDesign\\customizer\\announcement_bar',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'classic_header' => [
            'class'    => 'OmegaDesign\\customizer\\classic_header',
            'priority' => 31,
            'required' => false,
            'deps'     => ['hooks', 'megamenu', 'header_visibility'],
            'enabled'  => true,
        ],
        'woocommerce_header' => [
            'class'    => 'OmegaDesign\\customizer\\woocommerce_header',
            'priority' => 31,
            'required' => false,
            'deps'     => ['hooks', 'classic_header'],
            'enabled'  => true,
        ],
        'product_page' => [
            'class'    => 'OmegaDesign\\customizer\\product_page',
            'priority' => 31,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'footer_visibility' => [
            'class'    => 'OmegaDesign\\customizer\\footer_visibility',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'featured_image_visibility' => [
            'class'    => 'OmegaDesign\\customizer\\featured_image_visibility',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'classic_footer' => [
            'class'    => 'OmegaDesign\\customizer\\classic_footer',
            'priority' => 31,
            'required' => false,
            'deps'     => ['hooks', 'footer_visibility'],
            'enabled'  => true,
        ],
        'patterns' => [
            'class'    => 'OmegaDesign\\customizer\\patterns',
            'priority' => 40,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'title_visibility' => [
            'class'    => 'OmegaDesign\\customizer\\title',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'content_width' => [
            'class'    => 'OmegaDesign\\customizer\\content_width',
            'priority' => 21,
            'required' => false,
            'deps'     => ['hooks', 'title_visibility'],
            'enabled'  => true,
        ],
        'background_color' => [
            'class'    => 'OmegaDesign\\customizer\\background_color',
            'priority' => 22,
            'required' => false,
            'deps'     => ['hooks', 'content_width'],
            'enabled'  => true,
        ],
        'color_mode' => [
            'class'    => 'OmegaDesign\\customizer\\color_mode',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'color_scheme' => [
            'class'    => 'OmegaDesign\\customizer\\color_scheme',
            'priority' => 19,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'responsive_styles' => [
            'class'    => 'OmegaDesign\\core\\responsive_styles',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'icons' => [
            'class'    => 'OmegaDesign\\core\\icons',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'blocks' => [
            'class'    => 'OmegaDesign\\core\\blocks',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'leads' => [
            'class'    => 'OmegaDesign\\core\\leads',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'scroll_animations' => [
            'class'    => 'OmegaDesign\\core\\scroll_animations',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'svg_upload' => [
            'class'    => 'OmegaDesign\\core\\svg_upload',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'block_style_variations' => [
            'class'    => 'OmegaDesign\\core\\block_style_variations',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'loading_bar' => [
            'class'    => 'OmegaDesign\\core\\loading_bar',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
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