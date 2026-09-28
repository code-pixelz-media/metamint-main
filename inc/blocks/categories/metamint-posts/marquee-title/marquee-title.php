<?php
/**
 * Block Name: Marquee Title
 * Block Category: Posts
 * Block Icon: marquee
 * Block Keywords: marquee, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}

$marquee_content = get_field('marquee_content');

if (empty($marquee_content) || !is_array($marquee_content)) {
  if (!empty($is_preview)) {
    echo '<div style="padding: 24px; text-align: center; color: #999; font-size: 14px;">' . esc_html__('Add items to Marquee Content in the block settings.', 'metamint') . '</div>';
  }
  return;
}

// Normalize items
$items = [];
foreach ($marquee_content as $row) {
  $title = trim($row['marquee_title'] ?? '');
  if ($title === '') {
    continue;
  }

  $link_data = $row['marquee_link'] ?? '';
  $url = '';
  $target = '';

  if (is_array($link_data)) {
    $url = $link_data['url'] ?? '';
    if (!empty($link_data['target'])) {
      $target = ' target="' . esc_attr($link_data['target']) . '" rel="noopener noreferrer"';
    }
  } elseif (is_string($link_data)) {
    $url = trim($link_data);
  }

  $items[] = [
    'title' => $title,
    'url' => $url,
    'target' => $target,
  ];
}

if (empty($items)) {
  if (!empty($is_preview)) {
    echo '<div style="padding: 24px; text-align: center; color: #999; font-size: 14px;">' . esc_html__('No marquee items defined.', 'metamint') . '</div>';
  }
  return;
}
?>
<div class="mm-marquee" data-testid="editorial-marquee" aria-hidden="true">
  <div class="mm-marquee-track">
    <?php
    // Repeat items to ensure seamless infinite CSS loop (translateX: -50%)
    $repeat_count = count($items) < 6 ? 4 : 2;
    for ($i = 0; $i < $repeat_count; $i++):
      foreach ($items as $item):
        ?>
        <?php if (!empty($item['url'])): ?>
          <a class="mm-marquee-item" href="<?php echo esc_url($item['url']); ?>" <?= $item['target'] ?>>
            <?php echo esc_html($item['title']); ?><i></i>
          </a>
        <?php else: ?>
          <span class="mm-marquee-item">
            <?php echo esc_html($item['title']); ?><i></i>
          </span>
        <?php endif; ?>
        <?php
      endforeach;
    endfor;
    ?>
  </div>
</div>