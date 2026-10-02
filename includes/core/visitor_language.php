<?php
/**
 * Visitor language (English / Arabic) for bilingual pages.
 *
 * A page can hold the same content twice - one Group with the class
 * "omega-lang-en", one with "omega-lang-ar" (e.g. the "Beauty Salon -
 * English + Arabic" pattern) - and each visitor is only sent the version in
 * their own language:
 *
 * 1. ?lang=en / ?lang=ar in the URL (also remembered in a cookie, for a
 *    language toggle link or for testing),
 * 2. otherwise that cookie,
 * 3. otherwise the language their device/browser asks for
 *    (Accept-Language) - Arabic devices get Arabic, everything else English.
 *
 * A language block is only hidden when the page ALSO has a block in the
 * visitor's language, so a page with just one version is never left blank.
 *
 * Deliberately plain PHP with no WordPress dependencies in current():
 * includes/core/page_cache.php calls it before the theme has loaded, to keep
 * one cached copy per language.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class visitor_language {

    use singleton;

    const SUPPORTED = ['en', 'ar'];
    const DEFAULT   = 'en';
    const COOKIE    = 'omega_lang';

    /** Languages present on the page being rendered (null = not scanned yet). */
    private $page_languages = null;

    private function __construct() {
        add_action('init', [$this, 'remember_choice']);
        add_filter('pre_render_block', [$this, 'skip_other_language'], 10, 2);
        add_filter('render_block', [$this, 'mark_language'], 10, 2);
        add_action('template_redirect', [$this, 'send_vary_header']);
        add_action('wp_footer', [$this, 'render_switcher']);
    }

    /**
     * Floating "English | العربية" toggle, shown only on pages that actually
     * have both language versions. Each link sets ?lang=, which current()
     * honours and remember_choice() stores in the cookie. Styles:
     * .omega-lang-switch in assets/css/style.css. The page cache keeps one
     * copy per language, so the highlighted language is always right.
     */
    public function render_switcher() {
        if (!$this->is_bilingual_page()) {
            return;
        }

        $labels  = ['en' => 'English', 'ar' => 'العربية'];
        $current = self::current();
        $base    = remove_query_arg('lang');

        echo '<nav class="omega-lang-switch" aria-label="' . esc_attr('Language / اللغة') . '">';
        foreach ($labels as $code => $label) {
            $active = $code === $current;
            printf(
                '<a href="%1$s" hreflang="%2$s" lang="%2$s" class="omega-lang-switch__link%3$s"%4$s>%5$s</a>',
                esc_url(add_query_arg('lang', $code, $base)),
                esc_attr($code),
                $active ? ' is-active' : '',
                $active ? ' aria-current="true"' : '',
                esc_html($label)
            );
        }
        echo '</nav>';
    }

    /** The visitor's language: URL choice, then cookie, then device language. */
    public static function current() {
        static $lang = null;
        if (null !== $lang) {
            return $lang;
        }

        $choice = self::supported_or_empty($_GET['lang'] ?? '');
        if ('' !== $choice) {
            return $lang = $choice;
        }

        $cookie = self::supported_or_empty($_COOKIE[self::COOKIE] ?? '');
        if ('' !== $cookie) {
            return $lang = $cookie;
        }

        return $lang = self::from_accept_language((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    }

    /** The lowercased language code when it's a supported one, else ''. */
    private static function supported_or_empty($value) {
        $value = strtolower((string) $value);
        return in_array($value, self::SUPPORTED, true) ? $value : '';
    }

    /** Language codes of every language block in $content. */
    private static function languages_in($content) {
        preg_match_all('/omega-lang-(en|ar)\b/', (string) $content, $matches);
        return array_unique($matches[1]);
    }

    /** Highest-priority supported language in an Accept-Language header, e.g. "ar-SA,ar;q=0.9,en;q=0.8" -> "ar". */
    public static function from_accept_language($header) {
        $ranked = [];
        foreach (explode(',', $header) as $index => $part) {
            $pieces  = explode(';', trim($part));
            $primary = strtolower(trim(explode('-', $pieces[0])[0]));
            if ('' === $primary) {
                continue;
            }
            $q = 1.0;
            foreach (array_slice($pieces, 1) as $param) {
                if (0 === strpos(trim($param), 'q=')) {
                    $q = (float) substr(trim($param), 2);
                }
            }
            // Keep header order among equal weights.
            $ranked[] = [$q, -$index, $primary];
        }

        rsort($ranked);
        foreach ($ranked as $entry) {
            if ($entry[0] > 0 && in_array($entry[2], self::SUPPORTED, true)) {
                return $entry[2];
            }
        }

        return self::DEFAULT;
    }

    /** Stores an explicit ?lang= choice so the rest of the visit stays in that language. */
    public function remember_choice() {
        $choice = self::supported_or_empty($_GET['lang'] ?? '');
        if ('' === $choice || headers_sent() || is_admin()) {
            return;
        }
        if (($_COOKIE[self::COOKIE] ?? '') === $choice) {
            return;
        }
        setcookie(self::COOKIE, $choice, time() + YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true);
        $_COOKIE[self::COOKIE] = $choice;
    }

    /** Front-end page views only - the editor always shows every language. */
    private function is_front_end_render() {
        return !is_admin() && !(defined('REST_REQUEST') && REST_REQUEST) && !wp_doing_ajax();
    }

    private static function block_language($block) {
        $class = (string) ($block['attrs']['className'] ?? '');
        return preg_match('/(?:^|\s)omega-lang-(en|ar)(?:\s|$)/', $class, $match) ? $match[1] : '';
    }

    /** Languages the current page offers, from its own content. */
    private function page_languages() {
        if (null === $this->page_languages) {
            $post = get_post();
            $this->page_languages = self::languages_in($post ? $post->post_content : '');
        }
        return $this->page_languages;
    }

    public function skip_other_language($pre_render, $parsed_block) {
        if (null !== $pre_render || !$this->is_front_end_render()) {
            return $pre_render;
        }

        $lang = self::block_language($parsed_block);
        if ('' === $lang || self::current() === $lang) {
            return $pre_render;
        }

        // Only hide it when the visitor's own language is also on the page.
        return in_array(self::current(), $this->page_languages(), true) ? '' : $pre_render;
    }

    /** Adds lang/dir to a language block's wrapper, for screen readers, search engines and browser translation. */
    public function mark_language($block_content, $block) {
        $lang = self::block_language($block);
        if ('' === $lang || '' === $block_content || !$this->is_front_end_render()) {
            return $block_content;
        }

        $attrs = 'ar' === $lang ? ' lang="ar" dir="rtl"' : ' lang="en" dir="ltr"';
        return block_html::prepend_first_tag_attrs($block_content, $attrs);
    }

    /** Whether the page being viewed has both an English and an Arabic version. */
    private function is_bilingual_page() {
        if (!is_singular() || is_admin()) {
            return false;
        }

        $post = get_queried_object();
        $languages = self::languages_in($post instanceof \WP_Post ? $post->post_content : '');

        return count(array_intersect(self::SUPPORTED, $languages)) >= 2;
    }

    /**
     * Tells browsers/CDNs a bilingual page differs per visitor language.
     * Only on those pages - on every other page it would needlessly stop
     * CDNs (e.g. Cloudflare) and browsers from sharing one cached copy.
     */
    public function send_vary_header() {
        if (!headers_sent() && $this->is_bilingual_page()) {
            header('Vary: Accept-Language, Cookie', false);
        }
    }
}
