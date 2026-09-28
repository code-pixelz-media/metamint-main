<?php
/**
 * Block Name: Pricing Table
 * Block Category: Posts
 * Block Icon: products
 * Block Keywords: Pricing, table, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}
// Extract block data safely (handles block editor canvas previews and frontend)
$block_data = $block['data'] ?? [];

$pt_subtitle = !empty($block_data['pt_subtitle'])
  ? $block_data['pt_subtitle']
  : (get_field('pt_subtitle', $block['id'] ?? null) ?: (get_field('pt_subtitle') ?: ''));

$pt_title = !empty($block_data['pt_title'])
  ? $block_data['pt_title']
  : (get_field('pt_title', $block['id'] ?? null) ?: (get_field('pt_title') ?: ''));

$pt_title_desc = !empty($block_data['pt_title_desc'])
  ? $block_data['pt_title_desc']
  : (get_field('pt_title_desc', $block['id'] ?? null) ?: (get_field('pt_title_desc') ?: ''));

// Fetch Style Tab (pt_color_theme - checks block data first for live editor toggle, then get_field)
$pt_color_theme = '';
if (!empty($block_data['pt_color_theme'])) {
  $pt_color_theme = $block_data['pt_color_theme'];
} elseif (!empty($block_data['pt_style']['pt_color_theme'])) {
  $pt_color_theme = $block_data['pt_style']['pt_color_theme'];
} elseif (!empty($block_data['style']['pt_color_theme'])) {
  $pt_color_theme = $block_data['style']['pt_color_theme'];
} else {
  $pt_color_theme = get_field('pt_color_theme', $block['id'] ?? null) ?: (get_field('pt_color_theme') ?: '');
  if (empty($pt_color_theme)) {
    $pt_style = get_field('pt_style', $block['id'] ?? null) ?: (get_field('pt_style') ?: []);
    if (!empty($pt_style['pt_color_theme'])) {
      $pt_color_theme = $pt_style['pt_color_theme'];
    }
  }
}

if (is_array($pt_color_theme)) {
  $pt_color_theme = $pt_color_theme['value'] ?? ($pt_color_theme['label'] ?? 'Red');
}

$theme_slug = strtolower(trim((string) $pt_color_theme));
if ($theme_slug === 'green' || $theme_slug === 'teal' || $theme_slug === '#0f7173') {
  $accent_color = '#0F7173';
  $accent_hover = '#0C5E60';
  $theme_class = 'theme-green';
} else {
  // Default to Red (Coral)
  $accent_color = '#F05D5E';
  $accent_hover = '#D64545';
  $theme_class = 'theme-red';
}

$tables = [];

for ($num = 1; $num <= 3; $num++) {
  $prefix = "pt{$num}_";

  $package_name = !empty($block_data[$prefix . 'package_name'])
    ? $block_data[$prefix . 'package_name']
    : (get_field($prefix . 'package_name', $block['id'] ?? null) ?: (get_field($prefix . 'package_name') ?: ''));

  $package_desc = !empty($block_data[$prefix . 'package_desc'])
    ? $block_data[$prefix . 'package_desc']
    : (get_field($prefix . 'package_desc', $block['id'] ?? null) ?: (get_field($prefix . 'package_desc') ?: ''));

  $package_price = !empty($block_data[$prefix . 'package_price'])
    ? $block_data[$prefix . 'package_price']
    : (get_field($prefix . 'package_price', $block['id'] ?? null) ?: (get_field($prefix . 'package_price') ?: ''));

  $package_price_discount = !empty($block_data[$prefix . 'package_price_discount'])
    ? $block_data[$prefix . 'package_price_discount']
    : (get_field($prefix . 'package_price_discount', $block['id'] ?? null) ?: (get_field($prefix . 'package_price_discount') ?: ''));

  $package_badge = !empty($block_data[$prefix . 'package_badge'])
    ? $block_data[$prefix . 'package_badge']
    : (get_field($prefix . 'package_badge', $block['id'] ?? null)
      ?: (get_field($prefix . 'badge', $block['id'] ?? null) ?: ''));

  // Table 2 only: inline name price fields
  $package_name_price = '';
  $package_name_price_after = '';
  if ($num === 2) {
    $package_name_price = !empty($block_data['pt2_package_name_price'])
      ? $block_data['pt2_package_name_price']
      : (get_field('pt2_package_name_price', $block['id'] ?? null) ?: (get_field('pt2_package_name_price') ?: ''));
    $package_name_price_after = !empty($block_data['pt2_package_name_price_after'])
      ? $block_data['pt2_package_name_price_after']
      : (get_field('pt2_package_name_price_after', $block['id'] ?? null) ?: (get_field('pt2_package_name_price_after') ?: ''));
  }

  $package_button = !empty($block_data[$prefix . 'package_button'])
    ? $block_data[$prefix . 'package_button']
    : (get_field($prefix . 'package_button', $block['id'] ?? null) ?: (get_field($prefix . 'package_button') ?: ''));

  $package_button_url = !empty($block_data[$prefix . 'package_button_url'])
    ? $block_data[$prefix . 'package_button_url']
    : (get_field($prefix . 'package_button_url', $block['id'] ?? null) ?: (get_field($prefix . 'package_button_url') ?: ''));

  $package_bottom_text = !empty($block_data[$prefix . 'package_bottom_text'])
    ? $block_data[$prefix . 'package_bottom_text']
    : (get_field($prefix . 'package_bottom_text', $block['id'] ?? null) ?: (get_field($prefix . 'package_bottom_text') ?: ''));

  $package_bottom_text_url = !empty($block_data[$prefix . 'package_bottom_text_url'])
    ? $block_data[$prefix . 'package_bottom_text_url']
    : (get_field($prefix . 'package_bottom_text_url', $block['id'] ?? null) ?: (get_field($prefix . 'package_bottom_text_url') ?: ''));

  // Repeater features
  $features_raw = get_field("pt{$num}_repeater", $block['id'] ?? null)
    ?: (get_field("pt{$num}_repeater")
      ?: (get_field("pt{$num}_package_features", $block['id'] ?? null)
        ?: (get_field("pt{$num}_package_features") ?: [])));

  $features = [];
  if (!empty($features_raw) && is_array($features_raw)) {
    foreach ($features_raw as $item) {
      if (is_array($item)) {
        $val = $item["pt{$num}_package_features"] ?? ($item['feature'] ?? ($item['features'] ?? reset($item)));
        if (!empty($val) && is_string($val)) {
          $features[] = trim($val);
        }
      } elseif (is_string($item)) {
        $features[] = trim($item);
      }
    }
  }

  // Fallback to block_data for block editor canvas previews
  if (empty($features)) {
    $rep_count = isset($block_data["pt{$num}_repeater"]) ? (int) $block_data["pt{$num}_repeater"] : 0;
    if ($rep_count === 0 && isset($block_data["pt{$num}_package_features"])) {
      $rep_count = (int) $block_data["pt{$num}_package_features"];
    }
    for ($i = 0; $i < $rep_count; $i++) {
      $val = $block_data["pt{$num}_repeater_{$i}_pt{$num}_package_features"]
        ?? ($block_data["pt{$num}_package_features_{$i}_pt{$num}_package_features"]
          ?? ($block_data["pt{$num}_repeater_{$i}_feature"] ?? ''));
      if (!empty($val) && is_string($val)) {
        $features[] = trim($val);
      }
    }
  }

  // Currency detection
  $currency_symbol = '$';
  if (preg_match('/([^\d\s\/\.,]+)/u', (string) $package_price, $m)) {
    $currency_symbol = trim($m[1]);
  } elseif (preg_match('/([^\d\s\/\.,]+)/u', (string) $package_price_discount, $m)) {
    $currency_symbol = trim($m[1]);
  }

  // Suffix detection (e.g. "/ year" -> " / year")
  $price_suffix = '';
  if (strpos((string) $package_price, '/') !== false) {
    $parts = explode('/', (string) $package_price, 2);
    $price_suffix = ' / ' . trim($parts[1]);
  } elseif (strpos((string) $package_price_discount, '/') !== false) {
    $parts = explode('/', (string) $package_price_discount, 2);
    $price_suffix = ' / ' . trim($parts[1]);
  }

  // Numeric extraction for discount calculation
  preg_match('/[\d]+(?:\.[\d]+)?/', str_replace(',', '', (string) $package_price), $m_reg);
  preg_match('/[\d]+(?:\.[\d]+)?/', str_replace(',', '', (string) $package_price_discount), $m_disc);

  $val_reg = !empty($m_reg[0]) ? (float) $m_reg[0] : 0;
  $val_disc = !empty($m_disc[0]) ? (float) $m_disc[0] : 0;

  $has_discount = ($val_reg > 0 && $val_disc > 0 && $val_reg !== $val_disc);
  if ($has_discount) {
    $old_num = max($val_reg, $val_disc);
    $curr_num = min($val_reg, $val_disc);
    $discount_percent = round((($old_num - $curr_num) / $old_num) * 100);
    $price_old = number_format($old_num, 2);

    $curr_raw = ($val_disc < $val_reg) ? (string) $package_price_discount : (string) $package_price;
    $curr_part = trim(explode('/', $curr_raw, 2)[0]);
    if (strpos($curr_part, $currency_symbol) === false) {
      $price_current = $currency_symbol . $curr_part;
    } else {
      $price_current = $curr_part;
    }
  } else {
    $discount_percent = 0;
    $price_old = '';
    $price_raw = trim((string) $package_price);
    $price_current = $price_raw;
    if ($price_raw !== '' && strpos($price_raw, '/') !== false) {
      $parts = explode('/', $price_raw, 2);
      $price_current = trim($parts[0]);
    }
  }

  $has_content = !empty($package_name) || !empty($package_desc) || !empty($package_price) || !empty($package_price_discount) || !empty($features) || !empty($package_button);

  if ($has_content) {
    $tables[] = [
      'table_num' => $num,
      'package_name' => $package_name,
      'package_name_price' => $package_name_price,
      'package_name_price_after' => $package_name_price_after,
      'package_badge' => $package_badge,
      'package_desc' => $package_desc,
      'package_price' => $package_price,
      'package_price_discount' => $package_price_discount,
      'has_discount' => $has_discount,
      'discount_percent' => $discount_percent,
      'price_old' => $price_old,
      'currency_symbol' => $currency_symbol,
      'price_current' => $price_current,
      'price_suffix' => $price_suffix,
      'package_button' => $package_button,
      'package_button_url' => $package_button_url,
      'package_bottom_text' => $package_bottom_text,
      'package_bottom_text_url' => $package_bottom_text_url,
      'features' => $features,
    ];
  }
}
?>
<section id="pricing" class="mm-section mm-wrap <?php echo esc_attr($theme_class); ?>" data-testid="seo-pricing"
  style="--mm-accent:<?php echo esc_attr($accent_color); ?>;--mm-accent-d:<?php echo esc_attr($accent_hover); ?>;">
  <?php if (!empty($pt_subtitle)): ?>
    <p class="mm-eyebrow mm-center" style="color:var(--mm-accent)"><?php echo esc_html($pt_subtitle); ?></p>
  <?php endif; ?>

  <?php if (!empty($pt_title)): ?>
    <h2 class="mm-h2 mm-center"><?php echo esc_html($pt_title); ?></h2>
  <?php endif; ?>

  <?php if (!empty($pt_title_desc)): ?>
    <p class="mm-lede"><?php echo wp_kses_post($pt_title_desc); ?></p>
  <?php endif; ?>

  <?php if (!empty($tables)):
    $grid_class = (count($tables) === 2) ? 'mm-grid-2' : 'mm-grid-3';
    ?>
    <div class="<?php echo esc_attr($grid_class); ?>">
      <?php foreach ($tables as $idx => $table):
        $is_popular = ($table['table_num'] === 2);
        $card_classes = 'mm-card mm-plan' . ($is_popular ? ' mm-plan-popular' : '');
        $card_style = $is_popular ? ' style="border-color:var(--mm-accent)"' : '';
        ?>
        <div class="<?php echo esc_attr($card_classes); ?>" data-testid="pricing-plan-seo-<?php echo esc_attr($idx); ?>"
          <?php echo $card_style; ?>>
          <?php if ($is_popular): ?>
            <span class="mm-plan-flag"
              style="background:var(--mm-accent);color:#fff"><?php echo esc_html(!empty($table['package_badge']) ? $table['package_badge'] : 'Most popular'); ?></span>
          <?php endif; ?>

          <?php if (!empty($table['package_name'])): ?>
            <div class="mm-plan-name-row">
              <h3 class="mm-h4"><?php echo esc_html($table['package_name']); ?></h3>
              <?php if ($is_popular && !empty($table['package_name_price'])): ?>
                <span class="mmhd-regular-price"><?php echo esc_html($table['package_name_price']); ?></span>
              <?php endif; ?>
              <?php if ($is_popular && !empty($table['package_name_price_after'])): ?>
                <span class="mm-plan-name-after"><?php echo esc_html($table['package_name_price_after']); ?></span>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($table['package_desc'])): ?>
            <p class="mm-muted mm-sm"><?php echo esc_html($table['package_desc']); ?></p>
          <?php endif; ?>

          <?php if (!empty($table['has_discount'])): ?>
            <div class="mm-price-sale">
              <p class="mm-price">
                <span class="mmhd-regular-price"><?php echo esc_html($table['price_current']); ?></span>
                <?php if (!empty($table['price_suffix'])): ?>
                  <span class="mm-price-suffix"><?php echo esc_html($table['price_suffix']); ?></span>
                <?php endif; ?>
              </p>
              <p class="mm-price-meta">
                <s class="mm-price-old"><span class="woocommerce-Price-amount amount"><bdi><span
                        class="woocommerce-Price-currencySymbol"
                        translate="no"><?php echo esc_html($table['currency_symbol']); ?></span><?php echo esc_html($table['price_old']); ?></bdi></span></s>
                <span class="mm-price-save" style="background:var(--mm-accent);color:#fff">Save
                  <?php echo esc_html($table['discount_percent']); ?>%</span>
              </p>
            </div>
          <?php elseif (!empty($table['price_current'])): ?>
            <p class="mm-price">
              <span class="mmhd-discounted-price"><?php echo esc_html($table['price_current']); ?></span>
              <?php if (!empty($table['price_suffix'])): ?>
                <span class="mm-price-suffix"><?php echo esc_html($table['price_suffix']); ?></span>
              <?php endif; ?>
            </p>
          <?php endif; ?>

          <?php if (!empty($table['features'])): ?>
            <ul class="mm-checklist">
              <?php foreach ($table['features'] as $feat): ?>
                <li><?php echo esc_html($feat); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <?php if (!empty($table['package_button'])):
            $btn_class = $is_popular ? 'mm-btn mm-btn-block' : 'mm-btn mm-btn-outline mm-btn-block';
            $btn_style = $is_popular ? ' style="background:var(--mm-accent);color:#fff"' : '';
            $btn_url = !empty($table['package_button_url']) ? $table['package_button_url'] : '#';
            ?>
            <a class="<?php echo esc_attr($btn_class); ?>" <?php echo $btn_style; ?> href="<?php echo esc_url($btn_url); ?>"
              target="_blank" rel="noreferrer" data-testid="pricing-buy-seo-<?php echo esc_attr($idx); ?>">
              <?php echo esc_html($table['package_button']); ?>
            </a>
          <?php endif; ?>

          <?php if (!empty($table['package_bottom_text'])):
            $bottom_text = $table['package_bottom_text'];
            $bottom_url = $table['package_bottom_text_url'];
            if (!empty($bottom_url) && $bottom_url !== '#'): ?>
              <a class="mm-plan-demo" href="<?php echo esc_url($bottom_url); ?>" target="_blank" rel="noreferrer"
                data-testid="pricing-demo-seo-<?php echo esc_attr($idx); ?>">
                <?php echo esc_html($bottom_text); ?>
              </a>
            <?php else: ?>
              <button class="mm-plan-demo" type="button" data-mm-checkout="" data-mm-product="seo"
                data-testid="pricing-demo-seo-<?php echo esc_attr($idx); ?>">
                <?php echo esc_html($bottom_text); ?>
              </button>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>