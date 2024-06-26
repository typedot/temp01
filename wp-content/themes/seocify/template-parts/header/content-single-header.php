<?php
/**
 * Blog Header
 *
 */
$bg_image				 = $heading				 = $overlay				 = $overlay				 = '';
$page_banner_title		 = $page_banner_subtitle	 = $page_banner_desc		 = $header_image			 = $header_buttons			 = $header_icons			 = '';


$global_page_show_breadcrumb = seocify_option( 'show_breadcrumb' );


	$global_page_banner_img		 = seocify_option( 'single_banner_img' );
	$global_page_banner_title	 = seocify_option( 'single_banner_title' );
	$global_show_page_banner	 = seocify_option( 'show_single_banner' );


if ( defined( 'FW' ) ) {
	//Page settings
	$page_banner_title	 = fw_get_db_post_option( get_the_ID(), 'header_title' );
	$header_image		 = fw_get_db_post_option( get_the_ID(), 'header_image' );
}

if ( isset( $header_image[ 'url' ] ) && $header_image[ 'url' ] != '' ) {
	$bg_image = 'style="background-image: url(' . $header_image[ 'url' ] . ')"';
} elseif ( $global_page_banner_img != '' ) {
	$bg_image = 'style="background-image: url(' . $global_page_banner_img . ')"';
} else {
	$bg_image = 'style="background-image: url(' . SEOCIFY_IMAGES_URI . '/backgrounds/background-1.png)"';
}


if ( $page_banner_title != '' ) {
	$page_banner_title = $page_banner_title;
} elseif ( $global_page_banner_title != '' ) {
	$page_banner_title = $global_page_banner_title;
} else {
	$page_banner_title = get_the_title();
}


$page_show_breadcrumb = $global_page_show_breadcrumb;



if ( $global_show_page_banner ):
	?>

	<section class="inner-banner-area">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="inner-banner-content">
						<h1 class="inner-banner-title">
							<?php echo esc_html( $page_banner_title ); ?>
	                    </h1>
						<?php if ( $page_show_breadcrumb ): ?>
							<?php seocify_get_breadcrumbs(); ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
	    </div>
		<?php if ( $bg_image ): ?>
			<div class="banner-image" <?php echo wp_kses_post( $bg_image ); ?>></div>
		<?php endif; ?>
	</section>

<?php endif; ?>