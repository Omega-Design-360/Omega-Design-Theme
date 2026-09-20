<?php
/**
 * Title: Landing Page - Medical Clinic
 * Slug: omega-design/landing-medical-clinic
 * Categories: omega-design-general
 * Description: A full medical clinic/healthcare landing page - hero with an appointment badge, feature strip, a services grid, a "why choose us" band with a booking card, an about section, a specialists grid, a testimonial carousel, and a blog + newsletter row. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, clinic, healthcare, medical, doctors, appointment, hero
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Every block below sticks to a small, deliberately boring vocabulary of
 * attributes (backgroundColor/textColor/fontSize by slug, block align,
 * single-purpose preset-token spacing) plus a small set of shared
 * `className`s (assets/css/landing-pages.css, assets/css/landing-medical-clinic.css)
 * for anything more custom - no hand-typed multi-property inline `style`
 * JSON and no Cover blocks. That's what keeps every block's stored markup
 * matching what the block editor itself would generate, so opening this
 * page never shows an "attempt recovery" warning. See
 * pattern/landing-fashion-store.php for the same approach applied to a
 * different page.
 */

defined('ABSPATH') || exit;

$omega_ph = esc_url(OMEGA_DESIGN_IMAGES_URI . '/placeholder.svg');

/** An "OUR SERVICES"-style eyebrow line above a heading. */
$eyebrow = function ($text) {
	return '<!-- wp:paragraph {"className":"omega-eyebrow","textColor":"primary","fontSize":"small"} -->'
		. '<p class="omega-eyebrow has-primary-color has-text-color has-small-font-size">' . esc_html($text) . '</p>'
		. '<!-- /wp:paragraph -->' . "\n";
};

/** Icon + title (+ optional description) column, icon left of text. */
$build_icon_row_left = function ($items) {
	$out = '<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|m","left":"var:preset|spacing|m"}}}} -->' . "\n<div class=\"wp-block-columns alignwide\">\n";
	foreach ($items as $item) {
		$out .= '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
		$out .= '<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->' . "\n<div class=\"wp-block-group\">\n";
		$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($item['icon']) . '","textColor":"primary","style":{"dimensions":{"width":"28px"}}} /-->' . "\n";
		$out .= '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">' . "\n";
		$out .= '<!-- wp:paragraph {"className":"omega-strong","fontSize":"medium"} --><p class="omega-strong has-medium-font-size">' . esc_html($item['title']) . '</p><!-- /wp:paragraph -->' . "\n";
		if (!empty($item['desc'])) {
			$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($item['desc']) . '</p><!-- /wp:paragraph -->' . "\n";
		}
		$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:group -->\n</div><!-- /wp:column -->\n";
	}
	$out .= '</div><!-- /wp:columns -->' . "\n";
	return $out;
};

/** One service card: photo with an icon badge overlapping its corner, title, description, link. */
$build_service_card = function ($icon, $title, $desc) use ($omega_ph) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"omega-photo-frame","layout":{"type":"constrained"}} --><div class="wp-block-group omega-photo-frame">' . "\n";
	$out .= '<!-- wp:image {"sizeSlug":"medium","className":"omega-rounded-image"} --><figure class="wp-block-image size-medium omega-rounded-image"><img src="' . $omega_ph . '" alt="' . esc_attr($title) . '"/></figure><!-- /wp:image -->' . "\n";
	$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($icon) . '","textColor":"button-text","backgroundColor":"primary","className":"omega-icon-badge"} /-->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '<!-- wp:heading {"level":4,"fontSize":"medium"} --><h4 class="wp-block-heading has-medium-font-size">' . esc_html($title) . '</h4><!-- /wp:heading -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($desc) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size"><a href="#">' . esc_html__('Learn More →', 'omega-design') . '</a></p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:column -->' . "\n";
	return $out;
};

