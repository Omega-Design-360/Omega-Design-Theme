<?php
/**
 * Preview-card Customizer Control
 *
 * One control class for every preview-card picker in the Customizer
 * (header/footer style, color mode, color scheme, sidebar position, button
 * radius/look, fonts). Each module passes its own static card renderer -
 * the same one its Settings page form (menus.php) uses - so both surfaces
 * share one markup.
 *
 * Only ever loaded through the autoloader from a 'customize_register'
 * callback, by which point WP_Customize_Control exists - declaring it any
 * earlier during theme bootstrap would be a fatal error.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class card_control extends \WP_Customize_Control {

    public $type = 'omega_card';

    /**
     * callable($current_value, $link_callback, $control)
     */
    public $renderer = null;

    public function render_content() {
        if (!is_callable($this->renderer)) {
            return;
        }

        $this->render_heading();
        call_user_func($this->renderer, $this->value(), [$this, 'print_radio_link'], $this);
    }

    protected function render_heading() {
        if ($this->label) {
            echo '<span class="customize-control-title">' . esc_html($this->label) . '</span>';
        }
        if ($this->description) {
            echo '<span class="description customize-control-description">' . esc_html($this->description) . '</span>';
        }
    }

    /**
     * The $link_callback handed to radio-card renderers: a shared radio
     * group name plus the Customizer's two-way data-customize-setting-link.
     */
    public function print_radio_link($key = null) {
        printf('name="%s" ', esc_attr('_customize-radio-' . $this->id));
        $this->link();
    }

    /**
     * The $link_callback for non-radio renderers (font picker), which only
     * need the setting link itself.
     */
    public function print_link($key = null) {
        $this->link();
    }
}
