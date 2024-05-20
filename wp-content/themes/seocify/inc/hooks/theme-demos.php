<?php
// Initializing online demo contents
function _filter_seocify_fw_ext_backups_demos( $demos ) {
	$demo_content_installer	 = 'http://wp.xpeedstudio.com/demo-content/seocify';
	$demos_array			 = array(
		'home-1'			 => array(
			'title'			 => esc_html__( 'Home 1', 'seocify' ),
			'screenshot'	 => esc_url( $demo_content_installer ) . '/home-1/screenshot.png',
			'preview_link'	 => esc_url( 'https://wp.xpeedstudio.com/seocify/' ),
		),
		'home-2'			 => array(
			'title'			 => esc_html__( 'Home 2', 'seocify' ),
			'screenshot'	 => esc_url( $demo_content_installer ) . '/home-2/screenshot.png',
			'preview_link'	 => esc_url( 'https://wp.xpeedstudio.com/seocify/home-two/' ),
		),
		'home-3'			 => array(
			'title'			 => esc_html__( 'Home 3', 'seocify' ),
			'screenshot'	 => esc_url( $demo_content_installer ) . '/home-3/screenshot.png',
			'preview_link'	 => esc_url( 'https://wp.xpeedstudio.com/seocify/home-three/' ),
		),
		'home-4'			 => array(
			'title'			 => esc_html__( 'Home 4', 'seocify' ),
			'screenshot'	 => esc_url( $demo_content_installer ) . '/home-4/screenshot.jpg',
			'preview_link'	 => esc_url( 'https://wp.xpeedstudio.com/seocify/home-four/' ),
		),
		'home-5'			 => array(
			'title'			 => esc_html__( 'Home 5', 'seocify' ),
			'screenshot'	 => esc_url( $demo_content_installer ) . '/home-5/screenshot.png',
			'preview_link'	 => esc_url( 'https://wp.xpeedstudio.com/seocify/home-five/' ),
		),
		'home-6'			 => array(
			'title'			 => esc_html__( 'Home 6', 'seocify' ),
			'screenshot'	 => esc_url( $demo_content_installer ) . '/home-6/screenshot.png',
			'preview_link'	 => esc_url( 'https://wp.xpeedstudio.com/seocify/home-six/' ),
		),
        'onepage-style-one'			 => array(
            'title'			 => esc_html__( 'Onepage 1', 'seocify' ),
            'screenshot'	 => esc_url( $demo_content_installer ) . '/onepage-style-one/screenshot.png',
            'preview_link'	 => esc_url( 'https://wp.xpeedstudio.com/seocify/onepage-style-one/' ),
        ),
        'onepage-style-two'			 => array(
            'title'			 => esc_html__( 'Onepage 2', 'seocify' ),
            'screenshot'	 => esc_url( $demo_content_installer ) . '/onepage-style-two/screenshot.png',
            'preview_link'	 => esc_url( 'https://wp.xpeedstudio.com/seocify/onepage-style-two/' ),
        ),
        'onepage-style-three'			 => array(
            'title'			 => esc_html__( 'Onepage 3', 'seocify' ),
            'screenshot'	 => esc_url( $demo_content_installer ) . '/onepage-style-three/screenshot.jpg',
            'preview_link'	 => esc_url( 'https://wp.xpeedstudio.com/seocify/onepage-style-three/' ),
        ),
        'onepage-style-four'			 => array(
            'title'			 => esc_html__( 'Onepage 4', 'seocify' ),
            'screenshot'	 => esc_url( $demo_content_installer ) . '/onepage-style-four/screenshot.png',
            'preview_link'	 => esc_url( 'https://wp.xpeedstudio.com/seocify/onepage-style-four/' ),
        ),
        'onepage-style-five'			 => array(
            'title'			 => esc_html__( 'Onepage 5', 'seocify' ),
            'screenshot'	 => esc_url( $demo_content_installer ) . '/onepage-style-five/screenshot.png',
            'preview_link'	 => esc_url( 'https://wp.xpeedstudio.com/seocify/onepage-style-five/' ),
        ),
		
	);
	$download_url			 = esc_url( $demo_content_installer ) . '/manifest.php';
	foreach ( $demos_array as $id => $data ) {
		$demo						 = new FW_Ext_Backups_Demo( $id, 'piecemeal', array(
			'url'		 => $download_url,
			'file_id'	 => $id,
		) );
		$demo->set_title( $data[ 'title' ] );
		$demo->set_screenshot( $data[ 'screenshot' ] );
		$demo->set_preview_link( $data[ 'preview_link' ] );
		$demos[ $demo->get_id() ]	 = $demo;
		unset( $demo );
	}
	return $demos;
}
add_filter( 'fw:ext:backups-demo:demos', '_filter_seocify_fw_ext_backups_demos' );