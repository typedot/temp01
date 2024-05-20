<?php
$fields[] = array(
    'type'        => 'typography',
    'settings'    => 'body_font',
    'label'       => esc_html__( 'Body Font', 'seocify' ),
    'section'     => 'styling_section',
    'default'     => array(
        'font-family'    => '"Nunito", sans-serif',
        'variant'        => '',
        'font-size'      => '1rem',
        'font-weight'      => '400',
        'line-height'    => '1.7333333333',
        'color'          => '#192225'
    ),
    'output'      => array(
        array(
            'element' => 'body',
        ),
    ),
); 
$fields[] = array(
    'type'        => 'typography',
    'settings'    => 'heading_font',
    'label'       => esc_html__( 'Heading Font', 'seocify' ),
    'section'     => 'styling_section',
    'default'     => array(
        'font-family'    => '"Nunito", sans-serif',
        'variant'        => '',
        'font-size'      => '',
        'font-weight'      => '700',
        'line-height'    => '',
        'color'          => '#192225'
    ),
    'output'      => array(
        array(
            'element' => 'h1,h2,h3,h4,h5,h6',
        ),
    ),
); 
