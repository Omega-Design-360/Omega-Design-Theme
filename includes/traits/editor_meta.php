<?php
/**
 * Per-page Editor Meta
 *
 * Shared plumbing for the "Page Settings" modules that store a post meta
 * value set from the block editor sidebar and read it at render time
 * (title, header/footer/featured image visibility, content width,
 * background color, sidebar).
 *
 * A using class provides:
 * - post_types_filter(): filter name for the supported post types list,
 * - meta_fields(): [meta_key => register_post_meta() args],
 * - enqueue_editor_screen_assets(): its own editor script(s).
 *
 * @package OmegaDesign\traits
 */

namespace OmegaDesign\traits;

defined('ABSPATH') || exit;

trait editor_meta {

    use assets;

    /**
     * WordPress script dependencies every editor sidebar panel needs.
     */
    protected static function editor_script_deps($extra = []) {
        return array_merge(
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-compose', 'wp-i18n', self::editor_shared_script()],
            $extra
        );
    }

    abstract protected function post_types_filter();

    abstract protected function meta_fields();

    abstract protected function enqueue_editor_screen_assets();

    protected function register_editor_meta_hooks() {
        add_action('init', [$this, 'register_meta']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
    }

    /**
     * Post types that get this module's control in the editor.
     */
    public function get_supported_post_types() {
        return apply_filters($this->post_types_filter(), ['post', 'page']);
    }

    public function register_meta() {
        foreach ($this->get_supported_post_types() as $post_type) {
            foreach ($this->meta_fields() as $meta_key => $args) {
                register_post_meta($post_type, $meta_key, array_merge([
                    'show_in_rest'  => true,
                    'single'        => true,
                    'auth_callback' => [$this, 'can_edit_meta'],
                ], $args));
            }
        }
    }

    public function can_edit_meta() {
        return current_user_can('edit_posts');
    }

    public function enqueue_editor_assets() {
        if ($this->is_supported_editor_screen()) {
            $this->enqueue_editor_screen_assets();
        }
    }

    protected function is_supported_editor_screen() {
        $screen = get_current_screen();
        return $screen && in_array($screen->post_type, $this->get_supported_post_types(), true);
    }

    protected static function enqueue_editor_script($handle, $relative, $extra_deps = []) {
        return self::enqueue_script($handle, $relative, self::editor_script_deps($extra_deps), true);
    }

    /**
     * The post a rendered block belongs to, or 0 for Query Loop items
     * (they carry a 'query' block context) so archive/listing output is
     * never affected by a single post's own setting.
     */
    protected static function block_post_id($block) {
        if (isset($block->context['query'])) {
            return 0;
        }

        return (int) ($block->context['postId'] ?? get_the_ID());
    }

    /**
     * The post whose meta applies to the page currently being rendered.
     *
     * - A real singular view.
     * - The site's "Posts page" (Settings > Reading): a real Page with its
     *   own ID and meta, but WordPress swaps in the blog listing template
     *   there, so is_singular() is false even though a real post backs it.
     * - The block editor canvas re-fetching a template part through the
     *   wp/v2/block-renderer REST endpoint, which runs outside any normal
     *   page query (is_singular()/get_queried_object_id() never resolve
     *   there) but always passes the post being edited as `post_id`.
     */
    protected static function current_page_id() {
        $post_id = 0;

        if (is_singular()) {
            $post_id = get_queried_object_id();
        } elseif (is_home()) {
            $post_id = (int) get_option('page_for_posts');
        }

        if (!$post_id && !empty($_REQUEST['post_id'])) {
            $post_id = absint($_REQUEST['post_id']);
        }

        return $post_id;
    }

    protected static function boolean_meta_field() {
        return ['type' => 'boolean', 'default' => false];
    }

    protected static function string_meta_field($sanitize_callback = null) {
        $args = ['type' => 'string', 'default' => ''];

        if ($sanitize_callback) {
            $args['sanitize_callback'] = $sanitize_callback;
        }

        return $args;
    }
}
