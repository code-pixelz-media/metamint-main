<?php
/**
 * Block Name: Home Manifesto
 * Block Category: Posts
 * Block Icon: products
 * Block Keywords: Manifesto, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}
// Extract block data safely (handles block editor canvas previews and frontend)
$block_data = $block['data'] ?? [];

$mf_inner_content = !empty($block_data['mf_inner_content']) && is_array($block_data['mf_inner_content'])
  ? $block_data['mf_inner_content']
  : (get_field('mf_inner_content', $block['id'] ?? null) ?: (get_field('mf_inner_content') ?: []));

$mf_subtitle = !empty($block_data['mf_subtitle'])
  ? $block_data['mf_subtitle']
  : (!empty($mf_inner_content['mf_subtitle']) ? $mf_inner_content['mf_subtitle'] : (get_field('mf_subtitle', $block['id'] ?? null) ?: (get_field('mf_subtitle') ?: '')));

$mf_title = !empty($block_data['mf_title'])
  ? $block_data['mf_title']
  : (!empty($mf_inner_content['mf_title']) ? $mf_inner_content['mf_title'] : (get_field('mf_title', $block['id'] ?? null) ?: (get_field('mf_title') ?: '')));

$mf_description = !empty($block_data['mf_description'])
  ? $block_data['mf_description']
  : (!empty($mf_inner_content['mf_description']) ? $mf_inner_content['mf_description'] : (get_field('mf_description', $block['id'] ?? null) ?: (get_field('mf_description') ?: '')));

$mf_cards_repeater = !empty($mf_inner_content['mf_cards_repeater']) && is_array($mf_inner_content['mf_cards_repeater'])
  ? $mf_inner_content['mf_cards_repeater']
  : (get_field('mf_cards_repeater', $block['id'] ?? null) ?: (get_field('mf_cards_repeater') ?: []));

// Fetch Style Tab (mf_color_theme - checks block data first for live editor toggle, then get_field)
$mf_color_theme = '';
if (!empty($block_data['mf_color_theme'])) {
  $mf_color_theme = $block_data['mf_color_theme'];
} elseif (!empty($block_data['mf_style']['mf_color_theme'])) {
  $mf_color_theme = $block_data['mf_style']['mf_color_theme'];
} elseif (!empty($mf_inner_content['mf_color_theme'])) {
  $mf_color_theme = $mf_inner_content['mf_color_theme'];
} else {
  $mf_color_theme = get_field('mf_color_theme', $block['id'] ?? null) ?: (get_field('mf_color_theme') ?: '');
  if (empty($mf_color_theme)) {
    $mf_style = get_field('mf_style', $block['id'] ?? null) ?: (get_field('mf_style') ?: []);
    $mf_color_theme = !empty($mf_style['mf_color_theme']) ? $mf_style['mf_color_theme'] : 'Green';
  }
}

if (is_array($mf_color_theme)) {
  $mf_color_theme = $mf_color_theme['value'] ?? ($mf_color_theme['label'] ?? 'Green');
}

$theme_slug = strtolower(trim((string) $mf_color_theme));
if ($theme_slug === 'red' || $theme_slug === 'coral' || $theme_slug === '#f05d5e') {
  $theme_class = 'theme-red';
} else {
  $theme_class = 'theme-green';
}

// Fetch Style layout variant (mf_theme_style - choices: Style 1, Style 2)
$mf_theme_style = '';
if (!empty($block_data['mf_theme_style'])) {
  $mf_theme_style = $block_data['mf_theme_style'];
} elseif (!empty($block_data['mf_style_theme'])) {
  $mf_theme_style = $block_data['mf_style_theme'];
} elseif (!empty($block_data['mf_style']['mf_theme_style'])) {
  $mf_theme_style = $block_data['mf_style']['mf_theme_style'];
} else {
  $mf_theme_style = get_field('mf_theme_style', $block['id'] ?? null) ?: (get_field('mf_theme_style') ?: 'Style 1');
}

if (is_array($mf_theme_style)) {
  $mf_theme_style = $mf_theme_style['value'] ?? ($mf_theme_style['label'] ?? 'Style 1');
}

$style_slug  = strtolower(str_replace(' ', '-', trim((string) $mf_theme_style)));
$style_class = ($style_slug === 'style-2') ? 'style-2' : 'style-1';

// Fetch Background Color (mf_background_color)
$mf_background_color = '';
if (!empty($block_data['mf_background_color'])) {
  $mf_background_color = $block_data['mf_background_color'];
} elseif (!empty($block_data['mf_style']['mf_background_color'])) {
  $mf_background_color = $block_data['mf_style']['mf_background_color'];
} else {
  $mf_background_color = get_field('mf_background_color', $block['id'] ?? null) ?: (get_field('mf_background_color') ?: '');
  if (empty($mf_background_color)) {
    $mf_style = get_field('mf_style', $block['id'] ?? null) ?: (get_field('mf_style') ?: []);
    $mf_background_color = !empty($mf_style['mf_background_color']) ? $mf_style['mf_background_color'] : '';
  }
}

$section_style = '';
if (!empty($mf_background_color)) {
  $section_style = ' style="background-color: ' . esc_attr(trim((string) $mf_background_color)) . ';"';
}
?>
<section class="mm-section mm-wash home-manifesto <?= esc_attr($theme_class); ?> <?= esc_attr($style_class); ?>"<?= $section_style; ?> data-testid="home-manifesto">
  <div class="mm-wrap">
    <?php if (!empty($mf_subtitle)): ?>
      <p class="mm-eyebrow"><?php echo esc_html($mf_subtitle); ?></p>
    <?php endif; ?>

    <?php if (!empty($mf_title)): ?>
      <h2 class="mm-h2"><?php echo esc_html($mf_title); ?></h2>
    <?php endif; ?>

    <?php if (!empty($mf_description)): ?>
      <p class="mm-lede mm-left"><?php echo wp_kses_post($mf_description); ?></p>
    <?php endif; ?>

    <?php if (!empty($mf_cards_repeater) && is_array($mf_cards_repeater)):
      $grid_class = ($style_class === 'style-2') ? 'mm-grid-3' : 'mm-grid-2';
      ?>
      <div class="<?= esc_attr($grid_class); ?>">
        <?php foreach ($mf_cards_repeater as $index => $card):
          $card_tagline = !empty($card['mf_tagline']) ? $card['mf_tagline'] : '';
          $card_title = !empty($card['mf_title']) ? $card['mf_title'] : '';
          $card_content = !empty($card['mf_cards_content']) ? $card['mf_cards_content'] : '';
          $card_slug = !empty($card_title) ? sanitize_title($card_title) : str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
          ?>
          <article class="mm-card mm-chapter" data-testid="manifesto-chapter-<?php echo esc_attr($card_slug); ?>">
            <?php if (!empty($card_tagline)): ?>
              <span class="mm-chapter-n"><?php echo esc_html($card_tagline); ?></span>
            <?php endif; ?>

            <?php if (!empty($card_title)): ?>
              <h3 class="mm-h4"><?php echo esc_html($card_title); ?></h3>
            <?php endif; ?>

            <?php if (!empty($card_content)): ?>
              <p class="mm-muted"><?php echo wp_kses_post($card_content); ?></p>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>