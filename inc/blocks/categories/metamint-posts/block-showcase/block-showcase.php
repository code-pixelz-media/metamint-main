<?php
/**
 * Block Name: Block Showcase
 * Block Category: Posts
 * Block Icon: desktop
 * Block Keywords: showcase, mockup, device, desktop, mobile, metamint
 * Block Mode: auto
 */

// Preview Image Handling for WordPress Block inserter
if (isset($block['data']['is_preview']) && !empty($block['data']['_preview_image_url'])) {
  echo '<img src="' . esc_url($block['data']['_preview_image_url']) . '" style="width:100%; height:auto; display:block;" alt="Block Preview">';
  return;
}

$block_data = $block['data'] ?? [];

// Helper to normalize images (array, URL, or ID)
$parse_img = function ($img) {
  if (empty($img))
    return '';
  if (is_string($img))
    return $img;
  if (is_array($img)) {
    return $img['url'] ?? (!empty($img['ID']) ? wp_get_attachment_image_url($img['ID'], 'full') : '');
  }
  if (is_numeric($img)) {
    return wp_get_attachment_image_url((int) $img, 'full') ?: '';
  }
  return '';
};

// Helper to normalize mode (button group return format: value, label, or string)
$get_mode = function ($raw_val) {
  if (is_array($raw_val)) {
    $raw_val = $raw_val['value'] ?? ($raw_val['label'] ?? '');
  }
  $str = strtolower(trim((string) $raw_val));
  if (strpos($str, 'vid') !== false) {
    return 'video';
  }
  if (strpos($str, 'img') !== false || strpos($str, 'image') !== false) {
    return 'image';
  }
  return $str;
};

// Helper to parse and prepare video embed (oembed, URL, or direct video)
// Automatically enforces: autoplay, mute (required for browser autoplay),
// controls=0 (remove YouTube player controls), playsinline, loop, etc.
$parse_video_embed = function ($embed_data) {
  if (empty($embed_data)) {
    return '';
  }

  // Handle direct HTML5 video files (.mp4, .webm, .mov)
  if (is_string($embed_data) && preg_match('/\.(mp4|webm|mov)(\?.*)?$/i', trim($embed_data))) {
    return sprintf(
      '<video src="%s" autoplay muted loop playsinline></video>',
      esc_url(trim($embed_data))
    );
  }

  $iframe = '';
  if (is_string($embed_data) && strpos($embed_data, '<iframe') !== false) {
    if (preg_match('/<iframe[^>]*>.*?<\/iframe>/is', $embed_data, $m)) {
      $iframe = $m[0];
    } else {
      $iframe = $embed_data;
    }
  } elseif (is_string($embed_data) && (filter_var($embed_data, FILTER_VALIDATE_URL) || strpos($embed_data, 'http') === 0)) {
    if (function_exists('wp_oembed_get')) {
      $iframe = wp_oembed_get($embed_data);
    }
    // Fallback if wp_oembed_get is unavailable or returned false
    if (empty($iframe)) {
      if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/|youtube\.com\/shorts\/)([^"&?\/ ]{11})/i', $embed_data, $match)) {
        $video_id = $match[1];
        $iframe = sprintf(
          '<iframe src="https://www.youtube.com/embed/%s" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
          esc_attr($video_id)
        );
      } elseif (preg_match('/vimeo\.com\/([0-9]+)/i', $embed_data, $match)) {
        $video_id = $match[1];
        $iframe = sprintf(
          '<iframe src="https://player.vimeo.com/video/%s" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>',
          esc_attr($video_id)
        );
      }
    }
  }

  if (empty($iframe)) {
    return '';
  }

  // Extract YouTube video ID if available
  $video_id = '';
  if (preg_match('/(?:embed\/|youtu\.be\/|v\/|watch\?(?:.*&)?v=|shorts\/)([a-zA-Z0-9_-]{11})/i', $iframe, $matches)) {
    $video_id = $matches[1];
  }

  // Modify src attribute to enforce autoplay, mute, controls=0, playsinline, etc.
  $iframe = preg_replace_callback('/src=["\']([^"\']+)["\']/i', function ($matches) use ($video_id) {
    $url = html_entity_decode($matches[1]);
    $is_youtube = (strpos($url, 'youtube') !== false || strpos($url, 'youtu.be') !== false);
    $is_vimeo   = (strpos($url, 'vimeo') !== false);

    if ($is_youtube) {
      // Use privacy-enhanced domain
      $url = str_replace(
        ['http://www.youtube.com', 'https://www.youtube.com', 'http://youtube.com', 'https://youtube.com'],
        'https://www.youtube-nocookie.com',
        $url
      );
    }

    $params = [
      'autoplay'    => '1',
      'mute'        => '1',
      'controls'    => '0',
      'playsinline' => '1',
    ];

    if ($is_youtube) {
      $params['rel']            = '0';
      $params['modestbranding'] = '1';
      $params['iv_load_policy'] = '3';
      $params['disablekb']      = '1';
      $params['fs']             = '0';
      $params['showinfo']       = '0';
      $params['autohide']       = '1';
      $params['enablejsapi']    = '1';
      $params['vq']             = 'hd1080';
      if (!empty($video_id)) {
        $params['loop']     = '1';
        $params['playlist'] = $video_id;
      }
    }

    if ($is_vimeo) {
      $params['muted']      = '1';
      $params['background'] = '1';
      $params['loop']       = '1';
    }

    if (function_exists('add_query_arg')) {
      $new_url = add_query_arg($params, $url);
    } else {
      $url_parts = explode('?', $url, 2);
      $base = $url_parts[0];
      $existing_query = [];
      if (isset($url_parts[1])) {
        parse_str($url_parts[1], $existing_query);
      }
      $merged = array_merge($existing_query, $params);
      $new_url = $base . '?' . http_build_query($merged);
    }

    return 'src="' . esc_url($new_url) . '"';
  }, $iframe);

  // Ensure iframe has autoplay permission
  if (strpos($iframe, 'allow=') === false) {
    $iframe = str_replace('<iframe', '<iframe allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"', $iframe);
  } else {
    $iframe = preg_replace_callback('/allow=["\']([^"\']*)["\']/i', function ($matches) {
      $allowed = $matches[1];
      if (strpos($allowed, 'autoplay') === false) {
        $allowed = trim($allowed . '; autoplay; encrypted-media');
      }
      return 'allow="' . esc_attr($allowed) . '"';
    }, $iframe);
  }

  // Ensure allowfullscreen is present
  if (strpos($iframe, 'allowfullscreen') === false) {
    $iframe = str_replace('<iframe', '<iframe allowfullscreen', $iframe);
  }

  // Remove small fixed width/height attributes so YouTube renders in full HD resolution
  $iframe = preg_replace('/\s(width|height)=["\'][0-9]+["\']/i', '', $iframe);

  return $iframe;
};

