<?php
/**
 * Title: Landing Page - Real Estate (English + Arabic, automatic)
 * Slug: omega-design/landing-real-estate-bilingual
 * Categories: omega-design-general
 * Description: The Real Estate landing page ("Arab Real Estates") in English and Arabic on one page - each visitor sees their own language automatically, with an English / العربية switch. Its own header and footer, so the page uses the "Landing Page (No Header/Footer)" template automatically.
 * Keywords: landing page, real estate, property, bilingual, arabic, english, rtl, gcc, automatic language, عربي, عقارات
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Both versions on one page: their wrappers carry omega-lang-en /
 * omega-lang-ar, and includes/core/visitor_language.php sends each visitor
 * only the one in their language. Editing either pattern file updates
 * this one too.
 */

defined('ABSPATH') || exit;

include __DIR__ . '/landing-real-estate.php';
echo "\n\n";
include __DIR__ . '/landing-real-estate-ar.php';
