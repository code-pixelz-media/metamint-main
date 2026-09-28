<?php
/**
 * Block Name: Metamint Hero Default
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

$hero_capsule_text = get_field('hero_capsule_text') ?: '';
$hero_main_title_1 = get_field('hero_main_title_1') ?: '';
$hero_main_title_2 = get_field('hero_main_title_2') ?: '';
$hero_main_title_color_text = get_field('hero_main_title_color_text') ?: '';
$hero_main_subtitle = get_field('hero_main_subtitle') ?: '';
$hero_description = get_field('hero_description') ?: '';
$hero_button_1_label = get_field('hero_button_1_label') ?: '';
$hero_text_button_1_link = get_field('hero_text_button_1_link');
$hero_button_2_label = get_field('hero_button_2_label') ?: '';
$hero_text_button_2_link = get_field('hero_text_button_2_link');

//Styles
$hero_btn_alignment = get_field('hero_btn_alignment') ?: 'Left';
$alignment_class = 'align-' . strtolower($hero_btn_alignment);
$hero_accent_color = get_field('hero_accent_color') ?: 'Teal';
$color_class = 'color-' . strtolower($hero_accent_color);

// Normalize Button 1 (handles ACF Link Array or plain URL string)
$hero_btn1_url = '';
$hero_btn1_target = '';
if (is_array($hero_text_button_1_link)) {
    $hero_btn1_url = $hero_text_button_1_link['url'] ?? '';
    if (!empty($hero_text_button_1_link['target'])) {
        $hero_btn1_target = ' target="' . esc_attr($hero_text_button_1_link['target']) . '" rel="noopener noreferrer"';
    }
    if (empty($hero_button_1_label) && !empty($hero_text_button_1_link['title'])) {
        $hero_button_1_label = $hero_text_button_1_link['title'];
    }
} elseif (is_string($hero_text_button_1_link)) {
    $hero_btn1_url = $hero_text_button_1_link;
}

// Normalize Button 2 (handles ACF Link Array or plain URL string)
$hero_btn2_url = '';
$hero_btn2_target = '';
if (is_array($hero_text_button_2_link)) {
    $hero_btn2_url = $hero_text_button_2_link['url'] ?? '';
    if (!empty($hero_text_button_2_link['target'])) {
        $hero_btn2_target = ' target="' . esc_attr($hero_text_button_2_link['target']) . '" rel="noopener noreferrer"';
    }
    if (empty($hero_button_2_label) && !empty($hero_text_button_2_link['title'])) {
        $hero_button_2_label = $hero_text_button_2_link['title'];
    }
} elseif (is_string($hero_text_button_2_link)) {
    $hero_btn2_url = $hero_text_button_2_link;
}

// Safety check for button labels if defined as ACF Link array
if (is_array($hero_button_1_label)) {
    $hero_button_1_label = $hero_button_1_label['title'] ?? '';
}
if (is_array($hero_button_2_label)) {
    $hero_button_2_label = $hero_button_2_label['title'] ?? '';
}

// Define allowed HTML tags: <br> and <i> (for safe output using wp_kses)
$allowed_html_tags = [
    'br' => [], // Allows <br>, <br/>, or <br /> tags
    'i' => []  // Allows <i> tags
];
?>
<section id="home-hero"
    class="custom-block block-hero-banner mm-hero mm-dots <?= esc_attr($color_class) ?> <?= esc_attr($alignment_class) ?>">
    <div class="mm-orb mm-orb-teal" aria-hidden="true"></div>
    <div class="mm-orb mm-orb-coral" aria-hidden="true"></div>
    <div class="mm-wrap mm-hero-inner">
        <?php if (!empty($hero_capsule_text)): ?>
            <span class="mm-pill mm-pill-teal" data-testid="hero-badge-studio">
                <?php echo wp_kses($hero_capsule_text, $allowed_html_tags); ?>
            </span>
        <?php endif; ?>

        <?php if (!empty($hero_main_title_1) || !empty($hero_main_title_2)): ?>
            <h1 class="mm-display">
                <?php if (!empty($hero_main_title_1)): ?>
                    <?php echo wp_kses($hero_main_title_1, $allowed_html_tags); ?>
                <?php endif; ?>
                <?php if (!empty($hero_main_title_1) && !empty($hero_main_title_2)): ?><br><?php endif; ?>
                <?php if (!empty($hero_main_title_2)): ?>
                    <?php echo wp_kses($hero_main_title_2, $allowed_html_tags); ?>
                <?php endif; ?>
            </h1>
        <?php endif; ?>

        <?php if (!empty($hero_main_subtitle)): ?>
            <p class="mm-lede">
                <?php echo wp_kses($hero_main_subtitle, $allowed_html_tags); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($hero_button_1_label) || !empty($hero_button_2_label)): ?>
            <div class="mm-btnrow <?= esc_attr($alignment_class) ?>">
                <?php if (!empty($hero_button_1_label)): ?>
                    <a class="mm-btn mm-btn-ink" href="<?php echo esc_url($hero_btn1_url); ?>" <?= $hero_btn1_target ?>
                        data-testid="hero-explore-products-button">
                        <?php echo wp_kses($hero_button_1_label, $allowed_html_tags); ?>
                    </a>
                <?php endif; ?>
                <?php if (!empty($hero_button_2_label)): ?>
                    <a class="mm-btn mm-btn-outline" href="<?php echo esc_url($hero_btn2_url); ?>" <?= $hero_btn2_target ?>
                        data-testid="hero-our-story-link">
                        <?php echo wp_kses($hero_button_2_label, $allowed_html_tags); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>