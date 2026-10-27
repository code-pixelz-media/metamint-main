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

// Padding options
$hero_padding_top = get_field('hero_padding_top', $block['id'] ?? null);
if ($hero_padding_top === null || $hero_padding_top === '') {
    $hero_padding_top = get_field('hero_padding_top') ?: ($block['data']['hero_padding_top'] ?? '');
}

$hero_padding_bottom = get_field('hero_padding_bottom', $block['id'] ?? null);
if ($hero_padding_bottom === null || $hero_padding_bottom === '') {
    $hero_padding_bottom = get_field('hero_padding_bottom') ?: ($block['data']['hero_padding_bottom'] ?? '');
}

// Format padding helper: appends 'px' if purely numeric, preserves unit if string, empty if unset
$format_padding = function ($val) {
    if ($val === null || $val === '') {
        return '';
    }
    $val = trim((string) $val);
    if ($val === '') {
        return '';
    }
    return is_numeric($val) ? $val . 'px' : $val;
};

//Styles
$hero_btn_alignment = get_field('hero_btn_alignment') ?: 'Left';
$alignment_class = 'align-' . strtolower($hero_btn_alignment);
$hero_accent_color = get_field('hero_accent_color') ?: 'Teal';
$raw_accent = trim((string) $hero_accent_color);
$lower_accent = strtolower($raw_accent);

if ($lower_accent === 'red' || $lower_accent === 'coral' || $lower_accent === '#f05d5e') {
    $color_class = 'color-red color-coral';
} elseif ($lower_accent === 'green' || $lower_accent === 'teal' || $lower_accent === '#0f7173') {
    $color_class = 'color-green color-teal';
} elseif (str_starts_with($raw_accent, '#')) {
    $color_class = 'color-custom';
} else {
    $color_class = 'color-' . sanitize_html_class($lower_accent);
}

// Build inline styles (custom accent color and custom padding overrides)
$hero_inline_styles = [];

if (str_starts_with($raw_accent, '#')) {
    $hero_inline_styles[] = '--mm-hero-accent:' . esc_attr($raw_accent);
}

$pad_top = $format_padding($hero_padding_top);
if ($pad_top !== '') {
    $hero_inline_styles[] = 'padding-top:' . esc_attr($pad_top);
}

$pad_bottom = $format_padding($hero_padding_bottom);
if ($pad_bottom !== '') {
    $hero_inline_styles[] = 'padding-bottom:' . esc_attr($pad_bottom);
}

$custom_hero_style = !empty($hero_inline_styles) ? ' style="' . implode('; ', $hero_inline_styles) . ';"' : '';

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
    class="custom-block block-hero-banner mm-hero mm-dots <?= esc_attr($color_class) ?> <?= esc_attr($alignment_class) ?>"
    <?= $custom_hero_style ?>>
    <div class="mm-orb-wrap mm-orb-wrap-teal" aria-hidden="true"><div class="mm-orb mm-orb-teal"></div></div>
    <div class="mm-orb-wrap mm-orb-wrap-coral" aria-hidden="true"><div class="mm-orb mm-orb-coral"></div></div>
    <div class="mm-wrap mm-hero-inner">
        <?php if (!empty($hero_capsule_text)): ?>
            <span class="mm-pill" data-testid="hero-badge-studio">
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


        <?php if (!empty($hero_description)): ?>
            <p class="mm-description">
                <?php echo wp_kses($hero_description, $allowed_html_tags); ?>
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