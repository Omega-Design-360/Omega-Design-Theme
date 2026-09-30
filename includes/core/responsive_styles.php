<?php
/**
 * Tablet/mobile overrides for the Hover Colors, Hover Shadow, Custom Size
 * (Width/Height) and Text Alignment controls added to the Group/Row/Stack/
 * Grid, Columns and Column blocks in assets/js/editor.js.
 *
 * Those blocks are static (no PHP render callback) - editor.js already
 * bakes the "desktop" values in directly as inline styles/CSS custom
 * properties at save time, which works with no PHP involved at all. But a
 * single inline style can't vary by viewport width, so tablet/mobile
 * overrides need a real `@media` rule instead. WordPress re-parses every
 * block's attributes from post_content on every request regardless of
 * whether the block is static, so `render_block` still fires here - this
 * reads those attributes and prepends a `<style>` block scoped to a class
 * generated fresh on every render (via wp_unique_id()), so two copies of
 * the same duplicated block never collide over a shared selector.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class responsive_styles {

    private static $instance = null;

    const HOVER_BLOCKS = ['core/group', 'core/columns', 'core/column'];
    const SIZE_BLOCKS   = ['core/group', 'core/columns'];

    const BREAKPOINTS = [
        'tablet' => '1024px',
        'mobile' => '599px',
    ];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('render_block', [$this, 'inject_responsive_styles'], 10, 2);
        add_filter('render_block', [$this, 'inject_element_styles'], 10, 2);
        add_filter('render_block', [$this, 'inject_image_size_styles'], 10, 2);
    }


    /**
     * "Image Size" panel (editor.js) for the Image block: desktop rules
     * unconditionally, tablet/mobile ones in the same max-width queries as
     * everything else here. Stored in the omegaImage attribute
     * ({desktop:{...}, tablet:{...}, mobile:{...}}).
     */
    public function inject_image_size_styles($block_content, $block) {
        $settings = $block['attrs']['omegaImage'] ?? [];

        if (empty($settings) || !is_array($settings) || 'core/image' !== ($block['blockName'] ?? '')) {
            return $block_content;
        }

        // An Image fit chosen on a larger screen carries down, so a smaller
        // screen's own default "cover" never silently overrides it.
        $fit = $settings['desktop']['objectFit'] ?? '';
        $css = $this->build_image_size_rules($settings['desktop'] ?? [], '');
        foreach (self::BREAKPOINTS as $device => $max_width) {
            $rules = $this->build_image_size_rules($settings[$device] ?? [], $fit);
            $fit   = $settings[$device]['objectFit'] ?? $fit;
            if ($rules !== '') {
                $css .= '@media (max-width:' . $max_width . '){' . $rules . '}';
            }
        }

        if ($css === '') {
            return $block_content;
        }

        $unique_class = 'omega-img-' . wp_unique_id();
        $css          = str_replace('STRONG', ':is(.' . $unique_class . ',#' . $unique_class . ')', $css);

        return '<style>' . $css . '</style>' . $this->add_class_to_first_tag($block_content, $unique_class);
    }

    /**
     * Mirrors editor.js buildImageSizeRules().
     */
    private function build_image_size_rules($settings, $inherited_fit = '') {
        if (empty($settings) || !is_array($settings)) {
            return '';
        }

        $get = function ($key) use ($settings) {
            return trim($this->sanitize_css_value($settings[$key] ?? ''));
        };

        $width      = $get('width');
        $max_width  = $get('maxWidth');
        $height     = $get('height');
        $min_height = $get('minHeight');
        $radius     = $get('borderRadius');
        $position   = $get('objectPosition');
        $fit        = in_array($settings['objectFit'] ?? '', ['cover', 'contain', 'fill', 'none', 'scale-down'], true) ? $settings['objectFit'] : '';
        $ratio      = (string) ($settings['aspectRatio'] ?? '');
        $ratio      = preg_match('#^(auto|\d+(\.\d+)?\s*/\s*\d+(\.\d+)?)$#', $ratio) ? $ratio : '';

        $fig = '';
        $img = '';

        if ($width !== '') {
            $fig .= 'width:' . $width . ' !important;max-width:100%;';
            $img .= 'width:100% !important;';
        }
        if ($max_width !== '') {
            $fig .= 'max-width:' . $max_width . ' !important;';
            $img .= 'max-width:100% !important;';
        }
        if ($height !== '') {
            $img .= 'height:' . $height . ' !important;';
        }
        if ($min_height !== '') {
            $img .= 'min-height:' . $min_height . ' !important;';
        }
        if ($ratio !== '') {
            $img .= 'aspect-ratio:' . $ratio . ' !important;';
            if ($height === '') {
                $img .= 'height:auto !important;';
            }
        }
        if ($height !== '' || $min_height !== '' || $ratio !== '') {
            $fig .= 'height:auto !important;';
            if ($width === '') {
                $img .= 'width:100% !important;';
            }
            if ($fit === '' && $inherited_fit === '') {
                $img .= 'object-fit:cover !important;';
            }
        }
        if ($fit !== '') {
            $img .= 'object-fit:' . $fit . ' !important;';
        }
        if ($position !== '') {
            $img .= 'object-position:' . $position . ' !important;';
        }
        if ($radius !== '') {
            $img .= 'border-radius:' . $radius . ' !important;';
            $fig .= 'border-radius:' . $radius . ' !important;';
        }

        return ($fig !== '' ? 'STRONG{' . $fig . '}' : '') . ($img !== '' ? 'STRONG img{' . $img . '}' : '');
    }

    /**
     * "Responsive Settings" (editor.js) for every other block - Paragraph,
     * Heading, Image, Buttons... Stored in the block's own top-level
     * omegaResponsive attribute ({tablet:{...}, mobile:{...}}), registered
     * for all blocks by includes/core/blocks.php add_responsive_attribute().
     */
    public function inject_element_styles($block_content, $block) {
        $settings = $block['attrs']['omegaResponsive'] ?? [];
        $name     = $block['blockName'] ?? '';

        if (empty($settings) || !is_array($settings) || in_array($name, ['core/group', 'core/columns', 'core/column'], true)) {
            return $block_content;
        }

        $css = '';
        foreach (self::BREAKPOINTS as $device => $max_width) {
            $rules = $this->build_element_rules($settings[$device] ?? [], $name);
            if ($rules !== '') {
                $css .= '@media (max-width:' . $max_width . '){' . $rules . '}';
            }
        }

        if ($css === '') {
            return $block_content;
        }

        $unique_class = 'omega-rid-' . wp_unique_id();
        $css          = str_replace('STRONG', ':is(.' . $unique_class . ',#' . $unique_class . ')', $css);

        return '<style>' . $css . '</style>' . $this->add_class_to_first_tag($block_content, $unique_class);
    }

    /**
     * Mirrors editor.js buildElementRules().
     */
    private function build_element_rules($settings, $name) {
        if (empty($settings) || !is_array($settings)) {
            return '';
        }

        $self  = '';
        $extra = '';

        $align = $settings['textAlign'] ?? '';
        if (in_array($align, ['left', 'center', 'right', 'justify'], true)) {
            $self .= 'text-align:' . $align . ' !important;';
            // Flex-based blocks line their items up with justify-content,
            // not text-align.
            if (in_array($name, ['core/buttons', 'core/social-links'], true) && 'justify' !== $align) {
                $self .= 'justify-content:' . ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$align] . ' !important;';
            }
            // A floated/aligned image figure shrinks to the image, so
            // text-align alone wouldn't move it.
            if ('core/image' === $name && 'justify' !== $align) {
                $self .= 'float:none !important;display:block !important;margin-left:0 !important;margin-right:0 !important;';
            }
        }

        $font_size = $this->sanitize_css_value($settings['fontSize'] ?? '');
        if ($font_size !== '') {
            $self .= 'font-size:' . $font_size . ' !important;';
        }

        $width = $this->sanitize_css_value($settings['width'] ?? '');
        if ($width !== '') {
            if ('core/image' === $name) {
                $extra .= 'STRONG img{width:' . $width . ' !important;max-width:100%;height:auto !important;}';
            } else {
                $self .= 'width:' . $width . ' !important;max-width:100%;';
            }
        }

        foreach (['margin', 'padding'] as $property) {
            if (!empty($settings[$property]) && is_array($settings[$property])) {
                foreach (['top', 'right', 'bottom', 'left'] as $side) {
                    $value = $this->sanitize_css_value($settings[$property][$side] ?? '');
                    if ($value !== '') {
                        $self .= $property . '-' . $side . ':' . $value . ' !important;';
                    }
                }
            }
        }

        if (!empty($settings['hide'])) {
            $self .= 'display:none !important;';
        }

        return ($self !== '' ? 'STRONG{' . $self . '}' : '') . $extra;
    }

    public function init() {}

    public function inject_responsive_styles($block_content, $block) {
        $name         = $block['blockName'] ?? '';
        $is_hover_block = in_array($name, self::HOVER_BLOCKS, true);
        $is_size_block  = in_array($name, self::SIZE_BLOCKS, true);

        if (!$is_hover_block && !$is_size_block) {
            return $block_content;
        }

        $style      = $block['attrs']['style'] ?? [];
        $hover      = $is_hover_block ? ($style['omegaHover'] ?? []) : [];
        $dimensions = $is_size_block ? ($style['dimensions'] ?? []) : [];
        $align      = $is_hover_block ? ($style['omegaAlign'] ?? []) : [];
        // HOVER_BLOCKS is the same Group/Columns/Column set editor.js's
        // "Responsive Background" panel targets.
        $background = $is_hover_block ? ($style['omegaBackground'] ?? []) : [];
        // "Responsive Layout" (editor.js) - {tablet:{...}, mobile:{...}}.
        $responsive = $is_hover_block ? ($style['omegaResponsive'] ?? []) : [];

        // Group "Content Position" (editor.js renderContentPosition()) -
        // a class, not a style, and only for flow/constrained Groups; Row/
        // Stack/Grid have core's own alignment controls.
        $valign      = $dimensions['omegaVAlign'] ?? '';
        $layout_type = $block['attrs']['layout']['type'] ?? '';
        if ('core/group' === $name && in_array($valign, ['center', 'bottom'], true) && !in_array($layout_type, ['flex', 'grid'], true)) {
            $block_content = $this->add_class_to_first_tag($block_content, 'omega-valign-' . $valign);
        }

        if (empty($hover) && empty($dimensions) && empty($align) && empty($background) && empty($responsive)) {
            return $block_content;
        }

        $css = '';
        foreach (self::BREAKPOINTS as $device => $max_width) {
            $hover_decl  = $this->build_hover_declarations($hover, $device);
            $base_decl   = $this->build_size_declarations($dimensions, $device)
                . $this->build_align_declarations($align, $device)
                . $this->build_background_declarations($background, $device, $style['background'] ?? []);
            $layout_css  = $this->build_layout_rules($responsive[$device] ?? [], $name, $block['attrs']['layout'] ?? []);

            if ($hover_decl === '' && $base_decl === '' && $layout_css === '') {
                continue;
            }

            $css .= '@media (max-width:' . $max_width . '){';
            if ($base_decl !== '') {
                $css .= 'SELECTOR{' . $base_decl . '}';
            }
            if ($hover_decl !== '') {
                $css .= 'SELECTOR:hover{' . $hover_decl . '}';
            }
            $css .= $layout_css . '}';
        }

        if ($css === '') {
            return $block_content;
        }

        $unique_class = 'omega-rid-' . wp_unique_id();
        // STRONG: same element, but at id-level specificity via :is() - the
        // layout rules below have to beat core's own layout/columns CSS,
        // some of which is already !important with 3-class specificity
        // (e.g. the stacked-on-mobile column flex-basis).
        $css = str_replace(
            ['STRONG', 'SELECTOR'],
            [':is(.' . $unique_class . ',#' . $unique_class . ')', '.' . $unique_class],
            $css
        );

        return '<style>' . $css . '</style>' . $this->add_class_to_first_tag($block_content, $unique_class);
    }

    private function build_hover_declarations($hover, $device) {
        if (empty($hover)) {
            return '';
        }

        $property_map = [
            'Text'       => 'color',
            'Background' => 'background-color',
            'Border'     => 'border-color',
        ];

        $declarations = '';
        foreach ($property_map as $suffix => $property) {
            $value = $hover[$device . $suffix] ?? '';
            if ($value !== '') {
                $declarations .= $property . ':' . $this->sanitize_css_value($value) . ';';
            }
        }

        $size = $hover[$device . 'ShadowSize'] ?? null;
        if (!empty($size) && is_numeric($size)) {
            $size     = (int) $size;
            $offset_x = isset($hover[$device . 'ShadowOffsetX']) && is_numeric($hover[$device . 'ShadowOffsetX'])
                ? (int) $hover[$device . 'ShadowOffsetX']
                : 0;
            $offset_y = isset($hover[$device . 'ShadowOffsetY']) && is_numeric($hover[$device . 'ShadowOffsetY'])
                ? (int) $hover[$device . 'ShadowOffsetY']
                : (int) round($size / 3);
            $color = $hover[$device . 'ShadowColor'] ?? 'rgba(0, 0, 0, 0.35)';

            $declarations .= 'box-shadow:' . $offset_x . 'px ' . $offset_y . 'px ' . $size . 'px ' . $this->sanitize_css_value($color) . ';';
        }

        return $declarations;
    }

    private function build_size_declarations($dimensions, $device) {
        if (empty($dimensions)) {
            return '';
        }

        $width  = $dimensions[$device . 'Width'] ?? '';
        $height = $dimensions[$device . 'Height'] ?? '';

        // !important: the desktop value is baked into the block's own inline
        // style at save time (editor.js), and an inline style beats any
        // stylesheet rule - without it these overrides never applied.
        $declarations = '';
        if ($width !== '') {
            $declarations .= 'width:' . $this->sanitize_css_value($width) . ' !important;';
        }
        if ($height !== '') {
            $declarations .= 'height:' . $this->sanitize_css_value($height) . ' !important;';
        }

        return $declarations;
    }

    private function build_align_declarations($align, $device) {
        if (empty($align)) {
            return '';
        }

        $value = $align[$device . 'TextAlign'] ?? '';
        if (!in_array($value, ['left', 'center', 'right', 'justify'], true)) {
            return '';
        }

        return 'text-align:' . $value . ';';
    }

    /**
     * "Responsive Layout" (editor.js) for one device - full rules rather
     * than declarations, since some settings target the block's children
     * (column widths, constrained-layout justification). Mirrors
     * editor.js buildResponsiveLayoutCss(), which renders the same thing
     * for the editor canvas's device preview.
     */
    private function build_layout_rules($settings, $name, $layout) {
        if (empty($settings) || !is_array($settings)) {
            return '';
        }

        $self     = '';
        $children = '';
        $type     = $layout['type'] ?? ('core/group' === $name ? 'flow' : '');
        $pick     = function ($key, $allowed) use ($settings) {
            $value = $settings[$key] ?? '';
            return in_array($value, $allowed, true) ? $value : '';
        };
        $flex_map = [
            'left'          => 'flex-start',
            'top'           => 'flex-start',
            'center'        => 'center',
            'right'         => 'flex-end',
            'bottom'        => 'flex-end',
            'stretch'       => 'stretch',
            'space-between' => 'space-between',
        ];

        if ('core/group' === $name && 'flex' === $type) {
            $orientation = $pick('orientation', ['horizontal', 'vertical']);
            if ($orientation !== '') {
                $self .= 'flex-direction:' . ('vertical' === $orientation ? 'column' : 'row') . ' !important;';
            }
            $is_vertical = ('' !== $orientation ? $orientation : ($layout['orientation'] ?? 'horizontal')) === 'vertical';

            $justify = $pick('justify', ['left', 'center', 'right', 'space-between']);
            $valign  = $pick('valign', ['top', 'center', 'bottom', 'stretch', 'space-between']);
            // A direction change swaps which axis each setting drives, so
            // whatever a larger screen set would otherwise land on the wrong
            // axis - reset both to core's own Row/Stack defaults unless set.
            if ($orientation !== '') {
                $justify = $justify !== '' ? $justify : 'left';
                $valign  = $valign !== '' ? $valign : ($is_vertical ? 'top' : 'center');
            }
            // Justification runs along the main axis of a Row but across a
            // Stack, and vertical alignment the other way round - core's
            // own flex layout maps them exactly like this.
            if ($justify !== '') {
                $value = $is_vertical && 'space-between' === $justify ? 'flex-start' : $flex_map[$justify];
                $self .= ($is_vertical ? 'align-items:' : 'justify-content:') . $value . ' !important;';
            }
            if ($valign !== '') {
                $value = !$is_vertical && 'space-between' === $valign ? 'stretch' : $flex_map[$valign];
                $self .= ($is_vertical ? 'justify-content:' : 'align-items:') . $value . ' !important;';
            }

            $wrap = $pick('wrap', ['wrap', 'nowrap']);
            if ($wrap !== '') {
                $self .= 'flex-wrap:' . $wrap . ' !important;';
            }
        }

        if ('core/group' === $name && 'grid' === $type) {
            $columns = isset($settings['gridColumns']) && is_numeric($settings['gridColumns']) ? (int) $settings['gridColumns'] : 0;
            if ($columns >= 1 && $columns <= 12) {
                $self .= 'grid-template-columns:repeat(' . $columns . ',minmax(0,1fr)) !important;';
            }

            // Content justification/alignment of each item within its cell.
            $grid_map = ['left' => 'start', 'top' => 'start', 'center' => 'center', 'right' => 'end', 'bottom' => 'end', 'stretch' => 'stretch'];
            $justify  = $pick('justify', ['left', 'center', 'right', 'stretch']);
            $valign   = $pick('valign', ['top', 'center', 'bottom', 'stretch']);
            if ($justify !== '') {
                $self .= 'justify-items:' . $grid_map[$justify] . ' !important;';
            }
            if ($valign !== '') {
                $self .= 'align-items:' . $grid_map[$valign] . ' !important;';
            }
        }

        // Plain Group (flow/constrained) and Column: both are normal block
        // flow with no alignment of their own, so either setting switches
        // them to a flex column - Content alignment then moves the content
        // up/down within the block's height, Content justification moves
        // it left/right (text included).
        if (('core/group' === $name && in_array($type, ['flow', 'constrained'], true)) || 'core/column' === $name) {
            $justify = $pick('justify', ['left', 'center', 'right']);
            $valign  = $pick('valign', ['top', 'center', 'bottom']);

            if ('constrained' === $type && $justify !== '') {
                // Constrained keeps core's own approach: the content column
                // (contentSize wide) moves, not each child separately.
                $margins   = ['left' => ['0', 'auto'], 'center' => ['auto', 'auto'], 'right' => ['auto', '0']][$justify];
                $children .= 'STRONG>:not(.alignleft):not(.alignright):not(.alignfull){margin-left:' . $margins[0] . ' !important;margin-right:' . $margins[1] . ' !important;}';
            }

            $use_flex_justify = $justify !== '' && 'constrained' !== $type;
            if ($valign !== '' || $use_flex_justify) {
                $self .= 'display:flex !important;flex-direction:column !important;';
                if ($valign !== '') {
                    $self .= 'justify-content:' . $flex_map[$valign] . ' !important;';
                }
                if ('core/column' === $name && $valign !== '') {
                    // Fill the row's full height so there's room to move
                    // the content within it.
                    $self .= 'align-self:stretch !important;';
                }
                if ($use_flex_justify) {
                    $self     .= 'align-items:' . $flex_map[$justify] . ' !important;text-align:' . $justify . ' !important;';
                    $children .= 'STRONG>*{max-width:100%;}';
                } elseif ('constrained' === $type) {
                    // Constrained children carry auto side margins, which
                    // would shrink them to their content as flex items.
                    $children .= 'STRONG>*{width:100%;box-sizing:border-box;}';
                }
            }
        }

        if ('core/columns' === $name) {
            $valign = $pick('valign', ['top', 'center', 'bottom', 'stretch']);
            if ($valign !== '') {
                $children .= 'STRONG>.wp-block-column{align-self:' . $flex_map[$valign] . ' !important;}';
            }
            // Only visible when the columns don't fill the row (fixed widths).
            $justify = $pick('justify', ['left', 'center', 'right', 'space-between']);
            if ($justify !== '') {
                $self .= 'justify-content:' . $flex_map[$justify] . ' !important;';
            }

            $stack = $pick('stack', ['stack', 'row']);
            if ('stack' === $stack) {
                $self     .= 'flex-wrap:wrap !important;';
                $children .= 'STRONG>.wp-block-column{flex-basis:100% !important;flex-grow:0 !important;}';
            } elseif ('row' === $stack) {
                $self     .= 'flex-wrap:nowrap !important;';
                $children .= 'STRONG>.wp-block-column{flex-basis:0 !important;flex-grow:1 !important;min-width:0;}';
            }
        }

        if ('core/column' === $name) {
            $width = $this->sanitize_css_value($settings['width'] ?? '');
            if ($width !== '') {
                $self .= 'flex-basis:' . $width . ' !important;flex-grow:0 !important;max-width:100%;';
            }
        }

        $gap = $this->sanitize_css_value($settings['gap'] ?? '');
        if ($gap !== '' && in_array($name, ['core/group', 'core/columns'], true)) {
            $self .= 'gap:' . $gap . ' !important;';
        }

        if (!empty($settings['padding']) && is_array($settings['padding'])) {
            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                $value = $this->sanitize_css_value($settings['padding'][$side] ?? '');
                if ($value !== '') {
                    $self .= 'padding-' . $side . ':' . $value . ' !important;';
                }
            }
        }

        if (!empty($settings['hide'])) {
            $self .= 'display:none !important;';
        }

        // A Column's own rule has to outweigh its parent Columns' child rule
        // (STRONG>.wp-block-column, e.g. "Side by side"); matching that
        // specificity lets the Column's rule, printed later, win.
        $self_selector = 'core/column' === $name ? '.wp-block-columns>STRONG' : 'STRONG';

        return ($self !== '' ? $self_selector . '{' . $self . '}' : '') . $children;
    }

    /**
     * Tablet/mobile background image + focal point. !important because
     * core writes the desktop background image into the block's inline
     * style. Size falls back to cover only when the block has no desktop
     * size of its own (e.g. an image set for mobile only).
     */
    private function build_background_declarations($background, $device, $desktop_background) {
        if (empty($background)) {
            return '';
        }

        // "No image" - hides whatever a larger screen shows. A mobile image
        // still overrides a tablet "No image", since its rule comes later.
        if (!empty($background[$device . 'None'])) {
            return 'background-image:none !important;';
        }

        $declarations = '';
        $url          = esc_url_raw($background[$device . 'Image']['url'] ?? '');
        if ($url !== '') {
            $declarations .= 'background-image:url("' . str_replace(['"', '\\', ')'], ['%22', '%5C', '%29'], $url) . '") !important;';
            if (empty($desktop_background['backgroundSize'])) {
                $declarations .= 'background-size:cover;';
            }
            if (empty($desktop_background['backgroundRepeat'])) {
                $declarations .= 'background-repeat:no-repeat;';
            }
        }

        $position = $background[$device . 'Position'] ?? '';
        if ($position !== '' && preg_match('/^[0-9.]+% [0-9.]+%$/', $position)) {
            $declarations .= 'background-position:' . $position . ' !important;';
        }

        return $declarations;
    }

    /**
     * Keeps only characters valid in a CSS color/length/var() value, so a
     * stray value can't break out of the generated <style> tag.
     */
    private function sanitize_css_value($value) {
        return preg_replace('/[^a-zA-Z0-9#.,%\-\s()]/', '', (string) $value);
    }

    /**
     * Injects a class into the block's root element - the element the
     * generated `@media` rules above target via `.$unique_class`.
     */
    private function add_class_to_first_tag($html, $class) {
        $class = esc_attr($class);

        // Skip any <style> blocks another filter here already prepended
        // (an Image can carry both Responsive Settings and Image Size).
        if (preg_match('#^((?:\s*<style>.*?</style>)+)(.*)$#s', $html, $parts)) {
            return $parts[1] . $this->add_class_to_first_tag($parts[2], $class);
        }

        if (preg_match('/^\s*<[a-z0-9]+[^>]*\sclass="/i', $html)) {
            return preg_replace('/^(\s*<[a-z0-9]+[^>]*\sclass=")/i', '$1' . $class . ' ', $html, 1);
        }

        return preg_replace('/^(\s*<[a-z0-9]+)/i', '$1 class="' . $class . '"', $html, 1);
    }
}
