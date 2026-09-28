<?php
/**
 * Block Name: Icon Card
 * Block Category: Posts
 * Block Icon: products
 * Block Keywords: Icon, card, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}

// Extract block data safely (handles block editor canvas previews and frontend)
$block_data = $block['data'] ?? [];

$ic_inner_content = !empty($block_data['ic_inner_content']) && is_array($block_data['ic_inner_content'])
  ? $block_data['ic_inner_content']
  : (get_field('ic_inner_content', $block['id'] ?? null) ?: (get_field('ic_inner_content') ?: []));

$ic_subtitle = !empty($block_data['ic_subtitle'])
  ? $block_data['ic_subtitle']
  : (!empty($ic_inner_content['ic_subtitle']) ? $ic_inner_content['ic_subtitle'] : (get_field('ic_subtitle', $block['id'] ?? null) ?: (get_field('ic_subtitle') ?: '')));

$ic_title = !empty($block_data['ic_title'])
  ? $block_data['ic_title']
  : (!empty($ic_inner_content['ic_title']) ? $ic_inner_content['ic_title'] : (get_field('ic_title', $block['id'] ?? null) ?: (get_field('ic_title') ?: '')));

$ic_description = !empty($block_data['ic_description'])
  ? $block_data['ic_description']
  : (!empty($ic_inner_content['ic_description']) ? $ic_inner_content['ic_description'] : (get_field('ic_description', $block['id'] ?? null) ?: (get_field('ic_description') ?: '')));

// Repeater: ic_cards_repeater
$ic_cards_repeater = get_field('ic_cards_repeater', $block['id'] ?? null) ?: (get_field('ic_cards_repeater') ?: []);

if (empty($ic_cards_repeater) && !empty($ic_inner_content['ic_cards_repeater']) && is_array($ic_inner_content['ic_cards_repeater'])) {
  $ic_cards_repeater = $ic_inner_content['ic_cards_repeater'];
}

// Fallback: reconstruct from flat block data if present (e.g. Gutenberg block editor REST preview)
if (empty($ic_cards_repeater) && !empty($block_data['ic_cards_repeater']) && is_numeric($block_data['ic_cards_repeater'])) {
  $count = (int) $block_data['ic_cards_repeater'];
  for ($i = 0; $i < $count; $i++) {
    $ic_cards_repeater[] = [
      'ic_icon'          => $block_data['ic_cards_repeater_' . $i . '_ic_icon'] ?? '',
      'ic_title'         => $block_data['ic_cards_repeater_' . $i . '_ic_title'] ?? '',
      'ic_cards_content' => $block_data['ic_cards_repeater_' . $i . '_ic_cards_content'] ?? '',
    ];
  }
}

// Color Theme: ic_color_theme (Choices: Green, Red)
$ic_color_theme = '';
if (!empty($block_data['ic_color_theme'])) {
  $ic_color_theme = $block_data['ic_color_theme'];
} elseif (!empty($block_data['mf_color_theme'])) {
  $ic_color_theme = $block_data['mf_color_theme'];
} elseif (!empty($block_data['ic_style']['ic_color_theme'])) {
  $ic_color_theme = $block_data['ic_style']['ic_color_theme'];
} elseif (!empty($ic_inner_content['ic_color_theme'])) {
  $ic_color_theme = $ic_inner_content['ic_color_theme'];
} else {
  $ic_color_theme = get_field('ic_color_theme', $block['id'] ?? null) ?: (get_field('mf_color_theme', $block['id'] ?? null) ?: (get_field('ic_color_theme') ?: (get_field('mf_color_theme') ?: 'Green')));
}

if (is_array($ic_color_theme)) {
  $ic_color_theme = $ic_color_theme['value'] ?? ($ic_color_theme['label'] ?? 'Green');
}

$theme_slug = strtolower(trim((string) $ic_color_theme));
if ($theme_slug === 'red' || $theme_slug === 'coral' || $theme_slug === '#f05d5e') {
  $theme_class = 'theme-red';
  $theme_accent = '#f05d5e';
  $theme_accent_d = '#d94142';
} else {
  $theme_class = 'theme-green';
  $theme_accent = '#0f7173';
  $theme_accent_d = '#0b5658';
}

// Layout Style: ic_theme_style (Choices: Style 1, Style 2)
$ic_theme_style = '';
if (!empty($block_data['ic_theme_style'])) {
  $ic_theme_style = $block_data['ic_theme_style'];
} elseif (!empty($block_data['mf_theme_style'])) {
  $ic_theme_style = $block_data['mf_theme_style'];
} elseif (!empty($block_data['ic_style']['ic_theme_style'])) {
  $ic_theme_style = $block_data['ic_style']['ic_theme_style'];
} elseif (!empty($ic_inner_content['ic_theme_style'])) {
  $ic_theme_style = $ic_inner_content['ic_theme_style'];
} else {
  $ic_theme_style = get_field('ic_theme_style', $block['id'] ?? null) ?: (get_field('mf_theme_style', $block['id'] ?? null) ?: (get_field('ic_theme_style') ?: (get_field('mf_theme_style') ?: 'Style 1')));
}

if (is_array($ic_theme_style)) {
  $ic_theme_style = $ic_theme_style['value'] ?? ($ic_theme_style['label'] ?? 'Style 1');
}

$style_slug = strtolower(str_replace(' ', '-', trim((string) $ic_theme_style)));
if ($style_slug === 'style-3') {
  $style_class = 'style-3';
} elseif ($style_slug === 'style-2') {
  $style_class = 'style-2';
} else {
  $style_class = 'style-1';
}

// Background Color: ic_background_color
$ic_background_color = '';
if (!empty($block_data['ic_background_color'])) {
  $ic_background_color = $block_data['ic_background_color'];
} elseif (!empty($block_data['mf_background_color'])) {
  $ic_background_color = $block_data['mf_background_color'];
} elseif (!empty($block_data['ic_style']['ic_background_color'])) {
  $ic_background_color = $block_data['ic_style']['ic_background_color'];
} elseif (!empty($ic_inner_content['ic_background_color'])) {
  $ic_background_color = $ic_inner_content['ic_background_color'];
} else {
  $ic_background_color = get_field('ic_background_color', $block['id'] ?? null) ?: (get_field('mf_background_color', $block['id'] ?? null) ?: (get_field('ic_background_color') ?: (get_field('mf_background_color') ?: '')));
}

$section_inline_styles = [];
if (!empty($ic_background_color)) {
  $section_inline_styles[] = 'background-color: ' . esc_attr(trim((string) $ic_background_color));
}
$section_inline_styles[] = '--mm-accent: ' . esc_attr($theme_accent);
$section_inline_styles[] = '--mm-accent-d: ' . esc_attr($theme_accent_d);

$section_style_attr = !empty($section_inline_styles) ? ' style="' . implode('; ', $section_inline_styles) . ';"' : '';
?>
<section class="mm-section mm-wash icon-card-section <?= esc_attr($theme_class); ?> <?= esc_attr($style_class); ?>"<?= $section_style_attr; ?> data-testid="icon-card">
  <div class="mm-wrap">
    <?php if (!empty($ic_subtitle)): ?>
      <p class="mm-eyebrow"><?php echo esc_html($ic_subtitle); ?></p>
    <?php endif; ?>

    <?php if (!empty($ic_title)): ?>
      <h2 class="mm-h2"><?php echo esc_html($ic_title); ?></h2>
    <?php endif; ?>

    <?php if (!empty($ic_description)): ?>
      <p class="mm-lede mm-left"><?php echo wp_kses_post($ic_description); ?></p>
    <?php endif; ?>

    <?php if (!empty($ic_cards_repeater) && is_array($ic_cards_repeater)): ?>
      <div class="mm-grid-3 mm-icon-cards-grid">
        <?php foreach ($ic_cards_repeater as $index => $card):
          $card_title   = !empty($card['ic_title']) ? $card['ic_title'] : '';
          $card_content = !empty($card['ic_cards_content']) ? $card['ic_cards_content'] : '';
          $card_icon    = !empty($card['ic_icon']) ? $card['ic_icon'] : '';

          $icon_url = '';
          $icon_alt = $card_title ?: ('Icon ' . ($index + 1));

          if (is_array($card_icon)) {
            $icon_url = $card_icon['url'] ?? '';
            if (!empty($card_icon['alt'])) {
              $icon_alt = $card_icon['alt'];
            }
          } elseif (is_numeric($card_icon)) {
            $icon_url = wp_get_attachment_image_url($card_icon, 'full');
            $alt_meta = get_post_meta($card_icon, '_wp_attachment_image_alt', true);
            if (!empty($alt_meta)) {
              $icon_alt = $alt_meta;
            }
          } elseif (is_string($card_icon)) {
            $icon_url = $card_icon;
          }

          $card_slug = !empty($card_title) ? sanitize_title($card_title) : ($index + 1);
          ?>
          <article class="mm-card mm-icon-card-item" data-testid="icon-card-item-<?php echo esc_attr($card_slug); ?>">
            <?php if ($style_class === 'style-3'): ?>
              <div class="mm-icon-card-header">
                <?php if (!empty($icon_url)): ?>
                  <div class="mm-icon-card-icon-box">
                    <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($icon_alt); ?>" loading="lazy" class="mm-icon-card-img" />
                  </div>
                <?php endif; ?>

                <?php if (!empty($card_title)): ?>
                  <h3 class="mm-h4 mm-icon-card-title"><?php echo esc_html($card_title); ?></h3>
                <?php endif; ?>
              </div>

              <?php if (!empty($card_content)): ?>
                <div class="mm-muted mm-icon-card-desc"><?php echo wp_kses_post($card_content); ?></div>
              <?php endif; ?>
            <?php else: ?>
              <?php if (!empty($icon_url)): ?>
                <div class="mm-icon-card-icon-box">
                  <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($icon_alt); ?>" loading="lazy" class="mm-icon-card-img" />
                </div>
              <?php endif; ?>

              <div class="mm-icon-card-body">
                <?php if (!empty($card_title)): ?>
                  <h3 class="mm-h4 mm-icon-card-title"><?php echo esc_html($card_title); ?></h3>
                <?php endif; ?>

                <?php if (!empty($card_content)): ?>
                  <div class="mm-muted mm-icon-card-desc"><?php echo wp_kses_post($card_content); ?></div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>