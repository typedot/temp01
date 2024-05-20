<?php

/**
 * functions.php
 *
 * The theme's functions and definitions.
 */
/**
 * 1.0 - Define constants. Current Version number & Theme Name.
 */
define('SEOCIFY_THEME', 'Seocify WordPress Theme');
define('SEOCIFY_VERSION', '1.0');

define('SEOCIFY_THEMEROOT', get_template_directory_uri());
define('SEOCIFY_THEMEROOT_DIR', get_parent_theme_file_path());
define('SEOCIFY_IMAGES', SEOCIFY_THEMEROOT . '/assets/images');
define('SEOCIFY_IMAGES_DIR', SEOCIFY_THEMEROOT_DIR . '/assets/images');
define('SEOCIFY_IMAGES_URI', SEOCIFY_THEMEROOT . '/assets/images');
define('SEOCIFY_CSS', SEOCIFY_THEMEROOT . '/assets/css');
define('SEOCIFY_CSS_DIR', SEOCIFY_THEMEROOT_DIR . '/assets/css');
define('SEOCIFY_SCRIPTS', SEOCIFY_THEMEROOT . '/assets/js');
define('SEOCIFY_SCRIPTS_DIR', SEOCIFY_THEMEROOT_DIR . '/assets/js');
define('SEOCIFY_PHPSCRIPTS', SEOCIFY_THEMEROOT . '/assets/php');
define('SEOCIFY_PHPSCRIPTS_DIR', SEOCIFY_THEMEROOT_DIR . '/assets/php');
define('SEOCIFY_INC', SEOCIFY_THEMEROOT_DIR . '/inc');
define('SEOCIFY_CUSTOMIZER_DIR', SEOCIFY_INC . '/customizer/');
define('SEOCIFY_SHORTCODE_DIR', SEOCIFY_INC . '/shortcode/');
define('SEOCIFY_SHORTCODE_DIR_STYLE', SEOCIFY_INC . '/shortcode/style');
define('SEOCIFY_REMOTE_CONTENT', esc_url('http://xpeedstudio.net/demo-content/seocify'));
define('SEOCIFY_PLUGINS_DIR', SEOCIFY_INC . '/includes/plugins');
define('SEOCIFY_REMOTE_URL', esc_url('https://wp.xpeedstudio.com/demo-content/seocify/plugins'));

/**
 * ----------------------------------------------------------------------------------------
 * 3.0 - Set up the content width value based on the theme's design.
 * ----------------------------------------------------------------------------------------
 */
if (!isset($content_width)) {
    $content_width = 800;
}


/**
 * ----------------------------------------------------------------------------------------
 * 4.0 - Set up theme default and register various supported features.
 * ----------------------------------------------------------------------------------------
 */
if (!function_exists('seocify_setup')) {

    function seocify_setup()
    {
        /**
         * Make the theme available for translation.
         */
        load_theme_textdomain( 'seocify', get_template_directory() . '/languages' );
		$locale		 = get_locale();
		$locale_file = get_template_directory() . "/languages/$locale.php";

		if ( is_readable( $locale_file ) ) {
			require_once( $locale_file );
		}

        /**
         * Add support for post formats.
         */
        add_theme_support('post-formats', array('standard', 'gallery', 'video', 'audio')
        );

        /**
         * Add support for automatic feed links.
         */
        add_theme_support('automatic-feed-links');

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support('woocommerce');
        add_theme_support('title-tag');
        /**
         * Add support for post thumbnails.
         */
        add_theme_support('post-thumbnails');
        set_post_thumbnail_size(750, 465, array('center', 'center')); // Hard crop center center

        /**
         * Register nav menus.
         */
        register_nav_menus(
            array(
                'primary' => esc_html__('Primary Menu', 'seocify'),
            )
        );

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support('html5', array(
            'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
        ));
    }

    add_action('after_setup_theme', 'seocify_setup');
}

/**
 * ----------------------------------------------------------------------------------------
 * 7.0 - theme INC.
 * ----------------------------------------------------------------------------------------
 */
include_once get_template_directory() . '/inc/init.php';
include_once get_template_directory() . '/inc/mav-menu-custom-fields.php';

add_action( 'admin_menu', 'seocify_remove_theme_settings', 999 );
function seocify_remove_theme_settings() {
    remove_submenu_page( 'themes.php', 'fw-settings' );
}