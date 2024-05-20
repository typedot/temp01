<?php

if ( !defined( 'ABSPATH' ) )
	die( 'Direct access forbidden.' );

class Seocify_Theme_Includes {

	private static $rel_path	 = null;
	private static $initialized	 = false;

	public static function init() {


		if ( class_exists( 'XsCustomPost\Xs_CustomPost' ) ) {

			$tab = new XsCustomPost\Xs_CustomPost( 'seocify' );
			$tab->xs_init( 'mega_menu', 'Mega Menu', 'Mega Menu', array( 'menu_icon'	 => 'dashicons-exerpt-view',
				'supports'	 => array( 'title', 'editor', 'thumbnail' ),
				'rewrite'	 => array( 'slug' => 'mega_menu' ) ) );

			$tab = new XsCustomPost\Xs_CustomPost( 'charitious' );
			$tab->xs_init( 'case_study', 'Case Study', 'Case Studies', array( 'menu_icon'	 => 'dashicons-exerpt-view',
				'supports'	 => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'rewrite'	 => array( 'slug' => 'case_study' ) ) );
			$tab_tax = new  XsCustomPost\Xs_Taxonomies('charitious');
			$tab_tax->xs_init('case_study_cat', 'Case Study Category', 'Case Study Categories', 'case_study');
	
			$tab = new XsCustomPost\Xs_CustomPost( 'charitious' );
			$tab->xs_init( 'portfolio', 'Portfolio', 'Portfolios', array( 'menu_icon'	 => 'dashicons-exerpt-view',
				'supports'	 => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'rewrite'	 => array( 'slug' => 'portfolio' ) ) );
			$tab_tax = new  XsCustomPost\Xs_Taxonomies('charitious');
			$tab_tax->xs_init('portfolio_cat', 'Portfolio Category', 'Portfolio Categories', 'portfolio');
	
		}

		if ( self::$initialized ) {
			return;
		} else {
			self::$initialized = true;
		}

		/**
		 * Both frontend and backend
		 */ {
			self::include_child_first( '/helpers.php' );
			self::include_child_first( '/hooks.php' );
			self::include_child_first( '/includes/enqueue-inline.php' );
			self::include_child_first( '/includes/class-tgm-plugin-activation.php' );
			self::include_child_first( '/includes/template-tags.php' );
			self::include_child_first( '/includes/option-types.php' );
			self::include_child_first( '/shortcode/elementor.php' );
			self::include_child_first( '/customizer/customizer-config.php' );
			
			add_action( 'init', array( __CLASS__, '_action_init' ) );
			add_action( 'widgets_init', array( __CLASS__, '_action_widgets_init' ) );
		}

		/**
		 * Only frontend
		 */
		if ( !is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( __CLASS__, '_action_enqueue_scripts' ), 20 // Include later to be able to make wp_dequeue_style|script()
			);
		} else { //ar
			// for include back-end files
			add_action( 'admin_enqueue_scripts', array( __CLASS__, '_action_enqueue_admin_scripts' ), 20 // Include later to be able to make wp_dequeue_style|script()
			);
		}
	}

	private static function get_rel_path( $append = '' ) {
		if ( self::$rel_path === null ) {
			self::$rel_path = '/inc';
		}

		return self::$rel_path . $append;
	}

	/**
	 * @param string $dirname 'foo-bar'
	 * @return string 'Foo_Bar'
	 */
	private static function dirname_to_classname( $dirname ) {
		$class_name	 = explode( '-', $dirname );
		$class_name	 = array_map( 'ucfirst', $class_name );
		$class_name	 = implode( '_', $class_name );

		return $class_name;
	}

	public static function get_parent_path( $rel_path ) {
		return get_template_directory() . self::get_rel_path( $rel_path );
	}

	public static function get_child_path( $rel_path ) {
		if ( !is_child_theme() ) {
			return null;
		}

		return get_stylesheet_directory() . self::get_rel_path( $rel_path );
	}

	public static function include_isolated( $path ) {
        include $path;
	}

	public static function include_child_first( $rel_path ) {
		if ( is_child_theme() ) {
			$path = self::get_child_path( $rel_path );

			if ( file_exists( $path ) ) {
				self::include_isolated( $path );
			}
		} {
			$path = self::get_parent_path( $rel_path );

			if ( file_exists( $path ) ) {
				self::include_isolated( $path );
			}
		}
	}

	/**
	 * @internal
	 */
	public static function _action_enqueue_scripts() {
		self::include_child_first( '/static.php' );
	}

	/**
	 * @internal
	 * ar
	 */
	public static function _action_enqueue_admin_scripts() {
		self::include_child_first( '/admin-static.php' );
	}

	/**
	 * @internal
	 */
	public static function _action_init() {
		self::include_child_first( '/menus.php' );
		self::include_child_first( '/posts.php' );
	}

	/**
	 * @internal
	 */
	public static function _action_widgets_init() { {
			$paths = array();

			if ( is_child_theme() ) {
				$paths[] = self::get_child_path( '/widgets' );
			}

			$paths[] = self::get_parent_path( '/widgets' );
		}

		$included_widgets = array();

		foreach ( $paths as $path ) {
			$dirs = glob( $path . '/*', GLOB_ONLYDIR );

			if ( !$dirs ) {
				continue;
			}

			foreach ( $dirs as $dir ) {
				$dirname = basename( $dir );

				if ( isset( $included_widgets[ $dirname ] ) ) {
					// this happens when a widget in child theme wants to overwrite the widget from parent theme
					continue;
				} else {
					$included_widgets[ $dirname ] = true;
				}

				self::include_isolated( $dir . '/class-widget-' . $dirname . '.php' );

				register_widget( 'Seocify_' . self::dirname_to_classname( $dirname ) );
			}
		}
	}

}

Seocify_Theme_Includes::init();

