<?php
/**
 * Title: Section - Fashion Store: Latest from the Blog
 * Slug: omega-design/section-fashion-blog
 * Categories: omega-design-sections
 * Description: A live 3-post grid of the site's latest blog posts - insertable on its own, on any page.
 * Keywords: blog, posts, query, section
 */

defined('ABSPATH') || exit;
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Latest from Our Blog', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:query {"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false}} -->
<div class="wp-block-query">
<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:post-featured-image {"isLink":true} /-->
<!-- wp:post-title {"level":4,"isLink":true} /-->
<!-- wp:post-date {"fontSize":"small"} /-->
<!-- /wp:post-template -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:group -->
