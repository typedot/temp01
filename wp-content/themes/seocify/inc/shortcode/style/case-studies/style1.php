<?php if($carousel == 'yes'){ ?>
    <div class="case-study-slider-item">
        <div class="single-cases-card">
            <div class="card-image">
                <?php
                    if (has_post_thumbnail()):
                    $img = wp_get_attachment_image_src(get_post_thumbnail_id($xs_query->ID), 'full');
                    $img = $img[0];

                ?>
                <img src="<?php echo esc_url($img); ?>" alt="<?php the_title_attribute($xs_query->ID); ?>">
                <div class="hover-area">
                    <a href="<?php echo get_the_permalink(); ?>"><i class="icon icon-bullhorn"></i></a>
                </div>
            <?php endif; ?>
                
            </div>
            <div class="cases-content">
                <h2 class="xs-title">
                    <a href="<?php echo get_the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>

                <?php $categories = get_the_terms( get_the_ID(), 'case_study_cat' ); 

                $tags = array();
                foreach($categories as $category){
                $tags[] = $category->name;
                }
                $tags_str = implode(', ', $tags);
                ?>


                <span class="tag"><?php echo esc_html($tags_str);?></span>
            </div>
        </div>
    </div>
<?php }else{ ?>
<div class="<?php if($filter == 'yes'){ echo 'cases-grid-item'.' '.esc_attr($case_terms); }else{ echo ' col-md-6 col-lg-'.esc_attr($count_col);} ?>">

<div class="single-cases-card">
    <div class="card-image">
        <?php
            if (has_post_thumbnail()):
            $img = wp_get_attachment_image_src(get_post_thumbnail_id($xs_query->ID), 'full');
            $img = $img[0];

        ?>
        <img src="<?php echo esc_url($img); ?>" alt="<?php the_title_attribute($xs_query->ID); ?>">
        <div class="hover-area">
            <a href="<?php echo get_the_permalink(); ?>"><i class="icon icon-bullhorn"></i></a>
        </div>
    <?php endif; ?>
        
    </div>
    <div class="cases-content">
        <h2 class="xs-title">
            <a href="<?php echo get_the_permalink(); ?>"><?php the_title(); ?></a>
        </h2>

        <?php $categories = get_the_terms( get_the_ID(), 'case_study_cat' ); 

        $tags = array();
        foreach($categories as $category){
           $tags[] = $category->name;
        }
        $tags_str = implode(', ', $tags);
        ?>


        <span class="tag"><?php echo esc_html($tags_str);?></span>
    </div>
</div>
</div>
<?php } ?>