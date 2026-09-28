<?php

/**
 * Theme Support Functions & ACF Configuration
 *
 * This file handles the registration of ACF blocks and theme constants.
 * It is designed to be portable between themes by using generic constants.
 */

// 1. Define Global Theme Constants
//    Uses THEME_BLOCK_PREFIX from functions.php if available, otherwise defaults to 'theme'.
$theme_prefix = defined('THEME_BLOCK_PREFIX') ? THEME_BLOCK_PREFIX : 'theme';

// Unique String for Block Naming (e.g. 'christie-hero')
if (!defined('THEME_ACF_PREFIX')) {
    define('THEME_ACF_PREFIX', $theme_prefix);
}

// Text Domain for Translations (same as prefix for simplicity)
if (!defined('THEME_TEXT_DOMAIN')) {
    define('THEME_TEXT_DOMAIN', $theme_prefix);
}

// Paths
if (!defined('THEME_BLOCKS_DIR')) {
    define('THEME_BLOCKS_DIR', get_theme_file_path('/inc/blocks/'));
}

if (!defined('THEME_ASSETS_DIR')) {
    define('THEME_ASSETS_DIR', get_theme_file_path('/assets/'));
}

if (!defined('THEME_ASSETS_URI')) {
    define('THEME_ASSETS_URI', get_template_directory_uri() . '/assets/');
}

// Legacy Aliases (Optional: maintain backward compat if needed, but we are refactoring)
// define('BERJE_PREFIX', THEME_ACF_PREFIX); // Uncomment if legacy code persists


/**
 * Register ACF Blocks Automatically
 */
