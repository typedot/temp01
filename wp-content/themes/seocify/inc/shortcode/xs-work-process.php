<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class XS_Work_Process_Widget extends Widget_Base {

    public function get_name() {
        return 'xs-work-process';
    }

    public function get_title() {
        return esc_html__( 'Seocify Work Process', 'seocify' );
    }

    public function get_icon() {
        return 'eicon-work-process';
    }

    public function get_categories() {
        return [ 'seocify-elements' ];
    }

    protected function _register_controls() {

        $this->start_controls_section(
            'work_process',
            [
                'label' => esc_html__('Work Process', 'seocify'),
            ]
        );

        /*Work Process*/
        $this->add_control(
            'work_process_items',
            [
                'type' => Controls_Manager::REPEATER,
                'default' => [
                    [
                        'title' => esc_html__('Planning','seocify'),
                    ]

                ],
                'fields' => [
                    [
                        'name' => 'title',
                        'type' => Controls_Manager::TEXT,
                        'label' => esc_html__('Title', 'seocify'),
                        'default'   =>  esc_html__('Planning','seocify'),
                        'label_block' => true,
                    ],
                    [
                        'name' => 'image',
                        'type' => Controls_Manager::MEDIA,
                        'label' => esc_html__('Image', 'seocify'),
                        'label_block' => true,
                    ],
                ],
                'title_field' => '{{{ title }}}',
            ]
        );

        
        $this->end_controls_section();

        $this->start_controls_section(
            'section_title_style',
            [
                'label' 	=> esc_html__( 'Title Styles', 'seocify' ),
                'tab' 		=> Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'		=> esc_html__( 'Title Color', 'seocify' ),
                'type'		=> Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .single-work-process h4' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'title_hover_color',
            [
                'label'		=> esc_html__( 'Title Hover Color', 'seocify' ),
                'type'		=> Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .single-work-process:hover h4' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => esc_html__('Typography', 'seocify'),
                'selector' => '{{WRAPPER}} .single-work-process:hover h4',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_image_style',
            [
                'label' 	=> esc_html__( 'Circle Style', 'seocify' ),
                'tab' 		=> Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'circle_bg_color',
            [
                'label'		=> esc_html__( 'Circle BG Color', 'seocify' ),
                'type'		=> Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .single-work-process .work-process-icon' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'circle_bg_hover_color',
            [
                'label'		=> esc_html__( 'Circle BG Hover Color', 'seocify' ),
                'type'		=> Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .single-work-process:hover .work-process-icon' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'circle_border_color',
            [
                'label'		=> esc_html__( 'Circle Border Hover Color', 'seocify' ),
                'type'		=> Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .single-work-process:hover .work-process-icon' => 'border-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'circle_border_hover_color',
            [
                'label'		=> esc_html__( 'Circle Border Hover Color', 'seocify' ),
                'type'		=> Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .single-work-process:hover .work-process-icon' => 'border-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->end_controls_section();


    }

    protected function render( ) {
        $settings = $this->get_settings();


        $work_process_items = $settings['work_process_items'];
        ?><div class="row no-gutters working-process-group"><?php
        foreach($work_process_items as $work_process_item):

            if(!empty($work_process_item['image']['id'])){
                $alt = get_post_meta($work_process_item['image']['id'], '_wp_attachment_image_alt', true);
                if(!empty($alt)) {
                    $alt = $alt;
                }else{
                    $alt = get_the_title($work_process_item['image']['id']);
                }
            } 
        ?>
            <div class="col-lg-3 col-md-6">
                <div class="single-work-process">
                    <div class="work-process-icon">
                        <img src="<?php echo esc_url($work_process_item['image']['url']);?>" alt="<?php echo esc_attr($alt); ?>" draggable="false">
                    </div>
                    <h4 class="small"><?php echo esc_html($work_process_item['title']);?></h4>
                </div>
            </div>
        <?php
        endforeach; 
        ?></div><?php
    }


    protected function _content_template() { }
}