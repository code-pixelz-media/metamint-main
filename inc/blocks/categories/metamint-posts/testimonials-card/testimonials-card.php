<?php
/**
 * Block Name: Testimonials card
 * Block Category: Posts
 * Block Icon: products
 * Block Keywords: Testimonials, card, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}
// Fetch group and subfields safely (supports both testi_inner_content group and root-level fields)
$testi_inner_content = get_field('testi_inner_content') ?: [];

$testi_subtitle   = !empty($testi_inner_content['testi_subtitle']) ? $testi_inner_content['testi_subtitle'] : (get_field('testi_subtitle') ?: '');
$testi_title      = !empty($testi_inner_content['testi_title']) ? $testi_inner_content['testi_title'] : (get_field('testi_title') ?: '');
$testi_title_desc = !empty($testi_inner_content['testi_title_desc']) ? $testi_inner_content['testi_title_desc'] : (get_field('testi_title_desc') ?: '');

$testi_cards_repeater = !empty($testi_inner_content['testi_cards_repeater']) && is_array($testi_inner_content['testi_cards_repeater'])
  ? $testi_inner_content['testi_cards_repeater']
  : (get_field('testi_cards_repeater') ?: []);

// Helper to normalize rating into star icons (e.g. "1 star", "2 stars", 5, "★★★★★")
$render_stars = function ($rating) {
  if (empty($rating)) {
    return '';
  }

  // If it already contains star symbols (e.g. ★★★★★)
  if (is_string($rating) && mb_strpos($rating, '★') !== false) {
    return trim($rating);
  }

  $rating_str = is_string($rating) ? strtolower(trim($rating)) : (string) $rating;

  // Extract digits (e.g. "1 star", "2 star", "5", 4)
  if (preg_match('/\d+(\.\d+)?/', $rating_str, $matches)) {
    $count = max(1, min(5, (int) round((float) $matches[0])));
    return str_repeat('★', $count);
  }

  // Support word numbers (e.g. "one star", "two stars")
  $words = ['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5];
  foreach ($words as $word => $count) {
    if (strpos($rating_str, $word) !== false) {
      return str_repeat('★', $count);
    }
  }

  return '';
};
?>
<section class="mm-section mm-wrap block-testimonials-card testimonials-card" data-testid="home-testimonials">
  <?php if (!empty($testi_subtitle)): ?>
    <p class="mm-eyebrow mm-coral"><?php echo esc_html($testi_subtitle); ?></p>
  <?php endif; ?>

  <?php if (!empty($testi_title)): ?>
    <h2 class="mm-h2"><?php echo esc_html($testi_title); ?></h2>
  <?php endif; ?>

  <?php if (!empty($testi_title_desc)): ?>
    <p class="mm-lede mm-left"><?php echo wp_kses_post($testi_title_desc); ?></p>
  <?php endif; ?>

  <?php if (!empty($testi_cards_repeater) && is_array($testi_cards_repeater)): ?>
    <div class="mm-grid-2">
      <?php foreach ($testi_cards_repeater as $index => $card):
        $card_rating      = !empty($card['testi_ratings']) ? $card['testi_ratings'] : '';
        $card_content     = !empty($card['testi_cards_content']) ? $card['testi_cards_content'] : '';
        $card_author      = !empty($card['testi_author']) ? $card['testi_author'] : '';
        $card_designation = !empty($card['testi_author_designation']) ? $card['testi_author_designation'] : '';
        $card_slug        = !empty($card_author) ? sanitize_title($card_author) : ($index + 1);
        $stars_display    = $render_stars($card_rating);
        ?>
        <figure class="mm-card mm-quote-card" data-testid="testimonial-<?php echo esc_attr($card_slug); ?>">
          <?php if (!empty($stars_display)): ?>
            <div class="mm-stars" aria-label="<?php echo esc_attr(mb_strlen($stars_display) . ' star review'); ?>">
              <?php echo esc_html($stars_display); ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($card_content)): ?>
            <blockquote><?php echo wp_kses_post($card_content); ?></blockquote>
          <?php endif; ?>

          <?php if (!empty($card_author) || !empty($card_designation)): ?>
            <figcaption>
              <?php if (!empty($card_author)): ?>
                <b><?php echo esc_html($card_author); ?></b>
              <?php endif; ?>
              <?php if (!empty($card_designation)): ?>
                <span><?php echo esc_html($card_designation); ?></span>
              <?php endif; ?>
            </figcaption>
          <?php endif; ?>
        </figure>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>