$metamint_register_acf_blocks = function () {
    static $registered = false;
    if ($registered) {
        return;
    }
    if (!function_exists('acf_register_block_type')) {
        return;
    }
    if (!defined('THEME_BLOCKS_DIR') || !is_dir(THEME_BLOCKS_DIR)) {
        return;
    }
    $registered = true;

    $block_folders_list = [];
    $categories_found = [];

    // Helper to normalize category slugs
    $normalize_cat_slug = function ($raw) {
        $slug = strtolower(trim($raw));
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug ?: 'theme-blocks';
    };

    // Generic Icon (Purple Square)
    $icon_path = get_theme_file_path('/assets/images/block-icon.svg');
    if (file_exists($icon_path)) {
        $custom_logo_svg = file_get_contents($icon_path);
    } else {
        // Fallback if file not found
        $custom_logo_svg = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="20" height="20" fill="#41006E"/></svg>';
    }

    // Look for blocks in 'inc/blocks/'
    $root_items = glob(rtrim(THEME_BLOCKS_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);

    foreach ($root_items as $item_path) {
        $folder_name = basename($item_path);

        // Case 1: 'categories' subfolder structure
        if ($folder_name === 'categories' && is_dir($item_path)) {
            $category_dirs = glob($item_path . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
            foreach ($category_dirs as $cat_dir) {
                $cat_folder = basename($cat_dir);

                // Check if this folder itself is a block (contains {folder}.php or index.php)
                $direct_block_file = $cat_dir . DIRECTORY_SEPARATOR . $cat_folder . '.php';
                $direct_index_file = $cat_dir . DIRECTORY_SEPARATOR . 'index.php';
                $child_block_dirs  = glob($cat_dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);

                if (file_exists($direct_block_file) || (file_exists($direct_index_file) && empty($child_block_dirs))) {
                    $block_folders_list[] = [
                        'dir'      => $cat_dir,
                        'category' => null,
                    ];
                    continue;
                }

                $cat_raw   = (strpos($cat_folder, 'category-') === 0) ? substr($cat_folder, 9) : $cat_folder;
                $cat_slug  = $normalize_cat_slug($cat_raw);
                $cat_title = ucwords(THEME_ACF_PREFIX) . ' - ' . ucwords(str_replace('-', ' ', $cat_raw));

                // Check index.php for custom Category Name
                $index_file = $cat_dir . DIRECTORY_SEPARATOR . 'index.php';
                if (file_exists($index_file) && is_readable($index_file)) {
                    $index_content = file_get_contents($index_file, false, null, 0, 4096);
                    if (preg_match('/Category Name:\s*(.+)/i', $index_content, $m)) {
                        $raw_label = trim($m[1]);
                        $raw_label = str_replace('{prefix}', ucwords(THEME_ACF_PREFIX), $raw_label);
                        $raw_label = str_replace('{PREFIX}', strtoupper(THEME_ACF_PREFIX), $raw_label);
                        if ($raw_label !== '') {
                            $cat_title = $raw_label;
                        }
                    }
                }

                $categories_found[$cat_slug] = $cat_title;

                // Scan blocks inside this category
                foreach ($child_block_dirs as $block_dir) {
                    $block_folders_list[] = [
                        'dir'      => $block_dir,
                        'category' => $cat_slug,
                    ];
                }
            }
            continue;
        }

        // Case 2: Direct category folders (starts with 'category-') in root
        if (strpos($folder_name, 'category-') === 0) {
            $cat_raw = substr($folder_name, 9);
            $cat_slug = $normalize_cat_slug($cat_raw);
            $cat_title = ucwords(THEME_ACF_PREFIX) . ' - ' . ucwords(str_replace('-', ' ', $cat_raw));

            $index_file = $item_path . DIRECTORY_SEPARATOR . 'index.php';
            if (file_exists($index_file) && is_readable($index_file)) {
                $index_content = file_get_contents($index_file, false, null, 0, 4096);
                if (preg_match('/Category Name:\s*(.+)/i', $index_content, $m)) {
                    $raw_label = trim($m[1]);
                    $raw_label = str_replace('{prefix}', ucwords(THEME_ACF_PREFIX), $raw_label);
                    $raw_label = str_replace('{PREFIX}', strtoupper(THEME_ACF_PREFIX), $raw_label);
                    if ($raw_label !== '') {
                        $cat_title = $raw_label;
                    }
                }
            }

            $categories_found[$cat_slug] = $cat_title;

            foreach (glob($item_path . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $block_dir) {
                $block_folders_list[] = [
                    'dir' => $block_dir,
                    'category' => $cat_slug,
                ];
            }
            continue;
        }

        // Case 3: Loose blocks in root
        $block_folders_list[] = [
            'dir' => $item_path,
            'category' => null,
        ];
    }

    // Register Found Blocks
    foreach ($block_folders_list as $block_data) {
        $block_dir = $block_data['dir'];
        $block_cat = $block_data['category'];

        $raw_folder_name = basename($block_dir);
        $block_name = sanitize_title($raw_folder_name);
        $block_file = $block_dir . DIRECTORY_SEPARATOR . $raw_folder_name . '.php';

        if (!file_exists($block_file)) {
            $norm_file = $block_dir . DIRECTORY_SEPARATOR . $block_name . '.php';
            if (file_exists($norm_file)) {
                $block_file = $norm_file;
            } else {
                $alt_file = $block_dir . DIRECTORY_SEPARATOR . 'index.php';
                if (file_exists($alt_file)) {
                    $block_file = $alt_file;
                } else {
                    continue;
                }
            }
        }

        $content = file_get_contents($block_file, false, null, 0, 4096);

        // defaults
        $title = ucwords(str_replace('-', ' ', $block_name));
        $icon = $custom_logo_svg;
        $keywords = [THEME_ACF_PREFIX];

        // parse header keys
        if (preg_match('/Block Name:\s*(.+)/i', $content, $m)) {
            $title = trim($m[1]);
        }
        if (preg_match('/Block Category:\s*(.+)/i', $content, $m)) {
            $cat_custom_raw = trim($m[1]);
            $cat_custom_slug = $normalize_cat_slug($cat_custom_raw);
            $categories_found[$cat_custom_slug] = ucwords(str_replace('-', ' ', THEME_ACF_PREFIX)) . ' - ' . ucwords(str_replace('-', ' ', $cat_custom_raw));
            $block_cat = $cat_custom_slug;
        }
        if (preg_match('/Block Icon:\s*(.+)/i', $content, $m)) {
            $icon = trim($m[1]);
        }
        if (preg_match('/Block Keywords:\s*(.+)/i', $content, $m)) {
            $keywords = array_map('trim', explode(',', $m[1]));
        }

        $acf_category = THEME_ACF_PREFIX;
        if ($block_cat && isset($categories_found[$block_cat])) {
            $acf_category = $block_cat;
        }

        // Check for Preview Image
        $preview_image_url = '';
        $preview_files = ['preview.jpg', 'preview.jpeg', 'preview.png'];
        foreach ($preview_files as $p_file) {
            if (file_exists($block_dir . DIRECTORY_SEPARATOR . $p_file)) {
                $preview_image_url = str_replace(get_template_directory(), get_template_directory_uri(), $block_dir . DIRECTORY_SEPARATOR . $p_file);
                break;
            }
        }

        $block_mode = 'edit';
        if (preg_match('/Block Mode:\s*(.+)/i', $content, $m)) {
            $block_mode = strtolower(trim($m[1]));
        }

        $block_args = [
            'name' => THEME_ACF_PREFIX . '-' . $block_name,
            'title' => __($title, THEME_TEXT_DOMAIN),
            'render_template' => $block_file,
            'category' => $acf_category,
            'icon' => $icon,
            'keywords' => $keywords,
            'mode' => $block_mode,
            'supports' => [
                'mode' => true,
                'align' => true,
                'anchor' => true,
            ],
        ];

        if ($preview_image_url) {
            $block_args['example'] = [
                'attributes' => [
                    'mode' => 'preview',
                    'data' => [
                        '_preview_image_url' => $preview_image_url,
                        'is_preview' => true
                    ]
                ]
            ];
        }

        // Auto-enqueue Assets
        $css_file = $block_dir . DIRECTORY_SEPARATOR . $block_name . '.css';
        if (file_exists($css_file)) {
            $block_args['enqueue_style'] = str_replace(get_template_directory(), get_template_directory_uri(), $css_file);
        }

        $js_file = $block_dir . DIRECTORY_SEPARATOR . $block_name . '.js';
        if (file_exists($js_file)) {
            $block_args['enqueue_script'] = str_replace(get_template_directory(), get_template_directory_uri(), $js_file);
        }

        acf_register_block_type($block_args);
    }

    // Register Categories (prioritize and show at the TOP of the inserter)
    add_filter('block_categories_all', function ($cats, $post) use ($categories_found) {
        $custom_cats = [];
        $existing_slugs = [];

        // 1. Add Discovered Categories first (e.g. "Metamint Main - Banner", etc.)
        foreach ($categories_found as $slug => $label) {
            $custom_cats[] = [
                'slug'  => $slug,
                'title' => $label,
                'icon'  => null,
            ];
            $existing_slugs[] = $slug;
        }

        // 2. Add Default Theme Category (e.g. "Metamint Main Elements")
        $default_slug = THEME_ACF_PREFIX;
        if (!in_array($default_slug, $existing_slugs)) {
            $custom_cats[] = [
                'slug'  => $default_slug,
                'title' => ucwords(str_replace('-', ' ', THEME_ACF_PREFIX)) . ' Elements',
                'icon'  => null,
            ];
            $existing_slugs[] = $default_slug;
        }

        // 3. Filter out our custom slugs from existing $cats if already present
        $filtered_existing = array_values(array_filter((array) $cats, function ($c) use ($existing_slugs) {
            return !in_array($c['slug'] ?? '', $existing_slugs);
        }));

        // 4. Prepend custom categories to the very top
        return array_merge($custom_cats, $filtered_existing);
    }, 10, 2);

};

add_action('acf/init', $metamint_register_acf_blocks);
add_action('init', $metamint_register_acf_blocks, 20);