// Section Header fields
$showcase_subtitle = !empty($block_data['showcase_subtitle'])
  ? $block_data['showcase_subtitle']
  : (get_field('showcase_subtitle', $block['id'] ?? null) ?: (get_field('showcase_subtitle') ?: ''));

$showcase_title = !empty($block_data['showcase_title'])
  ? $block_data['showcase_title']
  : (get_field('showcase_title', $block['id'] ?? null) ?: (get_field('showcase_title') ?: ''));

$showcase_description = !empty($block_data['showcase_description'])
  ? $block_data['showcase_description']
  : (get_field('showcase_description', $block['id'] ?? null) ?: (get_field('showcase_description') ?: ''));

// Repeater: showcase_slides with subfields
$raw_slides = get_field('showcase_slides', $block['id'] ?? null)
  ?: (get_field('showcase_slides') ?: []);

// Fallback to block data for editor preview when get_field is empty
if (empty($raw_slides) && !empty($block_data['showcase_slides'])) {
  $count = is_numeric($block_data['showcase_slides']) ? (int) $block_data['showcase_slides'] : 0;
  for ($i = 0; $i < $count; $i++) {
    $raw_slides[] = [
      'showcase_image_video'        => $block_data["showcase_slides_{$i}_showcase_image_video"] ?? ($block_data["showcase_slides_{$i}_desktop_image_video"] ?? ''),
      'desktop_image'               => $block_data["showcase_slides_{$i}_desktop_image"] ?? '',
      'desktop_video'               => $block_data["showcase_slides_{$i}_desktop_video"] ?? '',
      'showcase_image_video_mobile' => $block_data["showcase_slides_{$i}_showcase_image_video_mobile"] ?? ($block_data["showcase_slides_{$i}_mobile_image_video"] ?? ''),
      'mobile_image'                => $block_data["showcase_slides_{$i}_mobile_image"] ?? '',
      'mobile_video'                => $block_data["showcase_slides_{$i}_mobile_video"] ?? '',
    ];
  }
}

