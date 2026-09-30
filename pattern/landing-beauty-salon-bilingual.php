<?php
/**
 * Title: Landing Page - Beauty Salon (English + Arabic, automatic)
 * Slug: omega-design/landing-beauty-salon-bilingual
 * Categories: omega-design-general
 * Description: The Beauty Salon landing page in English and Arabic on one page - each visitor automatically sees the version matching their phone or browser language (Arabic devices get Arabic, right-to-left; everyone else gets English). Add ?lang=ar or ?lang=en to a link to choose. Both versions are editable in the editor.
 * Keywords: landing page, beauty salon, salon, bilingual, arabic, english, rtl, gcc, automatic language, عربي
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Just the English and Arabic salon patterns back to back. Their wrappers
 * carry omega-lang-en / omega-lang-ar, and includes/core/visitor_language.php
 * sends each visitor only the one in their language. Editing either
 * pattern file updates this one too.
 */

defined('ABSPATH') || exit;

include __DIR__ . '/landing-beauty-salon.php';
echo "\n\n";
include __DIR__ . '/landing-beauty-salon-ar.php';
