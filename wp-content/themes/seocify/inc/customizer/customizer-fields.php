<?php
/**
 *	Customizer General Settings
 *	styles for logo/title - sizing, spacing ...
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Xs_Fields{

	/**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     */
    
	public static $_instance;

	/**
     * Load Construct
     * 
     * @since 1.0.0
     */

	public function __construct(){
		$this->xs_customizer_init();
	}

	/**
     * Customizer field Initialization
     *
     * @since 1.0.0
     *
     */

	public function xs_customizer_init(){
		add_filter( 'kirki/fields', array($this,'seocify_general_setting') );
	}

	public function seocify_general_setting( $fields ){

		require SEOCIFY_CUSTOMIZER_DIR . 'general-settings.php' ;
		require SEOCIFY_CUSTOMIZER_DIR . 'nav-settings.php' ;
        require SEOCIFY_CUSTOMIZER_DIR . 'banner-settings.php' ;
        require SEOCIFY_CUSTOMIZER_DIR . 'page-settings.php' ;
        require SEOCIFY_CUSTOMIZER_DIR . 'blog-settings.php' ;
        require SEOCIFY_CUSTOMIZER_DIR . 'shop-settings.php' ;
        require SEOCIFY_CUSTOMIZER_DIR . 'footer-settings.php' ;
        require SEOCIFY_CUSTOMIZER_DIR . 'error-settings.php' ;
        require SEOCIFY_CUSTOMIZER_DIR . 'style-settings.php' ;

		return $fields;
	}

	public static function xs_get_instance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new Xs_Fields();
        }
        return self::$_instance;
    }
}
$Xs_Fields = Xs_Fields::xs_get_instance();