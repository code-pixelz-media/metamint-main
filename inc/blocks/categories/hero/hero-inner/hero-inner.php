<?php
/**
 * Block Name: Hero Inner
 * Block Category: Hero
 * Block Icon: cover-image
 * Block Keywords: hero, banner, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
    echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
    return;
}

// Support root-level fields, tabs, or grouped fields (hero_inner_content / inner_content)
$hero_group = get_field('hero_inner_content') ?: (get_field('inner_content') ?: []);

$hero_inner_capsule_text = !empty($hero_group['hero_inner_capsule_text'])
    ? $hero_group['hero_inner_capsule_text']
    : (get_field('hero_inner_capsule_text') ?: (get_field('hero_capsule_text') ?: ''));

$hero_inner_main = !empty($hero_group['hero_inner_main'])
    ? $hero_group['hero_inner_main']
    : (get_field('hero_inner_main') ?: (get_field('hero_main_title_1') ?: ''));

$hero_inner_title_main_color_text = !empty($hero_group['hero_inner_title_main_color_text'])
    ? $hero_group['hero_inner_title_main_color_text']
    : (get_field('hero_inner_title_main_color_text') ?: (get_field('hero_main_title_color_text') ?: ''));

$hero_description = !empty($hero_group['hero_description'])
    ? $hero_group['hero_description']
    : (get_field('hero_description') ?: (get_field('hero_inner_description') ?: (get_field('hero_main_subtitle') ?: '')));

$hero_inner_button_1_label = !empty($hero_group['hero_inner_button_1_label'])
    ? $hero_group['hero_inner_button_1_label']
    : (get_field('hero_inner_button_1_label') ?: (get_field('hero_button_1_label') ?: ''));

$hero_inner_text_button_1_link = !empty($hero_group['hero_inner_text_button_1_link'])
    ? $hero_group['hero_inner_text_button_1_link']
    : (get_field('hero_inner_text_button_1_link') ?: get_field('hero_text_button_1_link'));

$hero_inner_button_2_label = !empty($hero_group['hero_inner_button_2_label'])
    ? $hero_group['hero_inner_button_2_label']
    : (get_field('hero_inner_button_2_label') ?: (get_field('hero_button_2_label') ?: ''));

$hero_inner_button_2_price = !empty($hero_group['hero_inner_button_2_price'])
    ? $hero_group['hero_inner_button_2_price']
    : (get_field('hero_inner_button_2_price') ?: (get_field('hero_inner_button_2_price') ?: ''));

$hero_inner_text_button_2_link = !empty($hero_group['hero_inner_text_button_2_link'])
    ? $hero_group['hero_inner_text_button_2_link']
    : (get_field('hero_inner_text_button_2_link') ?: get_field('hero_text_button_2_link'));

// Styles
$hero_inner_btn_alignment = !empty($hero_group['hero_inner_btn_alignment'])
    ? $hero_group['hero_inner_btn_alignment']
    : (get_field('hero_inner_btn_alignment') ?: (get_field('hero_btn_alignment') ?: 'Left'));
$alignment_class = 'align-' . strtolower($hero_inner_btn_alignment);

$hero_inner_accent_color = !empty($hero_group['hero_inner_accent_color'])
    ? $hero_group['hero_inner_accent_color']
    : (get_field('hero_inner_accent_color') ?: (get_field('hero_accent_color') ?: 'Teal'));

$raw_accent = trim((string) $hero_inner_accent_color);
$lower_accent = strtolower($raw_accent);
$custom_hero_style = '';

if ($lower_accent === 'red' || $lower_accent === 'coral' || $lower_accent === '#f05d5e') {
    $color_class = 'color-red color-coral';
} elseif ($lower_accent === 'green' || $lower_accent === 'teal' || $lower_accent === '#0f7173') {
    $color_class = 'color-green color-teal';
} elseif (str_starts_with($raw_accent, '#')) {
    $color_class = 'color-custom';
    $custom_hero_style = ' style="--mm-hero-accent:' . esc_attr($raw_accent) . ';"';
} else {
    $color_class = 'color-' . sanitize_html_class($lower_accent);
}

// Normalize Button 1 (handles ACF Link Array or plain URL string)
$hero_inner_btn1_url = '';
$hero_inner_btn1_target = '';
if (is_array($hero_inner_text_button_1_link)) {
    $hero_inner_btn1_url = $hero_inner_text_button_1_link['url'] ?? '';
    if (!empty($hero_inner_text_button_1_link['target'])) {
        $hero_inner_btn1_target = ' target="' . esc_attr($hero_inner_text_button_1_link['target']) . '" rel="noopener noreferrer"';
    }
    if (empty($hero_inner_button_1_label) && !empty($hero_inner_text_button_1_link['title'])) {
        $hero_inner_button_1_label = $hero_inner_text_button_1_link['title'];
    }
} elseif (is_string($hero_inner_text_button_1_link)) {
    $hero_inner_btn1_url = $hero_inner_text_button_1_link;
}

// Normalize Button 2 (handles ACF Link Array or plain URL string)
$hero_inner_btn2_url = '';
$hero_inner_btn2_target = '';
if (is_array($hero_inner_text_button_2_link)) {
    $hero_inner_btn2_url = $hero_inner_text_button_2_link['url'] ?? '';
    if (!empty($hero_inner_text_button_2_link['target'])) {
        $hero_inner_btn2_target = ' target="' . esc_attr($hero_inner_text_button_2_link['target']) . '" rel="noopener noreferrer"';
    }
    if (empty($hero_inner_button_2_label) && !empty($hero_inner_text_button_2_link['title'])) {
        $hero_inner_button_2_label = $hero_inner_text_button_2_link['title'];
    }
} elseif (is_string($hero_inner_text_button_2_link)) {
    $hero_inner_btn2_url = $hero_inner_text_button_2_link;
}

// Safety check for button labels if defined as ACF Link array
if (is_array($hero_inner_button_1_label)) {
    $hero_inner_button_1_label = $hero_inner_button_1_label['title'] ?? '';
}
if (is_array($hero_inner_button_2_label)) {
    $hero_inner_button_2_label = $hero_inner_button_2_label['title'] ?? '';
}

// Define allowed HTML tags: <br> and <i> (for safe output using wp_kses)
$allowed_html_tags = [
    'br' => [], // Allows <br>, <br/>, or <br /> tags
    'i' => []  // Allows <i> tags
];
?>
<section id="home-hero"
    class="custom-block block-hero-banner hero-inner mm-hero mm-dots <?= esc_attr($color_class) ?> <?= esc_attr($alignment_class) ?>"
    <?= $custom_hero_style ?>>
    <div class="mm-orb mm-orb-teal" aria-hidden="true"></div>
    <div class="mm-orb mm-orb-coral" aria-hidden="true"></div>
    <div class="mm-wrap mm-hero-inner">
        <?php if (!empty($hero_inner_capsule_text)): ?>
            <span class="mm-pill" data-testid="hero-badge-studio">
                <?php echo wp_kses($hero_inner_capsule_text, $allowed_html_tags); ?>
            </span>
        <?php endif; ?>

        <?php if (!empty($hero_inner_main) || !empty($hero_inner_title_main_color_text)): ?>
            <h1 class="mm-display">
                <?php if (!empty($hero_inner_main)): ?>
                    <?php echo wp_kses($hero_inner_main, $allowed_html_tags); ?>
                <?php endif; ?>
                <?php if (!empty($hero_inner_title_main_color_text)): ?>
                    <em><?php echo wp_kses($hero_inner_title_main_color_text, $allowed_html_tags); ?></em>
                <?php endif; ?>
            </h1>
        <?php endif; ?>

        <?php if (!empty($hero_description)): ?>
            <p class="mm-lede">
                <?php echo wp_kses($hero_description, $allowed_html_tags); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($hero_inner_button_1_label) || !empty($hero_inner_button_2_label)): ?>
            <div class="mm-btnrow <?= esc_attr($alignment_class) ?>">
                <?php if (!empty($hero_inner_button_1_label)): ?>
                    <a class="mm-btn mm-btn-ink" href="<?php echo esc_url($hero_inner_btn1_url ?: '#'); ?>"
                        <?= $hero_inner_btn1_target ?> data-testid="hero-explore-products-button">
                        <?php echo wp_kses($hero_inner_button_1_label, $allowed_html_tags); ?>
                    </a>
                <?php endif; ?>
                <?php if (!empty($hero_inner_button_2_label)): ?>
                    <a class="mm-btn mm-btn-outline" href="<?php echo esc_url($hero_inner_btn2_url ?: '#'); ?>"
                        <?= $hero_inner_btn2_target ?> data-testid="hero-our-story-link">
                        <?php echo wp_kses($hero_inner_button_2_label, $allowed_html_tags); ?>        <?php if (!empty($hero_inner_button_2_price)): ?><span
                                class="mmhd-discounted-price"><?php echo esc_html($hero_inner_button_2_price); ?></span><?php endif; ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>