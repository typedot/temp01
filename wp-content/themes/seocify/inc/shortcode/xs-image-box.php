<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Xs_Image_Box_Widget extends Widget_Base {

    public function get_name() {
        return 'xs-image-box';
    }

    public function get_title() {
        return esc_html__( 'Seocify Image Box', 'seocify' );
    }

    public function get_icon() {
        return 'eicon-insert-image';
    }

    public function get_categories() {
        return [ 'seocify-elements' ];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'section_tab',
            [
                'label' =>esc_html__('Seocify Image Box', 'seocify'),
            ]
        );

        $this->add_control(
            'image',
            [
                'label' =>esc_html__( 'Image', 'seocify' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'image',
                'label' =>esc_html__( 'Image Size', 'seocify' ),
                'default' => 'full',
            ]
        );


        $this->add_control(
            'title',
            [
                'label' =>esc_html__( 'Title', 'seocify' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'placeholder' =>esc_html__( '99.9% Uptime Guarantee', 'seocify' ),
                'default' =>esc_html__( 'Add Title', 'seocify' ),
            ]
        );

        $this->add_control(
            'sub_title',
            [
                'label' =>esc_html__( 'Sub Title', 'seocify' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'default' => esc_html__( 'Share processes and data secure lona need to know basis', 'seocify' ),
                
            ]
        );
        
		$this->add_responsive_control(
			'box_align', [
				'label'			 =>esc_html__( 'Alignment', 'seocify' ),
				'type'			 => Controls_Manager::CHOOSE,
				'options'		 => [

					'left'		 => [
						'title'	 =>esc_html__( 'Left', 'seocify' ),
						'icon'	 => 'fa fa-align-left',
					],
					'center'	 => [
						'title'	 =>esc_html__( 'Center', 'seocify' ),
						'icon'	 => 'fa fa-align-center',
					],
					'right'		 => [
						'title'	 =>esc_html__( 'Right', 'seocify' ),
						'icon'	 => 'fa fa-align-right',
					],
					'justify'	 => [
						'title'	 =>esc_html__( 'Justified', 'seocify' ),
						'icon'	 => 'fa fa-align-justify',
					],
				],
				'default'		 => '',
                'selectors' => [
                    '{{WRAPPER}} .why-choose-us-block' => 'text-align: {{VALUE}};'
                ],
			]
		);

        $this->end_controls_section();

        /**
		 *
		 *Title Style
		 *
		*/

        $this->start_controls_section(
			'section_title_tab',
			[
				'label' =>esc_html__( 'Title', 'seocify' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' =>esc_html__( 'Color', 'seocify' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .why-choose-us-block h4.xs-content-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => esc_html__( 'Typography', 'seocify' ),
				'selector' => '{{WRAPPER}} .why-choose-us-block .xs-content-title, h4',
			]
		);

		$this->end_controls_section();


		/**
		 *
		 *Sub Title Style
		 *
		*/

        $this->start_controls_section(
			'section_sub_title_tab',
			[
				'label' => esc_html__( 'Sub Title', 'seocify' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'sub_title_color',
			[
				'label' => esc_html__( 'Color', 'seocify' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .why-choose-us-block p ' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'sub_title_typography',
				'label' => esc_html__( 'Typography', 'seocify' ),
				'selector' => '{{WRAPPER}} .why-choose-us-block p',
			]
		);

		$this->end_controls_section();

		/**
		 *
		 *Image Style
		 *
		*/

        $this->start_controls_section(
			'section_image_tab',
			[
				'label' => esc_html__( 'Image', 'seocify' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
            'image_margin_bottom',
            [
                'label' => esc_html__( 'Margin Bottom', 'seocify' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => '',
                ],

                'size_units' => [ 'px'],
                'selectors' => [
                    '{{WRAPPER}} .why-choose-us-block .choose-us-img' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->end_controls_section();

    }

    protected function render( ) {
    	
        $settings = $this->get_settings();
        $title = $settings['title'];
        $sub_title = $settings['sub_title'];
        ?>
        <div class="why-choose-us-block wow fadeInUp">
			<div class="choose-us-img">
                <?php echo Group_Control_Image_Size::get_attachment_image_html( $settings); ?>
            </div>
            <h4 class="xs-content-title"><?php echo esc_html( $title ); ?></h4>
            <p><?php echo esc_html( $sub_title ); ?></p>
        </div>
        <?php
    }



    protected function _content_template() { }
}