/** Small icon + label item, for the "why choose us" 3x2 mini-grid. */
$build_mini_icon_item = function ($icon, $label) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} --><div class="wp-block-group">' . "\n";
	$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($icon) . '","textColor":"background","style":{"dimensions":{"width":"22px"}}} /-->' . "\n";
	$out .= '<!-- wp:paragraph {"textColor":"background","fontSize":"small"} --><p class="has-background-color has-text-color has-small-font-size">' . esc_html($label) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

/** One "Book an Appointment" form field, styled as a plain (non-interactive) card row. */
$build_form_field = function ($label) {
	return '<!-- wp:paragraph {"backgroundColor":"surface","className":"omega-form-field","fontSize":"small"} -->'
		. '<p class="omega-form-field has-surface-background-color has-background has-small-font-size">' . esc_html($label) . '</p>'
		. '<!-- /wp:paragraph -->' . "\n";
};

/** One doctor card: photo, name, specialty, rating. */
$build_doctor_card = function ($name, $specialty, $rating) use ($omega_ph) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:image {"sizeSlug":"medium","className":"omega-rounded-image"} --><figure class="wp-block-image size-medium omega-rounded-image"><img src="' . $omega_ph . '" alt="' . esc_attr($name) . '"/></figure><!-- /wp:image -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong"} --><p class="omega-strong">' . esc_html($name) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($specialty) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-stars","fontSize":"small"} --><p class="omega-stars has-small-font-size">★★★★★</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($rating) . ' ' . esc_html__('Rating', 'omega-design') . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:column -->' . "\n";
	return $out;
};

