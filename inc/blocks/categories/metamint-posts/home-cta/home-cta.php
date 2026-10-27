<?php
/**
 * Block Name: Home CTA
 * Block Category: Posts
 * Block Icon: products
 * Block Keywords: CTA, card, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}
// Extract block data safely (handles block editor canvas previews and frontend)
$block_data = $block['data'] ?? [];

// Fetch fields safely (supports root-level fields and optional cta_inner_content / inner_content group)
$cta_group = get_field('cta_inner_content', $block['id'] ?? null)
  ?: (get_field('inner_content', $block['id'] ?? null)
    ?: (get_field('cta_inner_content') ?: (get_field('inner_content') ?: [])));

$cta_main_title_1 = !empty($block_data['cta_main_title_1'])
  ? $block_data['cta_main_title_1']
  : (!empty($cta_group['cta_main_title_1'])
    ? $cta_group['cta_main_title_1']
    : (get_field('cta_main_title_1', $block['id'] ?? null) ?: (get_field('cta_main_title_1') ?: '')));

$cta_title_main_color_text = !empty($block_data['cta_title_main_color_text'])
  ? $block_data['cta_title_main_color_text']
  : (!empty($cta_group['cta_title_main_color_text'])
    ? $cta_group['cta_title_main_color_text']
    : (get_field('cta_title_main_color_text', $block['id'] ?? null) ?: (get_field('cta_title_main_color_text') ?: '')));

$cta_description = !empty($block_data['cta_description'])
  ? $block_data['cta_description']
  : (!empty($cta_group['cta_description'])
    ? $cta_group['cta_description']
    : (get_field('cta_description', $block['id'] ?? null) ?: (get_field('cta_description') ?: '')));

$cta_button_1_label = !empty($block_data['cta_button_1_label'])
  ? $block_data['cta_button_1_label']
  : (!empty($cta_group['cta_button_1_label'])
    ? $cta_group['cta_button_1_label']
    : (get_field('cta_button_1_label', $block['id'] ?? null) ?: (get_field('cta_button_1_label') ?: '')));

$cta_text_button_1_link = !empty($block_data['cta_text_button_1_link'])
  ? $block_data['cta_text_button_1_link']
  : (!empty($cta_group['cta_text_button_1_link'])
    ? $cta_group['cta_text_button_1_link']
    : (get_field('cta_text_button_1_link', $block['id'] ?? null) ?: (get_field('cta_text_button_1_link') ?: '')));

// Fetch Background Color (cta_background_color)
$cta_background_color = '';
if (!empty($block_data['cta_background_color'])) {
  $cta_background_color = $block_data['cta_background_color'];
} elseif (!empty($block_data['cta_style']['cta_background_color'])) {
  $cta_background_color = $block_data['cta_style']['cta_background_color'];
} elseif (!empty($block_data['style']['cta_background_color'])) {
  $cta_background_color = $block_data['style']['cta_background_color'];
} elseif (!empty($block_data['cta_inner_content']['cta_background_color'])) {
  $cta_background_color = $block_data['cta_inner_content']['cta_background_color'];
} elseif (!empty($block_data['inner_content']['cta_background_color'])) {
  $cta_background_color = $block_data['inner_content']['cta_background_color'];
} elseif (!empty($cta_group['cta_background_color'])) {
  $cta_background_color = $cta_group['cta_background_color'];
} else {
  $cta_background_color = get_field('cta_background_color', $block['id'] ?? null)
    ?: (get_field('cta_background_color') ?: '');
  if (empty($cta_background_color)) {
    $cta_style = get_field('cta_style', $block['id'] ?? null)
      ?: (get_field('cta_style')
        ?: (get_field('style', $block['id'] ?? null) ?: (get_field('style') ?: [])));
    if (!empty($cta_style['cta_background_color'])) {
      $cta_background_color = $cta_style['cta_background_color'];
    }
  }
}

if (is_array($cta_background_color)) {
  $cta_background_color = $cta_background_color['value'] ?? ($cta_background_color['label'] ?? '');
}

$cta_bg_clean = trim((string) $cta_background_color);
$section_style = '';
if (!empty($cta_bg_clean)) {
  $section_style = ' style="background-color: ' . esc_attr($cta_bg_clean) . ';"';
}

// Helper to normalize link (supports URL string or ACF Link array)
$parse_link = function ($val) {
  if (is_array($val)) {
    return [
      'url' => !empty($val['url']) ? $val['url'] : '',
      'target' => !empty($val['target']) ? ' target="' . esc_attr($val['target']) . '" rel="noopener noreferrer"' : '',
    ];
  }
  if (is_string($val) && trim($val) !== '') {
    return [
      'url' => trim($val),
      'target' => '',
    ];
  }
  return [
    'url' => '',
    'target' => '',
  ];
};

$button_data = $parse_link($cta_text_button_1_link);
$btn_url = $button_data['url'];
$btn_target = $button_data['target'];
?>
<section class="mm-section" <?= $section_style; ?> data-testid="home-cta">
  <div class="mm-wrap">
    <div class="mm-cta-dark mm-dots">


      <?php if (!empty($cta_main_title_1) || !empty($cta_title_main_color_text)): ?>
        <h2>
          <?php echo esc_html($cta_main_title_1); ?>
          <?php if (!empty($cta_title_main_color_text)): ?>
            <em><?php echo esc_html($cta_title_main_color_text); ?></em>
          <?php endif; ?>
        </h2>
      <?php endif; ?>

      <?php if (!empty($cta_description)): ?>
        <p><?php echo wp_kses_post($cta_description); ?></p>
      <?php endif; ?>

      <?php if (!empty($cta_button_1_label)): ?>
        <a class="mm-btn mm-btn-light" href="<?php echo esc_url($btn_url ?: '#'); ?>" <?php echo $btn_target; ?>
          data-testid="cta-contact-button">
          <?php echo esc_html($cta_button_1_label); ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>