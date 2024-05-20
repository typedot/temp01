<?php
if ( defined( 'FW' ) ) {
    //Page settings
    $custom_logo	 = fw_get_db_post_option( get_the_ID(), 'custom_logo' );
}
if(isset($custom_logo['url']) && $custom_logo['url'] !=''){
    $logo = $custom_logo['url'];
}else{
    $logo = seocify_option('site_logo');
}
$sticky_logo = seocify_option('sticky_logo');

$nav_search = seocify_option('nav_search');
$nav_sidebar = seocify_option('nav_sidebar');
$nav_cart = seocify_option('nav_cart');
$nav_cart_url = seocify_option('nav_cart_url');
$nav_lang = seocify_option('nav_lang');
$is_sticky_header = seocify_option('is_sticky_header');
?>
<header class="xs-header header-main">
    <div class="container">
        <div class="row">
            <div class="col-lg-2">
                <div class="xs-logo-wraper">
                    <a href="<?php echo esc_url(home_url('/'));?>" class="xs-logo">
                        <?php if(!empty($logo)): ?>
                            <img src="<?php echo esc_url($logo);  ?>" alt="<?php echo get_bloginfo(); ?>">
                        <?php else: ?>
                            <span><?php bloginfo('name'); ?></span>
                        <?php endif ?>
                        <?php if(!empty($sticky_logo) && $is_sticky_header): ?>
                            <img src="<?php echo esc_url($sticky_logo);  ?>" alt="<?php echo get_bloginfo(); ?>" class="logo-sticky">
                        <?php endif ?>
                    </a>
                </div>
            </div>
            <?php if(!empty($nav_sidebar) || !empty($nav_search)): ?>
                <div class="col-lg-8">
            <?php else: ?>
                <div class="col-lg-10">
            <?php endif; ?>
                <nav class="xs-menus">
                    <div class="nav-header">
                        
                        <a class="nav-brand" href="<?php echo esc_url(home_url('/'));?>"></a>
                        
                        
                        <div class="nav-toggle"></div>
                    </div>
                    <?php
                    if(has_nav_menu('primary')) {
                        wp_nav_menu(
                            array(
                                'theme_location'	 => 'primary',
                                'container_class'	 => 'nav-menus-wrapper',
                                'menu_class'		 => 'nav-menu align-to-right',
                                'fallback_cb'		 => false,
                                'menu_id'			 => 'main-menu',
                                'walker'			 => new Seocify_Custom_Nav_Walker(),
                            )
                        );
                    }
                    ?>
                </nav> 
            </div>
            <?php if(!empty($nav_sidebar) || !empty($nav_search) || !empty($nav_lang) || !empty($nav_cart)): ?>
                <div class="col-lg-2 col-md-6">
                    <ul class="xs-menu-tools">
                        <?php if ($nav_lang): ?>
                            <li>
                                <a href="#modal-popup-wpml" class="languageSwitcher-button xs-modal-popup">
                                    <span class="xs-flag" style="background-image: url(<?php echo esc_url(get_template_directory_uri());?>/assets/images/united-states.svg);"></span>
                                    <span class="lang-title"><?php esc_html_e('EN','seocify');?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($nav_search): ?>
                            <li>
                                <a href="#modal-popup-2" class="navsearch-button xs-modal-popup"><i class="icon icon-search"></i></a>
                            </li>
                        <?php endif; ?>
                        <?php if ($nav_sidebar): ?>
                            <li class="navSidebar-wraper clearfix">
                                <div class="single-navicon">
                                    <a href="#" class="navSidebar-button"><i class="icon icon-menu-1"></i></a>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

    </div>
</header>