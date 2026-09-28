<?php
/**
 * Block Name: Image list
 * Block Category: Posts
 * Block Icon: images-alt2
 * Block Keywords: Image list, logos, partners, clients, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}

$block_data = $block['data'] ?? [];

$imagelist_title = !empty($block_data['imagelist_title'])
  ? $block_data['imagelist_title']
  : (get_field('imagelist_title', $block['id'] ?? null) ?: (get_field('imagelist_title') ?: ''));

$imagelist_subtitle = !empty($block_data['imagelist_subtitle'])
  ? $block_data['imagelist_subtitle']
  : (get_field('imagelist_subtitle', $block['id'] ?? null) ?: (get_field('imagelist_subtitle') ?: ''));

$imagelist_description = !empty($block_data['imagelist_description'])
  ? $block_data['imagelist_description']
  : (get_field('imagelist_description', $block['id'] ?? null) ?: (get_field('imagelist_description') ?: ''));

// Background Color: il_background_color
$il_background_color = !empty($block_data['il_background_color'])
  ? $block_data['il_background_color']
  : (get_field('il_background_color', $block['id'] ?? null) ?: (get_field('il_background_color') ?: ''));

// Repeater: images_lists
$images_lists = get_field('images_lists', $block['id'] ?? null) ?: (get_field('images_lists') ?: []);

if (empty($images_lists) && !empty($block_data['images_lists'])) {
  if (is_array($block_data['images_lists'])) {
    $images_lists = $block_data['images_lists'];
  } elseif (is_numeric($block_data['images_lists'])) {
    $count = (int) $block_data['images_lists'];
    for ($i = 0; $i < $count; $i++) {
      $images_lists[] = [
        'image_item' => $block_data['images_lists_' . $i . '_image_item'] ?? '',
      ];
    }
  }
}

$block_id = 'mm-image-list-' . ($block['id'] ?? uniqid());
?>
<section id="<?php echo esc_attr($block_id); ?>" class="mm-section mm-image-list block-image-list"
  data-testid="image-list" <?php if (!empty($il_background_color))
    echo ' style="background-color:' . esc_attr($il_background_color) . ';"'; ?>>
  <div class="mm-wrap">
    <div class="mm-image-list-content-wrap">
      <?php if (!empty($imagelist_subtitle)): ?>
        <p class="mm-eyebrow mm-image-list-subtitle"><?php echo esc_html($imagelist_subtitle); ?></p>
      <?php endif; ?>

      <?php if (!empty($imagelist_title)): ?>
        <p class="mm-image-list-title text-uppercase"><?php echo esc_html($imagelist_title); ?></p>
      <?php endif; ?>

      <?php if (!empty($imagelist_description)): ?>
        <p class="mm-lede mm-image-list-desc"><?php echo wp_kses_post($imagelist_description); ?></p>
      <?php endif; ?>

      <?php
      $valid_images = [];
      if (!empty($images_lists) && is_array($images_lists)) {
        foreach ($images_lists as $index => $item) {
          $image_data = $item['image_item'] ?? '';
          if (empty($image_data))
            continue;

          $img_url = '';
          $img_alt = 'Logo ' . ($index + 1);

          if (is_array($image_data)) {
            $img_url = !empty($image_data['url']) ? $image_data['url'] : '';
            if (empty($img_url) && !empty($image_data['ID'])) {
              $img_url = wp_get_attachment_image_url($image_data['ID'], 'full');
            } elseif (empty($img_url) && !empty($image_data['id'])) {
              $img_url = wp_get_attachment_image_url($image_data['id'], 'full');
            }
            if (!empty($image_data['alt'])) {
              $img_alt = $image_data['alt'];
            } elseif (!empty($image_data['title'])) {
              $img_alt = $image_data['title'];
            }
          } elseif (is_numeric($image_data)) {
            $img_url = wp_get_attachment_image_url($image_data, 'full');
            $alt_meta = get_post_meta($image_data, '_wp_attachment_image_alt', true);
            if (!empty($alt_meta)) {
              $img_alt = $alt_meta;
            }
          } elseif (is_string($image_data)) {
            $img_url = $image_data;
          }

          if (!empty($img_url)) {
            $valid_images[] = [
              'url' => $img_url,
              'alt' => $img_alt,
            ];
          }
        }
      }
      $grid_count = count($valid_images);
      ?>

      <?php if (!empty($valid_images)): ?>
        <div class="mm-image-list-row" style="--col-count: <?php echo esc_attr($grid_count); ?>;">
          <?php foreach ($valid_images as $img): ?>
            <figure class="mm-image-list-item grid-item">
              <img decoding="async" src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>"
                loading="lazy" class="mm-image-list-img" />
            </figure>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>