$slides = [];
if (!empty($raw_slides) && is_array($raw_slides)) {
  foreach ($raw_slides as $item) {
    $desktop_mode = $get_mode($item['showcase_image_video'] ?? ($item['desktop_image_video'] ?? ''));
    $desktop_img  = $parse_img($item['desktop_image'] ?? '');
    $desktop_vid  = $parse_video_embed($item['desktop_video'] ?? '');

    $mobile_mode  = $get_mode($item['showcase_image_video_mobile'] ?? ($item['mobile_image_video'] ?? ''));
    $mobile_img   = $parse_img($item['mobile_image'] ?? '');
    $mobile_vid   = $parse_video_embed($item['mobile_video'] ?? '');

    // Determine desktop type
    if ($desktop_mode === 'video') {
      $desktop_type = !empty($desktop_vid) ? 'video' : (!empty($desktop_img) ? 'image' : '');
    } elseif ($desktop_mode === 'image') {
      $desktop_type = !empty($desktop_img) ? 'image' : (!empty($desktop_vid) ? 'video' : '');
    } else {
      if (!empty($desktop_vid) && empty($desktop_img)) {
        $desktop_type = 'video';
      } elseif (!empty($desktop_img)) {
        $desktop_type = 'image';
      } elseif (!empty($desktop_vid)) {
        $desktop_type = 'video';
      } else {
        $desktop_type = '';
      }
    }

    // Determine mobile type
    if ($mobile_mode === 'video') {
      $mobile_type = !empty($mobile_vid) ? 'video' : (!empty($mobile_img) ? 'image' : '');
    } elseif ($mobile_mode === 'image') {
      $mobile_type = !empty($mobile_img) ? 'image' : (!empty($mobile_vid) ? 'video' : '');
    } else {
      if (!empty($mobile_vid) && empty($mobile_img)) {
        $mobile_type = 'video';
      } elseif (!empty($mobile_img)) {
        $mobile_type = 'image';
      } elseif (!empty($mobile_vid)) {
        $mobile_type = 'video';
      } else {
        $mobile_type = '';
      }
    }

    // Fall back to desktop media for mobile device if separate mobile media is not set
    if (empty($mobile_type)) {
      $mobile_type  = $desktop_type;
      $mobile_img   = $desktop_img;
      $mobile_vid   = $desktop_vid;
    }

    if (!empty($desktop_type) || !empty($mobile_type)) {
      $slides[] = [
        'desktop_type'  => $desktop_type,
        'desktop_image' => $desktop_img,
        'desktop_video' => $desktop_vid,
        'mobile_type'   => $mobile_type,
        'mobile_image'  => $mobile_img,
        'mobile_video'  => $mobile_vid,
        'desktop'       => $desktop_img,
        'mobile'        => $mobile_img,
      ];
    }
  }
}

