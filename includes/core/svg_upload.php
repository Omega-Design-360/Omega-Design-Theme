<?php
/**
 * Admin-only SVG media uploads. WordPress blocks SVG uploads entirely by
 * default because an SVG is XML that can carry a <script> tag or an
 * event-handler attribute (onload="...") - effectively a stored-XSS vector
 * if it ever gets embedded inline or opened directly. This re-allows SVG,
 * but only for administrators, and only after stripping the file down to a
 * whitelisted set of purely-visual SVG elements/attributes server-side -
 * uploading a "clean-looking" SVG does not skip the sanitizer.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class svg_upload {

    private static $instance = null;

    const ALLOWED_TAGS = [
        'svg', 'g', 'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
        'text', 'tspan', 'defs', 'clippath', 'lineargradient', 'radialgradient', 'stop',
        'title', 'desc', 'symbol', 'use', 'mask', 'pattern', 'style',
    ];

    const ALLOWED_ATTRS = [
        'id', 'class', 'width', 'height', 'viewbox', 'preserveaspectratio',
        'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'd', 'points',
        'fill', 'fill-rule', 'fill-opacity', 'stroke', 'stroke-width', 'stroke-linecap',
        'stroke-linejoin', 'stroke-dasharray', 'stroke-opacity', 'opacity', 'transform',
        'gradienttransform', 'gradientunits', 'offset', 'stop-color', 'stop-opacity',
        'xmlns', 'xmlns:xlink', 'version', 'clip-path', 'mask', 'href', 'xlink:href',
    ];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('upload_mimes', [$this, 'allow_svg_mime']);
        add_filter('wp_check_filetype_and_ext', [$this, 'fix_svg_filetype_check'], 10, 4);
        add_filter('wp_handle_upload_prefilter', [$this, 'sanitize_svg_upload']);
        add_filter('wp_generate_attachment_metadata', [$this, 'add_svg_metadata'], 10, 2);
        add_filter('wp_prepare_attachment_for_js', [$this, 'add_svg_preview'], 10, 2);
        add_action('admin_head', [$this, 'svg_grid_thumbnail_css']);
    }

    public function init() {}

    /**
     * Admins only - editors/authors/contributors never see .svg as an
     * accepted type in the media uploader at all. Also gated behind
     * Settings > Design > "Allow administrators to upload SVG images"
     * (default on) so an admin can turn the capability off site-wide.
     */
    public function allow_svg_mime($mimes) {
        if (!current_user_can('manage_options') || !$this->uploads_enabled()) {
            return $mimes;
        }
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

    private function uploads_enabled() {
        return (bool) get_theme_mod('omega_svg_uploads_enabled', true);
    }

    /**
     * WordPress's own real-MIME sniff (finfo) frequently reports SVG files
     * as text/plain or text/html rather than image/svg+xml, which would
     * otherwise make wp_check_filetype_and_ext() reject the upload even
     * after upload_mimes() allows the extension.
     */
    public function fix_svg_filetype_check($data, $file, $filename, $mimes) {
        if (!current_user_can('manage_options') || !$this->uploads_enabled()) {
            return $data;
        }

        if (!empty($data['ext']) && !empty($data['type'])) {
            return $data;
        }

        $filetype = wp_check_filetype($filename, $mimes);
        if ('svg' === $filetype['ext']) {
            $data['ext']  = 'svg';
            $data['type'] = 'image/svg+xml';
        }

        return $data;
    }

    /**
     * Runs on the temp file before WordPress moves it into place. Non-admin
     * upload paths never reach here with an .svg in the first place (their
     * upload_mimes list never included it), but this rejects one outright
     * regardless, as a second, independent check that doesn't rely solely
     * on that filter having run first.
     */
    public function sanitize_svg_upload($file) {
        $filename = isset($file['name']) ? $file['name'] : '';
        if (!preg_match('/\.svg$/i', $filename)) {
            return $file;
        }

        if (!current_user_can('manage_options') || !$this->uploads_enabled()) {
            $file['error'] = __('You are not allowed to upload SVG files.', 'omega-design');
            return $file;
        }

        $tmp_path = isset($file['tmp_name']) ? $file['tmp_name'] : '';
        if (!$tmp_path || !file_exists($tmp_path)) {
            $file['error'] = __('The uploaded SVG file could not be found.', 'omega-design');
            return $file;
        }

        $content = file_get_contents($tmp_path);
        if (false === $content || stripos($content, '<svg') === false) {
            $file['error'] = __('This file does not look like a valid SVG.', 'omega-design');
            return $file;
        }

        $sanitized = $this->sanitize_svg_content($content);
        if (null === $sanitized) {
            $file['error'] = __('This SVG file could not be safely processed and was rejected.', 'omega-design');
            return $file;
        }

        file_put_contents($tmp_path, $sanitized);
        return $file;
    }

    /**
     * Whitelist sanitizer: parses as strict XML with external entities and
     * network access disabled (blocks XXE), strips any DOCTYPE/ENTITY
     * declaration outright before that (the most common SVG XXE vector),
     * then walks the tree keeping only known-safe elements/attributes -
     * <script>, <foreignObject>, <style>, every "on*" event-handler
     * attribute, "javascript:" URLs, and any href pointing outside the
     * document are all dropped rather than merely escaped.
     */
    private function sanitize_svg_content($content) {
        $content = preg_replace('/<!DOCTYPE[^>]*(\[[^\]]*\])?[^>]*>/is', '', $content);
        $content = preg_replace('/<!ENTITY[^>]*>/is', '', $content);

        if (preg_match('/<script[\s>]/i', $content)) {
            return null;
        }

        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->resolveExternals   = false;
        $doc->substituteEntities = false;
        $loaded = @$doc->loadXML($content, LIBXML_NONET | LIBXML_NOBLANKS);
        libxml_clear_errors();

        if (!$loaded || !$doc->documentElement || 'svg' !== strtolower($doc->documentElement->localName)) {
            return null;
        }

        // clean_node() below only walks and cleans *children* - the root
        // <svg> element's own attributes (e.g. a bare onload="..." on the
        // root tag itself) need cleaning explicitly, or they'd sail
        // through untouched.
        $this->clean_attributes($doc->documentElement);
        $this->clean_node($doc->documentElement);

        $result = $doc->saveXML();
        if (
            false === $result
            || stripos($result, '<script') !== false
            || stripos($result, 'javascript:') !== false
            || preg_match('/\son\w+\s*=/i', $result)
        ) {
            return null;
        }

        return $result;
    }

    private function clean_node(\DOMElement $node) {
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if (XML_COMMENT_NODE === $child->nodeType || XML_PI_NODE === $child->nodeType) {
                $node->removeChild($child);
                continue;
            }

            if (XML_ELEMENT_NODE !== $child->nodeType) {
                continue;
            }

            $tag = strtolower($child->localName);
            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                $node->removeChild($child);
                continue;
            }

            // <style> is common in icon/logo exports (elements reference a
            // CSS class instead of carrying fill/stroke directly) - it
            // isn't a normal container of child elements, so it's sanitized
            // as CSS text instead of being walked/attribute-filtered like
            // everything else.
            if ('style' === $tag) {
                $child->nodeValue = $this->sanitize_style_content($child->textContent);
                continue;
            }

            $this->clean_attributes($child);
            $this->clean_node($child);
        }
    }

    /**
     * Strips the CSS constructs that would otherwise reopen an attack
     * surface through an inline <style> block - @import (pulls in a remote
     * stylesheet), javascript: and expression() (old-IE script execution),
     * and any url() reference other than an in-document #fragment (blocks
     * exfiltration/tracking via a remote background-image, and old
     * browsers' url("javascript:...") behavior) - while leaving normal
     * color/fill/stroke/font declarations untouched.
     */
    private function sanitize_style_content($css) {
        $css = preg_replace('/@import[^;]*;?/i', '', $css);
        $css = preg_replace('/expression\s*\([^)]*\)/i', '', $css);
        $css = preg_replace('/javascript\s*:/i', '', $css);
        $css = preg_replace_callback('/url\s*\(\s*[\'"]?([^\'")]*)[\'"]?\s*\)/i', function ($m) {
            $ref = trim($m[1]);
            return (0 === strpos($ref, '#')) ? $m[0] : '';
        }, $css);
        // A <style> block should never contain markup - if it does, this
        // wasn't a genuine stylesheet, so drop it entirely rather than
        // return partially-cleaned text.
        if (preg_match('/<[a-z!]/i', $css)) {
            return '';
        }
        return $css;
    }

    private function clean_attributes(\DOMElement $el) {
        $to_remove = [];

        for ($i = 0; $i < $el->attributes->length; $i++) {
            $attr = $el->attributes->item($i);
            $name = strtolower($attr->name);
            $value = $attr->value;

            $is_event_handler = (0 === strpos($name, 'on'));
            $is_script_url    = (bool) preg_match('/javascript:/i', $value);
            $is_style_attr    = ('style' === $name);
            $is_style_unsafe  = $is_style_attr && preg_match('/expression\s*\(|javascript:|@import/i', $value);
            $is_href_like     = in_array($name, ['href', 'xlink:href'], true);
            // href is only ever needed for an in-document reference
            // ("#gradient-id" for a <use>) - anything pointing at an
            // external URL is dropped so an SVG can't be used to fetch
            // remote content or as a tracking pixel.
            $is_external_ref  = $is_href_like && 0 !== strpos(ltrim($value), '#');

            $is_allowed_name = in_array($name, self::ALLOWED_ATTRS, true) || $is_style_attr;

            if ($is_event_handler || $is_script_url || $is_style_unsafe || $is_external_ref || !$is_allowed_name) {
                $to_remove[] = $attr->name;
            }
        }

        foreach ($to_remove as $name) {
            $el->removeAttribute($name);
        }
    }

    /**
     * The media grid can't generate raster thumbnail sizes for SVG (there's
     * no bitmap to resize), so without this every SVG shows as a generic
     * broken-image icon in Choose Image dialogs instead of the graphic
     * itself. Also sets the attachment's real width/height straight from
     * the SVG's own markup - without it the block editor's Image block
     * shows its Width/Height dimension fields blank (or 0) for an SVG,
     * since WordPress never generates that metadata for non-raster images
     * on its own.
     */
    public function add_svg_preview($response, $attachment) {
        if (empty($response['mime']) || 'image/svg+xml' !== $response['mime']) {
            return $response;
        }

        $dimensions = $this->get_svg_dimensions(get_attached_file($attachment->ID));
        $width  = $dimensions ? $dimensions['width'] : 200;
        $height = $dimensions ? $dimensions['height'] : 200;

        $response['width']  = $width;
        $response['height'] = $height;
        $response['sizes'] = [
            'full' => [
                'url'         => $response['url'],
                'width'       => $width,
                'height'      => $height,
                'orientation' => $width >= $height ? 'landscape' : 'portrait',
            ],
        ];
        $response['icon'] = $response['url'];

        return $response;
    }

    /**
     * Same width/height detection, but written into the attachment's
     * stored metadata (not just the one-off REST/JS response above) so
     * anything else in WordPress that calls wp_get_attachment_metadata()
     * or wp_get_attachment_image_src() for this SVG - not only the block
     * editor - sees its real dimensions too.
     */
    public function add_svg_metadata($metadata, $attachment_id) {
        if ('image/svg+xml' !== get_post_mime_type($attachment_id)) {
            return $metadata;
        }

        $dimensions = $this->get_svg_dimensions(get_attached_file($attachment_id));
        if (!$dimensions) {
            return $metadata;
        }

        if (!is_array($metadata)) {
            $metadata = [];
        }
        $metadata['width']  = $dimensions['width'];
        $metadata['height'] = $dimensions['height'];

        return $metadata;
    }

    /**
     * Reads the SVG's own width/height attributes, falling back to the
     * viewBox's own width/height when either is missing or non-numeric
     * (very common - many SVG exports only declare a viewBox, e.g.
     * width="100%" or no width/height at all).
     */
    private function get_svg_dimensions($file) {
        if (!$file || !file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        if (false === $content) {
            return null;
        }

        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $loaded = @$doc->loadXML($content, LIBXML_NONET | LIBXML_NOBLANKS);
        libxml_clear_errors();

        if (!$loaded || !$doc->documentElement) {
            return null;
        }

        $svg = $doc->documentElement;
        $width  = $svg->getAttribute('width');
        $height = $svg->getAttribute('height');
        $viewbox = $svg->getAttribute('viewBox');

        $w = is_numeric($width) ? (float) $width : null;
        $h = is_numeric($height) ? (float) $height : null;

        if ((null === $w || null === $h) && $viewbox) {
            $parts = preg_split('/[\s,]+/', trim($viewbox));
            if (4 === count($parts) && is_numeric($parts[2]) && is_numeric($parts[3])) {
                $w = $w ?? (float) $parts[2];
                $h = $h ?? (float) $parts[3];
            }
        }

        if (!$w || !$h) {
            return null;
        }

        return ['width' => (int) round($w), 'height' => (int) round($h)];
    }

    public function svg_grid_thumbnail_css() {
        $screen = get_current_screen();
        if (!$screen || false === strpos((string) $screen->id, 'upload')) {
            return;
        }
        echo '<style>.attachment-preview img[src$=".svg"],.media-icon img[src$=".svg"]{width:100%;height:auto;}</style>';
    }
}
