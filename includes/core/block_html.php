<?php
/**
 * Rendered Block Markup Helpers
 *
 * Small string operations on a block's rendered HTML, used by the
 * render_block filters across the theme - every one of them works on the
 * block's outermost (first) opening tag.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class block_html {

    const FIRST_TAG = '/^(\s*<[a-z0-9]+)([^>]*)(>)/i';

    /**
     * The first opening tag split into [tag_open, attrs, tag_close, rest]
     * ("<div", ' class="a"', ">", everything after), or null when $html
     * doesn't start with a tag.
     */
    public static function split_first_tag($html) {
        if (!preg_match(self::FIRST_TAG, $html, $matches)) {
            return null;
        }

        return [$matches[1], $matches[2], $matches[3], substr($html, strlen($matches[0]))];
    }

    /**
     * Prepends $class to an attribute string's class="", or adds one.
     * $class must already be escaped.
     */
    public static function merge_class_attr($attrs, $class) {
        if (preg_match('/\sclass="/i', $attrs)) {
            return preg_replace('/\sclass="/i', ' class="' . $class . ' ', $attrs, 1);
        }

        return $attrs . ' class="' . $class . '"';
    }

    /**
     * Prepends $style to an attribute string's style="", or adds one.
     * $style must already be escaped.
     */
    public static function merge_style_attr($attrs, $style) {
        if (preg_match('/\sstyle="/i', $attrs)) {
            return preg_replace('/\sstyle="/i', ' style="' . $style . ' ', $attrs, 1);
        }

        return $attrs . ' style="' . $style . '"';
    }

    /**
     * Inserts $markup as the first child of the outermost element.
     */
    public static function insert_after_first_tag($html, $markup) {
        $parts = self::split_first_tag($html);
        if (null === $parts) {
            return $html;
        }

        list($tag_open, $attrs, $tag_close, $rest) = $parts;
        return $tag_open . $attrs . $tag_close . $markup . $rest;
    }

    /**
     * Adds $class to the first tag, skipping past any leading <style>
     * blocks another render filter already prepended.
     */
    public static function add_class_to_first_tag($html, $class) {
        $class = esc_attr($class);

        if (preg_match('#^((?:\s*<style>.*?</style>)+)(.*)$#s', $html, $parts)) {
            return $parts[1] . self::add_class_to_first_tag($parts[2], $class);
        }

        if (preg_match('/^\s*<[a-z0-9]+[^>]*\sclass="/i', $html)) {
            return preg_replace('/^(\s*<[a-z0-9]+[^>]*\sclass=")/i', '$1' . $class . ' ', $html, 1);
        }

        return preg_replace('/^(\s*<[a-z0-9]+)/i', '$1 class="' . $class . '"', $html, 1);
    }

    /**
     * Inserts an already-escaped attribute string right after the first
     * tag's name.
     */
    public static function prepend_first_tag_attrs($html, $attrs) {
        return preg_replace('/^(\s*<[a-z0-9]+)/i', '$1' . $attrs, $html, 1);
    }

    /**
     * Appends an already-escaped attribute string at the end of the first
     * tag. Returns $html unchanged on a regex failure.
     */
    public static function append_first_tag_attrs($html, $attrs) {
        $replaced = preg_replace(self::FIRST_TAG, '$1$2' . $attrs . '$3', $html, 1);
        return null === $replaced ? $html : $replaced;
    }
}