$block_id = !empty($block['anchor']) ? $block['anchor'] : ('showcase-' . ($block['id'] ?? uniqid()));
$custom_class = !empty($block['className']) ? ' ' . $block['className'] : '';
?>
<section id="<?php echo esc_attr($block_id); ?>"
  class="mm-section block-showcase-section<?php echo esc_attr($custom_class); ?>" data-mm-showcase>
  <div class="mm-wrap">
    <?php if (!empty($showcase_subtitle) || !empty($showcase_title) || !empty($showcase_description)): ?>
      <div class="mm-showcase-header">
        <?php if (!empty($showcase_subtitle)): ?>
          <p class="mm-eyebrow"><?php echo esc_html($showcase_subtitle); ?></p>
        <?php endif; ?>
        <?php if (!empty($showcase_title)): ?>
          <h2 class="mm-h2"><?php echo esc_html($showcase_title); ?></h2>
        <?php endif; ?>
        <?php if (!empty($showcase_description)): ?>
          <p class="mm-lede"><?php echo wp_kses_post($showcase_description); ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="mm-showcase-stage" data-showcase-stage>
      <!-- Desktop Mockup (Background Window) -->
      <div class="mm-mockup-desktop">


        <div class="mm-desktop-screen" data-desktop-screen>
          <div class="mm-slides-track" data-desktop-track>
            <?php foreach ($slides as $index => $slide): ?>
              <div class="mm-slide mm-desktop-slide<?php echo $index === 0 ? ' is-active' : ''; ?>"
                data-slide-index="<?php echo esc_attr($index); ?>">
                <?php if (($slide['desktop_type'] ?? '') === 'video' && !empty($slide['desktop_video'])): ?>
                  <div class="mm-slide-media mm-slide-video">
                    <?php echo $slide['desktop_video']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <div class="mm-video-shield" aria-hidden="true"></div>
                    <button type="button" class="mm-video-sound-toggle is-muted" data-sound-toggle aria-label="Unmute video">
                      <svg class="mm-sound-icon mm-icon-muted" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                        <line x1="23" y1="9" x2="17" y2="15"></line>
                        <line x1="17" y1="9" x2="23" y2="15"></line>
                      </svg>
                      <svg class="mm-sound-icon mm-icon-unmuted" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 5"></polygon>
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                      </svg>
                    </button>
                  </div>
                <?php elseif (!empty($slide['desktop_image']) || !empty($slide['desktop'])): ?>
                  <?php $d_img = !empty($slide['desktop_image']) ? $slide['desktop_image'] : $slide['desktop']; ?>
                  <img src="<?php echo esc_url($d_img); ?>"
                    alt="Desktop showcase <?php echo esc_attr($index + 1); ?>"
                    loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Mobile Mockup (Foreground Overlapping) -->
      <div class="mm-mockup-mobile">
        <div class="mm-mobile-bezel">
          <div class="mm-mobile-notch" aria-hidden="true">
            <span class="notch-lens"></span>
            <span class="notch-sensor"></span>
          </div>

          <div class="mm-mobile-screen" data-mobile-screen>
            <div class="mm-slides-track" data-mobile-track>
              <?php foreach ($slides as $index => $slide): ?>
                <div class="mm-slide mm-mobile-slide<?php echo $index === 0 ? ' is-active' : ''; ?>"
                  data-slide-index="<?php echo esc_attr($index); ?>">
                  <?php if (($slide['mobile_type'] ?? '') === 'video' && !empty($slide['mobile_video'])): ?>
                    <div class="mm-slide-media mm-slide-video">
                      <?php echo $slide['mobile_video']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                      <div class="mm-video-shield" aria-hidden="true"></div>
                      <button type="button" class="mm-video-sound-toggle is-muted" data-sound-toggle aria-label="Unmute video">
                        <svg class="mm-sound-icon mm-icon-muted" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                          <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                          <line x1="23" y1="9" x2="17" y2="15"></line>
                          <line x1="17" y1="9" x2="23" y2="15"></line>
                        </svg>
                        <svg class="mm-sound-icon mm-icon-unmuted" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                          <polygon points="11 5 6 9 2 9 2 15 6 15 11 5"></polygon>
                          <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                        </svg>
                      </button>
                    </div>
                  <?php elseif (!empty($slide['mobile_image']) || !empty($slide['mobile'])): ?>
                    <?php $m_img = !empty($slide['mobile_image']) ? $slide['mobile_image'] : $slide['mobile']; ?>
                    <img src="<?php echo esc_url($m_img); ?>"
                      alt="Mobile showcase <?php echo esc_attr($index + 1); ?>"
                      loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="mm-mobile-home-indicator" aria-hidden="true"></div>
        </div>
      </div>
    </div>

    <!-- Controls / Navigation Indicators (Commented out per user request) -->
    <?php /* if (count($slides) > 1): ?>
      <div class="mm-showcase-controls" data-showcase-controls>
        <button type="button" class="mm-showcase-nav-btn prev" data-showcase-prev aria-label="Previous slide">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>

        <div class="mm-showcase-dots" data-showcase-dots>
          <?php foreach ($slides as $index => $slide): ?>
            <button type="button" class="mm-dot-indicator<?php echo $index === 0 ? ' is-active' : ''; ?>"
              data-slide-to="<?php echo esc_attr($index); ?>" aria-label="Go to slide <?php echo esc_attr($index + 1); ?>">
              <span class="dot-progress"></span>
            </button>
          <?php endforeach; ?>
        </div>

        <button type="button" class="mm-showcase-nav-btn next" data-showcase-next aria-label="Next slide">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>
    <?php endif; */ ?>
  </div>
</section>