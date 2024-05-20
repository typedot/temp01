<?php
/**
 * header.php
 *
 * The header for the theme.
 */
?>
<!DOCTYPE html>
<!--[if !IE]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->

    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta name="google-site-verification" content="nJO0HpS7xu4pDE9hT2zI_75VC32J8o1rhYhDHQPutWo" />
		<meta name="msvalidate.01" content="AF4C5B995D9B142C3A5C4B5CDC5F6D1D" />
		<?php wp_head(); ?>
		
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-139134406-1"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());

		  gtag('config', 'UA-139134406-1');
		</script>
		
		<script type="application/ld+json">
			{
			  "@context": "https://schema.org",
			  "@type": "Organization",
			  "name": "Caze Technologies",
			  "url": "https://cazetechnologies.com/",
			  "logo": "https://cazetechnologies.com/wp-content/uploads/2019/04/Caze-Technologies-logo.png",
			  "sameAs": [
				"https://www.linkedin.com/company/caze-technologies",
				"https://twitter.com/CazeTech",
				"https://www.facebook.com/CazeTechnologies"
			  ]
			}
		</script>
		
    </head>

    <body <?php body_class(); ?> data-spy="scroll" data-target="#header">
<?php

$is_sticky_header = seocify_option('is_sticky_header');
$is_transparent_header = seocify_option('is_transparent_header');
$show_top_header = seocify_option('show_top_header');
$header_style = seocify_option('header_style');
$header_class = 'header';
if ($is_sticky_header):
    $header_class .= ' nav-sticky';
endif;

if ($is_transparent_header):
    $header_class .= ' header-transparent';
endif; 

?>

    <div class="<?php echo esc_attr($header_class);?>">
        <?php if($show_top_header): get_template_part('template-parts/navigation/nav','top-bar-'.$header_style.''); endif; ?>
        <?php get_template_part( 'template-parts/navigation/nav','primary-'.$header_style.'' ); ?>
    </div>