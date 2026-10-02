<?php
/**
 * Customizer Section Helpers
 *
 * Shared by every module that adds its own section to the "Omega Design"
 * Customizer panel.
 *
 * @package OmegaDesign\traits
 */

namespace OmegaDesign\traits;

use OmegaDesign\customizer\card_control;

defined('ABSPATH') || exit;

trait customizer_section {

    /**
     * Whichever module registers first creates the shared panel; every
     * later one just reuses it.
     */
    protected static function ensure_design_panel($wp_customize) {
        if ($wp_customize->get_panel('omega_design_panel')) {
            return;
        }

        $wp_customize->add_panel('omega_design_panel', [
            'title'       => __('Omega Design', 'omega-design'),
            'description' => __('Theme-specific options for Omega Design. General site identity, colors, typography and layout are managed in Global Styles via the Site Editor.', 'omega-design'),
            'priority'    => 30,
        ]);
    }

    /**
     * Adds a card_control (preview-card radio grid) for $setting_id.
     * $renderer receives ($current_value, $link_callback, $control) - see
     * card_control::render_content().
     */
    protected static function add_card_control($wp_customize, $setting_id, $type, callable $renderer, array $args) {
        $wp_customize->add_control(new card_control($wp_customize, $setting_id, array_merge($args, [
            'type'     => $type,
            'renderer' => $renderer,
        ])));
    }

    /**
     * Shared wrapper for every preview-card radio grid (Settings page and
     * Customizer alike): <div class="{$prefix}-grid"> with one
     * <label class="{$prefix}-card"> per item, each starting with its radio
     * <input class="{$prefix}-card__input">. $render_body($key, $item)
     * echoes the rest of the card. $link_callback receives each key and
     * must echo whatever attributes bind that <input> to its context - a
     * plain name="..." for the POST form, or the Customizer's own name +
     * link() for two-way JS binding.
     */
    public static function render_radio_card_grid($prefix, array $items, $current, $link_callback, callable $render_body) {
        ?>
        <div class="<?php echo esc_attr($prefix); ?>-grid">
            <?php foreach ($items as $key => $item) : ?>
                <label class="<?php echo esc_attr($prefix); ?>-card">
                    <input
                        type="radio"
                        <?php call_user_func($link_callback, $key); ?>
                        value="<?php echo esc_attr($key); ?>"
                        <?php checked($current, $key); ?>
                        class="<?php echo esc_attr($prefix); ?>-card__input"
                    />
                    <?php call_user_func($render_body, $key, $item); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * The custom radio dot most card styles draw next to their input.
     */
    protected static function render_card_radio_dot($prefix) {
        echo '<span class="' . esc_attr($prefix) . '-card__radio"></span>';
    }

    /**
     * sanitize_key() the value and fall back to $default unless it's one of
     * $allowed (a list of values, or an array keyed by value when $keyed).
     */
    protected static function sanitize_key_choice($value, array $allowed, $default, $keyed = true) {
        $value = sanitize_key((string) $value);
        $valid = $keyed ? array_key_exists($value, $allowed) : in_array($value, $allowed, true);
        return $valid ? $value : $default;
    }
}
