<?php

/* ---------------------------------------------------
* Theme: Seocify - WordPress Theme
* Author: XpeedStudio
* Author URI: http://www.xpeedstudio.com
  -------------------------------------------------- */

function seocify_theme_enqueue_styles()
{

	$parent_style = 'parent-style';

	if(!wp_style_is( $parent_style, $list = 'enqueued' )){
		wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css', array());
	}
	wp_enqueue_style('child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array($parent_style)
	);
	wp_enqueue_script('child-custom', get_stylesheet_directory_uri() . '/custom.js', array('jquery'), '', true);
}

add_action('wp_enqueue_scripts', 'seocify_theme_enqueue_styles');