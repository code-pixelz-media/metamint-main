<?php
/**
 * Block Name: FAQ Section
 * Block Category: Posts
 * Block Icon: products
 * Block Keywords: FAQ, card, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}

// Extract block data safely (handles block editor canvas previews and frontend)
$block_data = $block['data'] ?? [];

$faq_subtitle = !empty($block_data['faq_subtitle'])
  ? $block_data['faq_subtitle']
  : (get_field('faq_subtitle', $block['id'] ?? null) ?: (get_field('faq_subtitle') ?: ''));

$faq_title = !empty($block_data['faq_title'])
  ? $block_data['faq_title']
  : (get_field('faq_title', $block['id'] ?? null) ?: (get_field('faq_title') ?: ''));

$faq_title_desc = !empty($block_data['faq_title_desc'])
  ? $block_data['faq_title_desc']
  : (get_field('faq_title_desc', $block['id'] ?? null) ?: (get_field('faq_title_desc') ?: ''));

// Repeater data
$faq_repeater = get_field('faq_repeater', $block['id'] ?? null) ?: (get_field('faq_repeater') ?: []);

if (empty($faq_repeater) && !empty($block_data['faq_repeater'])) {
  $count = (int) $block_data['faq_repeater'];
  for ($i = 0; $i < $count; $i++) {
    $q = $block_data["faq_repeater_{$i}_faq_question"] ?? '';
    $a = $block_data["faq_repeater_{$i}_faq_answer"] ?? '';
    if ($q !== '' || $a !== '') {
      $faq_repeater[] = [
        'faq_question' => $q,
        'faq_answer'   => $a,
      ];
    }
  }
}
?>
<section class="mm-section mm-wash" style="--mm-accent:#F05D5E">
  <div class="mm-wrap mm-narrow">
    <?php if (!empty($faq_subtitle)): ?>
      <p class="mm-eyebrow mm-center" style="color:var(--mm-accent)"><?php echo esc_html($faq_subtitle); ?></p>
    <?php endif; ?>

    <?php if (!empty($faq_title)): ?>
      <h2 class="mm-h2 mm-center"><?php echo esc_html($faq_title); ?></h2>
    <?php endif; ?>

    <?php if (!empty($faq_title_desc)): ?>
      <p class="mm-lede mm-center"><?php echo wp_kses_post($faq_title_desc); ?></p>
    <?php endif; ?>

    <?php if (!empty($faq_repeater) && is_array($faq_repeater)): ?>
      <div class="mm-faq" data-mm-faq="" data-testid="seo-faq">
        <?php foreach ($faq_repeater as $index => $item):
          $question = !empty($item['faq_question']) ? trim($item['faq_question']) : '';
          $answer   = !empty($item['faq_answer']) ? trim($item['faq_answer']) : '';
          if ($question === '' && $answer === '') {
            continue;
          }
          ?>
          <div class="mm-faq-item">
            <?php if ($question !== ''): ?>
              <button class="mm-faq-q" type="button" data-testid="seo-faq-question-<?php echo esc_attr($index); ?>">
                <?php echo esc_html($question); ?><span class="mm-faq-icon" aria-hidden="true"></span>
              </button>
            <?php endif; ?>
            <?php if ($answer !== ''): ?>
              <div class="mm-faq-a" data-testid="seo-faq-answer-<?php echo esc_attr($index); ?>" hidden="">
                <?php echo wp_kses_post(wpautop($answer)); ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>