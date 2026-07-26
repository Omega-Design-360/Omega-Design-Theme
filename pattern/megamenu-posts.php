<?php
/**
 * Title: Mega Menu - Categories & Latest Posts
 * Slug: omega-design/megamenu-posts
 * Categories: omega-design-megamenu, header
 * Description: Mega menu combining category link columns with a dynamic list of the latest posts and thumbnails.
 * Keywords: mega menu, navigation, posts, blog, query
 * Block Types: core/template-part/megamenu
 * Viewport Width: 1280
 */
?>
<!-- wp:group {"tagName":"nav","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|xl","right":"var:preset|spacing|xl"}}},"backgroundColor":"background","textColor":"body-text","className":"omega-megamenu omega-megamenu--posts","layout":{"type":"constrained"}} -->
<nav class="wp-block-group alignfull omega-megamenu omega-megamenu--posts has-body-text-color has-text-color has-background-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--xl)"><!-- wp:columns {"align":"wide","verticalAlignment":"top"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-top"><!-- wp:column {"verticalAlignment":"top","width":"33%"} -->
<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:33%"><!-- wp:heading {"level":3,"textColor":"primary","fontSize":"medium"} -->
<h3 class="wp-block-heading has-primary-color has-text-color has-medium-font-size">Topics</h3>
<!-- /wp:heading -->
<!-- wp:list {"className":"is-style-none"} -->
<ul class="wp-block-list is-style-none"><!-- wp:list-item --><li><a href="#tutorials">Tutorials</a></li><!-- /wp:list-item -->
<!-- wp:list-item --><li><a href="#news">News</a></li><!-- /wp:list-item -->
<!-- wp:list-item --><li><a href="#guides">Guides</a></li><!-- /wp:list-item -->
<!-- wp:list-item --><li><a href="#case-studies">Case Studies</a></li><!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"top","width":"67%"} -->
<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:67%"><!-- wp:heading {"level":3,"textColor":"primary","fontSize":"medium"} -->
<h3 class="wp-block-heading has-primary-color has-text-color has-medium-font-size">Latest Posts</h3>
<!-- /wp:heading -->
<!-- wp:query {"queryId":0,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false},"className":"omega-megamenu-query"} -->
<div class="wp-block-query omega-megamenu-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","style":{"border":{"radius":"8px"}}} /-->
<!-- wp:post-title {"isLink":true,"fontSize":"small"} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></nav>
<!-- /wp:group -->
