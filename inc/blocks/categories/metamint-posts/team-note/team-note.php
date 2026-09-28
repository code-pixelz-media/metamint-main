<?php
/**
 * Block Name: Team Note
 * Block Category: Posts
 * Block Icon: team-note
 * Block Keywords: Team, Note, metamint
 * Block Mode: auto
 */

// Preview Image Handling
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}
// Fetch group and subfields safely (supports both tn_inner_content group and root-level fields)
$tn_inner_content = get_field('tn_inner_content') ?: [];

$tn_subtitle      = !empty($tn_inner_content['tn_subtitle']) ? $tn_inner_content['tn_subtitle'] : (get_field('tn_subtitle') ?: '');
$tn_title_content = !empty($tn_inner_content['tn_title_content']) ? $tn_inner_content['tn_title_content'] : (get_field('tn_title_content') ?: '');
$tn_team_name     = !empty($tn_inner_content['tn_team_name']) ? $tn_inner_content['tn_team_name'] : (get_field('tn_team_name') ?: '');
$tn_team_collab   = !empty($tn_inner_content['tn_team_collab']) ? $tn_inner_content['tn_team_collab'] : (get_field('tn_team_collab') ?: '');

$tn_cards_repeater = !empty($tn_inner_content['tn_cards_repeater']) && is_array($tn_inner_content['tn_cards_repeater'])
  ? $tn_inner_content['tn_cards_repeater']
  : (get_field('tn_cards_repeater') ?: []);
?>
<section class="mm-section mm-wash2" data-testid="team-note">
  <div class="mm-wrap mm-narrow mm-center">
    <?php if (!empty($tn_subtitle)): ?>
      <p class="mm-eyebrow mm-teal"><?php echo esc_html($tn_subtitle); ?></p>
    <?php endif; ?>

    <?php if (!empty($tn_title_content)): ?>
      <blockquote class="mm-quote">
        <?php echo wp_kses_post($tn_title_content); ?>
      </blockquote>
    <?php endif; ?>

    <?php if (!empty($tn_team_name) || !empty($tn_team_collab)): ?>
      <p class="mm-sign">
        <?php if (!empty($tn_team_name)): ?>
          <b><?php echo esc_html($tn_team_name); ?></b>
        <?php endif; ?>
        <?php if (!empty($tn_team_collab)): ?>
          <span><?php echo esc_html($tn_team_collab); ?></span>
        <?php endif; ?>
      </p>
    <?php endif; ?>

    <?php if (!empty($tn_cards_repeater) && is_array($tn_cards_repeater)): ?>
      <dl class="mm-stats-row">
        <?php foreach ($tn_cards_repeater as $card):
          $card_title   = !empty($card['hf_cards_title']) ? $card['hf_cards_title'] : '';
          $card_content = !empty($card['hf_cards_content']) ? $card['hf_cards_content'] : '';
          ?>
          <div>
            <?php if (!empty($card_content)): ?>
              <dt class="mm-sr"><?php echo esc_html($card_content); ?></dt>
            <?php endif; ?>
            <?php if (!empty($card_title)): ?>
              <dd class="mm-stat-v"><?php echo esc_html($card_title); ?></dd>
            <?php endif; ?>
            <?php if (!empty($card_content)): ?>
              <dd class="mm-stat-l"><?php echo esc_html($card_content); ?></dd>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </dl>
    <?php endif; ?>
  </div>
</section>