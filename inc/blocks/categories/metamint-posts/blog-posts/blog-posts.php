<?php
/**
 * Block Name: Blog posts
 * Block Category: Posts
 * Block Icon: products
 * Block Keywords: Blog, posts, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}

$paged = get_query_var('paged') ? (int) get_query_var('paged') : (get_query_var('page') ? (int) get_query_var('page') : 1);

$custom_logo_id = get_theme_mod('custom_logo');
$custom_logo_url = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'medium') : '';

$featured_ids = [];

$sticky_posts = get_option('sticky_posts');
$sticky_posts = is_array($sticky_posts) ? array_filter(array_map('absint', $sticky_posts)) : [];

$featured_args = [
  'posts_per_page'      => 1,
  'post_type'           => 'post',
  'post_status'         => 'publish',
  'ignore_sticky_posts' => true,
  'orderby'             => 'date',
  'order'               => 'DESC',
];

if (!empty($sticky_posts)) {
  $sticky_args = array_merge($featured_args, [
    'post__in' => $sticky_posts,
  ]);
  $featured_query = new WP_Query($sticky_args);

  // Fallback to latest post if no sticky post is published
  if (!$featured_query->have_posts()) {
    $featured_query = new WP_Query($featured_args);
  }
} else {
  $featured_query = new WP_Query($featured_args);
}

if ($featured_query->have_posts()):
  while ($featured_query->have_posts()):
    $featured_query->the_post();
    $featured_ids[] = get_the_ID();
    $featured_excerpt = get_the_excerpt();
    if (empty(trim($featured_excerpt))) {
      $featured_excerpt = wp_trim_words(get_the_content(), 28, '...');
    }
    $featured_author = get_the_author() ?: 'Metamint Team';
    $featured_content = get_post_field('post_content', get_the_ID());
    $featured_words = str_word_count(wp_strip_all_tags($featured_content));
    $featured_read = max(1, (int) ceil($featured_words / 200));

    // Show featured post on the first page
    if ($paged <= 1):
      ?>
      <section class="mm-section mm-wrap block-blog-posts">
        <article class="featured mm-card">
          <div class="blog-featured-thumbnail">
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
                        <svg width="42" height="42" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <rect width="40" height="40" rx="10" fill="#0F7173" />
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
          <div class="blog-featured-body">
            <span class="blog-featured-tag"><?php the_category(); ?></span>
            <h2 class="mm-h2"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <p style="color:var(--muted);margin:0"><?php echo esc_html(wp_strip_all_tags($featured_excerpt)); ?></p>
            <div class="blog-featured-meta"><?php echo esc_html($featured_author); ?> ·
              <?php echo esc_html($featured_read); ?> min read
            </div>
            <div><a class="btn mm-btn mm-btn-ink" href="<?php the_permalink(); ?>">Read article</a></div>
          </div>
        </article>
      </section>
      <?php
    endif;
  endwhile;
  wp_reset_postdata();
endif;

global $wpdb;

$remaining_stickies = array_values(array_diff($sticky_posts, $featured_ids));

$grid_args = [
  'posts_per_page'      => 6,
  'paged'               => $paged,
  'post_type'           => 'post',
  'post_status'         => 'publish',
  'post__not_in'        => $featured_ids,
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
  <section class="mm-section mm-wrap block-blog-grid">
    <div class="blog-posts-grid mm-grid-3">
      <?php while ($grid_query->have_posts()):
        $grid_query->the_post();
        // Change the number (e.g. 15) below to adjust the word count
        $grid_excerpt = wp_trim_words(get_the_excerpt(), 15, '...');
        $grid_author = get_the_author() ?: 'Metamint Team';
        $grid_content = get_post_field('post_content', get_the_ID());
        $grid_words = str_word_count(wp_strip_all_tags($grid_content));
        $grid_read = max(1, (int) ceil($grid_words / 200));
        ?>
        <article class="blog-card mm-card">
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
                          <rect width="40" height="40" rx="10" fill="#0F7173" />
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
            <span class="blog-card-tag"><?php the_category(); ?></span>
            <h3 class="mm-h3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <p style="color:var(--muted);margin:0"><?php echo esc_html(wp_strip_all_tags($grid_excerpt)); ?></p>
            <div class="blog-card-meta"><?php echo esc_html($grid_author); ?> · <?php echo esc_html($grid_read); ?> min read
            </div>
            <div><a class="btn mm-btn mm-btn-ink" href="<?php the_permalink(); ?>">Read article</a></div>
          </div>
        </article>
      <?php endwhile; ?>
    </div>

    <?php if ($grid_query->max_num_pages > 1): ?>
      <nav class="blog-pagination" aria-label="Blog posts pagination">
        <?php
        $big = 999999999;
        echo paginate_links([
          'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
          'format' => '?paged=%#%',
          'current' => max(1, $paged),
          'total' => $grid_query->max_num_pages,
          'prev_text' => '← Prev',
          'next_text' => 'Next →',
        ]);
        ?>
      </nav>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>
  </section>
<?php endif; ?>