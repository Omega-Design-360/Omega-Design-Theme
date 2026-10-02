<?php
/**
 * Classic Template Part Rendering
 *
 * Shared by classic_header and classic_footer: each swaps the rendered
 * "header"/"footer" template part for one of its hand-written layouts
 * under template-parts/{dir}/, picked by a style slug.
 *
 * A using class provides:
 * - const STYLES: the valid classic style slugs,
 * - const TEMPLATE_FILES: style slug => template file name.
 *
 * @package OmegaDesign\traits
 */

namespace OmegaDesign\traits;

defined('ABSPATH') || exit;

trait classic_template_part {

    /**
     * Whether a parsed core/template-part block is the given area slug.
     */
    protected static function is_template_part($block, $slug) {
        return $slug === ($block['attrs']['slug'] ?? '');
    }

    /**
     * $mode when it's one of this class's classic styles, else ''.
     */
    protected static function classic_style_or_empty($mode) {
        return in_array($mode, static::STYLES, true) ? $mode : '';
    }

    /**
     * Full path to $style's template file under template-parts/$dir/, or
     * '' when the style has no file or the file is missing.
     */
    protected static function style_template_path($dir, $style) {
        $template_file = static::TEMPLATE_FILES[$style] ?? '';
        if ('' === $template_file) {
            return '';
        }

        $template_path = get_template_directory() . '/template-parts/' . $dir . '/' . $template_file;
        return file_exists($template_path) ? $template_path : '';
    }

    /**
     * The rendered (container-less) classic menu picked in the theme mod
     * $theme_mod, or '' when none is set or it no longer exists.
     */
    protected static function classic_menu_html($theme_mod) {
        $menu_id = (int) get_theme_mod($theme_mod, 0);
        if (!$menu_id || !wp_get_nav_menu_object($menu_id)) {
            return '';
        }

        return (string) wp_nav_menu([
            'menu'        => $menu_id,
            'echo'        => false,
            'container'   => false,
            'fallback_cb' => false,
        ]);
    }

    /**
     * Includes a template file with $vars as its local variables and
     * returns its output.
     */
    protected static function render_template_file($template_path, array $vars) {
        extract($vars, EXTR_SKIP);

        ob_start();
        include $template_path;
        return (string) ob_get_clean();
    }

    /**
     * $value, or $fallback when $value has no visible text.
     */
    protected static function text_or_fallback($value, $fallback) {
        return '' === trim(wp_strip_all_tags($value)) ? $fallback : $value;
    }
}
