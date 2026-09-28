<?php

if (!defined('ABSPATH')) {
    exit;
}


global $theme_loaded_blocks;
$theme_loaded_blocks = [];

add_filter('pre_render_block', function ($pre_render, $block) {
    global $theme_loaded_blocks;

    if (!empty($block['blockName'])) {
        $theme_loaded_blocks[] = $block['blockName'];
    }

    return $pre_render;
}, 10, 2);




add_action('wp_enqueue_scripts', 'theme_enqueue_assets', 5);
add_action('admin_enqueue_scripts', 'theme_enqueue_assets', 5);

function theme_enqueue_assets()
{

    // Ensure constants are defined (fallback)
    $prefix = defined('THEME_ACF_PREFIX') ? THEME_ACF_PREFIX : 'theme';
    $assets_dir = defined('THEME_ASSETS_DIR') ? THEME_ASSETS_DIR : get_theme_file_path('/assets/');
    $assets_uri = defined('THEME_ASSETS_URI') ? THEME_ASSETS_URI : get_template_directory_uri() . '/assets/';

    /**
     * ----------------------------
     * Files that should load in BOTH
     * frontend + backend
     * ----------------------------
     */
    $global_styles = [
        //  'assets/css/header-footer.css',
        'inc/blocks/categories/category-banner/hero-default/hero-default.css'
    ];

    $global_scripts = [
        'mega-menu.js',
        'inc/blocks/categories/category-banner/hero-default/hero-default.js'
    ];

    /**
     * ----------------------------
     * Frontend-only files
     * ----------------------------
     */
    if (!is_admin()) {
        $frontend_styles = [
            'style.css',
            'assets/css/header-footer.css',
            'assets/css/main-style.css',
            'assets/css/slick.css',
            'assets/css/fancybox.css',
            'assets/css/responsive.css',
        ];

        $frontend_scripts = [
            'lenis.min.js',
            'slick.min.js',
            'mega-menu.js',
            'gsap.min.js',
            'ScrollTrigger.min.js',
            'MotionPathPlugin.min.js',
            'Flip.min.js',
            'fancybox.umd.js',
            'scripts.js'
        ];
    } else {
        $frontend_styles = [
            'assets/css/admin-style.css',
        ];
        $frontend_scripts = [
            'assets/js/admin-block-tabs.js',
        ];
    }

    /** Merge both groups */
    $styles = array_merge($global_styles, $frontend_styles);
    $scripts = array_merge($global_scripts, $frontend_scripts);


    /**
     * ----------------------------
     * Enqueue Styles
     * ----------------------------
     */
    foreach ($styles as $style) {
        $handle = $prefix . '-' . basename($style, '.css');
        $path = get_template_directory() . '/' . $style;
        $uri = get_template_directory_uri() . '/' . $style;

        if (file_exists($path)) {
            wp_enqueue_style($handle, $uri, [], filemtime($path));
        }
    }

    /**
     * ----------------------------
     * Enqueue Scripts
     * ----------------------------
     */
    foreach ($scripts as $script) {
        $handle = $prefix . '-' . basename($script, '.js');

        // LOGIC FIX:
        // If script string contains '/', assume it is a path relative to theme root.
        // If it is just a filename (e.g. 'scripts.js'), assume it is in assets/js/
        if (strpos($script, '/') !== false) {
            $path = get_template_directory() . '/' . $script;
            $uri = get_template_directory_uri() . '/' . $script;
        } else {
            $path = $assets_dir . 'js/' . $script;
            $uri = $assets_uri . 'js/' . $script;
        }

        if (file_exists($path)) {
            wp_enqueue_script($handle, $uri, ['jquery'], filemtime($path), true);
        }
    }

}



/**
 * ----------------------------------------------------------------------------
 *  Enqueue ACF block assets only if rendered on page
 * ----------------------------------------------------------------------------
 */

add_action('wp_enqueue_scripts', function () {

    if (!function_exists('acf_get_block_types')) {
        return;
    }

    global $theme_loaded_blocks;

    if (empty($theme_loaded_blocks)) {
        return;
    }

    $blocks = acf_get_block_types();
    $prefix = defined('THEME_ACF_PREFIX') ? THEME_ACF_PREFIX : 'theme';

    foreach ($blocks as $block_name => $block) {

        // Only match prefixed blocks
        if (strpos($block_name, $prefix . '-') === false) {
            continue;
        }

        // Load only if block was rendered
        if (!in_array($block_name, $theme_loaded_blocks)) {
            continue;
        }

        if (empty($block['render_template'])) {
            continue;
        }

        // Extract short name
        $short = str_replace(['acf/', $prefix . '-'], '', $block_name);

        // Block directory
        $dir = dirname($block['render_template']);

        /**
         * Block CSS
         */
        $css = $dir . '/' . $short . '.css';
        if (file_exists($css)) {
            wp_enqueue_style(
                $prefix . '-' . $short . '-style',
                str_replace(get_template_directory(), get_template_directory_uri(), $css),
                [],
                filemtime($css)
            );
        }

        /**
         * Block JS
         */
        $js = $dir . '/' . $short . '.js';
        if (file_exists($js)) {
            wp_enqueue_script(
                $prefix . '-' . $short . '-script',
                str_replace(get_template_directory(), get_template_directory_uri(), $js),
                ['jquery'],
                filemtime($js),
                true
            );
        }
    }

}, 20);
