<?php

/**
 * Theme Block Helper Functions
 *
 * Encapsulates block logic to allow easy portability between themes.
 * Uses THEME_BLOCK_PREFIX defined in functions.php.
 */

class Theme_Blocks
{
    public static $styles = [];

    /**
     * Get Block Options from ACF Fields
     */
    public static function get_options()
    {
        //  Fetch ACF values from Option Tab
        $wrap_background = get_field('wrap_background');
        $wrap_height_parallax = get_field('wrap_height_parallax');
        $height = $wrap_height_parallax['height'] ?? '';

        $spacing = get_field('spacing');

        $data = [];

        // =====================
        //  Background Options
        // =====================
        $data['wrap_size'] = $wrap_background['wrap_size'] ?? '';
        $data['background'] = $wrap_background['background'] ?? 'no-bg-img';
        $data['background_color'] = $wrap_background['background_color'] ?? '';
        $data['background_image'] = $wrap_background['background_image'] ?? '';
        $data['background_video'] = $wrap_background['background_video'] ?? '';

        // =====================
        //  Section Height and Parallax
        // =====================
        $data['section_height'] = $height ?? 100;



        // =====================
        //  Desktop Spacing
        // =====================
        $desktop = $spacing['desktop'] ?? [];
        $data['desktop_margin_top'] = $desktop['margin_top'] ?? '';
        $data['desktop_margin_bottom'] = $desktop['margin_bottom'] ?? '';
        $data['desktop_padding_top'] = $desktop['padding_top'] ?? '';
        $data['desktop_padding_bottom'] = $desktop['padding_bottom'] ?? '';

        // =====================
        //  Mobile Spacing
        // =====================
        $mobile = $spacing['mobile'] ?? [];
        $data['mobile_margin_top'] = $mobile['margin_top'] ?? '';
        $data['mobile_margin_bottom'] = $mobile['margin_bottom'] ?? '';
        $data['mobile_padding_top'] = $mobile['padding_top'] ?? '';
        $data['mobile_padding_bottom'] = $mobile['padding_bottom'] ?? '';

        $data['mobile_enabled'] = !empty($mobile['status']) ||
            ($data['mobile_margin_top'] !== '') ||
            ($data['mobile_margin_bottom'] !== '') ||
            ($data['mobile_padding_top'] !== '') ||
            ($data['mobile_padding_bottom'] !== '');

        // =====================
        // Wrapper Class
        // =====================
        $wrap_class = '';

        if ($data['wrap_size'] === 'full') {
            $wrap_class = 'wide';
        } elseif ($data['wrap_size'] === 'narrow') {
            $wrap_class = 'narrow';
        }
        $data['wrap_class'] = trim($wrap_class);

        return $data;
    }

    /**
     * Render Block Options and Styles
     */
    public static function render_options($block, $slug_suffix = 'block')
    {
        $opts = self::get_options() ?: [];

        // Use global prefix if defined, otherwise default
        $prefix = defined('THEME_BLOCK_PREFIX') ? THEME_BLOCK_PREFIX : 'theme';
        $block_id = $prefix . '_' . $slug_suffix . '_' . uniqid();

        // ===========================
        // Build Desktop Styles
        // ===========================
        $desktop_styles = [];

        if (!empty($opts['section_height'])) {
            $desktop_styles[] = "min-height:{$opts['section_height']}%;";
        }

        // Check if we need to fix subpixel gaps (Background Color present)
        $fix_gap = ($opts['background'] === 'color' && !empty($opts['background_color']));

        if ($opts['desktop_margin_top'] !== '') {
            if ($fix_gap) {
                $desktop_styles[] = "margin-top:calc({$opts['desktop_margin_top']}px - 1px);";
            } else {
                $desktop_styles[] = "margin-top:{$opts['desktop_margin_top']}px;";
            }
        }

        if ($opts['desktop_margin_bottom'] !== '') {
            $desktop_styles[] = "margin-bottom:{$opts['desktop_margin_bottom']}px;";
        }

        if ($opts['desktop_padding_top'] !== '') {
            if ($fix_gap) {
                $desktop_styles[] = "padding-top:calc({$opts['desktop_padding_top']}px + 1px);";
            } else {
                $desktop_styles[] = "padding-top:{$opts['desktop_padding_top']}px;";
            }
        } elseif ($fix_gap && $opts['desktop_margin_top'] !== '') {
            // If padding is empty but we adjusted margin, we need to compensate padding
            $desktop_styles[] = "padding-top:1px;";
        }

        if ($opts['desktop_padding_bottom'] !== '') {
            $desktop_styles[] = "padding-bottom:{$opts['desktop_padding_bottom']}px;";
        }

        // Background Handling
        if ($opts['background'] === 'color' && !empty($opts['background_color'])) {
            $desktop_styles[] = "background-color:{$opts['background_color']};";
        } elseif ($opts['background'] === 'image' && !empty($opts['background_image'])) {
            $bg_url = is_array($opts['background_image'])
                ? $opts['background_image']['url']
                : $opts['background_image'];
            $desktop_styles[] = "background-image:url({$bg_url});";
        }

        // ===========================
        // Build Mobile Styles
        // ===========================

        $style_output = '';

        $style_output .= '#' . esc_attr($block_id) . ' { ' . implode(' ', $desktop_styles) . ' } ';

        if (!empty($opts['mobile_enabled'])) {
            $style_output .= '@media (max-width: 768px) {';
            $style_output .= '#' . esc_attr($block_id) . ' {';
            if ($opts['mobile_margin_top'] !== '') {
                $style_output .= $fix_gap ? "margin-top:calc({$opts['mobile_margin_top']}px - 1px);" : "margin-top:{$opts['mobile_margin_top']}px;";
            }

            $style_output .= ($opts['mobile_margin_bottom'] !== '') ? "margin-bottom:{$opts['mobile_margin_bottom']}px;" : '';

            if ($opts['mobile_padding_top'] !== '') {
                $style_output .= $fix_gap ? "padding-top:calc({$opts['mobile_padding_top']}px + 1px);" : "padding-top:{$opts['mobile_padding_top']}px;";
            } elseif ($fix_gap && $opts['mobile_margin_top'] !== '') {
                $style_output .= "padding-top:1px;";
            }
            $style_output .= ($opts['mobile_padding_bottom'] !== '') ? "padding-bottom:{$opts['mobile_padding_bottom']}px;" : '';
            $style_output .= '}';
            $style_output .= '}';
        }



        return [
            'id' => $block_id,
            'class' => $opts['wrap_class'] ?? '',
            'style' => $style_output, // raw CSS only
            'opts' => $opts,

        ];
    }

    /**
     * Collect Block Styles
     */
    public static function add_style($css)
    {
        if (empty($css)) {
            return;
        }

        self::$styles[] = $css;
    }

    /**
     * Output Collected Styles in Footer
     */
    public static function output_styles()
    {
        if (empty(self::$styles)) {
            return;
        }

        echo '<style>' . implode("\n", self::$styles) . '</style>';
    }
}

// Hook to output styles in footer
add_action('wp_footer', ['Theme_Blocks', 'output_styles']);
