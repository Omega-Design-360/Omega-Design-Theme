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

use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class responsive_styles {

    use singleton;

    const HOVER_BLOCKS = ['core/group', 'core/columns', 'core/column'];
    const SIZE_BLOCKS   = ['core/group', 'core/columns'];

    const BREAKPOINTS = [
        'tablet' => '1024px',
        'mobile' => '599px',
    ];

    const BOX_SIDES = ['top', 'right', 'bottom', 'left'];

    /** Layout keyword => flex alignment value (core's own flex layout mapping). */
    const FLEX_MAP = [
        'left'          => 'flex-start',
        'top'           => 'flex-start',
        'center'        => 'center',
        'right'         => 'flex-end',
        'bottom'        => 'flex-end',
        'stretch'       => 'stretch',
        'space-between' => 'space-between',
    ];

    /** Layout keyword => grid item alignment value. */
    const GRID_MAP = [
        'left'    => 'start',
        'top'     => 'start',
        'center'  => 'center',
        'right'   => 'end',
        'bottom'  => 'end',
        'stretch' => 'stretch',
    ];

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
            $css .= self::media_query($max_width, $this->build_image_size_rules($settings[$device] ?? [], $fit));
            $fit  = $settings[$device]['objectFit'] ?? $fit;
        }

        return self::prepend_scoped_style($block_content, $css, 'omega-img-');
    }

    /**
     * Mirrors editor.js buildImageSizeRules().
     */
    private function build_image_size_rules($settings, $inherited_fit = '') {
        if (empty($settings) || !is_array($settings)) {
            return '';
        }

        $get = function ($key) use ($settings) {
            return trim(self::sanitize_css_value($settings[$key] ?? ''));
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

        return self::rule('STRONG', $fig) . self::rule('STRONG img', $img);
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
            $css .= self::media_query($max_width, $this->build_element_rules($settings[$device] ?? [], $name));
        }

        return self::prepend_scoped_style($block_content, $css, 'omega-rid-');
    }

    /**
     * Mirrors editor.js buildElementRules().
     */
    private function build_element_rules($settings, $name) {
        if (empty($settings) || !is_array($settings)) {
            return '';
        }

        $self  = self::element_align_declarations($settings['textAlign'] ?? '', $name);
        $extra = '';

        $font_size = self::sanitize_css_value($settings['fontSize'] ?? '');
        if ($font_size !== '') {
            $self .= 'font-size:' . $font_size . ' !important;';
        }

        $width = self::sanitize_css_value($settings['width'] ?? '');
        if ($width !== '') {
            if ('core/image' === $name) {
                $extra .= 'STRONG img{width:' . $width . ' !important;max-width:100%;height:auto !important;}';
            } else {
                $self .= 'width:' . $width . ' !important;max-width:100%;';
            }
        }

        foreach (['margin', 'padding'] as $property) {
            $self .= self::box_side_declarations($property, $settings[$property] ?? []);
        }

        if (!empty($settings['hide'])) {
            $self .= 'display:none !important;';
        }

        return self::rule('STRONG', $self) . $extra;
    }

    private static function element_align_declarations($align, $name) {
        if (!in_array($align, ['left', 'center', 'right', 'justify'], true)) {
            return '';
        }

        $declarations = 'text-align:' . $align . ' !important;';

        if ('justify' === $align) {
            return $declarations;
        }

        switch ((string) $name) {
            // Flex-based blocks line their items up with justify-content,
            // not text-align.
            case 'core/buttons':
            case 'core/social-links':
                return $declarations . 'justify-content:' . self::FLEX_MAP[$align] . ' !important;';

            // A floated/aligned image figure shrinks to the image, so
            // text-align alone wouldn't move it.
            case 'core/image':
                return $declarations . 'float:none !important;display:block !important;margin-left:0 !important;margin-right:0 !important;';

            default:
                return $declarations;
        }
    }

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

        $block_content = $this->maybe_add_valign_class($block_content, $name, $dimensions['omegaVAlign'] ?? '', $block['attrs']['layout']['type'] ?? '');

        if (empty($hover) && empty($dimensions) && empty($align) && empty($background) && empty($responsive)) {
            return $block_content;
        }

        $css = '';
        foreach (self::BREAKPOINTS as $device => $max_width) {
            $base_decl = $this->build_size_declarations($dimensions, $device)
                . $this->build_align_declarations($align, $device)
                . $this->build_background_declarations($background, $device, $style['background'] ?? []);

            $css .= self::media_query(
                $max_width,
                self::rule('SELECTOR', $base_decl)
                    . self::rule('SELECTOR:hover', $this->build_hover_declarations($hover, $device))
                    . $this->build_layout_rules($responsive[$device] ?? [], $name, $block['attrs']['layout'] ?? [])
            );
        }

        return self::prepend_scoped_style($block_content, $css, 'omega-rid-');
    }

    /**
     * Group "Content Position" (editor.js renderContentPosition()) - a
     * class, not a style, and only for flow/constrained Groups; Row/Stack/
     * Grid have core's own alignment controls.
     */
    private function maybe_add_valign_class($block_content, $name, $valign, $layout_type) {
        if ('core/group' === $name && in_array($valign, ['center', 'bottom'], true) && !in_array($layout_type, ['flex', 'grid'], true)) {
            return block_html::add_class_to_first_tag($block_content, 'omega-valign-' . $valign);
        }
        return $block_content;
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
                $declarations .= $property . ':' . self::sanitize_css_value($value) . ';';
            }
        }

        return $declarations . self::hover_shadow_declaration($hover, $device);
    }

    private static function hover_shadow_declaration($hover, $device) {
        $size = $hover[$device . 'ShadowSize'] ?? null;
        if (empty($size) || !is_numeric($size)) {
            return '';
        }

        $size     = (int) $size;
        $offset_x = self::numeric_or($hover[$device . 'ShadowOffsetX'] ?? null, 0);
        $offset_y = self::numeric_or($hover[$device . 'ShadowOffsetY'] ?? null, (int) round($size / 3));
        $color    = $hover[$device . 'ShadowColor'] ?? 'rgba(0, 0, 0, 0.35)';

        return 'box-shadow:' . $offset_x . 'px ' . $offset_y . 'px ' . $size . 'px ' . self::sanitize_css_value($color) . ';';
    }

    private static function numeric_or($value, $default) {
        return null !== $value && is_numeric($value) ? (int) $value : $default;
    }

    private function build_size_declarations($dimensions, $device) {
        if (empty($dimensions)) {
            return '';
        }

        // !important: the desktop value is baked into the block's own inline
        // style at save time (editor.js), and an inline style beats any
        // stylesheet rule - without it these overrides never applied.
        $declarations = '';
        foreach (['width' => 'Width', 'height' => 'Height'] as $property => $suffix) {
            $value = $dimensions[$device . $suffix] ?? '';
            if ($value !== '') {
                $declarations .= $property . ':' . self::sanitize_css_value($value) . ' !important;';
            }
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

        $type = $layout['type'] ?? ('core/group' === $name ? 'flow' : '');
        $pick = function ($key, $allowed) use ($settings) {
            $value = $settings[$key] ?? '';
            return in_array($value, $allowed, true) ? $value : '';
        };

        list($self, $children) = self::block_type_layout($pick, $name, $type, $layout, $settings);

        $self .= self::shared_layout_declarations($settings, $name);

        // A Column's own rule has to outweigh its parent Columns' child rule
        // (STRONG>.wp-block-column, e.g. "Side by side"); matching that
        // specificity lets the Column's rule, printed later, win.
        $self_selector = 'core/column' === $name ? '.wp-block-columns>STRONG' : 'STRONG';

        return self::rule($self_selector, $self) . $children;
    }

    /**
     * The layout rules specific to this block and layout type, as
     * [self declarations, children rules].
     */
    private static function block_type_layout(callable $pick, $name, $type, $layout, $settings) {
        switch ((string) $name) {
            case 'core/group':
                switch ((string) $type) {
                    case 'flex':
                        return [self::flex_group_layout($pick, $layout), ''];
                    case 'grid':
                        return [self::grid_group_layout($pick, $settings), ''];
                    case 'flow':
                    case 'constrained':
                        return self::flow_layout($pick, $name, $type);
                    default:
                        return ['', ''];
                }

            case 'core/column':
                return self::flow_layout($pick, $name, $type);

            case 'core/columns':
                return self::columns_layout($pick);

            default:
                return ['', ''];
        }
    }

    /**
     * Row/Stack (flex Group): direction, justification, alignment, wrap.
     */
    private static function flex_group_layout(callable $pick, $layout) {
        $self = '';

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
            $value = $is_vertical && 'space-between' === $justify ? 'flex-start' : self::FLEX_MAP[$justify];
            $self .= ($is_vertical ? 'align-items:' : 'justify-content:') . $value . ' !important;';
        }
        if ($valign !== '') {
            $value = !$is_vertical && 'space-between' === $valign ? 'stretch' : self::FLEX_MAP[$valign];
            $self .= ($is_vertical ? 'justify-content:' : 'align-items:') . $value . ' !important;';
        }

        $wrap = $pick('wrap', ['wrap', 'nowrap']);
        if ($wrap !== '') {
            $self .= 'flex-wrap:' . $wrap . ' !important;';
        }

        return $self;
    }

    /**
     * Grid Group: column count, plus each item's justification/alignment
     * within its cell.
     */
    private static function grid_group_layout(callable $pick, $settings) {
        $self = '';

        $columns = isset($settings['gridColumns']) && is_numeric($settings['gridColumns']) ? (int) $settings['gridColumns'] : 0;
        if ($columns >= 1 && $columns <= 12) {
            $self .= 'grid-template-columns:repeat(' . $columns . ',minmax(0,1fr)) !important;';
        }

        $justify = $pick('justify', ['left', 'center', 'right', 'stretch']);
        $valign  = $pick('valign', ['top', 'center', 'bottom', 'stretch']);
        if ($justify !== '') {
            $self .= 'justify-items:' . self::GRID_MAP[$justify] . ' !important;';
        }
        if ($valign !== '') {
            $self .= 'align-items:' . self::GRID_MAP[$valign] . ' !important;';
        }

        return $self;
    }

    /**
     * Plain Group (flow/constrained) and Column: both are normal block
     * flow with no alignment of their own, so either setting switches them
     * to a flex column - Content alignment then moves the content up/down
     * within the block's height, Content justification moves it left/right
     * (text included). Returns [self declarations, children rules].
     */
    private static function flow_layout(callable $pick, $name, $type) {
        $self     = '';
        $children = '';

        $justify = $pick('justify', ['left', 'center', 'right']);
        $valign  = $pick('valign', ['top', 'center', 'bottom']);

        if ('constrained' === $type && $justify !== '') {
            // Constrained keeps core's own approach: the content column
            // (contentSize wide) moves, not each child separately.
            $margins   = ['left' => ['0', 'auto'], 'center' => ['auto', 'auto'], 'right' => ['auto', '0']][$justify];
            $children .= 'STRONG>:not(.alignleft):not(.alignright):not(.alignfull){margin-left:' . $margins[0] . ' !important;margin-right:' . $margins[1] . ' !important;}';
        }

        $use_flex_justify = $justify !== '' && 'constrained' !== $type;
        if ($valign === '' && !$use_flex_justify) {
            return [$self, $children];
        }

        $self .= 'display:flex !important;flex-direction:column !important;';
        if ($valign !== '') {
            $self .= 'justify-content:' . self::FLEX_MAP[$valign] . ' !important;';
        }
        if ('core/column' === $name && $valign !== '') {
            // Fill the row's full height so there's room to move the
            // content within it.
            $self .= 'align-self:stretch !important;';
        }
        if ($use_flex_justify) {
            $self     .= 'align-items:' . self::FLEX_MAP[$justify] . ' !important;text-align:' . $justify . ' !important;';
            $children .= 'STRONG>*{max-width:100%;}';
        } elseif ('constrained' === $type) {
            // Constrained children carry auto side margins, which would
            // shrink them to their content as flex items.
            $children .= 'STRONG>*{width:100%;box-sizing:border-box;}';
        }

        return [$self, $children];
    }

    /**
     * Columns: column alignment, row justification, stacking. Returns
     * [self declarations, children rules].
     */
    private static function columns_layout(callable $pick) {
        $self     = '';
        $children = '';

        $valign = $pick('valign', ['top', 'center', 'bottom', 'stretch']);
        if ($valign !== '') {
            $children .= 'STRONG>.wp-block-column{align-self:' . self::FLEX_MAP[$valign] . ' !important;}';
        }
        // Only visible when the columns don't fill the row (fixed widths).
        $justify = $pick('justify', ['left', 'center', 'right', 'space-between']);
        if ($justify !== '') {
            $self .= 'justify-content:' . self::FLEX_MAP[$justify] . ' !important;';
        }

        switch ((string) $pick('stack', ['stack', 'row'])) {
            case 'stack':
                $self     .= 'flex-wrap:wrap !important;';
                $children .= 'STRONG>.wp-block-column{flex-basis:100% !important;flex-grow:0 !important;}';
                break;
            case 'row':
                $self     .= 'flex-wrap:nowrap !important;';
                $children .= 'STRONG>.wp-block-column{flex-basis:0 !important;flex-grow:1 !important;min-width:0;}';
                break;
        }

        return [$self, $children];
    }

    /**
     * Column width, gap, padding and hide - the settings every layout type
     * shares.
     */
    private static function shared_layout_declarations($settings, $name) {
        $self = '';

        if ('core/column' === $name) {
            $width = self::sanitize_css_value($settings['width'] ?? '');
            if ($width !== '') {
                $self .= 'flex-basis:' . $width . ' !important;flex-grow:0 !important;max-width:100%;';
            }
        }

        $gap = self::sanitize_css_value($settings['gap'] ?? '');
        if ($gap !== '' && in_array($name, ['core/group', 'core/columns'], true)) {
            $self .= 'gap:' . $gap . ' !important;';
        }

        $self .= self::box_side_declarations('padding', $settings['padding'] ?? []);

        if (!empty($settings['hide'])) {
            $self .= 'display:none !important;';
        }

        return $self;
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
     * "{property}-{side}:{value} !important;" for each set side of a
     * {top,right,bottom,left} box value.
     */
    private static function box_side_declarations($property, $box) {
        if (empty($box) || !is_array($box)) {
            return '';
        }

        $declarations = '';
        foreach (self::BOX_SIDES as $side) {
            $value = self::sanitize_css_value($box[$side] ?? '');
            if ($value !== '') {
                $declarations .= $property . '-' . $side . ':' . $value . ' !important;';
            }
        }
        return $declarations;
    }

    /**
     * "selector{declarations}", or '' when there are no declarations.
     */
    private static function rule($selector, $declarations) {
        return $declarations !== '' ? $selector . '{' . $declarations . '}' : '';
    }

    /**
     * Wraps $rules in a max-width media query, or '' when there are none.
     */
    private static function media_query($max_width, $rules) {
        return $rules !== '' ? '@media (max-width:' . $max_width . '){' . $rules . '}' : '';
    }

    /**
     * Prepends a <style> block with $css scoped to a class generated fresh
     * for this render (so duplicated blocks never share a selector) and
     * adds that class to the block's root element. STRONG becomes the same
     * element at id-level specificity via :is() - layout rules have to beat
     * core's own layout/columns CSS, some of which is already !important
     * with 3-class specificity (e.g. the stacked-on-mobile column
     * flex-basis); SELECTOR becomes the plain class.
     */
    private static function prepend_scoped_style($block_content, $css, $class_prefix) {
        if ($css === '') {
            return $block_content;
        }

        $unique_class = $class_prefix . wp_unique_id();
        $css = str_replace(
            ['STRONG', 'SELECTOR'],
            [':is(.' . $unique_class . ',#' . $unique_class . ')', '.' . $unique_class],
            $css
        );

        return '<style>' . $css . '</style>' . block_html::add_class_to_first_tag($block_content, $unique_class);
    }

    /**
     * Keeps only characters valid in a CSS color/length/var() value, so a
     * stray value can't break out of the generated <style> tag.
     */
    private static function sanitize_css_value($value) {
        return preg_replace('/[^a-zA-Z0-9#.,%\-\s()]/', '', (string) $value);
    }
}
