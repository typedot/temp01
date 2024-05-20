<?php

if ( !defined( 'ABSPATH' ) )
	die( 'Direct access forbidden.' );
/**
 * Enqueue all theme scripts and styles
 *
 * ** REGISTERING THEME ASSETS
 * ** ------------------------------------ */
/**
 * Enqueue styles.
 */
if ( !is_admin() ) {
    wp_enqueue_style( 'seocify-fonts', seocify_google_fonts_url(), null, SEOCIFY_VERSION );
    wp_enqueue_style( 'bootstrap', SEOCIFY_CSS . '/bootstrap.min.css', null, SEOCIFY_VERSION );
    wp_style_add_data( 'bootstrap', 'rtl', 'replace' );

    wp_enqueue_style( 'iconfont', SEOCIFY_CSS . '/iconfont.css', null, SEOCIFY_VERSION );
    wp_enqueue_style( 'magnific-popup', SEOCIFY_CSS . '/magnific-popup.css', null, SEOCIFY_VERSION );
    wp_enqueue_style( 'animate', SEOCIFY_CSS . '/animate.css', null, SEOCIFY_VERSION );
    wp_enqueue_style( 'font-awesome', SEOCIFY_CSS . '/font-awesome.min.css', null, SEOCIFY_VERSION );
    wp_enqueue_style( 'owl-carousel', SEOCIFY_CSS . '/owl.carousel.min.css', null, SEOCIFY_VERSION );
    wp_enqueue_style( 'owl-theme-default', SEOCIFY_CSS . '/owl.theme.default.min.css', null, SEOCIFY_VERSION );
    wp_enqueue_style( 'seocify-navigation', SEOCIFY_CSS . '/navigation.min.css', null, SEOCIFY_VERSION );
    wp_enqueue_style( 'jquery-ui-structure', SEOCIFY_CSS . '/jquery-ui.structure.min.css', null, SEOCIFY_VERSION );
    wp_enqueue_style( 'jquery-ui-theme', SEOCIFY_CSS . '/jquery-ui.theme.min.css', null, SEOCIFY_VERSION );
    wp_enqueue_style( 'seocify-style', SEOCIFY_CSS . '/style.css', null, SEOCIFY_VERSION );
    wp_style_add_data( 'seocify-style', 'rtl', 'replace' );
    wp_enqueue_style( 'seocify-responsive', SEOCIFY_CSS . '/responsive.css', null, SEOCIFY_VERSION );
}



/**
 * Enqueue scripts.
 */
if ( !is_admin() ) {

	wp_enqueue_script( 'navigation', SEOCIFY_SCRIPTS . '/navigation.min.js', array( 'jquery' ), SEOCIFY_VERSION, true );
	wp_enqueue_script( 'wow', SEOCIFY_SCRIPTS . '/wow.min.js', array( 'jquery' ), SEOCIFY_VERSION, true );
	//Bootstrap Main JS
	wp_enqueue_script( 'Popper', SEOCIFY_SCRIPTS . '/Popper.js', array( 'jquery' ), SEOCIFY_VERSION, true );
    if ( is_rtl() ) {
        wp_enqueue_script( 'bootstrap-js', SEOCIFY_SCRIPTS . '/bootstrap.min-rtl.js', array( 'jquery' ), SEOCIFY_VERSION, true );
    } else {
        wp_enqueue_script( 'bootstrap-js', SEOCIFY_SCRIPTS . '/bootstrap.min.js', array( 'jquery' ), SEOCIFY_VERSION, true );
    }


    wp_enqueue_script('magnific-popup', SEOCIFY_SCRIPTS . '/jquery.magnific-popup.min.js', array( 'jquery' ), SEOCIFY_VERSION, true );
    wp_enqueue_script( 'owl-carousel', SEOCIFY_SCRIPTS . '/owl.carousel.min.js', array( 'jquery' ), SEOCIFY_VERSION, true );

    

	wp_enqueue_script( 'ajaxchimp', SEOCIFY_SCRIPTS . '/jquery.ajaxchimp.min.js', array( 'jquery' ), SEOCIFY_VERSION, true );
	wp_enqueue_script( 'waypoints', SEOCIFY_SCRIPTS . '/jquery.waypoints.min.js', array( 'jquery' ), SEOCIFY_VERSION, true );
	wp_enqueue_script( 'isotope-pkgd', SEOCIFY_SCRIPTS . '/isotope.pkgd.min.js', array( 'jquery' ), SEOCIFY_VERSION, true );
	wp_enqueue_script( 'scrollax', SEOCIFY_SCRIPTS . '/scrollax.js', array( 'jquery' ), SEOCIFY_VERSION, true );
    wp_enqueue_script( 'delighters', SEOCIFY_SCRIPTS . '/delighters.js', array( 'jquery' ), SEOCIFY_VERSION, true );
    wp_enqueue_script( 'easypiechart', SEOCIFY_SCRIPTS . '/jquery.easypiechart.min.js', array( 'jquery' ), SEOCIFY_VERSION, true );
    wp_enqueue_script( 'jquery-parallax', SEOCIFY_SCRIPTS . '/jquery.parallax.js', array( 'jquery' ), SEOCIFY_VERSION, true );
    wp_enqueue_script( 'seocify-main', SEOCIFY_SCRIPTS . '/main.js', array( 'jquery' ), SEOCIFY_VERSION, true );

    /*Ajax Call*/
    $params = array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'marketpess_nonce' => wp_create_nonce('xs_nonce'),
    );
    wp_localize_script('seocify-setting', 'xs_ajax_obj', $params);

	// Load WordPress Comment js
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}