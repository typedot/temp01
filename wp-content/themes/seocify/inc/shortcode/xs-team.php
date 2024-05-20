<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Xs_Team_Widget extends Widget_Base {

    public function get_name() { 
        return 'xs-team';
    }

    public function get_title() {
        return esc_html__( 'Seocify Team', 'seocify' );
    }

    public function get_icon() {
        return 'fa fa-user-o';
    }

    public function get_categories() {
        return [ 'seocify-elements' ];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'section_tab',
            [
                'label' => esc_html__('Seocify Team', 'seocify'),
            ]
        );

        /**
         *
         * Member Content Feild
         *
        */

        $this->add_control(

            'member_name', 
            [

                'label' =>esc_html__('Team Member', 'seocify'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   =>esc_html__('Team Member', 'seocify'),
                
            ]
        );

        $this->add_control(

            'member_position', 
            [

                'label' =>esc_html__('Position', 'seocify'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   =>esc_html__('CEO', 'seocify'),
                
            ]
        );


        $this->add_control(
            'image',
            [
                'label' =>esc_html__( 'Thumbnail Image', 'seocify' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );
        $this->add_control(
            'socials',
            [
                'label' => esc_html__('Social Icon', 'seocify'),
                'type' => Controls_Manager::REPEATER,
                'separator' => 'before',
                'default' => [
                    [
                        'icon' => esc_attr('fa fa-facebook'),
                        'url' => esc_url('#'),
                    ],
                ],
                'fields' => [
                    [
                        'name' => 'icon',
                        'label' => esc_html__('Icon CSS Class', 'seocify'),
                        'type' => Controls_Manager::TEXT,
                        'default' => esc_attr('fa fa-facebook'),
                        'label_block' => true,
                    ],

                    [
                        'name' => 'url',
                        'label' => esc_html__('Social URL', 'seocify'),
                        'type' => Controls_Manager::TEXT,
                        'default' => esc_url('#'),
                        'label_block' => true,
                    ],
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


        $this->end_controls_section();


        $this->start_controls_section(
            'section_title_style',
            [
                'label'     =>esc_html__( 'Team Style', 'seocify' ),
                'tab'       => Controls_Manager::TAB_STYLE,
            ]
        );

        /**
         *
         * Normal Style
         *
         */

        $this->add_control(
            'member_name_color',
            [
                'label'     =>esc_html__( 'Name color', 'seocify' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .xs-single-team .team-bio h4' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'member_pos_color',
            [
                'label'     =>esc_html__( 'Position color', 'seocify' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .xs-single-team .team-bio p' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'social_color',
            [
                'label'     =>esc_html__( 'Social color', 'seocify' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .xs-single-team .team-hover-content .simple-social-list li a' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render( ) {

        $settings = $this->get_settings();
        $member_name = $settings['member_name'];
        $member_position = $settings['member_position'];
        $socials = $settings['socials'];

        ?>
        <div class="single-box text-center">
            <div class="image">
                <?php echo Group_Control_Image_Size::get_attachment_image_html( $settings); ?>
                <div class="hover-area">
                    <h2 class="title"><a href="#"><?php echo esc_html( $member_name ); ?></a></h2>
                    <p class="description"><?php echo esc_html( $member_position ); ?></p>
                    <span class="line"></span>
                    <?php if (!empty($socials)): ?>
                        <ul class="xs-list list-inline">
                            <?php foreach ($socials as $social): ?>

                            <li><a href="<?php echo esc_url($social['url']);?>"><i class="<?php echo esc_attr($social['icon']);?>"></i></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-footer">
                <h2 class="title"><a href="#"><?php echo esc_html( $member_name ); ?></a></h2>
            </div>
        </div>

        <?php

    }

    protected function _content_template() { }
}