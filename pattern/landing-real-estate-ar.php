<?php
/**
 * Title: Landing Page - Real Estate (Arabic)
 * Slug: omega-design/landing-real-estate-ar
 * Categories: omega-design-general
 * Description: The Real Estate landing page ("Arab Real Estates") in Arabic, right-to-left, with Arabic fonts - its own header, hero with stats and a property search bar, properties, why choose us, categories, investment banner, services, agents, testimonials, a closing call-to-action and its own footer.
 * Keywords: landing page, real estate, property, villa, apartment, agents, arabic, rtl, gcc, عربي, عقارات
 * Viewport Width: 1400
 *
 * Arabic copy for the shared layout in
 * includes/patterns/landing-real-estate-builder.php - same keys as the
 * English table in landing-real-estate.php. The builder adds .omega-re-ar
 * to the wrapper, which switches assets/css/landing-real-estate.css to
 * right-to-left with Arabic fonts.
 */

defined('ABSPATH') || exit;

$re_lang = 'ar';
$re_text = [
	'arrow'                  => '←',
	'quote_open'             => '«',
	'quote_close'            => '»',
	'logo_name'              => __('ARAB', 'omega-design'),
	'logo_tag'               => __('REAL ESTATES', 'omega-design'),
	'nav_home'               => __('الرئيسية', 'omega-design'),
	'nav_properties'         => __('العقارات', 'omega-design'),
	'nav_about'              => __('من نحن', 'omega-design'),
	'nav_services'           => __('الخدمات', 'omega-design'),
	'nav_agents'             => __('الوكلاء', 'omega-design'),
	'nav_contact'            => __('تواصل معنا', 'omega-design'),
	'list_property'          => __('اكتشف منزلك القادم', 'omega-design'),

	'hero_eyebrow'           => __('اعثر على منزلك المثالي', 'omega-design'),
	'hero_title'             => __('الحياة العصرية تبدأ هنا', 'omega-design'),
	'hero_lead'              => __('اكتشف عقارات فاخرة في المملكة العربية السعودية. اشترِ أو استأجر أو استثمر بثقة.', 'omega-design'),
	'hero_alt'               => __('فيلا فاخرة حديثة مع مسبح', 'omega-design'),
	'explore'                => __('تصفح العقارات', 'omega-design'),
	'watch_video'            => __('شاهد الفيديو', 'omega-design'),
	'stat_properties'        => __('عقار', 'omega-design'),
	'stat_clients'           => __('عميل سعيد', 'omega-design'),
	'stat_years'             => __('سنوات من الخبرة', 'omega-design'),

	'tab_buy'                => __('شراء', 'omega-design'),
	'tab_rent'               => __('إيجار', 'omega-design'),
	'tab_commercial'         => __('تجاري', 'omega-design'),
	'field_location'         => __('الموقع', 'omega-design'),
	'field_location_value'   => __('الرياض، السعودية', 'omega-design'),
	'field_type'             => __('نوع العقار', 'omega-design'),
	'field_type_value'       => __('جميع الأنواع', 'omega-design'),
	'field_price'            => __('نطاق السعر', 'omega-design'),
	'field_price_value'      => __('أي سعر', 'omega-design'),
	'field_bedrooms'         => __('غرف النوم', 'omega-design'),
	'field_bedrooms_value'   => __('أي عدد', 'omega-design'),
	'search'                 => __('بحث', 'omega-design'),

	'featured_eyebrow'       => __('عقارات مميزة', 'omega-design'),
	'featured_title'         => __('العقارات الأكثر طلباً', 'omega-design'),
	'featured_subtitle'      => __('اكتشف مجموعتنا المختارة بعناية من العقارات الفاخرة.', 'omega-design'),
	'featured_button'        => __('عرض جميع العقارات', 'omega-design'),
	'for_sale'               => __('للبيع', 'omega-design'),
	'for_rent'               => __('للإيجار', 'omega-design'),
	'p1_title'               => __('فيلا فاخرة في الرياض', 'omega-design'),
	'p1_location'            => __('الرياض، السعودية', 'omega-design'),
	'p1_beds'                => __('5 غرف', 'omega-design'),
	'p1_baths'               => __('6 حمامات', 'omega-design'),
	'p1_area'                => '450 م²',
	'p1_price'               => __('3,500,000 ر.س', 'omega-design'),
	'p2_title'               => __('شقة عصرية', 'omega-design'),
	'p2_location'            => __('جدة، السعودية', 'omega-design'),
	'p2_beds'                => __('3 غرف', 'omega-design'),
	'p2_baths'               => __('3 حمامات', 'omega-design'),
	'p2_area'                => '180 م²',
	'p2_price'               => __('120,000 ر.س / سنوياً', 'omega-design'),
	'p3_title'               => __('منزل عائلي', 'omega-design'),
	'p3_location'            => __('الدمام، السعودية', 'omega-design'),
	'p3_beds'                => __('4 غرف', 'omega-design'),
	'p3_baths'               => __('4 حمامات', 'omega-design'),
	'p3_area'                => '320 م²',
	'p3_price'               => __('2,800,000 ر.س', 'omega-design'),
	'p4_title'               => __('شقة فاخرة', 'omega-design'),
	'p4_location'            => __('الرياض، السعودية', 'omega-design'),
	'p4_beds'                => __('غرفتان', 'omega-design'),
	'p4_baths'               => __('حمامان', 'omega-design'),
	'p4_area'                => '150 م²',
	'p4_price'               => __('95,000 ر.س / سنوياً', 'omega-design'),

	'why_eyebrow'            => __('لماذا العقارات العربية', 'omega-design'),
	'why_title'              => __('شريكك العقاري الموثوق', 'omega-design'),
	'why_text'               => __('نجعل شراء العقارات واستئجارها والاستثمار فيها تجربة سهلة وشفافة ومجزية. خبرتنا المحلية وخدمتنا الشخصية تضمن لك العثور على العقار المثالي.', 'omega-design'),
	'why_alt'                => __('فيلا فاخرة مع مسبح عند الغروب', 'omega-design'),
	'f1_title'               => __('عقارات موثقة', 'omega-design'),
	'f1_text'                => __('إعلانات موثقة 100%', 'omega-design'),
	'f2_title'               => __('استشارة خبراء', 'omega-design'),
	'f2_text'                => __('خبراء بالسوق المحلي', 'omega-design'),
	'f3_title'               => __('معاملات آمنة', 'omega-design'),
	'f3_text'                => __('آمنة وشفافة', 'omega-design'),
	'f4_title'               => __('خدمة مخصصة', 'omega-design'),
	'f4_text'                => __('مصممة حسب احتياجاتك', 'omega-design'),

	'categories_eyebrow'     => __('فئات العقارات', 'omega-design'),
	'categories_title'       => __('تصفح حسب الفئة', 'omega-design'),
	'categories_button'      => __('عرض جميع الفئات', 'omega-design'),
	'c1_title'               => __('فلل', 'omega-design'),
	'c1_text'                => __('فلل فاخرة للعائلات', 'omega-design'),
	'c2_title'               => __('شقق', 'omega-design'),
	'c2_text'                => __('شقق عصرية في مواقع مميزة', 'omega-design'),
	'c3_title'               => __('تجاري', 'omega-design'),
	'c3_text'                => __('مكاتب ومساحات تجارية', 'omega-design'),
	'c4_title'               => __('أراضٍ', 'omega-design'),
	'c4_text'                => __('فرص استثمارية', 'omega-design'),

	'invest_eyebrow'         => __('استثمر في مستقبلك', 'omega-design'),
	'invest_title_1'         => __('مواقع مميزة.', 'omega-design'),
	'invest_title_2'         => __('عوائد أعلى.', 'omega-design'),
	'invest_lead'            => __('استكشف فرص الاستثمار في أسرع مناطق المملكة نمواً.', 'omega-design'),
	'invest_button'          => __('عرض العقارات الاستثمارية', 'omega-design'),
	'invest_alt'             => __('أفق الرياض عند الغروب', 'omega-design'),

	'services_eyebrow'       => __('خدماتنا', 'omega-design'),
	'services_title'         => __('حلول عقارية متكاملة', 'omega-design'),
	's1_title'               => __('شراء عقار', 'omega-design'),
	's1_text'                => __('اعثر على منزل أحلامك', 'omega-design'),
	's2_title'               => __('استئجار عقار', 'omega-design'),
	's2_text'                => __('خيارات إيجار مرنة', 'omega-design'),
	's3_title'               => __('بيع عقار', 'omega-design'),
	's3_text'                => __('احصل على أفضل قيمة', 'omega-design'),
	's4_title'               => __('إدارة الأملاك', 'omega-design'),
	's4_text'                => __('إدارة بلا متاعب', 'omega-design'),

	'agents_eyebrow'         => __('وكلاؤنا', 'omega-design'),
	'agents_title'           => __('تعرّف على خبرائنا', 'omega-design'),
	'agents_button'          => __('عرض جميع الوكلاء', 'omega-design'),
	'a1_name'                => __('أحمد الفهد', 'omega-design'),
	'a1_role'                => __('مستشار عقاري أول', 'omega-design'),
	'a2_name'                => __('سارة المنصوري', 'omega-design'),
	'a2_role'                => __('أخصائية العقارات الفاخرة', 'omega-design'),
	'a3_name'                => __('خالد آل سعود', 'omega-design'),
	'a3_role'                => __('مستشار استثماري', 'omega-design'),
	'a4_name'                => __('نور الحربي', 'omega-design'),
	'a4_role'                => __('علاقات العملاء', 'omega-design'),

	'testimonials_eyebrow'   => __('ماذا يقول عملاؤنا', 'omega-design'),
	'testimonials_title'     => __('موثوق من مئات العملاء', 'omega-design'),
	't1_quote'               => __('خدمة ممتازة وفريق محترف. ساعدونا في العثور على الفيلا المثالية لعائلتنا.', 'omega-design'),
	't1_name'                => __('فهد القحطاني', 'omega-design'),
	't1_city'                => __('الرياض', 'omega-design'),
	't2_quote'               => __('إجراءات سلسة من البداية حتى النهاية. أنصح بهم بشدة!', 'omega-design'),
	't2_name'                => __('عائشة الزهراني', 'omega-design'),
	't2_city'                => __('جدة', 'omega-design'),
	't3_quote'               => __('فريق محترف وذو خبرة عالية. فرص استثمارية رائعة.', 'omega-design'),
	't3_name'                => __('محمد الحربي', 'omega-design'),
	't3_city'                => __('الدمام', 'omega-design'),

	'cta_title_1'            => __('هل أنت مستعد للعثور على', 'omega-design'),
	'cta_title_2'            => __('عقار أحلامك؟', 'omega-design'),
	'cta_lead'               => __('تواصل مع خبرائنا اليوم واتخذ الخطوة الأولى نحو امتلاك منزلك المثالي.', 'omega-design'),
	'cta_alt'                => __('شرفة فيلا ومسبح عند الغروب', 'omega-design'),
	'contact_us'             => __('تواصل معنا', 'omega-design'),

	'newsletter_label'       => __('اشترك في نشرتنا البريدية', 'omega-design'),
	'newsletter_placeholder' => __('بريدك الإلكتروني', 'omega-design'),
	'newsletter_success'     => __('شكراً لك — تم اشتراكك بنجاح!', 'omega-design'),
	'copyright'              => __('العقارات العربية. جميع الحقوق محفوظة.', 'omega-design'),
	'privacy'                => __('سياسة الخصوصية', 'omega-design'),
	'terms'                  => __('الشروط والأحكام', 'omega-design'),
];

include OMEGA_DESIGN_INCLUDES . '/patterns/landing-real-estate-builder.php';
