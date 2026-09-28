<?php
/**
 * Block Name: Home Features
 * Block Category: Posts
 * Block Icon: products
 * Block Keywords: features, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}
// Fetch group and subfields safely (supports both hf_inner_content group and root-level fields)
$hf_inner_content = get_field('hf_inner_content') ?: [];

$home_products_subtitle    = !empty($hf_inner_content['home_products_subtitle']) ? $hf_inner_content['home_products_subtitle'] : (get_field('home_products_subtitle') ?: '');
$home_products_title       = !empty($hf_inner_content['home_products_title']) ? $hf_inner_content['home_products_title'] : (get_field('home_products_title') ?: '');
$home_products_description = !empty($hf_inner_content['home_products_description']) ? $hf_inner_content['home_products_description'] : (get_field('home_products_description') ?: '');

$hf_cards_repeater = !empty($hf_inner_content['hf_cards_repeater']) && is_array($hf_inner_content['hf_cards_repeater'])
  ? $hf_inner_content['hf_cards_repeater']
  : (get_field('hf_cards_repeater') ?: []);
?>
<section class="mm-section mm-wrap block-home-features home-features" data-testid="home-features">
  <?php if (!empty($home_products_subtitle)): ?>
    <p class="mm-eyebrow mm-teal"><?php echo esc_html($home_products_subtitle); ?></p>
  <?php endif; ?>

  <?php if (!empty($home_products_title)): ?>
    <h2 class="mm-h2"><?php echo esc_html($home_products_title); ?></h2>
  <?php endif; ?>

  <?php if (!empty($home_products_description)): ?>
    <p class="mm-lede mm-left"><?php echo wp_kses_post($home_products_description); ?></p>
  <?php endif; ?>

  <?php if (!empty($hf_cards_repeater) && is_array($hf_cards_repeater)): ?>
    <div class="mm-grid-3">
      <?php foreach ($hf_cards_repeater as $index => $card):
        $card_title   = !empty($card['hf_cards_title']) ? $card['hf_cards_title'] : '';
        $card_content = !empty($card['hf_cards_content']) ? $card['hf_cards_content'] : '';
        $card_slug    = !empty($card_title) ? sanitize_title($card_title) : ($index + 1);
        ?>
        <div class="mm-card mm-feature" data-testid="home-feature-<?php echo esc_attr($card_slug); ?>">
          <?php if (!empty($card_title)): ?>
            <h3 class="mm-h4"><?php echo esc_html($card_title); ?></h3>
          <?php endif; ?>

          <?php if (!empty($card_content)): ?>
            <p class="mm-muted"><?php echo wp_kses_post($card_content); ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>