<?php
namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class core {
    
    private static $instance = null;
    private $loader = null;
    private $initialized = false;
    
    private function __construct() {
        if (!$this->check_requirements()) {
            return;
        }
    }
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function init() {
        spl_autoload_register(function ($class) {
            $prefix = 'OmegaDesign\\';
            $base_dir = OMEGA_DESIGN_INCLUDES . '/';
            
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }
            
            $relative_class = substr($class, $len);
            
            $namespace_dirs = [
                'core\\'       => 'core/',
                'customizer\\' => 'customizer/',
                'admin\\'      => 'admin/',
            ];
            
            foreach ($namespace_dirs as $namespace => $dir) {
                if (strpos($relative_class, $namespace) === 0) {
                    $filename = $base_dir . $dir . str_replace('\\', '/', substr($relative_class, strlen($namespace))) . '.php';
                    if (file_exists($filename)) {
                        require_once $filename;
                        return true;
                    }
                }
            }
            
            $file_path = str_replace('\\', '/', $relative_class);
            $default_file = $base_dir . $file_path . '.php';
            if (file_exists($default_file)) {
                require_once $default_file;
                return true;
            }
            
            return false;
        });
        
        if ($this->initialized) {
            return;
        }
        
        if (!$this->check_requirements()) {
            return;
        }
        
        $this->loader = loader::get_instance();
        $this->loader->init();
        
        $this->initialized = true;
        
        do_action('omega_design_initialized', $this);
    }
    
    private function check_requirements() {
        global $wp_version;
        
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', [$this, 'php_version_notice']);
            return false;
        }
        
        if (version_compare($wp_version, '5.8', '<')) {
            add_action('admin_notices', [$this, 'wp_version_notice']);
            return false;
        }
        
        return true;
    }
    
    public function php_version_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php printf(esc_html__('OmegaDesign requires PHP version 7.4 or higher. Your current version is %s.', 'omega-design'), esc_html(PHP_VERSION)); ?></p>
        </div>
        <?php
    }
    
    public function wp_version_notice() {
        global $wp_version;
        ?>
        <div class="notice notice-error">
            <p><?php printf(esc_html__('OmegaDesign requires WordPress version 5.8 or higher. Your current version is %s.', 'omega-design'), esc_html($wp_version)); ?></p>
        </div>
        <?php
    }
    
    public function get_module($module) {
        return $this->loader ? $this->loader->get_module($module) : null;
    }
    
    public function get_loader() {
        return $this->loader;
    }
}

add_action('after_setup_theme', function() {
    \OmegaDesign\core\core::get_instance()->init();
});