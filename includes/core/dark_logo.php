<?php
/**
 * Dark Mode Logo
 *
 * When a dark mode logo has been set from the Omega Design dashboard
 * (theme_mod 'omega_custom_logo_dark'), duplicates a rendered custom-logo
 * <img> into an .omega-logo-light/.omega-logo-dark pair so color-mode.css
 * can toggle between them using the same .omega-color-mode-* /
 * prefers-color-scheme rules used for the rest of the palette. Shared by
 * the block-based core/site-logo (hooks.php) and the classic header.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class dark_logo {

    const THEME_MOD = 'omega_custom_logo_dark';

    /**
     * $extra_attrs are merged into the dark <img>'s attributes (e.g. a
     * width style matching the light logo).
     */
    public static function add_variant($html, array $extra_attrs = []) {
        $dark_logo_id = (int) get_theme_mod(self::THEME_MOD);

        if (!$dark_logo_id || strpos($html, 'custom-logo') === false) {
            return $html;
        }

        $dark_image = wp_get_attachment_image($dark_logo_id, 'full', false, array_merge([
            'class' => 'custom-logo omega-logo-dark',
        ], $extra_attrs));

        $with_dark_logo = preg_replace_callback(
            '/<img\b[^>]*\bclass="[^"]*\bcustom-logo\b[^"]*"[^>]*\/?>/i',
            function ($matches) use ($dark_image) {
                $light_image = preg_replace('/class="([^"]*)"/', 'class="$1 omega-logo-light"', $matches[0], 1);
                return $light_image . $dark_image;
            },
            $html,
            1
        );

        return null !== $with_dark_logo ? $with_dark_logo : $html;
    }
}
