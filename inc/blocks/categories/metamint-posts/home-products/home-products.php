<?php
/**
 * Block Name: Home Products
 * Block Category: Posts
 * Block Icon: products
 * Block Keywords: products, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}
// Fetch group and subfields safely (supports both inner_content group and root-level fields)
$inner_content = get_field('inner_content') ?: [];

$home_products_subtitle = !empty($inner_content['home_products_subtitle']) ? $inner_content['home_products_subtitle'] : (get_field('home_products_subtitle') ?: '');
$home_products_title = !empty($inner_content['home_products_title']) ? $inner_content['home_products_title'] : (get_field('home_products_title') ?: '');
$home_products_description = !empty($inner_content['home_products_description']) ? $inner_content['home_products_description'] : (get_field('home_products_description') ?: '');

$cards_repeater = !empty($inner_content['cards_repeater']) && is_array($inner_content['cards_repeater'])
  ? $inner_content['cards_repeater']
  : (get_field('cards_repeater') ?: []);

// Helper to normalize URL and target
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

$color_palette = ['#0F7173', '#F05D5E', '#41006E', '#2E4057'];
?>
<section id="products" class="mm-section mm-wrap" data-testid="home-products">
  <?php if (!empty($home_products_subtitle)): ?>
    <p class="mm-eyebrow mm-teal"><?php echo esc_html($home_products_subtitle); ?></p>
  <?php endif; ?>

  <?php if (!empty($home_products_title)): ?>
    <h2 class="mm-h2"><?php echo esc_html($home_products_title); ?></h2>
  <?php endif; ?>

  <?php if (!empty($home_products_description)): ?>
    <p class="mm-lede mm-left"><?php echo wp_kses_post($home_products_description); ?></p>
  <?php endif; ?>

  <?php if (!empty($cards_repeater) && is_array($cards_repeater)): ?>
    <div class="mm-grid-2">
      <?php foreach ($cards_repeater as $index => $card):
        $tagline = !empty($card['hpcards_tagline']) ? $card['hpcards_tagline'] : '';
        $title = !empty($card['hpcards_title']) ? $card['hpcards_title'] : '';
        $content = !empty($card['hpcards_content']) ? $card['hpcards_content'] : '';

        $btn_1_data = $parse_link($card['button_1_url'] ?? '');
        $btn_1_text = !empty($card['button_1_text']) ? $card['button_1_text'] : '';
        $btn_1_url = $btn_1_data['url'];

        $btn_2_data = $parse_link($card['button_2_url'] ?? '');
        $btn_2_text = !empty($card['button_2_text']) ? $card['button_2_text'] : '';
        $btn_2_url = $btn_2_data['url'];

        $accent_color = $color_palette[$index % count($color_palette)];
        $card_slug = !empty($title) ? sanitize_title($title) : 'card-' . ($index + 1);
        ?>
        <article class="mm-card mm-product" data-testid="product-card-<?php echo esc_attr($card_slug); ?>">
          <div class="mm-product-body">
            <?php if (!empty($tagline)): ?>
              <span class="mm-pill"
                style="background:<?php echo esc_attr($accent_color); ?>1a;color:<?php echo esc_attr($accent_color); ?>"
                data-testid="product-badge-<?php echo esc_attr($card_slug); ?>">
                <?php echo esc_html($tagline); ?>
              </span>
            <?php endif; ?>

            <?php if (!empty($title)): ?>
              <h3 class="mm-h3">
                <span class="mm-dot" style="background:<?php echo esc_attr($accent_color); ?>"></span>
                <?php echo esc_html($title); ?>
              </h3>
            <?php endif; ?>

            <?php if (!empty($content)): ?>
              <p class="mm-muted"><?php echo wp_kses_post($content); ?></p>
            <?php endif; ?>

            <?php if (!empty($btn_1_text) || !empty($btn_2_text)): ?>
              <div class="mm-btnrow mm-left">
                <?php if (!empty($btn_1_text)): ?>
                  <a class="mm-btn mm-btn-ink" href="<?php echo esc_url($btn_1_url ?: '#'); ?>" <?php echo $btn_1_data['target']; ?> data-testid="buy-<?php echo esc_attr($card_slug); ?>-button">
                    <?php echo esc_html($btn_1_text); ?>
                  </a>
                <?php endif; ?>

                <?php if (!empty($btn_2_text)): ?>
                  <a class="mm-link mm-btn mm-btn-outline" href="<?php echo esc_url($btn_2_url ?: '#'); ?>" <?php echo $btn_2_data['target']; ?> data-testid="explore-<?php echo esc_attr($card_slug); ?>-link">
                    <?php echo esc_html($btn_2_text); ?>
                  </a>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>