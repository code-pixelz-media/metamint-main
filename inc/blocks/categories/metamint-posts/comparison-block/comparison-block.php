<?php
/**
 * Block Name: Comparison Block
 * Block Category: Posts
 * Block Icon: products
 * Block Keywords: comparison, blog, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}

$block_id = !empty($block['anchor']) ? $block['anchor'] : ('comparison-block-' . ($block['id'] ?? uniqid()));
$custom_class = !empty($block['className']) ? ' ' . $block['className'] : '';
$align_class = !empty($block['align']) ? ' align' . $block['align'] : '';

// Extract block data safely (handles block editor canvas previews and frontend)
$block_data = $block['data'] ?? [];

$cblock_subtitle = !empty($block_data['cblock_subtitle'])
  ? $block_data['cblock_subtitle']
  : (get_field('cblock_subtitle') ?: '');

$cblock_title = !empty($block_data['cblock_title'])
  ? $block_data['cblock_title']
  : (get_field('cblock_title') ?: '');

$cblock_description = !empty($block_data['cblock_description'])
  ? $block_data['cblock_description']
  : (get_field('cblock_description') ?: '');

// Background Color: cblock_background_color
$cblock_background_color = '';
if (!empty($block_data['cblock_background_color'])) {
  $cblock_background_color = $block_data['cblock_background_color'];
} elseif (!empty($block_data['cblock_style']['cblock_background_color'])) {
  $cblock_background_color = $block_data['cblock_style']['cblock_background_color'];
} elseif (!empty($block_data['style']['cblock_background_color'])) {
  $cblock_background_color = $block_data['style']['cblock_background_color'];
} else {
  $cblock_background_color = get_field('cblock_background_color', $block['id'] ?? null)
    ?: (get_field('cblock_background_color') ?: '');
  if (empty($cblock_background_color)) {
    $cblock_style = get_field('cblock_style', $block['id'] ?? null) ?: (get_field('cblock_style') ?: []);
    if (!empty($cblock_style['cblock_background_color'])) {
      $cblock_background_color = $cblock_style['cblock_background_color'];
    }
  }
}

if (is_array($cblock_background_color)) {
  if (isset($cblock_background_color['value'])) {
    $cblock_background_color = $cblock_background_color['value'];
  } elseif (isset($cblock_background_color['label'])) {
    $cblock_background_color = $cblock_background_color['label'];
  } elseif (!empty($cblock_background_color[0])) {
    $cblock_background_color = $cblock_background_color[0];
  } else {
    $cblock_background_color = reset($cblock_background_color);
  }
}

$cblock_bg_clean = trim((string) $cblock_background_color);

// Color Theme: faq_color_theme (Choices: Red, Green)
$cblock_color_theme = '';

// Check direct block_data keys first
$possible_keys = ['faq_color_theme', 'cblock_color_theme', 'color_theme', 'cblock_accent_color', 'accent_color', 'theme_color'];
foreach ($possible_keys as $key) {
  if (!empty($block_data[$key])) {
    $cblock_color_theme = $block_data[$key];
    break;
  }
}

// Check nested groups in block_data
if (empty($cblock_color_theme)) {
  foreach (['faq_style', 'cblock_style', 'style', 'cblock_settings', 'settings'] as $grp) {
    if (!empty($block_data[$grp]) && is_array($block_data[$grp])) {
      foreach ($possible_keys as $key) {
        if (!empty($block_data[$grp][$key])) {
          $cblock_color_theme = $block_data[$grp][$key];
          break 2;
        }
      }
    }
  }
}

// Check any block_data value containing red or green
if (empty($cblock_color_theme)) {
  foreach ($block_data as $b_key => $b_val) {
    if (is_string($b_key) && (stripos($b_key, 'color') !== false || stripos($b_key, 'theme') !== false || stripos($b_key, 'accent') !== false)) {
      if (is_string($b_val) && in_array(strtolower(trim($b_val)), ['red', 'green', 'coral', 'teal', '#f05d5e', '#0f7173'])) {
        $cblock_color_theme = $b_val;
        break;
      }
    }
  }
}

// Check get_field across keys
if (empty($cblock_color_theme)) {
  foreach ($possible_keys as $key) {
    $val = get_field($key, $block['id'] ?? null) ?: (get_field($key) ?: '');
    if (!empty($val)) {
      $cblock_color_theme = $val;
      break;
    }
  }
}

// Check get_field within groups
if (empty($cblock_color_theme)) {
  foreach (['faq_style', 'cblock_style', 'style', 'cblock_settings', 'settings'] as $grp) {
    $grp_val = get_field($grp, $block['id'] ?? null) ?: (get_field($grp) ?: []);
    if (is_array($grp_val)) {
      foreach ($possible_keys as $key) {
        if (!empty($grp_val[$key])) {
          $cblock_color_theme = $grp_val[$key];
          break 2;
        }
      }
    }
  }
}

// Normalize array / object / string return formats
if (is_array($cblock_color_theme)) {
  if (isset($cblock_color_theme['value'])) {
    $cblock_color_theme = $cblock_color_theme['value'];
  } elseif (isset($cblock_color_theme['label'])) {
    $cblock_color_theme = $cblock_color_theme['label'];
  } elseif (!empty($cblock_color_theme[0])) {
    $cblock_color_theme = $cblock_color_theme[0];
  } else {
    $cblock_color_theme = reset($cblock_color_theme);
  }
}

$raw_theme   = trim((string) $cblock_color_theme);
$lower_theme = strtolower($raw_theme);

if ($lower_theme === 'red' || $lower_theme === 'coral' || $lower_theme === '#f05d5e' || strpos($lower_theme, 'red') !== false) {
  $color_class      = 'color-red color-coral theme-red';
  $theme_accent     = '#f05d5e';
  $theme_accent_d   = '#d64545';
  $theme_accent_rgb = '240, 93, 94';
} elseif ($lower_theme === 'green' || $lower_theme === 'teal' || $lower_theme === '#0f7173' || strpos($lower_theme, 'green') !== false) {
  $color_class      = 'color-green color-teal theme-green';
  $theme_accent     = '#0f7173';
  $theme_accent_d   = '#0b5658';
  $theme_accent_rgb = '15, 113, 115';
} elseif (str_starts_with($raw_theme, '#')) {
  $color_class      = 'color-custom';
  $theme_accent     = $raw_theme;
  $theme_accent_d   = $raw_theme;
  $theme_accent_rgb = '240, 93, 94';
} else {
  // Default to Green (matching current color)
  $color_class      = 'color-green color-teal theme-green';
  $theme_accent     = '#0f7173';
  $theme_accent_d   = '#0b5658';
  $theme_accent_rgb = '15, 113, 115';
}

$section_inline_styles = [];
$section_inline_styles[] = '--mm-accent: ' . esc_attr($theme_accent);
$section_inline_styles[] = '--mm-accent-d: ' . esc_attr($theme_accent_d);
$section_inline_styles[] = '--mm-accent-rgb: ' . esc_attr($theme_accent_rgb);
if (!empty($cblock_bg_clean)) {
  $section_inline_styles[] = 'background-color: ' . esc_attr($cblock_bg_clean);
}
$section_style = !empty($section_inline_styles) ? ' style="' . implode('; ', $section_inline_styles) . ';"' : '';

// Repeater data
$comparison_repeater = get_field('comparison_repeater') ?: [];

// Fallback to block data for preview if get_field is empty (standard ACF block preview behavior)
if (empty($comparison_repeater) && !empty($block_data['comparison_repeater'])) {
  $count = (int) $block_data['comparison_repeater'];
  for ($i = 0; $i < $count; $i++) {
    $item_title = $block_data["comparison_repeater_{$i}_comparison_repeater_title"] ?? '';
    $item_desc  = $block_data["comparison_repeater_{$i}_comparison_repeater_description"] ?? '';
    if ($item_title !== '' || $item_desc !== '') {
      $comparison_repeater[] = [
        'comparison_repeater_title'       => $item_title,
        'comparison_repeater_description' => $item_desc,
      ];
    }
  }
}

// Fallback sample data for editor preview when fields are not yet populated
if (empty($comparison_repeater) && empty($cblock_title) && (is_admin() || (isset($block['data']['is_preview']) && $block['data']['is_preview']))) {
  $cblock_subtitle = 'Overview & Comparison';
  $cblock_title = 'Compare Features & Capabilities';
  $cblock_description = 'Explore how each solution compares side by side with detailed breakdowns and specifications.';
  $comparison_repeater = [
    [
      'comparison_repeater_title'       => 'Performance & Speed',
      'comparison_repeater_description' => '<p>High-throughput architecture built for minimal latency and blazing-fast response times under heavy concurrent loads.</p>',
    ],
    [
      'comparison_repeater_title'       => 'Security & Compliance',
      'comparison_repeater_description' => '<p>Enterprise-grade encryption, regular audits, and zero-trust protocol compliance straight out of the box.</p>',
    ],
    [
      'comparison_repeater_title'       => 'Integration & API Support',
      'comparison_repeater_description' => '<p>Seamless REST and GraphQL APIs with extensive SDK support across modern programming languages and platforms.</p>',
    ],
    [
      'comparison_repeater_title'       => 'Support & Reliability',
      'comparison_repeater_description' => '<p>24/7 dedicated engineering support, 99.99% guaranteed uptime SLA, and automated daily backups with instant one-click rollback.</p>',
    ],
  ];
}
?>

<section id="<?php echo esc_attr($block_id); ?>" class="mm-section block-comparison-block mm-comparison-section <?php echo esc_attr($color_class . $custom_class . $align_class); ?>"<?php echo $section_style; ?> data-testid="comparison-block">
  <div class="mm-wrap">

  <!-- ===================================================================
       ROW 1: Header (Subtitle, Title, Description)
       =================================================================== -->
  <div class="mm-comparison-header">
    <?php if (!empty($cblock_subtitle)): ?>
      <p class="mm-eyebrow mm-comparison-subtitle"><?php echo esc_html($cblock_subtitle); ?></p>
    <?php endif; ?>

    <?php if (!empty($cblock_title)): ?>
      <h2 class="mm-h2 mm-comparison-title"><?php echo esc_html($cblock_title); ?></h2>
    <?php endif; ?>

    <?php if (!empty($cblock_description)): ?>
      <div class="mm-lede mm-left mm-comparison-description">
        <?php echo wp_kses_post(wpautop($cblock_description)); ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- ===================================================================
       ROW 2: Comparison Layout (Left: Fixed Sidebar | Right: Description)
       =================================================================== -->
  <?php if (!empty($comparison_repeater) && is_array($comparison_repeater)): ?>
    <div class="mm-comparison-layout">

      <!-- Left Sidebar: Fixed / Sticky navigation with comparison titles -->
      <aside class="mm-comparison-sidebar">
        <div class="mm-toc-card mm-comparison-nav-card mm-card">
          <p class="mm-toc-eyebrow">Comparison</p>
          <h4 class="mm-toc-title">Quick overview</h4>
          <nav class="mm-toc-nav mm-comparison-nav" aria-label="Comparison Sections">
            <ul class="mm-toc-list mm-comparison-nav-list">
              <?php foreach ($comparison_repeater as $index => $item):
                $item_title = !empty($item['comparison_repeater_title']) ? $item['comparison_repeater_title'] : ('Feature ' . ($index + 1));
                $slug = sanitize_title($item_title) ?: ('feature-' . ($index + 1));
                $target_id = $block_id . '-item-' . $slug . '-' . ($index + 1);
                ?>
                <li class="mm-toc-item mm-comparison-nav-item">
                  <a href="#<?php echo esc_attr($target_id); ?>" class="mm-comparison-nav-link<?php echo $index === 0 ? ' is-active' : ''; ?>">
                    <?php echo esc_html($item_title); ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </nav>
        </div>
      </aside>

      <!-- Right Column: Comparison Descriptions -->
      <div class="mm-comparison-content-column">
        <div class="mm-comparison-items-list">
          <?php foreach ($comparison_repeater as $index => $item):
            $item_title = !empty($item['comparison_repeater_title']) ? $item['comparison_repeater_title'] : ('Feature ' . ($index + 1));
            $item_desc  = !empty($item['comparison_repeater_description']) ? $item['comparison_repeater_description'] : '';
            $slug = sanitize_title($item_title) ?: ('feature-' . ($index + 1));
            $target_id = $block_id . '-item-' . $slug . '-' . ($index + 1);
            ?>
            <article id="<?php echo esc_attr($target_id); ?>" class="mm-comparison-item mm-card">
              <?php if (!empty($item_title)): ?>
                <div class="mm-comparison-item-header">
                  <span class="mm-comparison-item-index"><?php echo sprintf('%02d', $index + 1); ?></span>
                  <h3 class="mm-comparison-item-title mm-h3"><?php echo esc_html($item_title); ?></h3>
                </div>
              <?php endif; ?>

              <?php if (!empty($item_desc)): ?>
                <div class="entry-content mm-prose mm-comparison-item-body">
                  <?php echo wp_kses_post(wpautop($item_desc)); ?>
                </div>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  <?php endif; ?>

  </div>
</section>