?>
<!-- wp:group {"className":"omega-landing omega-landing--medical-clinic","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--medical-clinic">

<?php // HERO ?>
<!-- wp:group {"backgroundColor":"surface","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--2-xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--2-xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading"><?php esc_html_e('Expert Care For A Healthier Brighter Tomorrow', 'omega-design'); ?></h1><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('Compassionate care. Advanced technology. A healthier you.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Book Appointment', 'omega-design'); ?></a></div><!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e('Our Services', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
<?php
echo $build_icon_row_left([
	['icon' => 'groups',    'title' => __('Experienced Doctors', 'omega-design')],
	['icon' => 'apartment', 'title' => __('Modern Facilities', 'omega-design')],
	['icon' => 'favorite',  'title' => __('Personalized Care', 'omega-design')],
	['icon' => 'payments',  'title' => __('Affordable Treatment', 'omega-design')],
]);
?>
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"45%"} -->
<div class="wp-block-column" style="flex-basis:45%">
<!-- wp:group {"className":"omega-photo-frame","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-photo-frame">
<!-- wp:image {"sizeSlug":"large","className":"omega-rounded-image"} -->
<figure class="wp-block-image size-large omega-rounded-image"><img src="<?php echo $omega_ph; ?>" alt="<?php esc_attr_e('Doctor', 'omega-design'); ?>"/></figure>
<!-- /wp:image -->
<!-- wp:group {"backgroundColor":"background","className":"omega-hero-note","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group has-background-background-color has-background omega-hero-note">
<!-- wp:icon {"icon":"omega-icons/calendar-month","textColor":"primary","style":{"dimensions":{"width":"22px"}}} /-->
<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size"><?php esc_html_e('Same Day Appointments Available →', 'omega-design'); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // FEATURE STRIP ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo $build_icon_row_left([
	['icon' => 'stethoscope',   'title' => __('General Checkup', 'omega-design'), 'desc' => __('Preventive care for a better tomorrow', 'omega-design')],
	['icon' => 'biotech',       'title' => __('Lab Tests', 'omega-design'), 'desc' => __('Accurate results, faster', 'omega-design')],
	['icon' => 'vaccines',      'title' => __('Vaccination', 'omega-design'), 'desc' => __('Stay safe, stay healthy', 'omega-design')],
	['icon' => 'support-agent', 'title' => __('24/7 Support', 'omega-design'), 'desc' => __("We're here for you", 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // SERVICES ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:columns {"verticalAlignment":"bottom"} -->
<div class="wp-block-columns are-vertically-aligned-bottom">
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<?php echo $eyebrow(__('Our Services', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Comprehensive Care for You and Your Family', 'omega-design'); ?></h2><!-- /wp:heading -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<!-- wp:paragraph {"align":"right"} --><p class="has-text-align-right"><a href="#"><?php esc_html_e('View All Services →', 'omega-design'); ?></a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
<!-- wp:columns -->
<div class="wp-block-columns">
<?php
echo $build_service_card('ecg-heart', __('Cardiology', 'omega-design'), __('Advanced heart care for a healthier life.', 'omega-design'));
echo $build_service_card('child-care', __('Pediatrics', 'omega-design'), __('Compassionate care for your little ones.', 'omega-design'));
echo $build_service_card('face', __('Dermatology', 'omega-design'), __('Healthy skin, more confident you.', 'omega-design'));
?>
</div>
<!-- /wp:columns -->
<!-- wp:columns -->
<div class="wp-block-columns">
<?php
echo $build_service_card('accessibility-new', __('Orthopedics', 'omega-design'), __('Move better, live stronger.', 'omega-design'));
echo $build_service_card('woman', __("Women's Health", 'omega-design'), __('Specialized care for every stage of life.', 'omega-design'));
echo $build_service_card('dentistry', __('Dental Care', 'omega-design'), __('A brighter, healthier smile.', 'omega-design'));
?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // WHY CHOOSE US ?>
<!-- wp:group {"align":"full","backgroundColor":"heading","textColor":"background","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull has-background-color has-heading-background-color has-text-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"32%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:32%">
<?php echo $eyebrow(__('Why Choose MediCare', 'omega-design')); ?>
<!-- wp:heading {"level":2,"textColor":"background"} --><h2 class="wp-block-heading has-background-color has-text-color"><?php esc_html_e('Your Health Is Our Priority', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"background"} --><p class="has-background-color has-text-color"><?php esc_html_e('We combine experience, technology, and compassion to deliver the best healthcare experience.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"background","textColor":"heading"} --><div class="wp-block-button"><a class="wp-block-button__link has-heading-color has-background-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Learn More →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div><!-- /wp:buttons -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"36%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:36%">
<!-- wp:columns -->
<div class="wp-block-columns">
<?php
echo $build_mini_icon_item('groups', __('Experienced & Certified Doctors', 'omega-design'));
echo $build_mini_icon_item('apartment', __('State-of-the-Art Facilities', 'omega-design'));
?>
</div>
<!-- /wp:columns -->
<!-- wp:columns -->
<div class="wp-block-columns">
<?php
echo $build_mini_icon_item('favorite', __('Patient-Centered Approach', 'omega-design'));
echo $build_mini_icon_item('sanitizer', __('Safe & Hygienic Environment', 'omega-design'));
?>
</div>
<!-- /wp:columns -->
<!-- wp:columns -->
<div class="wp-block-columns">
<?php
echo $build_mini_icon_item('schedule', __('Short Waiting Times', 'omega-design'));
echo $build_mini_icon_item('verified', __('Insurance Accepted', 'omega-design'));
?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"32%","backgroundColor":"background","textColor":"heading","className":"omega-card"} -->
<div class="wp-block-column is-vertically-aligned-center has-heading-color has-background-background-color has-text-color has-background omega-card" style="flex-basis:32%">
<!-- wp:heading {"level":4,"fontSize":"medium"} --><h4 class="wp-block-heading has-medium-font-size"><?php esc_html_e('Book an Appointment', 'omega-design'); ?></h4><!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php esc_html_e('Take the first step towards a healthier you.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<?php
echo $build_form_field(__('Full Name', 'omega-design'));
echo $build_form_field(__('Select Department', 'omega-design'));
echo $build_form_field(__('Select Date', 'omega-design'));
echo $build_form_field(__('Select Time', 'omega-design'));
?>
<!-- wp:buttons --><div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"heading","textColor":"background"} --><div class="wp-block-button"><a class="wp-block-button__link has-background-color has-heading-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Book Now →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div><!-- /wp:buttons -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // ABOUT ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"28%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:28%">
<!-- wp:image {"sizeSlug":"medium","className":"omega-rounded-image"} --><figure class="wp-block-image size-medium omega-rounded-image"><img src="<?php echo $omega_ph; ?>" alt=""/></figure><!-- /wp:image -->
<!-- wp:paragraph {"className":"omega-italic","fontSize":"small"} --><p class="omega-italic has-small-font-size">"<?php esc_html_e('Better Health A Brighter Tomorrow', 'omega-design'); ?>" — <?php esc_html_e('Dr. Ahmed Khan', 'omega-design'); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"44%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:44%">
<?php echo $eyebrow(__('About MediCare', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('More Than Just a Clinic', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('At MediCare, we believe healthcare is not just about treatment, it\'s about people. Our mission is to provide high-quality, affordable, and compassionate care to help you and your family live healthier, happier lives.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-columns">
<?php foreach ([['10+', __('Years of Experience', 'omega-design')], ['25K+', __('Happy Patients', 'omega-design')], ['15+', __('Specialists', 'omega-design')], ['98%', __('Patient Satisfaction', 'omega-design')]] as $stat) : ?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:paragraph {"className":"omega-strong","textColor":"primary","fontSize":"large"} --><p class="omega-strong has-primary-color has-text-color has-large-font-size"><?php echo esc_html($stat[0]); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php echo esc_html($stat[1]); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
<!-- wp:buttons --><div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Our Story →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div><!-- /wp:buttons -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"28%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:28%">
<!-- wp:group {"className":"omega-photo-frame","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-photo-frame">
<!-- wp:image {"sizeSlug":"medium","className":"omega-rounded-image"} --><figure class="wp-block-image size-medium omega-rounded-image"><img src="<?php echo $omega_ph; ?>" alt=""/></figure><!-- /wp:image -->
<!-- wp:icon {"icon":"omega-icons/play-circle","textColor":"background","className":"omega-video-play"} /-->
</div>
<!-- /wp:group -->
<!-- wp:paragraph {"align":"center","fontSize":"small"} --><p class="has-text-align-center has-small-font-size"><?php esc_html_e('Watch Our Video', 'omega-design'); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // SPECIALISTS ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:columns {"verticalAlignment":"bottom"} -->
<div class="wp-block-columns are-vertically-aligned-bottom">
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<?php echo $eyebrow(__('Meet Our Specialists', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Caring Experts, Here for You', 'omega-design'); ?></h2><!-- /wp:heading -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<!-- wp:paragraph {"align":"right"} --><p class="has-text-align-right"><a href="#"><?php esc_html_e('View All Doctors →', 'omega-design'); ?></a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
<!-- wp:columns -->
<div class="wp-block-columns">
<?php
echo $build_doctor_card(__('Dr. Sarah Ali', 'omega-design'), __('MBBS, MD - General Physician', 'omega-design'), '4.9');
echo $build_doctor_card(__('Dr. Omar Sheikh', 'omega-design'), __('MD - Cardiologist', 'omega-design'), '4.8');
echo $build_doctor_card(__('Dr. Ayesha Malik', 'omega-design'), __('MD - Pediatrician', 'omega-design'), '4.9');
echo $build_doctor_card(__('Dr. Bilal Khan', 'omega-design'), __('MS - Orthopedic Surgeon', 'omega-design'), '4.8');
?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // TESTIMONIALS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo $eyebrow(__('What Our Patients Say', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Trusted by Thousands', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/slider {"autoplay":true,"showArrows":true,"showDots":false,"slidesPerView":3,"slidesPerViewTablet":2,"slidesPerViewMobile":1,"gap":"24px","prevLabel":"Previous testimonials","nextLabel":"Next testimonials"} -->
<div class="wp-block-omega-design-slider omega-slider" data-autoplay="1" data-autoplay-speed="6000" data-loop="1" data-arrows="1" data-dots="0" data-spv="3" data-spv-tablet="2" data-spv-mobile="1" data-gap="24px" data-prev-label="Previous testimonials" data-next-label="Next testimonials" data-dots-label="Slides" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php
$testimonials = [
	[__('Excellent care and very friendly staff. I felt comfortable and well taken care of throughout my treatment.', 'omega-design'), __('Sara M.', 'omega-design')],
	[__('The doctors are highly professional and the facility is amazing. Highly recommended!', 'omega-design'), __('Hassan R.', 'omega-design')],
	[__('A wonderful experience from start to finish. Clean, modern, and caring staff.', 'omega-design'), __('Fatima A.', 'omega-design')],
];
foreach ($testimonials as $t) :
	?>
<!-- wp:group {"backgroundColor":"surface","className":"omega-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-surface-background-color has-background omega-card">
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">"<?php echo esc_html($t[0]); ?>"</p><!-- /wp:paragraph -->
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->
<div class="wp-block-group">
<!-- wp:image {"className":"omega-round-image omega-avatar-md","sizeSlug":"thumbnail"} --><figure class="wp-block-image size-thumbnail omega-round-image omega-avatar-md"><img src="<?php echo $omega_ph; ?>" alt="<?php echo esc_attr($t[1]); ?>"/></figure><!-- /wp:image -->
<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">
<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size"><?php echo esc_html($t[1]); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"omega-stars","fontSize":"small"} --><p class="omega-stars has-small-font-size">★★★★★</p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:omega-design/slider -->

</div>
<!-- /wp:group -->

<?php // BLOG + NEWSLETTER ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo $eyebrow(__('Our Latest Articles', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Health Tips & News', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column {"width":"75%"} -->
<div class="wp-block-column" style="flex-basis:75%">
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
<!-- /wp:column -->
<!-- wp:column {"width":"25%"} -->
<div class="wp-block-column" style="flex-basis:25%">
<!-- wp:group {"backgroundColor":"heading","textColor":"background","className":"omega-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background-color has-heading-background-color has-text-color has-background omega-card">
<!-- wp:heading {"level":4,"textColor":"background","fontSize":"medium"} --><h4 class="wp-block-heading has-background-color has-text-color has-medium-font-size"><?php esc_html_e('Join Our Health Newsletter', 'omega-design'); ?></h4><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"background","fontSize":"small"} --><p class="has-background-color has-text-color has-small-font-size"><?php esc_html_e('Get tips, updates, and healthy living ideas straight to your inbox.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:omega-design/newsletter-form {"placeholder":"<?php echo esc_attr__('Enter your email address', 'omega-design'); ?>","buttonText":"<?php echo esc_attr__('Subscribe', 'omega-design'); ?>","successMessage":"<?php echo esc_attr__("Thanks — stay healthy!", 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-newsletter-form omega-newsletter-form-block" data-success-message="<?php echo esc_attr__("Thanks — stay healthy!", 'omega-design'); ?>">
<form class="omega-newsletter-form" novalidate>
<input type="text" name="omega_newsletter_company" class="omega-newsletter-form__honeypot" tabindex="-1" autocomplete="off" aria-hidden="true"/>
<div class="omega-newsletter-form__row">
<input type="email" class="omega-newsletter-form__input" placeholder="<?php echo esc_attr__('Enter your email address', 'omega-design'); ?>" name="omega_newsletter_email" required/>
<button type="submit" class="omega-newsletter-form__submit wp-element-button"><?php esc_html_e('Subscribe', 'omega-design'); ?></button>
</div>
<p class="omega-newsletter-form__message" aria-live="polite"></p>
</form>
</div>
<!-- /wp:omega-design/newsletter-form -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
