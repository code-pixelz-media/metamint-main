<?php
/**
 * Block Name: Text image
 * Block Category: Posts
 * Block Icon: images-alt2
 * Block Keywords: Text image, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}

$block_data = $block['data'] ?? [];

// Group: textimage_inner_content — read the whole group first as fallback
$textimage_inner_content = !empty($block_data['textimage_inner_content']) && is_array($block_data['textimage_inner_content'])
  ? $block_data['textimage_inner_content']
  : (get_field('textimage_inner_content', $block['id'] ?? null) ?: (get_field('textimage_inner_content') ?: []));

// Helper: resolve a field from block_data first, then group, then get_field
$resolve = function ($key) use ($block_data, $textimage_inner_content) {
  if (!empty($block_data[$key])) return $block_data[$key];
  if (!empty($textimage_inner_content[$key])) return $textimage_inner_content[$key];
  return get_field($key, $block['id'] ?? null) ?: (get_field($key) ?: '');
};

$textimage_subtitle    = $resolve('textimage_subtitle');
$textimage_title       = $resolve('textimage_title');
$textimage_description = $resolve('textimage_description');
$textimage_background_color = $resolve('textimage_background_color');

// Image field (returns array)
$textimage_image = $resolve('textimage_image');

// Layout Style: textimage_theme_style (button group: "Image left" | "Image right")
$textimage_theme_style = $resolve('textimage_theme_style');
if (is_array($textimage_theme_style)) {
  $textimage_theme_style = $textimage_theme_style['value'] ?? ($textimage_theme_style['label'] ?? 'Image left');
}
if (empty($textimage_theme_style)) {
  $textimage_theme_style = 'Image left';
}

// Determine layout class
$layout_class = (strtolower(trim($textimage_theme_style)) === 'image right') ? 'image-right' : 'image-left';

// Resolve image URL & alt
$img_url = '';
$img_alt = '';
if (is_array($textimage_image)) {
  $img_url = $textimage_image['url'] ?? '';
  if (empty($img_url) && !empty($textimage_image['ID'])) {
    $img_url = wp_get_attachment_image_url($textimage_image['ID'], 'full');
  } elseif (empty($img_url) && !empty($textimage_image['id'])) {
    $img_url = wp_get_attachment_image_url($textimage_image['id'], 'full');
  }
  $img_alt = $textimage_image['alt'] ?? ($textimage_image['title'] ?? '');
} elseif (is_numeric($textimage_image)) {
  $img_url = wp_get_attachment_image_url($textimage_image, 'full');
  $img_alt = get_post_meta($textimage_image, '_wp_attachment_image_alt', true);
} elseif (is_string($textimage_image)) {
  $img_url = $textimage_image;
}

$block_id        = 'mm-text-image-' . ($block['id'] ?? uniqid());
$section_style   = !empty($textimage_background_color) ? ' style="background-color:' . esc_attr($textimage_background_color) . ';"' : '';
?>
<section id="<?php echo esc_attr($block_id); ?>"
  class="mm-section block-text-image <?php echo esc_attr($layout_class); ?>"
  data-testid="text-image"<?php echo $section_style; ?>>
  <div class="mm-wrap">
    <div class="mm-ti-inner">

      <?php if (!empty($img_url)): ?>
        <div class="mm-ti-image-col">
          <figure class="mm-ti-figure">
            <img decoding="async" loading="lazy"
              src="<?php echo esc_url($img_url); ?>"
              alt="<?php echo esc_attr($img_alt); ?>"
              class="mm-ti-img" />
          </figure>
        </div>
      <?php endif; ?>

      <div class="mm-ti-text-col">
        <?php if (!empty($textimage_subtitle)): ?>
          <p class="mm-eyebrow mm-ti-subtitle"><?php echo esc_html($textimage_subtitle); ?></p>
        <?php endif; ?>

        <?php if (!empty($textimage_title)): ?>
          <h2 class="mm-ti-title"><?php echo esc_html($textimage_title); ?></h2>
        <?php endif; ?>

        <?php if (!empty($textimage_description)): ?>
          <div class="mm-ti-desc wysiwyg-content"><?php echo wp_kses_post($textimage_description); ?></div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>