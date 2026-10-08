<?php
/**
 * Block Name: Blog card
 * Block Category: Posts
 * Block Icon: products
 * Block Keywords: Blog, blog-card, posts, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}

// Extract block data safely (handles block editor canvas previews and frontend)
$block_data = $block['data'] ?? [];

$bc_inner_content = !empty($block_data['bc_inner_content']) && is_array($block_data['bc_inner_content'])
  ? $block_data['bc_inner_content']
  : (get_field('bc_inner_content', $block['id'] ?? null) ?: (get_field('bc_inner_content') ?: []));

$bc_style = !empty($block_data['bc_style']) && is_array($block_data['bc_style'])
  ? $block_data['bc_style']
  : (get_field('bc_style', $block['id'] ?? null) ?: (get_field('bc_style') ?: []));

// Subtitle: bc_subtitle
$bc_subtitle = !empty($block_data['bc_subtitle'])
  ? $block_data['bc_subtitle']
  : (!empty($bc_inner_content['bc_subtitle']) ? $bc_inner_content['bc_subtitle'] : (get_field('bc_subtitle', $block['id'] ?? null) ?: (get_field('bc_subtitle') ?: '')));

// Title: bc_title
$bc_title = !empty($block_data['bc_title'])
  ? $block_data['bc_title']
  : (!empty($bc_inner_content['bc_title']) ? $bc_inner_content['bc_title'] : (get_field('bc_title', $block['id'] ?? null) ?: (get_field('bc_title') ?: '')));

// Description: bc_description
$bc_description = !empty($block_data['bc_description'])
  ? $block_data['bc_description']
  : (!empty($bc_inner_content['bc_description']) ? $bc_inner_content['bc_description'] : (get_field('bc_description', $block['id'] ?? null) ?: (get_field('bc_description') ?: '')));

// Color Theme: bc_color_theme (Choices: Green, Red)
$bc_color_theme = '';
if (!empty($block_data['bc_color_theme'])) {
  $bc_color_theme = $block_data['bc_color_theme'];
} elseif (!empty($block_data['bc_style']['bc_color_theme'])) {
  $bc_color_theme = $block_data['bc_style']['bc_color_theme'];
} elseif (!empty($bc_inner_content['bc_color_theme'])) {
  $bc_color_theme = $bc_inner_content['bc_color_theme'];
} elseif (!empty($bc_style['bc_color_theme'])) {
  $bc_color_theme = $bc_style['bc_color_theme'];
} else {
  $bc_color_theme = get_field('bc_color_theme', $block['id'] ?? null) ?: (get_field('bc_color_theme') ?: 'Green');
}

if (is_array($bc_color_theme)) {
  $bc_color_theme = $bc_color_theme['value'] ?? ($bc_color_theme['label'] ?? 'Green');
}

$theme_slug = strtolower(trim((string) $bc_color_theme));
if ($theme_slug === 'red' || $theme_slug === 'coral' || $theme_slug === '#f05d5e') {
  $theme_class = 'theme-red';
  $theme_accent = '#f05d5e';
  $theme_accent_d = '#d94142';
} else {
  $theme_class = 'theme-green';
  $theme_accent = '#0f7173';
  $theme_accent_d = '#0b5658';
}

// Layout Style: bc_theme_style (Choices: Style 1, Style 2, Style 3)
$bc_theme_style = '';
if (!empty($block_data['bc_theme_style'])) {
  $bc_theme_style = $block_data['bc_theme_style'];
} elseif (!empty($block_data['bc_style']['bc_theme_style'])) {
  $bc_theme_style = $block_data['bc_style']['bc_theme_style'];
} elseif (!empty($bc_inner_content['bc_theme_style'])) {
  $bc_theme_style = $bc_inner_content['bc_theme_style'];
} elseif (!empty($bc_style['bc_theme_style'])) {
  $bc_theme_style = $bc_style['bc_theme_style'];
} else {
  $bc_theme_style = get_field('bc_theme_style', $block['id'] ?? null) ?: (get_field('bc_theme_style') ?: 'Style 1');
}

if (is_array($bc_theme_style)) {
  $bc_theme_style = $bc_theme_style['value'] ?? ($bc_theme_style['label'] ?? 'Style 1');
}

$style_slug = strtolower(str_replace(' ', '-', trim((string) $bc_theme_style)));
if ($style_slug === 'style-3') {
  $style_class = 'style-3';
} elseif ($style_slug === 'style-2') {
  $style_class = 'style-2';
} else {
  $style_class = 'style-1';
}

// Background Color: bc_background_color
$bc_background_color = '';
if (!empty($block_data['bc_background_color'])) {
  $bc_background_color = $block_data['bc_background_color'];
} elseif (!empty($block_data['bc_style']['bc_background_color'])) {
  $bc_background_color = $block_data['bc_style']['bc_background_color'];
} elseif (!empty($bc_inner_content['bc_background_color'])) {
  $bc_background_color = $bc_inner_content['bc_background_color'];
} elseif (!empty($bc_style['bc_background_color'])) {
  $bc_background_color = $bc_style['bc_background_color'];
} else {
  $bc_background_color = get_field('bc_background_color', $block['id'] ?? null) ?: (get_field('bc_background_color') ?: '');
}

// Columns: bc_theme_column (Choices: Column 1, Column 2, Column 3, Column 4)
$bc_theme_column = '';
if (!empty($block_data['bc_theme_column'])) {
  $bc_theme_column = $block_data['bc_theme_column'];
} elseif (!empty($block_data['bc_style']['bc_theme_column'])) {
  $bc_theme_column = $block_data['bc_style']['bc_theme_column'];
} elseif (!empty($bc_inner_content['bc_theme_column'])) {
  $bc_theme_column = $bc_inner_content['bc_theme_column'];
} elseif (!empty($bc_style['bc_theme_column'])) {
  $bc_theme_column = $bc_style['bc_theme_column'];
} else {
  $bc_theme_column = get_field('bc_theme_column', $block['id'] ?? null) ?: (get_field('bc_theme_column') ?: 'Column 3');
}

if (is_array($bc_theme_column)) {
  $bc_theme_column = $bc_theme_column['value'] ?? ($bc_theme_column['label'] ?? 'Column 3');
}

$column_num = 3;
$col_str = trim((string) $bc_theme_column);
if (preg_match('/[1-4]/', $col_str, $matches)) {
  $column_num = (int) $matches[0];
}
$columns_class = 'cols-' . $column_num;

$section_inline_styles = [];
if (!empty($bc_background_color)) {
  $section_inline_styles[] = 'background-color: ' . esc_attr(trim((string) $bc_background_color));
}
$section_inline_styles[] = '--mm-accent: ' . esc_attr($theme_accent);
$section_inline_styles[] = '--mm-accent-d: ' . esc_attr($theme_accent_d);

$section_style_attr = !empty($section_inline_styles) ? ' style="' . implode('; ', $section_inline_styles) . ';"' : '';

$paged = get_query_var('paged') ? (int) get_query_var('paged') : (get_query_var('page') ? (int) get_query_var('page') : 1);

$custom_logo_id = get_theme_mod('custom_logo');
$custom_logo_url = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'medium') : '';

$featured_ids = [];

$sticky_posts = get_option('sticky_posts');
$sticky_posts = is_array($sticky_posts) ? array_filter(array_map('absint', $sticky_posts)) : [];

global $wpdb;

$remaining_stickies = array_values(array_diff($sticky_posts, $featured_ids));

$posts_per_page = ($column_num === 4) ? 4 : (($column_num === 2) ? 4 : 3);

$grid_args = [
  'posts_per_page' => $posts_per_page,
  'paged' => $paged,
  'post_type' => 'post',
  'post_status' => 'publish',
  'post__not_in' => $featured_ids,
  'ignore_sticky_posts' => true,
];

if (!empty($remaining_stickies)) {
  $grid_args['sticky_card_priority'] = true;
  $sticky_orderby = function ($orderby, $query) use ($wpdb, $remaining_stickies) {
    if ($query->get('sticky_card_priority')) {
      $ids = implode(',', array_map('absint', $remaining_stickies));
      return "(CASE WHEN {$wpdb->posts}.ID IN ({$ids}) THEN 0 ELSE 1 END) ASC, {$wpdb->posts}.post_date DESC";
    }
    return $orderby;
  };
  add_filter('posts_orderby', $sticky_orderby, 10, 2);
}

$grid_query = new WP_Query($grid_args);

if (!empty($remaining_stickies) && isset($sticky_orderby)) {
  remove_filter('posts_orderby', $sticky_orderby, 10);
}

if ($grid_query->have_posts()):
  ?>
  <section
    class="mm-section mm-wash blog-card-section block-blog-grid <?= esc_attr($theme_class); ?> <?= esc_attr($style_class); ?> <?= esc_attr($columns_class); ?>"
    <?= $section_style_attr; ?> data-testid="blog-card">
    <div class="mm-wrap">
      <?php if (!empty($bc_subtitle)): ?>
        <p class="mm-eyebrow">
          <?php echo esc_html($bc_subtitle); ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($bc_title)): ?>
        <h2 class="mm-h2">
          <?php echo esc_html($bc_title); ?>
        </h2>
      <?php endif; ?>

      <?php if (!empty($bc_description)): ?>
        <p class="mm-lede mm-left">
          <?php echo wp_kses_post($bc_description); ?>
        </p>
      <?php endif; ?>

      <div
        class="blog-posts-grid mm-grid-<?= esc_attr($column_num); ?> mm-blog-cards-grid <?= esc_attr($columns_class); ?>"
        style="--grid-columns: <?= esc_attr($column_num); ?>;">
        <?php while ($grid_query->have_posts()):
          $grid_query->the_post();
          // Change the number (e.g. 15) below to adjust the word count
          $grid_excerpt = wp_trim_words(get_the_excerpt(), 15, '...');
          $grid_author = get_the_author() ?: 'Metamint Team';
          $grid_content = get_post_field('post_content', get_the_ID());
          $grid_words = str_word_count(wp_strip_all_tags($grid_content));
          $grid_read = max(1, (int) ceil($grid_words / 200));
          $post_slug = sanitize_title(get_the_title());
          ?>
          <article class="blog-card mm-card mm-blog-card-item" data-testid="blog-card-item-<?= esc_attr($post_slug); ?>">
            <div class="blog-card-thumbnail">
              <a href="<?php the_permalink(); ?>">
                <?php if (has_post_thumbnail()): ?>
                  <?php the_post_thumbnail(); ?>
                <?php else: ?>
                  <div class="blog-thumbnail-fallback">
                    <?php if (!empty($custom_logo_url)): ?>
                      <img src="<?php echo esc_url($custom_logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                        class="mm-thumbnail-logo">
                    <?php else: ?>
                      <div class="mm-thumbnail-brand">
                        <span class="mm-brand-icon" aria-hidden="true">
                          <svg width="32" height="32" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="40" height="40" rx="10" fill="var(--mm-accent, #0F7173)" />
                            <path d="M12 28V12L20 20L28 12V28" stroke="white" stroke-width="3" stroke-linecap="round"
                              stroke-linejoin="round" />
                          </svg>
                        </span>
                        <span class="mm-brand-name">Metamint<span class="mm-logo-caret">^</span>apps</span>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </a>
            </div>
            <div class="blog-card-body">
              <span class="blog-card-tag">
                <?php the_category(); ?>
              </span>
              <h3 class="mm-h3"><a href="<?php the_permalink(); ?>">
                  <?php the_title(); ?>
                </a></h3>
              <p style="color:var(--muted);margin:0">
                <?php echo esc_html(wp_strip_all_tags($grid_excerpt)); ?>
              </p>
              <div class="blog-card-meta">
                <?php echo esc_html($grid_author); ?> ·
                <?php echo esc_html($grid_read); ?> min read
              </div>
              <div class="blog-card-action">
                <a class="btn mm-btn mm-btn-ink" href="<?php the_permalink(); ?>">Read article</a>
              </div>
            </div>
          </article>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
      </div>



    </div>
  </section>
<?php endif; ?>