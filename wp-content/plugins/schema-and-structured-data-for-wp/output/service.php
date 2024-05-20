<?php 
/**
 * Service Class
 *
 * @author   Magazine3
 * @category Frontend
 * @path  output/service
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

Class saswp_output_service{           
        
        /**
         * List of hooks used in current class
         */
        public function saswp_service_hooks(){
            
           add_action( 'wp_ajax_saswp_get_custom_meta_fields', array($this, 'saswp_get_custom_meta_fields')); 
           add_action( 'wp_ajax_saswp_get_schema_type_fields', array($this, 'saswp_get_schema_type_fields')); 
           
           add_action( 'wp_ajax_saswp_get_meta_list', array($this, 'saswp_get_meta_list')); 
           
           add_filter( 'saswp_modify_post_meta_list', array( $this, 'saswp_get_acf_meta_keys' ) );
           
        }    
        /**
         * Function to get acf meta keys
         * @param type $fields
         * @return type array
         * @since version 1.9.3
         */     
        public function saswp_get_acf_meta_keys($fields){
            
            if ( function_exists( 'acf' ) && class_exists( 'acf' ) ) {

				$post_type = 'acf';
				if ( ( defined( 'ACF_PRO' ) && ACF_PRO ) || ( defined( 'ACF' ) && ACF ) ) {
					$post_type = 'acf-field-group';
				}
				$text_acf_field  = array();
				$image_acf_field = array();
				$args            = array(
					'post_type'      => $post_type,
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				);

				$the_query = new WP_Query( $args );
				if ( $the_query->have_posts() ) :
					while ( $the_query->have_posts() ) :
						$the_query->the_post();

						$post_id = get_the_id();
						
						$acf_fields = apply_filters( 'acf/field_group/get_fields', array(), $post_id ); // WPCS: XSS OK.						

						if ( 'acf-field-group' == $post_type ) {
							$acf_fields = acf_get_fields( $post_id );
						}

						if ( is_array( $acf_fields ) && ! empty( $acf_fields ) ) {
							foreach ( $acf_fields as $key => $value ) {

								if ( 'image' == $value['type'] ) {
									$image_acf_field[ $value['name'] ] = $value['label'];
								} else {
									$text_acf_field[ $value['name'] ] = $value['label'];
								}
							}
						}
					endwhile;
				endif;
				wp_reset_postdata();

				if ( ! empty( $text_acf_field ) ) {
					$fields['text'][] = array(
						'label'     => __( 'Advanced Custom Fields', 'schema-and-structured-data-for-wp' ),
						'meta-list' => $text_acf_field,
					);
				}

				if ( ! empty( $image_acf_field ) ) {
					$fields['image'][] = array(
						'label'     => __( 'Advanced Custom Fields', 'schema-and-structured-data-for-wp' ),
						'meta-list' => $image_acf_field,
					);
				}
			}

			return $fields;
            
        }
        /**
         * Ajax function to get meta list 
         * @return type json
         */
        public function saswp_get_meta_list(){
            
            if ( ! isset( $_GET['saswp_security_nonce'] ) ){
                return; 
             }
             if ( !wp_verify_nonce( $_GET['saswp_security_nonce'], 'saswp_ajax_check_nonce' ) ){
                return;  
             }
            
            $response = array();    
            $mappings_file = SASWP_DIR_NAME . '/core/array-list/meta_list.php';

            if ( file_exists( $mappings_file ) ) {
                $response = include $mappings_file;
            }  
                         
            wp_send_json( $response); 
                        
        }
        
        /**
         * @since version 1.9.1
         * This function replaces the value of schema's fields with the selected custom meta field
         * @param type $input1
         * @param type $schema_post_id
         * @return type array or string
         */        
        public function saswp_get_meta_list_value($key, $field, $schema_post_id){
            
            global $post;
            
            $fixed_image       = get_post_meta($schema_post_id, 'saswp_fixed_image', true) ;            
                        
            $response = null;
            
            switch ($field) {
                case 'blogname':
                    $response    = get_bloginfo();                    
                    break;
                case 'blogdescription':
                    $response = get_bloginfo('description');                    
                    break;
                case 'site_url':
                    $response = get_site_url();                    
                    break;
                case 'post_title':
                    $response = saswp_get_the_title();                    
                    break;
                case 'post_content':
                    $response = saswp_get_the_content();                        
                    break;
                case 'post_category':
                    $categories = get_the_category();
                    if($categories){
                        foreach ($categories as $category){
                            if(isset($category->name)){
                              $response[] = $category->name;  
                            }
                        }
                        
                    }                                           
                    break;
                case 'post_excerpt':
                    $response = saswp_get_the_excerpt(); 
                    break;
                case 'post_permalink':
                    $response = saswp_get_permalink();
                    break;
                case 'author_name':
                    $response =  get_the_author_meta('first_name').' '.get_the_author_meta('last_name');
                    break;
                case 'author_first_name':
                    $response = get_the_author_meta('first_name'); 
                    break;
                case 'author_last_name':
                    $response = get_the_author_meta('last_name');
                    break;
                case 'post_date':
                    $response = get_the_date("c");
                    break;
                case 'post_modified':
                    $response = get_the_modified_date("c");
                    break;
                case 'manual_text':    
                    
                    $fixed_text        = get_post_meta($schema_post_id, 'saswp_fixed_text', true) ; 

                    if(isset($fixed_text[$key])){
                    
                        if (strpos($fixed_text[$key], 'http') !== false) {
                        
                        $image_details = @getimagesize($fixed_text[$key]);
                        
                        if(is_array($image_details)){
                            $response['@type']  = 'ImageObject';
                            $response['url']    = $fixed_text[$key];
                            $response['width']  = $image_details[0]; 
                            $response['height'] = $image_details[1];
                        }else{
                            $response    = $fixed_text[$key];                  
                        }
                                                
                        }else{
                            $response    = $fixed_text[$key];                    
                        }
                        
                    }
                    
                    break;
                
                case 'taxonomy_term':    
                    
                    $response = '';
                    
                    $taxonomy_term       = get_post_meta( $schema_post_id, 'saswp_taxonomy_term', true) ; 
                                        
                    if($taxonomy_term[$key] == 'all'){
                        
                        $post_taxonomies      = get_post_taxonomies( $post->ID );
                                                
                        if($post_taxonomies){
                            
                            foreach ($post_taxonomies as $taxonomie ){
                                
                                $terms               = get_the_terms( $post->ID, $taxonomie);
                                
                                if($terms){
                                    foreach ($terms as $term){
                                        $response .= $term->name.', ';
                                    }    
                                }
                                
                            }
                            
                        }                        
                        
                    }else{
                    
                        $terms               = get_the_terms( $post->ID, $taxonomy_term[$key]);
                        
                        if($terms){
                            foreach ($terms as $term){
                                $response .= $term->name.', ';
                            }    
                        }
                        
                    }
                                                                                                    
                    if($response){
                        $response = substr(trim($response), 0, -1); 
                    }
                                                            
                    break;
                    
                case 'custom_field':
                    
                    $cus_field   = get_post_meta($schema_post_id, 'saswp_custom_meta_field', true); 
                    $response    = get_post_meta($post->ID, $cus_field[$key], true); 
                    
                    break;
                case 'fixed_image':                    
                    
                    $response['@type']  = 'ImageObject';
                    $response['url']    = $fixed_image[$key]['thumbnail'];
                    $response['width']  = $fixed_image[$key]['width']; 
                    $response['height'] = $fixed_image[$key]['height'];
                    
                    break;
                    
                case 'featured_img':                    
                    $image_id 	        = get_post_thumbnail_id();
                    $image_details      = wp_get_attachment_image_src($image_id, 'full');                    
                    $response['@type']  = 'ImageObject';
                    $response['url']    = $image_details[0];
                    $response['width']  = $image_details[1]; 
                    $response['height'] = $image_details[2];    
                    
                    break;
                case 'author_image':
                    $author_image       = array();
                    $author_id          = get_the_author_meta('ID');
                    
                    if(function_exists('get_avatar_data')){
                        $author_image	= get_avatar_data($author_id);      
                    }                                                          
                    $response['@type']  = 'ImageObject';
                    $response['url']    = $author_image['url'];
                    $response['width']  = $author_image['height']; 
                    $response['height'] = $author_image['width'];

                    break;
                case 'site_logo':
                    
                    $sizes = array(
                            'width'  => 600,
                            'height' => 60,
                            'crop'   => false,
                    ); 

                    $custom_logo_id = get_theme_mod( 'custom_logo' );     

                    if($custom_logo_id){

                        $custom_logo    = wp_get_attachment_image_src( $custom_logo_id, $sizes);

                    }

                    if(isset($custom_logo) && is_array($custom_logo)){

                         $response['@type']  = 'ImageObject';
                         $response['url']    = array_key_exists(0, $custom_logo)? $custom_logo[0]:'';
                         $response['width']  = array_key_exists(2, $custom_logo)? $custom_logo[2]:''; 
                         $response['height'] = array_key_exists(1, $custom_logo)? $custom_logo[1]:'';
                                              
                    }
                                    
                default:
                    
                    $response = get_post_meta($post->ID, $field, true );
                    
                    break;
            }
            
            return $response;
            
        }
        /**
         * Function to replace schema markup fields value with custom value enter or selected by users while modifying globally
         * @param type $input1
         * @param type $schema_post_id
         * @return type array
         */
        public function saswp_replace_with_custom_fields_value($input1, $schema_post_id){
                                                 
            $custom_fields    = get_post_meta($schema_post_id, 'saswp_meta_list_val', true);            
            $review_markup    = array();
            $review_response  = array();
            $main_schema_type = '';
                                                          
            if(!empty($custom_fields)){
                
                foreach ($custom_fields as $key => $field){
                                                                                                                                         
                    $custom_fields[$key] = $this->saswp_get_meta_list_value($key, $field, $schema_post_id);                                           
                                                           
                }   
                
                $schema_type      = get_post_meta( $schema_post_id, 'schema_type', true);                                     
            
                if($schema_type == 'Review'){

                    $main_schema_type = $schema_type;
                    $review_post_meta = get_post_meta($schema_post_id, 'saswp_review_schema_details', true);                                                                
                    $schema_type = $review_post_meta['saswp_review_schema_item_type'];
                    
                    
                    if(isset($custom_fields['saswp_review_name'])){
                        $review_markup['name']                       =    $custom_fields['saswp_review_name'];
                    }
                    if(isset($custom_fields['saswp_review_description'])){
                        $review_markup['description']                =    $custom_fields['saswp_review_description'];
                    }
                    if(isset($custom_fields['saswp_review_rating_value'])){
                       $review_markup['reviewRating']['@type']       =   'Rating';                                              
                       $review_markup['reviewRating']['ratingValue'] =    $custom_fields['saswp_review_rating_value'];
                       $review_markup['reviewRating']['bestRating']  =   5;
                       $review_markup['reviewRating']['worstRating'] =   1;
                    }
                    if(isset($custom_fields['saswp_review_publisher'])){
                       $review_markup['publisher']['@type']          =   'Organization';                                              
                       $review_markup['publisher']['name']           =    $custom_fields['saswp_review_publisher'];                                              
                    }
                    if(isset($custom_fields['saswp_review_body'])){
                        $review_markup['reviewBody']                 =    $custom_fields['saswp_review_body'];
                    }
                    if(isset($custom_fields['saswp_review_author'])){
                       $review_markup['author']['@type']             =   'Person';                                              
                       $review_markup['author']['name']              =    $custom_fields['saswp_review_author'];                                              
                    }

                }
                   
                
             switch ($schema_type) {
                 
               case 'Book':      
                      
                    if(isset($custom_fields['saswp_book_name'])){
                     $input1['name'] =    $custom_fields['saswp_book_name'];
                    }
                    if(isset($custom_fields['saswp_book_description'])){
                     $input1['description'] =    $custom_fields['saswp_book_description'];
                    }
                    if(isset($custom_fields['saswp_book_url'])){
                     $input1['url'] =    $custom_fields['saswp_book_url'];
                    }
                    if(isset($custom_fields['saswp_book_author'])){
                     $input1['author'] =    $custom_fields['saswp_book_author'];
                    }
                    if(isset($custom_fields['saswp_book_isbn'])){
                     $input1['isbn'] =    $custom_fields['saswp_book_isbn'];
                    }
                    if(isset($custom_fields['saswp_book_publisher'])){
                     $input1['publisher'] =    $custom_fields['saswp_book_publisher'];
                    }
                    if(isset($custom_fields['saswp_book_no_of_page'])){
                     $input1['numberOfPages'] =    $custom_fields['saswp_book_no_of_page'];
                    }
                    if(isset($custom_fields['saswp_book_image'])){
                     $input1['image']         =    $custom_fields['saswp_book_image'];
                    }
                    if(isset($custom_fields['saswp_book_date_published'])){
                     $input1['datePublished'] =    date('Y-m-d\TH:i:s\Z',strtotime($custom_fields['saswp_book_date_published']));
                    }                    
                    if(isset($custom_fields['saswp_book_price_currency']) && isset($custom_fields['saswp_book_price'])){
                        $input1['offers']['@type']         = 'Offer';
                        $input1['offers']['availability']  = $custom_fields['saswp_book_availability'];
                        $input1['offers']['price']         = $custom_fields['saswp_book_price'];
                        $input1['offers']['priceCurrency'] = $custom_fields['saswp_book_price_currency'];
                    }                            
                    if(isset($custom_fields['saswp_book_rating_value']) && isset($custom_fields['saswp_book_rating_count'])){
                        $input1['aggregateRating']['@type']         = 'aggregateRating';
                        $input1['aggregateRating']['worstRating']   =   0;
                       $input1['aggregateRating']['bestRating']     =   5;
                        $input1['aggregateRating']['ratingValue']   = $custom_fields['saswp_book_rating_value'];
                        $input1['aggregateRating']['ratingCount']   = $custom_fields['saswp_book_rating_count'];                                
                    }
                                        
                    break; 
                    
                case 'MusicPlaylist':      
                    
                    if(isset($custom_fields['saswp_music_playlist_name'])){
                     $input1['name'] =    $custom_fields['saswp_music_playlist_name'];
                    }
                    if(isset($custom_fields['saswp_music_playlist_description'])){
                     $input1['description'] =    $custom_fields['saswp_music_playlist_description'];
                    }
                    if(isset($custom_fields['saswp_music_playlist_url'])){
                     $input1['url'] =    $custom_fields['saswp_music_playlist_url'];
                    }
                                          
                    break; 
                    
                case 'MusicAlbum':      
                    
                    if(isset($custom_fields['saswp_music_album_name'])){
                     $input1['name'] =    $custom_fields['saswp_music_album_name'];
                    }
                    if(isset($custom_fields['saswp_music_album_url'])){
                     $input1['url'] =    $custom_fields['saswp_music_album_url'];
                    }
                    if(isset($custom_fields['saswp_music_album_description'])){
                     $input1['description'] =    $custom_fields['saswp_music_album_description'];
                    }
                    if(isset($custom_fields['saswp_music_album_genre'])){
                     $input1['genre'] =    $custom_fields['saswp_music_album_genre'];
                    }
                    if(isset($custom_fields['saswp_music_album_image'])){
                     $input1['image'] =    $custom_fields['saswp_music_album_image'];
                    }
                    if(isset($custom_fields['saswp_music_album_artist'])){
                     $input1['byArtist']['@type']     = 'MusicGroup';
                     $input1['byArtist']['name']      = $custom_fields['saswp_music_album_artist'];
                    }       
                    
                    break;     
                 
                case 'Article':      
                     
                    if(isset($custom_fields['saswp_article_main_entity_of_page'])){
                     $input1['mainEntityOfPage'] =    $custom_fields['saswp_article_main_entity_of_page'];
                    }
                    if(isset($custom_fields['saswp_article_image'])){
                     $input1['image'] =    $custom_fields['saswp_article_image'];
                    }
                    if(isset($custom_fields['saswp_article_url'])){
                     $input1['url'] =    $custom_fields['saswp_article_url'];
                    }
                    if(isset($custom_fields['saswp_article_body'])){
                     $input1['articleBody'] =    $custom_fields['saswp_article_body'];
                    }
                    if(isset($custom_fields['saswp_article_keywords'])){
                     $input1['keywords'] =    $custom_fields['saswp_article_keywords'];
                    }
                    if(isset($custom_fields['saswp_article_section'])){
                     $input1['articleSection'] =    $custom_fields['saswp_article_section'];
                    }
                    if(isset($custom_fields['saswp_article_headline'])){
                     $input1['headline'] =    $custom_fields['saswp_article_headline'];
                    }                    
                    if(isset($custom_fields['saswp_article_description'])){
                     $input1['description'] =    $custom_fields['saswp_article_description'];
                    }
                    if(isset($custom_fields['saswp_article_date_published'])){
                     $input1['datePublished'] =    $custom_fields['saswp_article_date_published'];
                    }
                    if(isset($custom_fields['saswp_article_date_modified'])){
                     $input1['dateModified'] =    $custom_fields['saswp_article_date_modified'];
                    }                    
                    if(isset($custom_fields['saswp_article_author_name'])){
                     $input1['author']['name'] =    $custom_fields['saswp_article_author_name'];
                    }                    
                    if(isset($custom_fields['saswp_article_organization_logo']) && isset($custom_fields['saswp_article_organization_name'])){
                     $input1['Publisher']['@type']       =    'Organization';
                     $input1['Publisher']['name']        =    $custom_fields['saswp_article_organization_name'];
                     $input1['Publisher']['logo']        =    $custom_fields['saswp_article_organization_logo'];
                    }                    
                    break; 
                    
                case 'HowTo':      
                      
                    if(isset($custom_fields['saswp_howto_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_howto_schema_name'];
                    }
                    if(isset($custom_fields['saswp_howto_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_howto_schema_description'];
                    }
                    if(isset($custom_fields['saswp_howto_ec_schema_currency'])){
                     $input1['estimatedCost']['currency'] =    $custom_fields['saswp_howto_ec_schema_currency'];
                    }
                    if(isset($custom_fields['saswp_howto_ec_schema_value'])){
                     $input1['estimatedCost']['value'] =    $custom_fields['saswp_howto_ec_schema_value'];
                    }
                    if(isset($custom_fields['saswp_howto_schema_totaltime'])){
                     $input1['totalTime']     =    $custom_fields['saswp_howto_schema_totaltime'];
                    }
                    if(isset($custom_fields['saswp_howto_ec_schema_date_published'])){
                     $input1['datePublished'] =    $custom_fields['saswp_howto_ec_schema_date_published'];
                    }
                    if(isset($custom_fields['saswp_howto_ec_schema_date_modified'])){
                     $input1['dateModified'] =    $custom_fields['saswp_howto_ec_schema_date_modified'];
                    }
                    if(isset($custom_fields['saswp_howto_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_howto_schema_image'];
                    }
                                                            
                    break;     
                                  
                case 'local_business':
                   
                    if(isset($custom_fields['local_business_id'])){
                        $input1['@id'] =    $custom_fields['local_business_id'];
                    }                   
                    if(isset($custom_fields['saswp_business_type'])){                     
                     $input1['@type'] =    $custom_fields['saswp_business_type'];                     
                    }
                    if(isset($custom_fields['saswp_business_name'])){
                     $input1['@type'] =    $custom_fields['saswp_business_name'];
                    }
                    if(isset($custom_fields['local_business_name'])){
                     $input1['name'] =    $custom_fields['local_business_name'];
                    }                    
                    if(isset($custom_fields['local_business_name_url'])){
                     $input1['url'] =    $custom_fields['local_business_name_url'];
                    }
                    if(isset($custom_fields['local_business_logo'])){
                     $input1['image'] =    $custom_fields['local_business_logo'];
                    }
                    if(isset($custom_fields['local_business_description'])){
                     $input1['description'] =    $custom_fields['local_business_description'];
                    }
                    if(isset($custom_fields['local_street_address'])){
                     $input1['address']['streetAddress'] =    $custom_fields['local_street_address'];
                    }                    
                    if(isset($custom_fields['local_city'])){
                     $input1['address']['addressLocality'] =    $custom_fields['local_city'];
                    }
                    if(isset($custom_fields['local_state'])){
                     $input1['address']['addressRegion'] =    $custom_fields['local_state'];
                    }
                    if(isset($custom_fields['local_postal_code'])){
                     $input1['address']['postalCode'] =    $custom_fields['local_postal_code'];
                    }                    
                    if(isset($custom_fields['local_latitude']) && isset($custom_fields['local_longitude'])){
                        
                     $input1['geo']['@type']     =    'GeoCoordinates';   
                     $input1['geo']['latitude']  =    $custom_fields['local_latitude'];
                     $input1['geo']['longitude'] =    $custom_fields['local_longitude'];
                     
                    }                    
                                                               
                    if(isset($custom_fields['local_phone'])){
                     $input1['telephone'] =    $custom_fields['local_phone'];
                    }
                    if(isset($custom_fields['local_website'])){
                     $input1['website'] =    $custom_fields['local_website'];
                    }                    
                    if(isset($custom_fields['saswp_dayofweek'])){
                     $input1['openingHours'] =    $custom_fields['saswp_dayofweek'];
                    }
                    if(isset($custom_fields['local_price_range'])){
                     $input1['priceRange'] =    $custom_fields['local_price_range'];
                    }
                    if(isset($custom_fields['local_hasmap'])){
                     $input1['hasMap'] =    $custom_fields['local_hasmap'];
                    }
                    if(isset($custom_fields['local_serves_cuisine'])){
                     $input1['servesCuisine'] =    $custom_fields['local_serves_cuisine'];
                    }                    
                    if(isset($custom_fields['local_menu'])){
                     $input1['hasMenu'] =    $custom_fields['local_menu'];
                    }
                    
                    if(isset($custom_fields['local_rating_value']) && isset($custom_fields['local_rating_count'])){
                       $input1['aggregateRating']['@type']       =   'AggregateRating';
                       $input1['aggregateRating']['worstRating'] =   0;
                       $input1['aggregateRating']['bestRating']  =   5;
                       $input1['aggregateRating']['ratingValue'] =    $custom_fields['local_rating_value'];
                       $input1['aggregateRating']['ratingCount'] =    $custom_fields['local_rating_count'];
                    }
                                     
                    break;
                
                case 'Blogposting':
                                       
                    if(isset($custom_fields['saswp_blogposting_main_entity_of_page'])){
                     $input1['mainEntityOfPage'] =    $custom_fields['saswp_blogposting_main_entity_of_page'];
                    }
                    if(isset($custom_fields['saswp_blogposting_headline'])){
                     $input1['headline'] =    $custom_fields['saswp_blogposting_headline'];
                    }
                    if(isset($custom_fields['saswp_blogposting_description'])){
                     $input1['description'] =    $custom_fields['saswp_blogposting_description'];
                    }
                    if(isset($custom_fields['saswp_blogposting_name'])){
                     $input1['name'] =    $custom_fields['saswp_blogposting_name'];
                    }
                    if(isset($custom_fields['saswp_blogposting_url'])){
                     $input1['url'] =    $custom_fields['saswp_blogposting_url'];
                    }
                    if(isset($custom_fields['saswp_blogposting_date_published'])){
                     $input1['datePublished'] =    $custom_fields['saswp_blogposting_date_published'];
                    }
                    if(isset($custom_fields['saswp_blogposting_date_modified'])){
                     $input1['dateModified'] =    $custom_fields['saswp_blogposting_date_modified'];
                    }
                    if(isset($custom_fields['saswp_blogposting_author_name'])){
                     $input1['author']['name'] =    $custom_fields['saswp_blogposting_author_name'];
                    }
                                        
                    if(isset($custom_fields['saswp_blogposting_organization_logo']) && isset($custom_fields['saswp_blogposting_organization_name'])){
                     $input1['Publisher']['@type']       =    'Organization';
                     $input1['Publisher']['name']        =    $custom_fields['saswp_blogposting_organization_name'];
                     $input1['Publisher']['logo']        =    $custom_fields['saswp_blogposting_organization_logo'];
                    }
                    
                    
                    break;
                    
                case 'AudioObject':
                    
                    if(isset($custom_fields['saswp_audio_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_audio_schema_name'];
                    }
                    if(isset($custom_fields['saswp_audio_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_audio_schema_description'];
                    }
                    if(isset($custom_fields['saswp_audio_schema_contenturl'])){
                     $input1['contentUrl'] =    $custom_fields['saswp_audio_schema_contenturl'];
                    }
                    if(isset($custom_fields['saswp_audio_schema_duration'])){
                     $input1['duration'] =    $custom_fields['saswp_audio_schema_duration'];
                    }
                    if(isset($custom_fields['saswp_audio_schema_encoding_format'])){
                     $input1['encodingFormat'] =    $custom_fields['saswp_audio_schema_encoding_format'];
                    }
                    
                    if(isset($custom_fields['saswp_audio_schema_date_published'])){
                     $input1['datePublished'] =    $custom_fields['saswp_audio_schema_date_published'];
                    }
                    if(isset($custom_fields['saswp_audio_schema_date_modified'])){
                     $input1['dateModified'] =    $custom_fields['saswp_audio_schema_date_modified'];
                    }
                    if(isset($custom_fields['saswp_audio_schema_author_name'])){
                     $input1['author']['name'] =    $custom_fields['saswp_audio_author_name'];
                    }                    
                    
                    break;   
                    
                case 'SoftwareApplication':
                    
                    if(isset($custom_fields['saswp_software_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_software_schema_name'];
                    }
                    if(isset($custom_fields['saswp_software_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_software_schema_description'];
                    }
                    if(isset($custom_fields['saswp_software_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_software_schema_image'];
                    }
                    if(isset($custom_fields['saswp_software_schema_operating_system'])){
                     $input1['operatingSystem'] =    $custom_fields['saswp_software_schema_operating_system'];
                    }
                    if(isset($custom_fields['saswp_software_schema_application_category'])){
                     $input1['applicationCategory'] =    $custom_fields['saswp_software_schema_application_category'];
                    }
                    if(isset($custom_fields['saswp_software_schema_price'])){
                     $input1['offers']['price'] =    $custom_fields['saswp_software_schema_price'];
                    }
                    if(isset($custom_fields['saswp_software_schema_price_currency'])){
                     $input1['offers']['priceCurrency'] =    $custom_fields['saswp_software_schema_price_currency'];
                    }                    
                    if(isset($custom_fields['saswp_software_schema_date_published'])){
                     $input1['datePublished'] =    $custom_fields['saswp_software_schema_date_published'];
                    }
                    if(isset($custom_fields['saswp_software_schema_date_modified'])){
                     $input1['dateModified'] =    $custom_fields['saswp_software_schema_date_modified'];
                    }
                    if(isset($custom_fields['saswp_software_rating_value']) && isset($custom_fields['saswp_software_rating_count'])){
                       $input1['aggregateRating']['@type']       =   'AggregateRating';
                       $input1['aggregateRating']['worstRating'] =   0;
                       $input1['aggregateRating']['bestRating']  =   5;
                       $input1['aggregateRating']['ratingValue'] =    $custom_fields['saswp_software_rating_value'];
                       $input1['aggregateRating']['ratingCount'] =    $custom_fields['saswp_software_rating_count'];
                    }
                    
                                                            
                    break;       
                
                case 'NewsArticle':
                                                                  
                    if(isset($custom_fields['saswp_newsarticle_main_entity_of_page'])){
                     $input1['mainEntityOfPage'] =    $custom_fields['saswp_newsarticle_main_entity_of_page'];
                    }
                    if(isset($custom_fields['saswp_newsarticle_URL'])){
                       $input1['url'] =    $custom_fields['saswp_newsarticle_URL']; 
                    }
                    if(isset($custom_fields['saswp_newsarticle_headline'])){
                       $input1['headline'] =    $custom_fields['saswp_newsarticle_headline']; 
                    }
                    if(isset($custom_fields['saswp_newsarticle_keywords'])){
                       $input1['keywords'] =    $custom_fields['saswp_newsarticle_keywords']; 
                    }
                    if(isset($custom_fields['saswp_newsarticle_date_published'])){
                       $input1['datePublished'] =    $custom_fields['saswp_newsarticle_date_published']; 
                    }
                    if(isset($custom_fields['saswp_newsarticle_date_modified'])){
                       $input1['dateModified'] =    $custom_fields['saswp_newsarticle_date_modified']; 
                    }
                    if(isset($custom_fields['saswp_newsarticle_description'])){
                       $input1['description'] =    $custom_fields['saswp_newsarticle_description'];  
                    }
                    if(isset($custom_fields['saswp_newsarticle_section'])){
                       $input1['articleSection'] = $custom_fields['saswp_newsarticle_section'];  
                    }
                    if(isset($custom_fields['saswp_newsarticle_body'])){
                       $input1['articleBody'] =    $custom_fields['saswp_newsarticle_body'];  
                    }
                    if(isset($custom_fields['saswp_newsarticle_name'])){
                       $input1['name'] =    $custom_fields['saswp_newsarticle_name'];  
                    }
                    if(isset($custom_fields['saswp_newsarticle_thumbnailurl'])){
                       $input1['thumbnailUrl'] =    $custom_fields['saswp_newsarticle_thumbnailurl'];  
                    }
                    if(isset($custom_fields['saswp_newsarticle_timerequired'])){
                       $input1['timeRequired'] =    $custom_fields['saswp_newsarticle_timerequired'];  
                    }
                    if(isset($custom_fields['saswp_newsarticle_main_entity_id'])){
                       $input1['mainEntity']['@id'] =    $custom_fields['saswp_newsarticle_main_entity_id'];  
                    }
                    if(isset($custom_fields['saswp_newsarticle_author_name'])){
                        $input1['author']['name'] =    $custom_fields['saswp_newsarticle_author_name']; 
                    }
                    if(isset($custom_fields['saswp_newsarticle_author_image'])){
                       $input1['author']['Image']['url'] =    $custom_fields['saswp_newsarticle_author_image'];  
                    }                    
                    if(isset($custom_fields['saswp_newsarticle_organization_logo']) && isset($custom_fields['saswp_newsarticle_organization_name'])){
                     $input1['Publisher']['@type']       =    'Organization';
                     $input1['Publisher']['name']        =    $custom_fields['saswp_newsarticle_organization_name'];
                     $input1['Publisher']['logo']        =    $custom_fields['saswp_newsarticle_organization_logo'];
                    }
                                        
                    break;
                
                case 'WebPage':
                    
                    if(isset($custom_fields['saswp_webpage_name'])){
                     $input1['name'] =    $custom_fields['saswp_webpage_name'];
                    }
                    if(isset($custom_fields['saswp_webpage_url'])){
                     $input1['url'] =    $custom_fields['saswp_webpage_url'];
                    }
                    if(isset($custom_fields['saswp_webpage_description'])){
                     $input1['description'] =    $custom_fields['saswp_webpage_description'];
                    }
                    
                    if(isset($custom_fields['saswp_webpage_main_entity_of_page'])){
                     $input1['mainEntity']['mainEntityOfPage'] =    $custom_fields['saswp_webpage_main_entity_of_page'];
                    }
                    if(isset($custom_fields['saswp_webpage_image'])){
                     $input1['mainEntity']['image'] =    $custom_fields['saswp_webpage_image'];
                    }
                    if(isset($custom_fields['saswp_webpage_headline'])){
                     $input1['mainEntity']['headline'] =    $custom_fields['saswp_webpage_headline'];
                    }
                    
                    if(isset($custom_fields['saswp_webpage_date_published'])){
                     $input1['mainEntity']['datePublished'] =    $custom_fields['saswp_webpage_date_published'];
                    }
                    if(isset($custom_fields['saswp_webpage_date_modified'])){
                     $input1['mainEntity']['dateModified'] =    $custom_fields['saswp_webpage_date_modified'];
                    }
                    if(isset($custom_fields['saswp_webpage_author_name'])){
                     $input1['mainEntity']['author']['name'] =    $custom_fields['saswp_webpage_author_name'];
                    }
                    
                    if(isset($custom_fields['saswp_webpage_organization_name'])){
                     $input1['mainEntity']['Publisher']['name'] =    $custom_fields['saswp_webpage_organization_name'];
                    }
                    if(isset($custom_fields['saswp_webpage_organization_logo'])){
                     $input1['mainEntity']['Publisher']['logo']['url'] =    $custom_fields['saswp_webpage_organization_logo'];
                    }
                    
                    break;
                                                    
                case 'Event':      
                      
                    if(isset($custom_fields['saswp_event_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_event_schema_name'];
                    }
                    if(isset($custom_fields['saswp_event_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_event_schema_description'];
                    }
                                       
                    if(isset($custom_fields['saswp_event_schema_location_name']) || isset($custom_fields['saswp_event_schema_location_streetaddress'])){
                        
                            $input1['location']['@type'] = 'Place';   
                            $input1['location']['name']  =    $custom_fields['saswp_event_schema_location_name'];

                            if(isset($custom_fields['saswp_event_schema_location_streetaddress'])){
                              $input1['location']['address']['streetAddress'] =    $custom_fields['saswp_event_schema_location_streetaddress'];
                            }                                          
                            if(isset($custom_fields['saswp_event_schema_location_locality'])){
                             $input1['location']['address']['addressLocality'] =    $custom_fields['saswp_event_schema_location_locality'];
                            }
                            if(isset($custom_fields['saswp_event_schema_location_region'])){
                             $input1['location']['address']['addressRegion'] =    $custom_fields['saswp_event_schema_location_region'];
                            }                    
                            if(isset($custom_fields['saswp_event_schema_location_postalcode'])){
                             $input1['location']['address']['postalCode'] =    $custom_fields['saswp_event_schema_location_postalcode'];
                            }
                            if(isset($custom_fields['saswp_event_schema_location_hasmap'])){
                             $input1['location']['hasMap']  =  $custom_fields['saswp_event_schema_location_hasmap'];
                            }
                    }                                        
                    
                    if(isset($custom_fields['saswp_event_schema_start_date'])){
                     $input1['startDate'] =    $custom_fields['saswp_event_schema_start_date'];
                    }
                    if(isset($custom_fields['saswp_event_schema_end_date'])){
                     $input1['endDate'] =    $custom_fields['saswp_event_schema_end_date'];
                    }
                    
                    if(isset($custom_fields['saswp_event_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_event_schema_image'];
                    }
                    if(isset($custom_fields['saswp_event_schema_performer_name'])){
                     $input1['performer']['name'] =    $custom_fields['saswp_event_schema_performer_name'];
                    }
                    if(isset($custom_fields['saswp_event_schema_price'])){
                     $input1['offers']['price'] =    $custom_fields['saswp_event_schema_price'];
                    }
                    if(isset($custom_fields['saswp_event_schema_price_currency'])){
                     $input1['offers']['priceCurrency'] =    $custom_fields['saswp_event_schema_price_currency'];
                    }
                    if(isset($custom_fields['saswp_event_schema_availability'])){
                     $input1['offers']['availability'] =    $custom_fields['saswp_event_schema_availability'];
                    }
                    if(isset($custom_fields['saswp_event_schema_validfrom'])){
                     $input1['offers']['validFrom'] =    $custom_fields['saswp_event_schema_validfrom'];
                    }
                    if(isset($custom_fields['saswp_event_schema_url'])){
                     $input1['offers']['url'] =    $custom_fields['saswp_event_schema_url'];
                    }
                    
                    break;    
                    
                case 'TechArticle':      
                      
                    if(isset($custom_fields['saswp_tech_article_main_entity_of_page'])){
                     $input1['mainEntityOfPage'] =    $custom_fields['saswp_tech_article_main_entity_of_page'];
                    }
                    if(isset($custom_fields['saswp_tech_article_image'])){
                     $input1['image'] =    $custom_fields['saswp_tech_article_image'];
                    }
                    if(isset($custom_fields['saswp_tech_article_url'])){
                     $input1['url'] =    $custom_fields['saswp_tech_article_url'];
                    }
                    if(isset($custom_fields['saswp_tech_article_body'])){
                     $input1['articleBody'] =    $custom_fields['saswp_tech_article_body'];
                    }
                    if(isset($custom_fields['saswp_tech_article_keywords'])){
                     $input1['keywords'] =    $custom_fields['saswp_tech_article_keywords'];
                    }
                    if(isset($custom_fields['saswp_tech_article_section'])){
                     $input1['articleSection'] =    $custom_fields['saswp_tech_article_section'];
                    }
                    if(isset($custom_fields['saswp_tech_article_headline'])){
                     $input1['headline'] =    $custom_fields['saswp_tech_article_headline'];
                    }
                    
                    if(isset($custom_fields['saswp_tech_article_description'])){
                     $input1['description'] =    $custom_fields['saswp_tech_article_description'];
                    }
                    if(isset($custom_fields['saswp_tech_article_date_published'])){
                     $input1['datePublished'] =    $custom_fields['saswp_tech_article_date_published'];
                    }
                    if(isset($custom_fields['saswp_tech_article_date_modified'])){
                     $input1['dateModified'] =    $custom_fields['saswp_tech_article_date_modified'];
                    }
                    
                    if(isset($custom_fields['saswp_tech_article_author_name'])){
                     $input1['author']['name'] =    $custom_fields['saswp_tech_article_author_name'];
                    }
                     
                    if(isset($custom_fields['saswp_tech_article_organization_logo']) && isset($custom_fields['saswp_tech_article_organization_name'])){
                     $input1['Publisher']['@type']       =    'Organization';
                     $input1['Publisher']['name']        =    $custom_fields['saswp_tech_article_organization_name'];
                     $input1['Publisher']['logo']        =    $custom_fields['saswp_tech_article_organization_logo'];
                    }
                    break;   
                    
                case 'Course':      
                      
                    if(isset($custom_fields['saswp_course_name'])){
                     $input1['name'] =    $custom_fields['saswp_course_name'];
                    }
                    if(isset($custom_fields['saswp_course_description'])){
                     $input1['description'] =    $custom_fields['saswp_course_description'];
                    }
                    if(isset($custom_fields['saswp_course_url'])){
                     $input1['url'] =    $custom_fields['saswp_course_url'];
                    }                    
                    if(isset($custom_fields['saswp_course_date_published'])){
                     $input1['datePublished'] =    $custom_fields['saswp_course_date_published'];
                    }
                    if(isset($custom_fields['saswp_course_date_modified'])){
                     $input1['dateModified'] =    $custom_fields['saswp_course_date_modified'];
                    }
                    if(isset($custom_fields['saswp_course_provider_name'])){
                     $input1['provider']['name'] =    $custom_fields['saswp_course_provider_name'];
                    }
                    
                    if(isset($custom_fields['saswp_course_sameas'])){
                     $input1['provider']['sameAs'] =    $custom_fields['saswp_course_sameas'];
                    }
                    
                    break;    
                    
                case 'DiscussionForumPosting':      
                      
                    if(isset($custom_fields['saswp_dfp_headline'])){
                     $input1['headline'] =    $custom_fields['saswp_dfp_headline'];
                    }
                    if(isset($custom_fields['saswp_dfp_description'])){
                     $input1['description'] =    $custom_fields['saswp_dfp_description'];
                    }
                    if(isset($custom_fields['saswp_dfp_url'])){
                     $input1['url'] =    $custom_fields['saswp_dfp_url'];
                    }                    
                    if(isset($custom_fields['saswp_dfp_date_published'])){
                     $input1['datePublished'] =    $custom_fields['saswp_dfp_date_published'];
                    }
                    if(isset($custom_fields['saswp_dfp_date_modified'])){
                     $input1['dateModified'] =    $custom_fields['saswp_dfp_date_modified'];
                    }
                    if(isset($custom_fields['saswp_dfp_author_name'])){
                     $input1['author']['name'] =    $custom_fields['saswp_dfp_author_name'];
                    }
                    
                    if(isset($custom_fields['saswp_dfp_main_entity_of_page'])){
                     $input1['mainEntityOfPage'] =    $custom_fields['saswp_dfp_main_entity_of_page'];
                    }
                    
                    if(isset($custom_fields['saswp_dfp_organization_logo']) && isset($custom_fields['saswp_dfp_organization_name'])){
                     $input1['Publisher']['@type']       =    'Organization';
                     $input1['Publisher']['name']        =    $custom_fields['saswp_dfp_organization_name'];
                     $input1['Publisher']['logo']        =    $custom_fields['saswp_dfp_organization_logo'];
                    }                                                            
                    break;        
                
                case 'Recipe':
                    if(isset($custom_fields['saswp_recipe_url'])){
                     $input1['url'] =    $custom_fields['saswp_recipe_url'];
                    }
                    if(isset($custom_fields['saswp_recipe_name'])){
                     $input1['name'] =    $custom_fields['saswp_recipe_name'];
                    }
                    if(isset($custom_fields['saswp_recipe_date_published'])){
                     $input1['datePublished'] =    $custom_fields['saswp_recipe_date_published'];
                    }
                    
                    if(isset($custom_fields['saswp_recipe_date_modified'])){
                     $input1['dateModified'] =    $custom_fields['saswp_recipe_date_modified'];
                    }
                    if(isset($custom_fields['saswp_recipe_description'])){
                     $input1['description'] =    $custom_fields['saswp_recipe_description'];
                    }
                    if(isset($custom_fields['saswp_recipe_main_entity'])){
                     $input1['mainEntity']['@id'] =    $custom_fields['saswp_recipe_main_entity'];
                    }
                    
                    if(isset($custom_fields['saswp_recipe_author_name'])){
                     $input1['author']['name'] =    $custom_fields['saswp_recipe_author_name'];
                    }
                    if(isset($custom_fields['saswp_recipe_author_image'])){
                     $input1['author']['Image']['url'] =    $custom_fields['saswp_recipe_author_image'];
                    }
                    if(isset($custom_fields['saswp_recipe_organization_name'])){
                     $input1['mainEntity']['Publisher']['name'] =    $custom_fields['saswp_recipe_organization_name'];
                    }
                    
                    if(isset($custom_fields['saswp_recipe_organization_logo'])){
                     $input1['mainEntity']['Publisher']['logo']['url'] =    $custom_fields['saswp_recipe_organization_logo'];
                    }
                    if(isset($custom_fields['saswp_recipe_preptime'])){
                     $input1['prepTime'] =    $custom_fields['saswp_recipe_preptime'];
                    }
                    if(isset($custom_fields['saswp_recipe_cooktime'])){
                     $input1['cookTime'] =    $custom_fields['saswp_recipe_cooktime'];
                    }
                    
                    if(isset($custom_fields['saswp_recipe_totaltime'])){
                     $input1['totalTime'] =    $custom_fields['saswp_recipe_totaltime'];
                    }
                    if(isset($custom_fields['saswp_recipe_keywords'])){
                     $input1['keywords'] =    $custom_fields['saswp_recipe_keywords'];
                    }
                    if(isset($custom_fields['saswp_recipe_recipeyield'])){
                     $input1['recipeYield'] =    $custom_fields['saswp_recipe_recipeyield'];
                    }
                    
                    if(isset($custom_fields['saswp_recipe_category'])){
                     $input1['recipeCategory'] =    $custom_fields['saswp_recipe_category'];
                    }
                    if(isset($custom_fields['saswp_recipe_cuisine'])){
                     $input1['recipeCuisine'] =    $custom_fields['saswp_recipe_cuisine'];
                    }
                    if(isset($custom_fields['saswp_recipe_nutrition'])){
                     $input1['nutrition']['calories'] =    $custom_fields['saswp_recipe_nutrition'];
                    }
                    
                    if(isset($custom_fields['saswp_recipe_ingredient'])){
                     $input1['recipeIngredient'] =    $custom_fields['saswp_recipe_ingredient'];
                    }
                    if(isset($custom_fields['saswp_recipe_instructions'])){
                     $input1['recipeInstructions'] =    $custom_fields['saswp_recipe_instructions'];
                    }
                    if(isset($custom_fields['saswp_recipe_video_name'])){
                     $input1['video']['name'] =    $custom_fields['saswp_recipe_video_name'];
                    }
                    
                    if(isset($custom_fields['saswp_recipe_video_description'])){
                     $input1['video']['description'] =    $custom_fields['saswp_recipe_video_description'];
                    }
                    if(isset($custom_fields['saswp_recipe_video_thumbnailurl'])){
                     $input1['video']['thumbnailUrl'] =    $custom_fields['saswp_recipe_video_thumbnailurl'];
                    }
                    if(isset($custom_fields['saswp_recipe_video_contenturl'])){
                     $input1['video']['contentUrl'] =    $custom_fields['saswp_recipe_video_contenturl'];
                    }                    
                    if(isset($custom_fields['saswp_recipe_video_embedurl'])){
                     $input1['video']['embedUrl'] =    $custom_fields['saswp_recipe_video_embedurl'];
                    }
                    if(isset($custom_fields['saswp_recipe_video_upload_date'])){
                     $input1['video']['uploadDate'] =    $custom_fields['saswp_recipe_video_upload_date'];
                    }
                    if(isset($custom_fields['saswp_recipe_video_duration'])){
                     $input1['video']['duration'] =    $custom_fields['saswp_recipe_video_duration'];
                    } 
                    
                    if(isset($custom_fields['saswp_recipe_rating_value']) && isset($custom_fields['saswp_recipe_rating_count'])){
                       $input1['aggregateRating']['@type']       =   'AggregateRating';
                       $input1['aggregateRating']['worstRating'] =   0;
                       $input1['aggregateRating']['bestRating']  =   5;
                       $input1['aggregateRating']['ratingValue'] =    $custom_fields['saswp_recipe_rating_value'];
                       $input1['aggregateRating']['ratingCount'] =    $custom_fields['saswp_recipe_rating_count'];
                    }
                    
                    break;
                
                case 'Product':                                                                                                  
                    if(isset($custom_fields['saswp_product_url'])){
                     $input1['url'] =    $custom_fields['saswp_product_url'];
                    }
                    if(isset($custom_fields['saswp_product_name'])){
                     $input1['name'] =    $custom_fields['saswp_product_name'];
                    }
                    
                    if(isset($custom_fields['saswp_product_brand'])){
                     $input1['brand']['name'] =    $custom_fields['saswp_product_brand'];
                    }
                    
                    if(isset($custom_fields['saswp_product_mpn'])){
                     $input1['mpn'] =    $custom_fields['saswp_product_mpn'];
                    }
                    if(isset($custom_fields['saswp_product_gtin8'])){
                     $input1['gtin8'] =    $custom_fields['saswp_product_gtin8'];
                    }                    
                    
                    if(isset($custom_fields['saswp_product_description'])){
                     $input1['description'] =    $custom_fields['saswp_product_description'];
                    }                    
                    if(isset($custom_fields['saswp_product_image'])){
                     $input1['image'] =    $custom_fields['saswp_product_image'];
                    }
                    if(isset($custom_fields['saswp_product_availability'])){
                     $input1['offers']['availability'] =    $custom_fields['saswp_product_availability'];
                     if(isset($custom_fields['saswp_product_url'])){
                         $input1['offers']['url']   =    $custom_fields['saswp_product_url'];
                     }
                    }
                    if(isset($custom_fields['saswp_product_price'])){
                     $input1['offers']['price'] =    $custom_fields['saswp_product_price'];
                     
                     if(isset($custom_fields['saswp_product_url'])){
                         $input1['offers']['url']   =    $custom_fields['saswp_product_url'];
                     }
                                          
                    }
                    if(isset($custom_fields['saswp_product_currency'])){
                     $input1['offers']['priceCurrency'] =    $custom_fields['saswp_product_currency'];
                     $input1['offers']['url'] =    $custom_fields['saswp_product_url'];
                    }
                    if(isset($custom_fields['saswp_product_priceValidUntil'])){
                     $input1['offers']['priceValidUntil'] =    $custom_fields['saswp_product_priceValidUntil'];
                     
                    }                   
                    if(isset($custom_fields['saswp_product_condition'])){
                     $input1['offers']['itemCondition'] =    $custom_fields['saswp_product_condition'];
                    }
                    if(isset($custom_fields['saswp_product_sku'])){
                     $input1['sku']                    =    $custom_fields['saswp_product_sku'];
                    }
                    if(isset($custom_fields['saswp_product_seller'])){
                     $input1['seller']['@type']         =    'Organization';
                     $input1['seller']['name']          =    $custom_fields['saswp_product_seller'];
                    }
                    
                    if(isset($custom_fields['saswp_product_rating']) && isset($custom_fields['saswp_product_rating_count'])){
                     $input1['aggregateRating']['@type']       = 'aggregateRating';
                     $input1['aggregateRating']['ratingValue'] = $custom_fields['saswp_product_rating'];
                     $input1['aggregateRating']['reviewCount'] = $custom_fields['saswp_product_rating_count'];
                    }
                                                            
                    break;
                
                case 'Service':
                    if(isset($custom_fields['saswp_service_schema_name'])){
                      $input1['name'] =    $custom_fields['saswp_service_schema_name'];
                    }
                    if(isset($custom_fields['saswp_service_schema_type'])){
                      $input1['serviceType'] =    $custom_fields['saswp_service_schema_type'];
                    }
                    if(isset($custom_fields['saswp_service_schema_provider_type']) && isset($custom_fields['saswp_service_schema_provider_name'])){
                      $input1['provider']['@type'] =    $custom_fields['saswp_service_schema_provider_type'];
                      $input1['provider']['name']  =    $custom_fields['saswp_service_schema_provider_name'];
                    }                                        
                    if(isset($custom_fields['saswp_service_schema_image'])){
                      $input1['provider']['image'] =    $custom_fields['saswp_service_schema_image'];
                    }
                    if(isset($custom_fields['saswp_service_schema_locality'])){
                     $input1['provider']['address']['addressLocality'] =    $custom_fields['saswp_service_schema_locality'];
                    }
                    if(isset($custom_fields['saswp_service_schema_postal_code'])){
                      $input1['provider']['address']['postalCode'] =    $custom_fields['saswp_service_schema_postal_code'];
                    }
                    if(isset($custom_fields['saswp_service_schema_telephone'])){
                      $input1['provider']['address']['telephone'] =    $custom_fields['saswp_service_schema_telephone'];
                    }
                    if(isset($custom_fields['saswp_service_schema_price_range'])){
                      $input1['provider']['priceRange'] =    $custom_fields['saswp_service_schema_price_range'];
                    }
                    if(isset($custom_fields['saswp_service_schema_description'])){
                      $input1['description'] =    $custom_fields['saswp_service_schema_description'];
                    }
                    if(isset($custom_fields['saswp_service_schema_area_served'])){
                      $input1['areaServed'] =    $custom_fields['saswp_service_schema_area_served'];
                    }
                    if(isset($custom_fields['saswp_service_schema_service_offer'])){
                      $input1['hasOfferCatalog'] =    $custom_fields['saswp_service_schema_service_offer'];
                    }
                    
                    if(isset($custom_fields['saswp_service_schema_rating_value']) && isset($custom_fields['saswp_service_schema_rating_count'])){
                       $input1['aggregateRating']['@type']       =   'AggregateRating';
                       $input1['aggregateRating']['worstRating'] =   0;
                       $input1['aggregateRating']['bestRating']  =   5;
                       $input1['aggregateRating']['ratingValue'] =    $custom_fields['saswp_service_schema_rating_value'];
                       $input1['aggregateRating']['ratingCount'] =    $custom_fields['saswp_service_schema_rating_count'];
                    }
                                                          
                    break;
                
                case 'VideoObject':
                    
                    if(isset($custom_fields['saswp_video_object_url'])){
                     $input1['url'] =    $custom_fields['saswp_video_object_url'];
                    }
                    if(isset($custom_fields['saswp_video_object_headline'])){
                     $input1['headline'] =    $custom_fields['saswp_video_object_headline'];
                    }
                    if(isset($custom_fields['saswp_video_object_date_published'])){
                     $input1['datePublished'] =    $custom_fields['saswp_video_object_date_published'];
                    }
                    
                    if(isset($custom_fields['saswp_video_object_date_modified'])){
                     $input1['dateModified'] =    $custom_fields['saswp_video_object_date_modified'];
                    }
                    if(isset($custom_fields['saswp_video_object_description'])){
                     $input1['description'] =    $custom_fields['saswp_video_object_description'];
                    }
                    if(isset($custom_fields['saswp_video_object_name'])){
                     $input1['name'] =    $custom_fields['saswp_video_object_name'];
                    }
                    
                    if(isset($custom_fields['saswp_video_object_upload_date'])){
                     $input1['uploadDate'] =    $custom_fields['saswp_video_object_upload_date'];
                    }
                    if(isset($custom_fields['saswp_video_object_thumbnail_url'])){
                     $input1['thumbnailUrl'] =    $custom_fields['saswp_video_object_thumbnail_url'];
                    }                                        
                    if(isset($custom_fields['saswp_video_object_content_url'])){
                     $input1['thumbnailUrl'] =    $custom_fields['saswp_video_object_content_url'];
                    }
                    if(isset($custom_fields['saswp_video_object_embed_url'])){
                     $input1['thumbnailUrl'] =    $custom_fields['saswp_video_object_embed_url'];
                    }                                                            
                    if(isset($custom_fields['saswp_video_object_main_entity_id'])){
                     $input1['mainEntity']['@id'] =    $custom_fields['saswp_video_object_main_entity_id'];
                    }
                    
                    if(isset($custom_fields['saswp_video_object_author_name'])){
                     $input1['author']['name'] =    $custom_fields['saswp_video_object_author_name'];
                    }
                    if(isset($custom_fields['saswp_video_object_author_image'])){
                     $input1['author']['Image']['url'] =    $custom_fields['saswp_video_object_author_image'];
                    }                      
                    if(isset($custom_fields['saswp_video_object_organization_logo']) && isset($custom_fields['saswp_video_object_organization_name'])){
                     $input1['Publisher']['@type']       =    'Organization';
                     $input1['Publisher']['name']        =    $custom_fields['saswp_video_object_organization_name'];
                     $input1['Publisher']['logo']        =    $custom_fields['saswp_video_object_organization_logo'];
                    }
                    break;
                
                case 'qanda':
                    
                    if(isset($custom_fields['saswp_qa_question_title'])){
                     $input1['mainEntity']['name'] =    $custom_fields['saswp_qa_question_title'];
                    }
                    if(isset($custom_fields['saswp_qa_question_description'])){
                     $input1['mainEntity']['text'] =    $custom_fields['saswp_qa_question_description'];
                    }
                    if(isset($custom_fields['saswp_qa_upvote_count'])){
                     $input1['mainEntity']['upvoteCount'] =    $custom_fields['saswp_qa_upvote_count'];
                    }
                    
                    if(isset($custom_fields['saswp_qa_date_created'])){
                     $input1['mainEntity']['dateCreated'] =    $custom_fields['saswp_qa_date_created'];
                    }
                    if(isset($custom_fields['saswp_qa_question_author_name'])){
                     $input1['mainEntity']['author']['name'] =    $custom_fields['saswp_qa_question_author_name'];
                    }
                    if(isset($custom_fields['saswp_qa_accepted_answer_text'])){
                     $input1['mainEntity']['acceptedAnswer']['text'] =    $custom_fields['saswp_qa_accepted_answer_text'];
                    }
                    
                    if(isset($custom_fields['saswp_qa_accepted_answer_date_created'])){
                     $input1['mainEntity']['acceptedAnswer']['dateCreated'] =    $custom_fields['saswp_qa_accepted_answer_date_created'];
                    }
                    if(isset($custom_fields['saswp_qa_accepted_answer_upvote_count'])){
                     $input1['mainEntity']['acceptedAnswer']['upvoteCount'] =    $custom_fields['saswp_qa_accepted_answer_upvote_count'];
                    }
                    if(isset($custom_fields['saswp_qa_accepted_answer_url'])){
                     $input1['mainEntity']['acceptedAnswer']['url'] =    $custom_fields['saswp_qa_accepted_answer_url'];
                    }
                    
                    if(isset($custom_fields['saswp_qa_accepted_author_name'])){
                     $input1['mainEntity']['acceptedAnswer']['author']['name'] =    $custom_fields['saswp_qa_accepted_author_name'];
                    }                                        
                    if(isset($custom_fields['saswp_qa_suggested_answer_text'])){
                     $input1['mainEntity']['suggestedAnswer']['text'] =    $custom_fields['saswp_qa_suggested_answer_text'];
                    }
                    if(isset($custom_fields['saswp_qa_suggested_answer_date_created'])){
                     $input1['mainEntity']['suggestedAnswer']['dateCreated'] =    $custom_fields['saswp_qa_suggested_answer_date_created'];
                    }
                    
                    if(isset($custom_fields['saswp_qa_suggested_answer_upvote_count'])){
                     $input1['mainEntity']['suggestedAnswer']['upvoteCount'] =    $custom_fields['saswp_qa_suggested_answer_upvote_count'];
                    }
                    if(isset($custom_fields['saswp_qa_suggested_answer_url'])){
                     $input1['mainEntity']['suggestedAnswer']['url'] =    $custom_fields['saswp_qa_suggested_answer_url'];
                    }
                    if(isset($custom_fields['saswp_qa_suggested_author_name'])){
                     $input1['mainEntity']['suggestedAnswer']['author']['name'] =    $custom_fields['saswp_qa_suggested_author_name'];
                    }
                                        
                    break;
                    
                case 'TVSeries':      
                      
                    if(isset($custom_fields['saswp_tvseries_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_tvseries_schema_name'];
                    }
                    if(isset($custom_fields['saswp_tvseries_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_tvseries_schema_description'];
                    }
                    if(isset($custom_fields['saswp_tvseries_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_tvseries_schema_image'];
                    }
                    if(isset($custom_fields['saswp_tvseries_schema_author_name'])){
                     $input1['author']['name'] =    $custom_fields['saswp_tvseries_schema_author_name'];
                    }
                    
                break;
                
                case 'TouristAttraction':      
                      
                    if(isset($custom_fields['saswp_ta_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_ta_schema_name'];
                    }
                    if(isset($custom_fields['saswp_ta_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_ta_schema_description'];
                    }
                    if(isset($custom_fields['saswp_ta_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_ta_schema_image'];
                    }
                    if(isset($custom_fields['saswp_ta_schema_url'])){
                     $input1['url'] =    $custom_fields['saswp_ta_schema_url'];
                    }
                    if(isset($custom_fields['saswp_ta_schema_is_acceesible_free'])){
                     $input1['isAccessibleForFree'] =    $custom_fields['saswp_ta_schema_is_acceesible_free'];
                    }
                    if(isset($custom_fields['saswp_ta_schema_locality'])){
                     $input1['address']['addressLocality'] =    $custom_fields['saswp_ta_schema_locality'];
                    }
                    if(isset($custom_fields['saswp_ta_schema_region'])){
                     $input1['address']['addressRegion'] =    $custom_fields['saswp_ta_schema_region'];
                    }
                    if(isset($custom_fields['saswp_ta_schema_country'])){
                     $input1['address']['addressCountry'] =    $custom_fields['saswp_ta_schema_country'];
                    }
                    if(isset($custom_fields['saswp_ta_schema_postal_code'])){
                     $input1['address']['PostalCode'] =    $custom_fields['saswp_ta_schema_postal_code'];
                    }
                    if(isset($custom_fields['saswp_ta_schema_latitude']) && isset($custom_fields['saswp_ta_schema_longitude'])){                        
                     $input1['geo']['@type']     =    'GeoCoordinates';   
                     $input1['geo']['latitude']  =    $custom_fields['saswp_ta_schema_latitude'];
                     $input1['geo']['longitude'] =    $custom_fields['saswp_ta_schema_longitude'];                     
                    }
                    
                break;
                
                case 'FAQ':   
                    
                    if(isset($custom_fields['saswp_faq_headline'])){
                     $input1['headline'] =    $custom_fields['saswp_faq_headline'];
                    }
                    if(isset($custom_fields['saswp_faq_keywords'])){
                     $input1['keywords'] =    $custom_fields['saswp_faq_keywords'];
                    }
                    if(isset($custom_fields['saswp_faq_date_created'])){
                     $input1['datePublished'] =    $custom_fields['saswp_faq_date_created'];
                    }
                    if(isset($custom_fields['saswp_faq_date_published'])){
                     $input1['dateModified'] =    $custom_fields['saswp_faq_date_published'];
                    }
                    if(isset($custom_fields['saswp_faq_date_modified'])){
                     $input1['dateCreated'] =    $custom_fields['saswp_faq_date_modified'];
                    }                    
                    if(isset($custom_fields['saswp_faq_author'])){
                       $input1['author']['@type']             =   'Person';                                              
                       $input1['author']['name']              =    $custom_fields['saswp_faq_author'];                                              
                    }
                                                             
                break;
                
                case 'TouristDestination':      
                      
                    if(isset($custom_fields['saswp_td_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_td_schema_name'];
                    }
                    if(isset($custom_fields['saswp_td_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_td_schema_description'];
                    }
                    if(isset($custom_fields['saswp_td_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_td_schema_image'];
                    }
                    if(isset($custom_fields['saswp_td_schema_url'])){
                     $input1['url'] =    $custom_fields['saswp_td_schema_url'];
                    }
                    if(isset($custom_fields['saswp_td_schema_locality'])){
                     $input1['address']['addressLocality'] =    $custom_fields['saswp_td_schema_locality'];
                    }
                    if(isset($custom_fields['saswp_td_schema_region'])){
                     $input1['address']['addressRegion'] =    $custom_fields['saswp_td_schema_region'];
                    }
                    if(isset($custom_fields['saswp_td_schema_country'])){
                     $input1['address']['addressCountry'] =    $custom_fields['saswp_td_schema_country'];
                    }
                    if(isset($custom_fields['saswp_td_schema_postal_code'])){
                     $input1['address']['PostalCode'] =    $custom_fields['saswp_td_schema_postal_code'];
                    }
                    if(isset($custom_fields['saswp_td_schema_latitude']) && isset($custom_fields['saswp_td_schema_longitude'])){                        
                     $input1['geo']['@type']     =    'GeoCoordinates';   
                     $input1['geo']['latitude']  =    $custom_fields['saswp_td_schema_latitude'];
                     $input1['geo']['longitude'] =    $custom_fields['saswp_td_schema_longitude'];                     
                    }
                    
                break;
                
                case 'LandmarksOrHistoricalBuildings':      
                      
                    if(isset($custom_fields['saswp_lorh_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_lorh_schema_name'];
                    }
                    if(isset($custom_fields['saswp_lorh_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_lorh_schema_description'];
                    }
                    if(isset($custom_fields['saswp_lorh_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_lorh_schema_image'];
                    }
                    if(isset($custom_fields['saswp_lorh_schema_url'])){
                     $input1['url'] =    $custom_fields['saswp_lorh_schema_url'];
                    }
                    if(isset($custom_fields['saswp_lorh_schema_hasmap'])){
                     $input1['hasMap'] =    $custom_fields['saswp_lorh_schema_hasmap'];
                    }
                    if(isset($custom_fields['saswp_lorh_schema_is_acceesible_free'])){
                     $input1['isAccessibleForFree'] =    $custom_fields['saswp_lorh_schema_is_acceesible_free'];
                    }
                    if(isset($custom_fields['saswp_lorh_schema_maximum_a_capacity'])){
                     $input1['maximumAttendeeCapacity'] =    $custom_fields['saswp_lorh_schema_maximum_a_capacity'];
                    }
                    if(isset($custom_fields['saswp_lorh_schema_locality'])){
                     $input1['address']['addressLocality'] =    $custom_fields['saswp_lorh_schema_locality'];
                    }
                    if(isset($custom_fields['saswp_lorh_schema_region'])){
                     $input1['address']['addressRegion'] =    $custom_fields['saswp_lorh_schema_region'];
                    }
                    if(isset($custom_fields['saswp_lorh_schema_country'])){
                     $input1['address']['addressCountry'] =    $custom_fields['saswp_lorh_schema_country'];
                    }
                    if(isset($custom_fields['saswp_lorh_schema_postal_code'])){
                     $input1['address']['PostalCode'] =    $custom_fields['saswp_lorh_schema_postal_code'];
                    }
                    if(isset($custom_fields['saswp_lorh_schema_latitude']) && isset($custom_fields['saswp_lorh_schema_longitude'])){                        
                     $input1['geo']['@type']     =    'GeoCoordinates';   
                     $input1['geo']['latitude']  =    $custom_fields['saswp_lorh_schema_latitude'];
                     $input1['geo']['longitude'] =    $custom_fields['saswp_lorh_schema_longitude'];                     
                    }
                    
                break;
                
                case 'HinduTemple':      
                      
                    if(isset($custom_fields['saswp_hindutemple_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_hindutemple_schema_name'];
                    }
                    if(isset($custom_fields['saswp_hindutemple_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_hindutemple_schema_description'];
                    }
                    if(isset($custom_fields['saswp_hindutemple_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_hindutemple_schema_image'];
                    }
                    if(isset($custom_fields['saswp_hindutemple_schema_url'])){
                     $input1['url'] =    $custom_fields['saswp_hindutemple_schema_url'];
                    }
                    if(isset($custom_fields['saswp_hindutemple_schema_hasmap'])){
                     $input1['hasMap'] =    $custom_fields['saswp_hindutemple_schema_hasmap'];
                    }
                    if(isset($custom_fields['saswp_hindutemple_schema_is_accesible_free'])){
                     $input1['isAccessibleForFree'] =    $custom_fields['saswp_hindutemple_schema_is_accesible_free'];
                    }
                    if(isset($custom_fields['saswp_hindutemple_schema_maximum_a_capacity'])){
                     $input1['maximumAttendeeCapacity'] =    $custom_fields['saswp_hindutemple_schema_maximum_a_capacity'];
                    }
                    if(isset($custom_fields['saswp_hindutemple_schema_locality'])){
                     $input1['address']['addressLocality'] =    $custom_fields['saswp_hindutemple_schema_locality'];
                    }
                    if(isset($custom_fields['saswp_hindutemple_schema_region'])){
                     $input1['address']['addressRegion'] =    $custom_fields['saswp_hindutemple_schema_region'];
                    }
                    if(isset($custom_fields['saswp_hindutemple_schema_country'])){
                     $input1['address']['addressCountry'] =    $custom_fields['saswp_hindutemple_schema_country'];
                    }
                    if(isset($custom_fields['saswp_hindutemple_schema_postal_code'])){
                     $input1['address']['PostalCode'] =    $custom_fields['saswp_hindutemple_schema_postal_code'];
                    }
                    if(isset($custom_fields['saswp_hindutemple_schema_latitude']) && isset($custom_fields['saswp_hindutemple_schema_longitude'])){                        
                     $input1['geo']['@type']     =    'GeoCoordinates';   
                     $input1['geo']['latitude']  =    $custom_fields['saswp_hindutemple_schema_latitude'];
                     $input1['geo']['longitude'] =    $custom_fields['saswp_hindutemple_schema_longitude'];                     
                    }
                    
                break;
                
                case 'Church':      
                      
                    if(isset($custom_fields['saswp_church_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_church_schema_name'];
                    }
                    if(isset($custom_fields['saswp_church_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_church_schema_description'];
                    }
                    if(isset($custom_fields['saswp_church_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_church_schema_image'];
                    }
                    if(isset($custom_fields['saswp_church_schema_url'])){
                     $input1['url'] =    $custom_fields['saswp_church_schema_url'];
                    }
                    if(isset($custom_fields['saswp_church_schema_hasmap'])){
                     $input1['hasMap'] =    $custom_fields['saswp_church_schema_hasmap'];
                    }
                    if(isset($custom_fields['saswp_church_schema_is_accesible_free'])){
                     $input1['isAccessibleForFree'] =    $custom_fields['saswp_church_schema_is_accesible_free'];
                    }
                    if(isset($custom_fields['saswp_church_schema_maximum_a_capacity'])){
                     $input1['maximumAttendeeCapacity'] =    $custom_fields['saswp_church_schema_maximum_a_capacity'];
                    }
                    if(isset($custom_fields['saswp_church_schema_locality'])){
                     $input1['address']['addressLocality'] =    $custom_fields['saswp_church_schema_locality'];
                    }
                    if(isset($custom_fields['saswp_church_schema_region'])){
                     $input1['address']['addressRegion'] =    $custom_fields['saswp_church_schema_region'];
                    }
                    if(isset($custom_fields['saswp_church_schema_country'])){
                     $input1['address']['addressCountry'] =    $custom_fields['saswp_church_schema_country'];
                    }
                    if(isset($custom_fields['saswp_church_schema_postal_code'])){
                     $input1['address']['PostalCode'] =    $custom_fields['saswp_church_schema_postal_code'];
                    }
                    if(isset($custom_fields['saswp_church_schema_latitude']) && isset($custom_fields['saswp_church_schema_longitude'])){                        
                     $input1['geo']['@type']     =    'GeoCoordinates';   
                     $input1['geo']['latitude']  =    $custom_fields['saswp_church_schema_latitude'];
                     $input1['geo']['longitude'] =    $custom_fields['saswp_church_schema_longitude'];                     
                    }
                    
                break;
                
                case 'Mosque':      
                      
                    if(isset($custom_fields['saswp_mosque_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_mosque_schema_name'];
                    }
                    if(isset($custom_fields['saswp_mosque_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_mosque_schema_description'];
                    }
                    if(isset($custom_fields['saswp_mosque_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_mosque_schema_image'];
                    }
                    if(isset($custom_fields['saswp_mosque_schema_url'])){
                     $input1['url'] =    $custom_fields['saswp_mosque_schema_url'];
                    }
                    if(isset($custom_fields['saswp_mosque_schema_hasmap'])){
                     $input1['hasMap'] =    $custom_fields['saswp_mosque_schema_hasmap'];
                    }
                    if(isset($custom_fields['saswp_mosque_schema_is_accesible_free'])){
                     $input1['isAccessibleForFree'] =    $custom_fields['saswp_mosque_schema_is_accesible_free'];
                    }
                    if(isset($custom_fields['saswp_mosque_schema_maximum_a_capacity'])){
                     $input1['maximumAttendeeCapacity'] =    $custom_fields['saswp_mosque_schema_maximum_a_capacity'];
                    }
                    if(isset($custom_fields['saswp_mosque_schema_locality'])){
                     $input1['address']['addressLocality'] =    $custom_fields['saswp_mosque_schema_locality'];
                    }
                    if(isset($custom_fields['saswp_mosque_schema_region'])){
                     $input1['address']['addressRegion'] =    $custom_fields['saswp_mosque_schema_region'];
                    }
                    if(isset($custom_fields['saswp_mosque_schema_country'])){
                     $input1['address']['addressCountry'] =    $custom_fields['saswp_mosque_schema_country'];
                    }
                    if(isset($custom_fields['saswp_mosque_schema_postal_code'])){
                     $input1['address']['PostalCode'] =    $custom_fields['saswp_mosque_schema_postal_code'];
                    }
                    if(isset($custom_fields['saswp_mosque_schema_latitude']) && isset($custom_fields['saswp_mosque_schema_longitude'])){                        
                     $input1['geo']['@type']     =    'GeoCoordinates';   
                     $input1['geo']['latitude']  =    $custom_fields['saswp_mosque_schema_latitude'];
                     $input1['geo']['longitude'] =    $custom_fields['saswp_mosque_schema_longitude'];                     
                    }
                    
                break;
                
                case 'Person':      
                      
                    if(isset($custom_fields['saswp_person_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_person_schema_name'];
                    }
                    if(isset($custom_fields['saswp_person_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_person_schema_description'];
                    }
                    if(isset($custom_fields['saswp_person_schema_url'])){
                     $input1['url'] =    $custom_fields['saswp_person_schema_url'];
                    }
                    if(isset($custom_fields['saswp_person_schema_street_address'])){
                     $input1['address']['streetAddress'] =    $custom_fields['saswp_person_schema_street_address'];
                    }
                    if(isset($custom_fields['saswp_person_schema_locality'])){
                     $input1['address']['addressLocality'] =    $custom_fields['saswp_person_schema_locality'];
                    }
                    if(isset($custom_fields['saswp_person_schema_region'])){
                     $input1['address']['addressRegion'] =    $custom_fields['saswp_person_schema_region'];
                    }
                    if(isset($custom_fields['saswp_person_schema_postal_code'])){
                      $input1['address']['PostalCode']  =    $custom_fields['saswp_person_schema_postal_code'];
                    }
                    if(isset($custom_fields['saswp_person_schema_country'])){
                     $input1['address']['addressCountry'] =    $custom_fields['saswp_person_schema_country'];
                    }
                    if(isset($custom_fields['saswp_person_schema_email'])){
                     $input1['email'] =    $custom_fields['saswp_person_schema_email'];
                    }
                    if(isset($custom_fields['saswp_person_schema_telephone'])){
                     $input1['telephone'] =    $custom_fields['saswp_person_schema_telephone'];
                    }
                    if(isset($custom_fields['saswp_person_schema_gender'])){
                     $input1['gender'] =    $custom_fields['saswp_person_schema_gender'];
                    }
                    if(isset($custom_fields['saswp_person_schema_date_of_birth'])){
                     $input1['birthDate'] =    $custom_fields['saswp_person_schema_date_of_birth'];
                    }
                    
                    if(isset($custom_fields['saswp_person_schema_nationality'])){
                     $input1['nationality'] =    $custom_fields['saswp_person_schema_nationality'];
                    }
                    if(isset($custom_fields['saswp_person_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_person_schema_image'];
                    }
                    if(isset($custom_fields['saswp_person_schema_job_title'])){
                     $input1['jobTitle'] =    $custom_fields['saswp_person_schema_job_title'];
                    }
                    
                break;
                
                case 'Apartment':      
                      
                    if(isset($custom_fields['saswp_apartment_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_apartment_schema_name'];
                    }
                    if(isset($custom_fields['saswp_apartment_schema_url'])){
                     $input1['url'] =    $custom_fields['saswp_apartment_schema_url'];
                    }
                    if(isset($custom_fields['saswp_apartment_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_apartment_schema_image'];
                    }
                    if(isset($custom_fields['saswp_apartment_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_apartment_schema_description'];
                    }
                    if(isset($custom_fields['saswp_apartment_schema_numberofrooms'])){
                     $input1['numberOfRooms'] =    $custom_fields['saswp_apartment_schema_numberofrooms'];
                    }
                    if(isset($custom_fields['saswp_apartment_schema_country'])){
                     $input1['address']['addressCountry'] =    $custom_fields['saswp_apartment_schema_country'];
                    }
                    if(isset($custom_fields['saswp_apartment_schema_locality'])){
                     $input1['address']['addressLocality'] =    $custom_fields['saswp_apartment_schema_locality'];
                    }
                    if(isset($custom_fields['saswp_apartment_schema_region'])){
                     $input1['address']['addressRegion'] =    $custom_fields['saswp_apartment_schema_region'];
                    }
                    if(isset($custom_fields['saswp_apartment_schema_postalcode'])){
                     $input1['address']['PostalCode'] =    $custom_fields['saswp_apartment_schema_postalcode'];
                    }
                    if(isset($custom_fields['saswp_apartment_schema_telephone'])){
                     $input1['telephone'] =    $custom_fields['saswp_apartment_schema_telephone'];
                    }
                    if(isset($custom_fields['saswp_apartment_schema_latitude']) && isset($custom_fields['saswp_apartment_schema_longitude'])){                        
                     $input1['geo']['@type']     =    'GeoCoordinates';   
                     $input1['geo']['latitude']  =    $custom_fields['saswp_apartment_schema_latitude'];
                     $input1['geo']['longitude'] =    $custom_fields['saswp_apartment_schema_longitude'];                     
                    }
                    
                break;
                
                case 'House':      
                      
                    if(isset($custom_fields['saswp_house_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_house_schema_name'];
                    }
                    if(isset($custom_fields['saswp_house_schema_url'])){
                     $input1['url'] =    $custom_fields['saswp_house_schema_url'];
                    }
                    if(isset($custom_fields['saswp_house_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_house_schema_image'];
                    }
                    if(isset($custom_fields['saswp_house_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_house_schema_description'];
                    }
                    if(isset($custom_fields['saswp_house_schema_pets_allowed'])){
                     $input1['petsAllowed'] =    $custom_fields['saswp_house_schema_pets_allowed'];
                    }
                    if(isset($custom_fields['saswp_house_schema_country'])){
                     $input1['address']['addressCountry'] =    $custom_fields['saswp_house_schema_country'];
                    }
                    if(isset($custom_fields['saswp_house_schema_locality'])){
                     $input1['address']['addressLocality'] =    $custom_fields['saswp_house_schema_locality'];
                    }
                    if(isset($custom_fields['saswp_house_schema_region'])){
                     $input1['address']['addressRegion'] =    $custom_fields['saswp_house_schema_region'];
                    }
                    if(isset($custom_fields['saswp_house_schema_postalcode'])){
                     $input1['address']['PostalCode'] =    $custom_fields['saswp_house_schema_postalcode'];
                    }
                    if(isset($custom_fields['saswp_house_schema_telephone'])){
                     $input1['telephone'] =    $custom_fields['saswp_house_schema_telephone'];
                    }
                    if(isset($custom_fields['saswp_house_schema_hasmap'])){
                     $input1['hasMap'] =    $custom_fields['saswp_house_schema_hasmap'];
                    }
                    if(isset($custom_fields['saswp_house_schema_floor_size'])){
                     $input1['floorSize'] =    $custom_fields['saswp_house_schema_floor_size'];
                    }
                    if(isset($custom_fields['saswp_house_schema_no_of_rooms'])){
                     $input1['numberOfRooms'] =    $custom_fields['saswp_house_schema_no_of_rooms'];
                    }
                    if(isset($custom_fields['saswp_house_schema_latitude']) && isset($custom_fields['saswp_house_schema_longitude'])){                        
                     $input1['geo']['@type']     =    'GeoCoordinates';   
                     $input1['geo']['latitude']  =    $custom_fields['saswp_house_schema_latitude'];
                     $input1['geo']['longitude'] =    $custom_fields['saswp_house_schema_longitude'];                     
                    }
                    
                break;
                
                case 'SingleFamilyResidence':      
                      
                    if(isset($custom_fields['saswp_sfr_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_sfr_schema_name'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_url'])){
                     $input1['url'] =    $custom_fields['saswp_sfr_schema_url'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_sfr_schema_image'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_sfr_schema_description'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_numberofrooms'])){
                     $input1['numberOfRooms'] =    $custom_fields['saswp_sfr_schema_numberofrooms'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_pets_allowed'])){
                     $input1['petsAllowed'] =    $custom_fields['saswp_sfr_schema_pets_allowed'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_country'])){
                     $input1['address']['addressCountry'] =    $custom_fields['saswp_sfr_schema_country'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_locality'])){
                     $input1['address']['addressLocality'] =    $custom_fields['saswp_sfr_schema_locality'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_region'])){
                     $input1['address']['addressRegion'] =    $custom_fields['saswp_sfr_schema_region'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_postalcode'])){
                     $input1['address']['PostalCode'] =    $custom_fields['saswp_sfr_schema_postalcode'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_telephone'])){
                     $input1['telephone'] =    $custom_fields['saswp_sfr_schema_telephone'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_hasmap'])){
                     $input1['hasMap'] =    $custom_fields['saswp_sfr_schema_hasmap'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_floor_size'])){
                     $input1['floorSize'] =    $custom_fields['saswp_sfr_schema_floor_size'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_no_of_rooms'])){
                     $input1['numberOfRooms'] =    $custom_fields['saswp_sfr_schema_no_of_rooms'];
                    }
                    if(isset($custom_fields['saswp_sfr_schema_latitude']) && isset($custom_fields['saswp_sfr_schema_longitude'])){                        
                     $input1['geo']['@type']     =    'GeoCoordinates';   
                     $input1['geo']['latitude']  =    $custom_fields['saswp_sfr_schema_latitude'];
                     $input1['geo']['longitude'] =    $custom_fields['saswp_sfr_schema_longitude'];                     
                    }
                    
                break;
                
                case 'VideoGame':      
                      
                    if(isset($custom_fields['saswp_vg_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_vg_schema_name'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_url'])){
                     $input1['url'] =    $custom_fields['saswp_vg_schema_url'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_vg_schema_image'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_vg_schema_description'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_operating_system'])){
                     $input1['operatingSystem'] =    $custom_fields['saswp_vg_schema_operating_system'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_application_category'])){
                     $input1['applicationCategory'] =    $custom_fields['saswp_vg_schema_application_category'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_author_name'])){
                     $input1['author']['name'] =    $custom_fields['saswp_vg_schema_author_name'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_price'])){
                     $input1['offers']['price'] =    $custom_fields['saswp_vg_schema_price'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_price_currency'])){
                     $input1['offers']['priceCurrency'] =    $custom_fields['saswp_vg_schema_price_currency'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_price_availability'])){
                     $input1['offers']['availability'] =    $custom_fields['saswp_vg_schema_price_availability'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_publisher'])){
                     $input1['publisher'] =    $custom_fields['saswp_vg_schema_publisher'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_genre'])){
                     $input1['genre'] =    $custom_fields['saswp_vg_schema_genre'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_processor_requirements'])){
                     $input1['processorRequirements'] =    $custom_fields['saswp_vg_schema_processor_requirements'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_memory_requirements'])){
                     $input1['memoryRequirements'] =    $custom_fields['saswp_vg_schema_memory_requirements'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_storage_requirements'])){
                     $input1['storageRequirements'] =    $custom_fields['saswp_vg_schema_storage_requirements'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_game_platform'])){
                     $input1['gamePlatform'] =    $custom_fields['saswp_vg_schema_game_platform'];
                    }
                    if(isset($custom_fields['saswp_vg_schema_cheat_code'])){
                     $input1['cheatCode'] =    $custom_fields['saswp_vg_schema_cheat_code'];
                    }
                    
                break;
                
                case 'JobPosting':      
                      
                    if(isset($custom_fields['saswp_jobposting_schema_title'])){
                     $input1['title'] =    $custom_fields['saswp_jobposting_schema_title'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_jobposting_schema_description'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_url'])){
                     $input1['url'] =    $custom_fields['saswp_jobposting_schema_url'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_dateposted'])){
                     $input1['datePosted'] =    $custom_fields['saswp_jobposting_schema_dateposted'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_validthrough'])){
                     $input1['validThrough'] =    $custom_fields['saswp_jobposting_schema_validthrough'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_employment_type'])){
                     $input1['employmentType'] =    $custom_fields['saswp_jobposting_schema_employment_type'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_ho_name'])){
                     $input1['hiringOrganization']['name'] =    $custom_fields['saswp_jobposting_schema_ho_name'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_ho_url'])){
                     $input1['hiringOrganization']['sameAs'] =    $custom_fields['saswp_jobposting_schema_ho_url'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_ho_logo'])){
                     $input1['hiringOrganization']['logo'] =    $custom_fields['saswp_jobposting_schema_ho_logo'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_street_address'])){
                     $input1['jobLocation']['address']['streetAddress'] =    $custom_fields['saswp_jobposting_schema_street_address'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_locality'])){
                     $input1['jobLocation']['address']['addressLocality'] =    $custom_fields['saswp_jobposting_schema_locality'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_region'])){
                     $input1['jobLocation']['address']['addressRegion'] =    $custom_fields['saswp_jobposting_schema_region'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_postalcode'])){
                     $input1['jobLocation']['address']['PostalCode'] =    $custom_fields['saswp_jobposting_schema_postalcode'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_country'])){
                     $input1['jobLocation']['address']['addressCountry'] =    $custom_fields['saswp_jobposting_schema_country'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_bs_currency'])){
                     $input1['baseSalary']['currency'] =    $custom_fields['saswp_jobposting_schema_bs_currency'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_bs_value'])){
                     $input1['baseSalary']['value']['value'] =    $custom_fields['saswp_jobposting_schema_bs_value'];
                    }
                    if(isset($custom_fields['saswp_jobposting_schema_bs_unittext'])){
                     $input1['baseSalary']['value']['unitText'] =    $custom_fields['saswp_jobposting_schema_bs_unittext'];
                    }
                    
                break;
                
                case 'Trip':      
                      
                    if(isset($custom_fields['saswp_trip_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_trip_schema_name'];
                    }
                    if(isset($custom_fields['saswp_trip_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_trip_schema_description'];
                    }
                    if(isset($custom_fields['saswp_trip_schema_url'])){
                     $input1['url'] =    $custom_fields['saswp_trip_schema_url'];
                    }
                    if(isset($custom_fields['saswp_trip_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_trip_schema_image'];
                    }
                    
                break;
                
                case 'MedicalCondition':      
                      
                    if(isset($custom_fields['saswp_mc_schema_name'])){
                     $input1['name'] =    $custom_fields['saswp_mc_schema_name'];
                    }
                    if(isset($custom_fields['saswp_mc_schema_alternate_name'])){
                     $input1['alternateName'] =    $custom_fields['saswp_mc_schema_alternate_name'];
                    }
                    if(isset($custom_fields['saswp_mc_schema_description'])){
                     $input1['description'] =    $custom_fields['saswp_mc_schema_description'];
                    }
                    if(isset($custom_fields['saswp_mc_schema_image'])){
                     $input1['image'] =    $custom_fields['saswp_mc_schema_image'];
                    }
                    if(isset($custom_fields['saswp_mc_schema_anatomy_name'])){
                     $input1['associatedAnatomy']['name'] =    $custom_fields['saswp_mc_schema_anatomy_name'];
                    }
                    if(isset($custom_fields['saswp_mc_schema_medical_code'])){
                     $input1['code']['code'] =    $custom_fields['saswp_mc_schema_medical_code'];
                    }
                    if(isset($custom_fields['saswp_mc_schema_coding_system'])){
                     $input1['code']['codingSystem'] =    $custom_fields['saswp_mc_schema_coding_system'];
                    }                    
                    
                break;
               
                     default:
                         break;
                 }    
                 
             if($main_schema_type == 'Review'){
                 
                 $review_response['item_reviewed'] = $input1;
                 $review_response['review']        = $review_markup;
                 
                 return $review_response;
             }    
                 
            }     
            
            return $input1;   
        }

        /**
         * This is a ajax handler to get all the schema type keys 
         * @return type json
         */
        public function saswp_get_schema_type_fields(){
            
             if ( ! isset( $_POST['saswp_security_nonce'] ) ){
                return; 
             }
             if ( !wp_verify_nonce( $_POST['saswp_security_nonce'], 'saswp_ajax_check_nonce' ) ){
                return;  
             }
            
            $schema_subtype = isset( $_POST['schema_subtype'] ) ? sanitize_text_field( $_POST['schema_subtype'] ) : ''; 
            $schema_type    = isset( $_POST['schema_type'] ) ? sanitize_text_field( $_POST['schema_type'] ) : '';                      
                      
            if($schema_type == 'Review'){
                
                $meta_fields = $this->saswp_get_all_schema_type_fields($schema_subtype);

                $review_fields['saswp_review_name']         = 'Review Name';
                $review_fields['saswp_review_description']  = 'Review Description';
                $review_fields['saswp_review_body']         = 'Review Body';                
                $review_fields['saswp_review_author']       = 'Review Author';
                $review_fields['saswp_review_publisher']    = 'Review Publisher';
                $review_fields['saswp_review_rating_value'] = 'Review Rating Value';                

                $meta_fields = $review_fields + $meta_fields;
                
            }else{
                $meta_fields = $this->saswp_get_all_schema_type_fields($schema_type);  
            }
            
            wp_send_json( $meta_fields );                                   
        }
        
        /**
         * This function gets all the custom meta fields from the wordpress meta fields table
         * @global type $wpdb
         * @return type json
         */
        public function saswp_get_custom_meta_fields(){
            
             if ( ! isset( $_POST['saswp_security_nonce'] ) ){
                return; 
             }
             if ( !wp_verify_nonce( $_POST['saswp_security_nonce'], 'saswp_ajax_check_nonce' ) ){
                return;  
             }
            
            $search_string = isset( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : '';                                    
	    $data          = array();
	    $result        = array();
            
            global $wpdb;
	    $saswp_meta_array = $wpdb->get_results( "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE '%{$search_string}%'", ARRAY_A ); // WPCS: unprepared SQL OK.         
            if ( isset( $saswp_meta_array ) && ! empty( $saswp_meta_array ) ) {
                
				foreach ( $saswp_meta_array as $value ) {
				//	if ( ! in_array( $value['meta_key'], $schema_post_meta_fields ) ) {
						$data[] = array(
							'id'   => $value['meta_key'],
							'text' => preg_replace( '/^_/', '', esc_html( str_replace( '_', ' ', $value['meta_key'] ) ) ),
						);
					//}
				}
                                
			}
                        
            if ( is_array( $data ) && ! empty( $data ) ) {
                
				$result[] = array(
					'children' => $data,
				);
                                
			}
                        
            wp_send_json( $result );            
            
            wp_die();
        }
        
        /**
         * This function gets the product details in schema markup from the current product type post create by 
         * WooCommerce ( https://wordpress.org/plugins/woocommerce/ )
         * @param type $post_id
         * @return type array
         */
        public function saswp_woocommerce_product_details($post_id){     
                             
             $product_details = array(); 
             $varible_prices = array();
             
             if (class_exists('WC_Product')) {
                 
             global $woocommerce;
                 
	     $product = wc_get_product($post_id); 
             
             if(is_object($product)){   
                                               
               if(is_object($woocommerce)){
				 			
                        if($product->get_type() == 'variable'){

                           $product_id_some = $woocommerce->product_factory->get_product();

                            $variations  = $product_id_some->get_available_variations();

                                if($variations){

                                        foreach($variations as $value){

                                                $varible_prices[] = $value['display_price']; 

                                        }
                                }

                        }
				 				 
                }  
                 
             $gtin = get_post_meta($post_id, $key='hwp_product_gtin', true);
             
             if($gtin !=''){
                 
             $product_details['product_gtin8'] = $gtin;   
             
             }  
             
             $brand = '';
             $brand = get_post_meta($post_id, $key='hwp_product_brand', true);
             
             if($brand !=''){
                 
             $product_details['product_brand'] = $brand;   
             
             }
             
             if($brand == ''){
               
                 $product_details['product_brand'] = get_bloginfo();
                 
             }
                                                   
             $date_on_sale                           = $product->get_date_on_sale_to();                            
             $product_details['product_name']        = $product->get_title();
             
             if($product->get_short_description() && $product->get_description()){
                 
                 $product_details['product_description'] = $product->get_short_description().' '.$product->get_description();
                 
             }else if($product->get_description()){
                 
                 $product_details['product_description'] = $product->get_description();
                 
             }else{
                 
                 $product_details['product_description'] = strip_tags(get_the_excerpt());
                 
             }
                                       
             if($product->get_attributes()){
                 
                 foreach ($product->get_attributes() as $attribute) {
                     
                     if(strtolower($attribute['name']) == 'isbn'){
                                            
                      $product_details['product_isbn'] = $attribute['options'][0];   
                                                                 
                     }
                     if(strtolower($attribute['name']) == 'mpn'){
                                            
                      $product_details['product_mpn'] = $attribute['options'][0];   
                                                                 
                     }
                     if(strtolower($attribute['name']) == 'gtin8'){
                                            
                      $product_details['product_gtin8'] = $attribute['options'][0];   
                                                                 
                     }
                     if(strtolower($attribute['name']) == 'brand'){
                                            
                      $product_details['product_brand'] = $attribute['options'][0];   
                                                                 
                     }
                     
                 }
                 
             }
                
             if(!isset($product_details['product_mpn'])){
                 $product_details['product_mpn'] = get_the_ID();
             }
             
             $product_image_id  = $product->get_image_id(); 
             
             $image_list = array();
             
             if($product_image_id){
                                                                    
              $image_details = wp_get_attachment_image_src($product_image_id, 'full');
              
              if(!empty($image_details)){
                  
                 $size_array = array('full', 'large', 'medium', 'thumbnail');
                                                   
                 for($i =0; $i< count($size_array); $i++){
                                                    
                    $image_details   = wp_get_attachment_image_src($product_image_id, $size_array[$i]); 

                        if(!empty($image_details)){

                                $image_list['image'][$i]['@type']  = 'ImageObject';
                                $image_list['image'][$i]['url']    = esc_url($image_details[0]);
                                $image_list['image'][$i]['width']  = esc_attr($image_details[1]);
                                $image_list['image'][$i]['height'] = esc_attr($image_details[2]);

                        }
                                                    
                   }
                 
                 }
              
             }
             
             if(!empty($image_list)){
                 
                 $product_details['product_image'] = $image_list;
             }
                               
             if(strtolower( $product->get_stock_status() ) == 'onbackorder'){
                 $product_details['product_availability'] = 'PreOrder';
             }else{
                 $product_details['product_availability'] = $product->get_stock_status();
             }
                          
             $product_details['product_price']           = $product->get_price();
             $product_details['product_varible_price']   = $varible_prices;
             $product_details['product_sku']             = $product->get_sku() ? $product->get_sku(): get_the_ID();             
             
             if(isset($date_on_sale)){
                 
             $product_details['product_priceValidUntil'] = $date_on_sale->date('Y-m-d G:i:s');    
             
             }else{
                 
             $product_details['product_priceValidUntil'] = get_the_modified_date("c"); 
             
             }       
             
             $product_details['product_currency'] = get_option( 'woocommerce_currency' );             
             
             $reviews_arr = array();
             $reviews     = get_approved_comments( $post_id );
             
             if($reviews){
                 
             foreach($reviews as $review){                 
                 
                 $reviews_arr[] = array(
                     'author'        => $review->comment_author ? $review->comment_author : 'Anonymous' ,
                     'datePublished' => $review->comment_date,
                     'description'   => $review->comment_content,
                     'reviewRating'  => get_comment_meta( $review->comment_ID, 'rating', true ) ? get_comment_meta( $review->comment_ID, 'rating', true ) : '5',
                 );
                 
             }   
             
             $product_details['product_review_count']   = $product->get_review_count();
             $product_details['product_average_rating'] = $product->get_average_rating();             
             
             }else{
                 
                 $reviews_arr[] = array(
                     'author'        => saswp_get_the_author_name(),
                     'datePublished' => get_the_date("c"),
                     'description'   => saswp_get_the_excerpt(),
                     'reviewRating'  => 5,
                 );
                 
                 $product_details['product_review_count']   = 1;
                 $product_details['product_average_rating'] = 5;                 
             }    
                          
             $product_details['product_reviews']        = $reviews_arr;      
             
             }
             
             }                                                                 
             return $product_details;                       
        }
        
        /**
         * This function gets the review details in schema markup from the current post which has extra theme enabled
         * Extra Theme ( https://www.elegantthemes.com/preview/Extra/ )
         * @global type $sd_data
         * @param type $post_id
         * @return type array
         */
        public function saswp_extra_theme_review_details($post_id){
            
            global $sd_data;
           
            $review_data        = array();
            $rating_value       = 0;
            $post_review_title  = '';
            $post_review_desc   = '';
            
            $post_meta   = get_post_meta($post_id, $key='', true);                                       
            
            if(isset($post_meta['_post_review_box_breakdowns_score'])){
                
              if(function_exists('bcdiv')){
                  $rating_value = bcdiv($post_meta['_post_review_box_breakdowns_score'][0], 20, 2);        
              }  
                                          
            }
            if(isset($post_meta['_post_review_box_title'])){
              $post_review_title = $post_meta['_post_review_box_title'][0];     
            }
            if(isset($post_meta['_post_review_box_summary'])){
              $post_review_desc = $post_meta['_post_review_box_summary'][0];        
            }                            
            if($post_review_title && $rating_value>0 &&  (isset($sd_data['saswp-extra']) && $sd_data['saswp-extra'] ==1) && get_template()=='Extra'){
            
            $review_data['aggregateRating'] = array(
                '@type'         => 'AggregateRating',
                'ratingValue'   => $rating_value,
                'reviewCount'   => 1,
            );
            
            $review_data['review'] = array(
                '@type'         => 'Review',
                'author'        => get_the_author(),
                'datePublished' => get_the_date("c"),
                'name'          => $post_review_title,
                'reviewBody'    => $post_review_desc,
                'reviewRating' => array(
                            '@type'       => 'Rating',
                            'ratingValue' => $rating_value,
                ),
                
            );
            
           }
           return $review_data;
            
        }
         /**
         * This function gets topic details as an array from bbpress posts
         * DW Question & Answer ( https://wordpress.org/plugins/bbpress/ )
         * @global type $sd_data
         * @param type $post_id
         * @return type array
         */       
        public function saswp_bb_press_topic_details($post_id){
                            
                $dw_qa          = array();
                $qa_page        = array();
                                                                                                                                              
                $dw_qa['@type']       = 'Question';
                $dw_qa['name']        = bbp_get_topic_title($post_id); 
                $dw_qa['upvoteCount'] = bbp_get_topic_reply_count();    
                $dw_qa['text']        = wp_strip_all_tags(bbp_get_topic_content());                                
                $dw_qa['dateCreated'] = date_format(date_create(get_post_time( get_option( 'date_format' ), false, $post_id, true )), "Y-m-d\TH:i:s\Z");
                                                                          
                $dw_qa['author']      = array(
                                                 '@type' => 'Person',
                                                 'name'  =>bbp_get_topic_author($post_id),
                                            ); 
                
                $dw_qa['answerCount'] = bbp_get_topic_reply_count();   
                
                $args = array(
			'post_type'     => 'reply',
			'post_parent'   => $post_id,
			'post_per_page' => '-1',
			'post_status'   => array('publish')
		);
                
                $answer_array = get_posts($args);                
                               
                $suggested_answer = array();
                
                foreach($answer_array as $answer){
                                       
                        $authorinfo = get_userdata($answer->post_author);  
                        
                        $suggested_answer[] =  array(
                            '@type'       => 'Answer',
                            'upvoteCount' => 1,
                            'url'         => get_permalink($answer->ID),
                            'text'        => wp_strip_all_tags($answer->post_content),
                            'dateCreated' => get_the_date("Y-m-d\TH:i:s\Z", $answer),
                            'author'      => array('@type' => 'Person', 'name' => $authorinfo->data->user_nicename),
                        );
                        
                    
                }
                                
                $dw_qa['suggestedAnswer'] = $suggested_answer;
                    
                $qa_page['@context']   = saswp_context_url();
                $qa_page['@type']      = 'QAPage';
                $qa_page['mainEntity'] = $dw_qa;                                                    
                return $qa_page;
        }
        
        /**
         * This function gets all the question and answers in schema markup from the current question type post create by 
         * DW Question & Answer ( https://wordpress.org/plugins/dw-question-answer/ )
         * @global type $sd_data
         * @param type $post_id
         * @return type array
         */
        public function saswp_dw_question_answers_details($post_id){
            
                global $sd_data;
                $dw_qa          = array();
                $qa_page        = array();
                $best_answer_id = '';
                                                
                $post_type = get_post_type($post_id);
                
                if($post_type =='dwqa-question' && isset($sd_data['saswp-dw-question-answer']) && $sd_data['saswp-dw-question-answer'] ==1 && (is_plugin_active('dw-question-answer/dw-question-answer.php') || is_plugin_active('dw-question-answer-pro/dw-question-answer.php')) ){
                 
                $post_meta      = get_post_meta($post_id, $key='', true);
                
                if(isset($post_meta['_dwqa_best_answer'])){
                    
                    $best_answer_id = $post_meta['_dwqa_best_answer'][0];
                    
                }
                                                                                                                                              
                $dw_qa['@type']       = 'Question';
                $dw_qa['name']        = saswp_get_the_title(); 
                $dw_qa['upvoteCount'] = get_post_meta( $post_id, '_dwqa_votes', true );                                             
                
                $args = array(
                    'p'         => $post_id, // ID of a page, post, or custom type
                    'post_type' => 'dwqa-question'
                  );
                
                $my_posts = new WP_Query($args);
                
                if ( $my_posts->have_posts() ) {
                    
                  while ( $my_posts->have_posts() ) : $my_posts->the_post();                   
                   $dw_qa['text'] = @get_the_content();
                  endwhile;
                  
                } 
                
                $dw_qa['dateCreated'] = get_the_date("c");                                                   
                $dw_qa['author']      = array(
                                                 '@type' => 'Person',
                                                 'name'  =>saswp_get_the_author_name(),
                                            ); 
                                                                                    
                $dw_qa['answerCount'] = $post_meta['_dwqa_answers_count'][0];                  
                
                $args = array(
			'post_type'     => 'dwqa-answer',
			'post_parent'   => $post_id,
			'post_per_page' => '-1',
			'post_status'   => array('publish')
		);
                
                $answer_array = get_posts($args);
               
                $accepted_answer  = array();
                $suggested_answer = array();
                
                foreach($answer_array as $answer){
                    
                    $authorinfo = get_userdata($answer->post_author);  
                    
                    if($answer->ID == $best_answer_id){
                        
                        $accepted_answer['@type']       = 'Answer';
                        $accepted_answer['upvoteCount'] = get_post_meta( $answer->ID, '_dwqa_votes', true );
                        $accepted_answer['url']         = get_permalink($answer->ID);
                        $accepted_answer['text']        = wp_strip_all_tags($answer->post_content);
                        $accepted_answer['dateCreated'] = get_the_date("Y-m-d\TH:i:s\Z", $answer);
                        $accepted_answer['author']      = array('@type' => 'Person', 'name' => $authorinfo->data->user_nicename);
                        
                    }else{
                        
                        $suggested_answer[] =  array(
                            '@type'       => 'Answer',
                            'upvoteCount' => get_post_meta( $answer->ID, '_dwqa_votes', true ),
                            'url'         => get_permalink($answer->ID),
                            'text'        => wp_strip_all_tags($answer->post_content),
                            'dateCreated' => get_the_date("Y-m-d\TH:i:s\Z", $answer),
                            'author'      => array('@type' => 'Person', 'name' => $authorinfo->data->user_nicename),
                        );
                        
                    }
                }
                
                $dw_qa['acceptedAnswer']  = $accepted_answer;
                $dw_qa['suggestedAnswer'] = $suggested_answer;
                    
                $qa_page['@context']   = saswp_context_url();
                $qa_page['@type']      = 'QAPage';
                $qa_page['mainEntity'] = $dw_qa;                
                }                           
                return $qa_page;
        }
                                
        /**
         * This function returns all the schema field's key by schema type or id
         * @param type $schema_type
         * @param type $id
         * @return string
         */
        public function saswp_get_all_schema_type_fields($schema_type){
            
            $meta_field = array();                                                                     
            switch ($schema_type) {
                
                case 'local_business':
                   
                    $meta_field = array(                                                                        
                        'local_business_id'          => 'ID',    
                        'local_business_name'        => 'Business Name',                           
                        'local_business_name_url'    => 'URL',
                        'local_business_description' => 'Description',
                        'local_street_address'       => 'Street Address',                            
                        'local_city'                 => 'City',
                        'local_state'                => 'State',
                        'local_postal_code'          => 'Postal Code',
                        'local_latitude'             => 'Latitude',
                        'local_longitude'            => 'Longitude',
                        'local_phone'                => 'Phone',
                        'local_website'              => 'Website',
                        'local_business_logo'        => 'Image', 
                        'saswp_dayofweek'            => 'Operation Days',
                        'local_price_range'          => 'Price Range', 
                        'local_hasmap'               => 'HasMap',
                        'local_menu'                 => 'Menu',
                        'local_serves_cuisine'       => 'Serves Cuisine',
                        'local_facebook'             => 'Facebook',
                        'local_twitter'              => 'Twitter',
                        'local_instagram'            => 'Instagram',
                        'local_pinterest'            => 'Pinterest',
                        'local_linkedin'             => 'LinkedIn',
                        'local_soundcloud'           => 'SoundCloud',
                        'local_tumblr'               => 'Tumblr',
                        'local_youtube'              => 'Youtube',                        
                        'local_rating_value'         => 'Rating Value',
                        'local_rating_count'         => 'Rating Count',
                        
                        );                   
                    break;
                
                case 'Blogposting':
                    
                    $meta_field = array(        
                        
                        'saswp_blogposting_main_entity_of_page' => 'Main Entity Of Page',
                        'saswp_blogposting_headline'            => 'Headline',
                        'saswp_blogposting_url'                 => 'URL', 
                        'saswp_blogposting_keywords'            => 'Tags',
                        'saswp_blogposting_section'             => 'Section',
                        'saswp_blogposting_body'                => 'Body',    
                        'saswp_blogposting_description'         => 'Description',                         
                        'saswp_blogposting_name'                => 'Name',
                        'saswp_blogposting_url'                 => 'URL',
                        'saswp_blogposting_date_published'      => 'Date Published',                         
                        'saswp_blogposting_date_modified'       => 'Date Modified',
                        'saswp_blogposting_author_name'         => 'Author Name',
                        'saswp_blogposting_organization_name'   => 'Organization Name', 
                        'saswp_blogposting_organization_logo'   => 'Organization Logo', 
                                                                                                                                            
                        ); 
                   
                    break;
                
                case 'NewsArticle':
                    
                   $meta_field = array(                        
                        'saswp_newsarticle_main_entity_of_page' => 'Main Entity Of Page',
                        'saswp_newsarticle_URL'                 => 'URL',
                        'saswp_newsarticle_headline'            => 'Headline',                         
                        'saswp_newsarticle_date_published'      => 'Date Published',
                        'saswp_newsarticle_date_modified'       => 'Date Modified',
                        'saswp_newsarticle_headline'            => 'Headline',                         
                        'saswp_newsarticle_description'         => 'Description',
                        'saswp_newsarticle_keywords'            => 'Tags',
                        'saswp_newsarticle_section'             => 'Article Section',
                        'saswp_newsarticle_body'                => 'Article Body',                         
                        'saswp_newsarticle_name'                => 'Name',
                        'saswp_newsarticle_thumbnailurl'        => 'Thumbnail URL',
                        'saswp_newsarticle_timerequired'        => 'Time Required',                         
                        'saswp_newsarticle_main_entity_id'      => 'Main Entity Id',
                        'saswp_newsarticle_author_name'         => 'Author Name',
                        'saswp_newsarticle_author_image'        => 'Author Image',                       
                        'saswp_newsarticle_organization_name'   => 'Organization Name',
                        'saswp_newsarticle_organization_logo'   => 'Organization Logo'                                                
                        ); 
                                        
                    break;
                
                case 'WebPage':
                    
                    $meta_field = array(                        
                        'saswp_webpage_name'                => 'Name',
                        'saswp_webpage_url'                 => 'URL',
                        'saswp_webpage_description'         => 'Description',                          
                        'saswp_webpage_main_entity_of_page' => 'Main Entity Of Page',
                        'saswp_webpage_image'               => 'Image',
                        'saswp_webpage_headline'            => 'Headline',                             
                        'saswp_webpage_date_published'      => 'Date Published',
                        'saswp_webpage_date_modified'       => 'Date Modified',
                        'saswp_webpage_author_name'         => 'Author Name',                          
                        'saswp_webpage_organization_name'   => 'Organization Name',
                        'saswp_webpage_organization_logo'   => 'Organization Logo',                          
                        ); 
                    
                    break;
                
                case 'Article':      
                    
                    $meta_field = array(                        
                        'saswp_article_main_entity_of_page' => 'Main Entity Of Page',
                        'saswp_article_url'                 => 'URL',
                        'saswp_article_image'               => 'Image',
                        'saswp_article_headline'            => 'Headline',
                        'saswp_article_body'                => 'Body',
                        'saswp_article_keywords'            => 'Tags',
                        'saswp_article_section'             => 'Section',
                        'saswp_article_description'         => 'Description',
                        'saswp_article_date_published'      => 'Date Published',
                        'saswp_article_date_modified'       => 'Date Modified',                          
                        'saswp_article_author_name'         => 'Author Name',
                        'saswp_article_organization_name'   => 'Organization Name',
                        'saswp_article_organization_logo'   => 'Organization Logo'                                                                        
                        );                                        
                    break;
                
                case 'TechArticle':      
                                          
                     $meta_field = array(                        
                        'saswp_tech_article_main_entity_of_page' => 'Main Entity Of Page',
                        'saswp_tech_article_url'                 => 'URL',
                        'saswp_tech_article_image'               => 'Image',
                        'saswp_tech_article_headline'            => 'Headline',
                        'saswp_tech_article_body'                => 'Body',
                        'saswp_tech_article_keywords'            => 'Tags',
                        'saswp_tech_article_section'             => 'Section',
                        'saswp_tech_article_description'         => 'Description',
                        'saswp_tech_article_date_published'      => 'Date Published',
                        'saswp_tech_article_date_modified'       => 'Date Modified',                          
                        'saswp_tech_article_author_name'         => 'Author Name',
                        'saswp_tech_article_organization_name'   => 'Organization Name',
                        'saswp_tech_article_organization_logo'   => 'Organization Logo',                                                                          
                        );     
                    break;
                
                case 'Course':      
                    
                    $meta_field = array(                        
                        'saswp_course_name'           => 'Name',
                        'saswp_course_description'    => 'Description',
                        'saswp_course_url'            => 'URL',                          
                        'saswp_course_date_published' => 'Date Published',
                        'saswp_course_date_modified'  => 'Date Modified',
                        'saswp_course_provider_name'  => 'Provider Name',                          
                        'saswp_course_facebook'       => 'Provider Facebook',
                        'saswp_course_twitter'        => 'Provider Twitter',
                        'saswp_course_instagram'      => 'Provider Instagram',
                        'saswp_course_linkedIn'       => 'Provider LinkedIn',
                        'saswp_course_youtube'        => 'Provider Youtube',
                        );                                        
                    break;
                
                case 'DiscussionForumPosting':      
                    
                    $meta_field = array(                        
                        'saswp_dfp_headline'              => 'Headline',
                        'saswp_dfp_description'           => 'Description',
                        'saswp_dfp_url'                   => 'URL',                          
                        'saswp_dfp_date_published'        => 'Date Published',
                        'saswp_dfp_date_modified'         => 'Date Modified',
                        'saswp_dfp_author_name'           => 'Author Name',                        
                        'saswp_dfp_main_entity_of_page'   => 'Main Entity of Page',
                        'saswp_dfp_organization_name'     => 'Organization Name',
                        'saswp_dfp_organization_logo'     => 'Organization Logo',
                        );     
                    
                    break;
                
                case 'TVSeries':      
                    
                    $meta_field = array(                        
                        'saswp_tvseries_schema_name'         => 'Name',
                        'saswp_tvseries_schema_description'  => 'Description',                        
                        'saswp_tvseries_schema_image'        => 'Image',
                        'saswp_tvseries_schema_author_name'  => 'Author Name'                                                  
                        );     
                    
                    break;
                
                case 'FAQ':      
                    
                    $meta_field = array(                        
                        'saswp_faq_headline'                 => 'Headline',
                        'saswp_faq_keywords'                 => 'Tags',
                        'saswp_faq_author'                   => 'Author',
                        'saswp_faq_date_created'             => 'DateCreated',
                        'saswp_faq_date_published'           => 'DatePublished',
                        'saswp_faq_date_modified'            => 'DateModified',                        
                        );                                                                                                                                              
                                                 
                    break;
                
                case 'Recipe':
                    
                    $meta_field = array(                        
                        'saswp_recipe_url'                  => 'URL',
                        'saswp_recipe_name'                 => 'Name',
                        'saswp_recipe_date_published'       => 'Date Published',                          
                        'saswp_recipe_date_modified'        => 'Date Modified',
                        'saswp_recipe_description'          => 'Description',
                        'saswp_recipe_main_entity'          => 'Main Entity Id',                        
                        'saswp_recipe_author_name'          => 'Author Name',
                        'saswp_recipe_author_image'         => 'Author Image',
                        'saswp_recipe_organization_name'    => 'Organization Name',                        
                        'saswp_recipe_organization_logo'    => 'Organization Logo',
                        'saswp_recipe_preptime'             => 'Prepare Time',
                        'saswp_recipe_cooktime'             => 'Cook Time',                        
                        'saswp_recipe_totaltime'            => 'Total Time',
                        'saswp_recipe_keywords'             => 'Keywords',
                        'saswp_recipe_recipeyield'          => 'Recipe Yield',                        
                        'saswp_recipe_category'             => 'Recipe Category',
                        'saswp_recipe_cuisine'              => 'Recipe Cuisine',
                        'saswp_recipe_nutrition'            => 'Nutrition',                        
                        'saswp_recipe_ingredient'           => 'Recipe Ingredient',
                        'saswp_recipe_instructions'         => 'Recipe Instructions',
                        'saswp_recipe_video_name'           => 'Video Name',                        
                        'saswp_recipe_video_description'    => 'Video Description',
                        'saswp_recipe_video_thumbnailurl'   => 'Video ThumbnailUrl',
                        'saswp_recipe_video_contenturl'     => 'Video ContentUrl',                        
                        'saswp_recipe_video_embedurl'       => 'Video EmbedUrl',
                        'saswp_recipe_video_upload_date'    => 'Video Upload Date',
                        'saswp_recipe_video_duration'       => 'Video Duration',                        
                        'saswp_recipe_rating_value'         => 'Rating Value',
                        'saswp_recipe_rating_count'         => 'Rating Count',
                    );
                    
                    break;
                
                case 'Product':
                    
                        $meta_field = array(                        
                            'saswp_product_url'                => 'URL',    
                            'saswp_product_name'               => 'Name',
                            'saswp_product_description'        => 'Description',                                                                         
                            'saswp_product_image'              => 'Image',
                            'saswp_product_brand'              => 'Brand Name',
                            'saswp_product_price'              => 'Price',
                            'saswp_product_priceValidUntil'    => 'Price Valid Until',                         
                            'saswp_product_currency'           => 'Currency',  
                            'saswp_product_availability'       => 'Availability',  
                            'saswp_product_condition'          => 'Product Condition',  
                            'saswp_product_sku'                => 'SKU', 
                            'saswp_product_mpn'                => 'MPN',                            
                            'saswp_product_gtin8'              => 'GTIN 8', 
                            'saswp_product_seller'             => 'Seller Organization',
                            'saswp_product_rating'             => 'Rating',
                            'saswp_product_rating_count'       => 'Rating Count',
                        );                                                                                                                                       
                    break;
                
                case 'TouristAttraction':
                    
                        $meta_field = array(                        
                            'saswp_ta_schema_name'               => 'Name',
                            'saswp_ta_schema_description'        => 'Description',                                                                         
                            'saswp_ta_schema_image'              => 'Image',
                            'saswp_ta_schema_url'                => 'URL',
                            'saswp_ta_schema_is_acceesible_free' => 'Is Accessible For Free',
                            'saswp_ta_schema_locality'           => 'Address Locality',                         
                            'saswp_ta_schema_region'             => 'Address Region',  
                            'saswp_ta_schema_country'            => 'Address Country',  
                            'saswp_ta_schema_postal_code'        => 'Address PostalCode',
                            'saswp_ta_schema_latitude'           => 'Latitude',
                            'saswp_ta_schema_longitude'          => 'Longitude',     
                        );                                                                                                                                       
                    break;
                
                case 'TouristDestination':
                    
                        $meta_field = array(                        
                            'saswp_td_schema_name'               => 'Name',
                            'saswp_td_schema_description'        => 'Description',                                                                         
                            'saswp_td_schema_image'              => 'Image',
                            'saswp_td_schema_url'                => 'URL',                            
                            'saswp_td_schema_locality'           => 'Address Locality',                         
                            'saswp_td_schema_region'             => 'Address Region',  
                            'saswp_td_schema_country'            => 'Address Country',  
                            'saswp_td_schema_postal_code'        => 'Address PostalCode',
                            'saswp_td_schema_latitude'           => 'Latitude',
                            'saswp_td_schema_longitude'          => 'Longitude',     
                        );                                                                                                                                       
                    break;
                
                case 'LandmarksOrHistoricalBuildings':
                    
                        $meta_field = array(                        
                            'saswp_lorh_schema_name'               => 'Name',
                            'saswp_lorh_schema_description'        => 'Description',                                                                         
                            'saswp_lorh_schema_image'              => 'Image',
                            'saswp_lorh_schema_url'                => 'URL',                            
                            'saswp_lorh_schema_hasmap'             => 'Has Map',                         
                            'saswp_lorh_schema_is_acceesible_free' => 'Is Accessible For Free',  
                            'saswp_lorh_schema_maximum_a_capacity' => 'Maximum Attendee Capacity',  
                            'saswp_lorh_schema_locality'           => 'Address Locality',
                            'saswp_lorh_schema_region'             => 'Address Region',
                            'saswp_lorh_schema_country'            => 'Address Country',
                            'saswp_lorh_schema_postal_code'        => 'Address PostalCode',
                            'saswp_lorh_schema_latitude'           => 'Latitude',
                            'saswp_lorh_schema_longitude'          => 'Longitude',     
                        );                                                                                                                                       
                    break;
                
                case 'HinduTemple':
                    
                        $meta_field = array(                        
                            'saswp_hindutemple_schema_name'               => 'Name',
                            'saswp_hindutemple_schema_description'        => 'Description',                                                                         
                            'saswp_hindutemple_schema_image'              => 'Image',
                            'saswp_hindutemple_schema_url'                => 'URL',                            
                            'saswp_hindutemple_schema_hasmap'             => 'Has Map',                         
                            'saswp_hindutemple_schema_is_accesible_free'  => 'Is Accessible For Free',  
                            'saswp_hindutemple_schema_maximum_a_capacity' => 'Maximum Attendee Capacity',  
                            'saswp_hindutemple_schema_locality'           => 'Address Locality',
                            'saswp_hindutemple_schema_region'             => 'Address Region',
                            'saswp_hindutemple_schema_country'            => 'Address Country',
                            'saswp_hindutemple_schema_postal_code'        => 'Address PostalCode',
                            'saswp_hindutemple_schema_latitude'           => 'Latitude',
                            'saswp_hindutemple_schema_longitude'          => 'Longitude',     
                        );                                                                                                                                       
                    break;
                
                case 'Church':
                    
                        $meta_field = array(                        
                            'saswp_church_schema_name'               => 'Name',
                            'saswp_church_schema_description'        => 'Description',                                                                         
                            'saswp_church_schema_image'              => 'Image',
                            'saswp_church_schema_url'                => 'URL',                            
                            'saswp_church_schema_hasmap'             => 'Has Map',                         
                            'saswp_church_schema_is_accesible_free'  => 'Is Accessible For Free',  
                            'saswp_church_schema_maximum_a_capacity' => 'Maximum Attendee Capacity',  
                            'saswp_church_schema_locality'           => 'Address Locality',
                            'saswp_church_schema_region'             => 'Address Region',
                            'saswp_church_schema_country'            => 'Address Country',
                            'saswp_church_schema_postal_code'        => 'Address PostalCode',
                            'saswp_church_schema_latitude'           => 'Latitude',
                            'saswp_church_schema_longitude'          => 'Longitude',     
                        );                                                                                                                                       
                    break;
                
                case 'Mosque':
                    
                        $meta_field = array(                        
                            'saswp_mosque_schema_name'               => 'Name',
                            'saswp_mosque_schema_description'        => 'Description',                                                                         
                            'saswp_mosque_schema_image'              => 'Image',
                            'saswp_mosque_schema_url'                => 'URL',                            
                            'saswp_mosque_schema_hasmap'             => 'Has Map',                         
                            'saswp_mosque_schema_is_accesible_free'  => 'Is Accessible For Free',  
                            'saswp_mosque_schema_maximum_a_capacity' => 'Maximum Attendee Capacity',  
                            'saswp_mosque_schema_locality'           => 'Address Locality',
                            'saswp_mosque_schema_region'             => 'Address Region',
                            'saswp_mosque_schema_country'            => 'Address Country',
                            'saswp_mosque_schema_postal_code'        => 'Address PostalCode',
                            'saswp_mosque_schema_latitude'           => 'Latitude',
                            'saswp_mosque_schema_longitude'          => 'Longitude',     
                        );                                                                                                                                       
                    break;
                
                case 'Person':
                    
                        $meta_field = array(                        
                            'saswp_person_schema_name'               => 'Name',
                            'saswp_person_schema_description'        => 'Description',                                                                         
                            'saswp_person_schema_url'                => 'URL',
                            'saswp_person_schema_street_address'     => 'Street Address',                            
                            'saswp_person_schema_locality'           => 'Locality',                         
                            'saswp_person_schema_region'             => 'Region',  
                            'saswp_person_schema_postal_code'        => 'Postal Code',  
                            'saswp_person_schema_country'            => 'Country',
                            'saswp_person_schema_email'              => 'Email',
                            'saswp_person_schema_telephone'          => 'Telephone',
                            'saswp_person_schema_gender'             => 'Gender',
                            'saswp_person_schema_date_of_birth'      => 'Date Of Birth',
                            'saswp_person_schema_member_of'          => 'Member Of',
                            'saswp_person_schema_nationality'        => 'Nationality',
                            'saswp_person_schema_image'              => 'Image',
                            'saswp_person_schema_job_title'          => 'Job Title',
                            'saswp_person_schema_company'            => 'Company',
                            'saswp_person_schema_website'            => 'Website'
                        );                                                                                                                                       
                    break;
                
                case 'Service':
                    
                    $meta_field = array(                        
                        'saswp_service_schema_url'              => 'URL',
                        'saswp_service_schema_name'             => 'Name',
                        'saswp_service_schema_type'             => 'Service Type',
                        'saswp_service_schema_provider_name'    => 'Provider Name',
                        'saswp_service_schema_provider_type'    => 'Provider Type',
                        'saswp_service_schema_image'            => 'Image',
                        'saswp_service_schema_locality'         => 'Locality',
                        'saswp_service_schema_postal_code'      => 'Postal Code',
                        'saswp_service_schema_telephone'        => 'Telephone',
                        'saswp_service_schema_price_range'      => 'Price Range',
                        'saswp_service_schema_description'      => 'Description',
                        'saswp_service_schema_area_served'      => 'Area Served (City)',
                        'saswp_service_schema_service_offer'    => 'Service Offer',
                        'saswp_service_schema_country'           => 'Address Country',
                        'saswp_service_schema_telephone'         => 'Telephone',  
                        'saswp_service_schema_rating_value'      => 'Rating Value',
                        'saswp_service_schema_rating_count'      => 'Rating Count',
                    );
                   
                    break;
                
                case 'VideoObject':
                    
                    $meta_field = array(
                        
                        'saswp_video_object_url'                => 'URL',
                        'saswp_video_object_headline'           => 'Headline',
                        'saswp_video_object_date_published'     => 'Date Published',
                        'saswp_video_object_date_modified'      => 'Date Modified',
                        'saswp_video_object_description'        => 'Description',
                        'saswp_video_object_name'               => 'Name',
                        'saswp_video_object_upload_date'        => 'Upload Date',
                        'saswp_video_object_duration'           => 'Duration',
                        'saswp_video_object_thumbnail_url'      => 'Thumbnail Url',
                        'saswp_video_object_content_url'        => 'Content URL',
                        'saswp_video_object_embed_url'          => 'Embed Url',
                        'saswp_video_object_main_entity_id'     => 'Main Entity Id',
                        'saswp_video_object_author_name'        => 'Author Name',
                        'saswp_video_object_author_image'       => 'Author Image',
                        'saswp_video_object_organization_name'  => 'Organization Name',
                        'saswp_video_object_organization_logo'  => 'Organization Logo',                                         
                    );
                    
                    break;
                
                case 'AudioObject':
                    
                    $meta_field = array(
                        
                        'saswp_audio_schema_name'           => 'Name',
                        'saswp_audio_schema_description'    => 'Description',
                        'saswp_audio_schema_contenturl'     => 'Content Url',
                        'saswp_audio_schema_duration'       => 'Duration',
                        'saswp_audio_schema_encoding_format'=> 'Encoding Format',
                        'saswp_audio_schema_date_published' => 'Date Published',
                        'saswp_audio_schema_date_modified'  => 'Date Modified',
                        'saswp_audio_schema_author_name'    => 'Author',                        
                    );
                    
                    break;
                
                case 'SoftwareApplication':
                    
                    $meta_field = array(
                        
                        'saswp_software_schema_name'                    => 'Name',
                        'saswp_software_schema_description'             => 'Description',
                        'saswp_software_schema_image'                   => 'Image',
                        'saswp_software_schema_operating_system'        => 'Operating System',
                        'saswp_software_schema_application_category'    => 'Application Category',
                        'saswp_software_schema_price'                   => 'Price',
                        'saswp_software_schema_price_currency'          => 'Price Currency',                        
                        'saswp_software_schema_date_published'          => 'Date Published',
                        'saswp_software_schema_date_modified'           => 'Date Modified',                        
                        'saswp_software_rating_value'                   => 'Rating Value',
                        'saswp_software_rating_count'                   => 'Rating Count',
                    );
                    
                    break;
                
                case 'Event':
                    
                    $meta_field = array(
                        
                        'saswp_event_schema_name'                    => 'Name',
                        'saswp_event_schema_description'             => 'Description',
                        'saswp_event_schema_location_name'           => 'Location Name',
                        'saswp_event_schema_location_streetaddress'  => 'Location Street Address',
                        'saswp_event_schema_location_locality'       => 'Location Locality',
                        'saswp_event_schema_location_region'         => 'Location Region',                        
                        'saswp_event_schema_location_postalcode'     => 'PostalCode',
                        'saswp_event_schema_location_hasmap'         => 'HasMape',
                        'saswp_event_schema_start_date'              => 'Start Date',                        
                        'saswp_event_schema_end_date'                => 'End Date',
                        'saswp_event_schema_image'                   => 'Image',
                        'saswp_event_schema_performer_name'          => 'Performer Name',
                        'saswp_event_schema_price'                   => 'Price',
                        'saswp_event_schema_price_currency'          => 'Price Currency',
                        'saswp_event_schema_availability'            => 'Availability',
                        'saswp_event_schema_validfrom'               => 'Valid From',
                        'saswp_event_schema_url'                     => 'URL',
                    );
                    
                    break;
                
                case 'qanda':
                    $meta_field = array(
                        
                        'saswp_qa_question_title'               => 'Question Title',
                        'saswp_qa_question_description'         => 'Question Description',
                        'saswp_qa_upvote_count'                 => 'Question Upvote Count',                        
                        'saswp_qa_date_created'                 => 'Question Date Created',
                        'saswp_qa_question_author_name'         => 'Author Name',
                        'saswp_qa_accepted_answer_text'         => 'Accepted Answer Text',
                        'saswp_qa_accepted_answer_date_created' => 'Accepted Answer Date Created',
                        'saswp_qa_accepted_answer_upvote_count' => 'Accepted Answer Upvote Count',
                        'saswp_qa_accepted_answer_url'          => 'Accepted Answer Url',
                        'saswp_qa_accepted_author_name'         => 'Accepted Answer Author Name',
                        'saswp_qa_suggested_answer_text'        => 'Suggested Answer Text',
                        'saswp_qa_suggested_answer_date_created'=> 'Suggested Answer Date Created',                        
                        'saswp_qa_suggested_answer_upvote_count'=> 'Suggested Answer Upvote Count',
                        'saswp_qa_suggested_answer_url'         => 'Suggested Answer Url',
                        'saswp_qa_suggested_author_name'        => 'Suggested Answer Author Name',
                                            
                    );                    
                    break;
                
                case 'Apartment':
                    $meta_field = array(
                        
                        'saswp_apartment_schema_name'          => 'Name',
                        'saswp_apartment_schema_url'           => 'URL',
                        'saswp_apartment_schema_image'         => 'Image',                        
                        'saswp_apartment_schema_description'   => 'Description',
                        'saswp_apartment_schema_numberofrooms' => 'Number of Rooms',
                        'saswp_apartment_schema_country'       => 'Country',
                        'saswp_apartment_schema_locality'      => 'Locality',
                        'saswp_apartment_schema_region'        => 'Region',
                        'saswp_apartment_schema_postalcode'    => 'PostalCode',
                        'saswp_apartment_schema_latitude'      => 'Latitude',
                        'saswp_apartment_schema_longitude'     => 'Longitude',                        
                        'saswp_apartment_schema_telephone'     => 'Telephone'                                                                    
                    );                    
                    break;
                case 'MusicPlaylist':
                    $meta_field = array(                        
                        'saswp_music_playlist_name'             => 'Name',
                        'saswp_music_playlist_description'      => 'Description',
                        'saswp_music_playlist_url'              => 'URL',
                    );                    
                    break;
                
                case 'MusicAlbum':
                    $meta_field = array(                        
                        'saswp_music_album_name'             => 'Name',
                        'saswp_music_album_description'      => 'Description',
                        'saswp_music_album_genre'            => 'Genre',
                        'saswp_music_album_image'            => 'Image',
                        'saswp_music_album_artist'           => 'Artist',
                        'saswp_music_album_url'              => 'URL',    
                    );                    
                    break;
                
                case 'Book':
                    $meta_field = array(                        
                        'saswp_book_name'              => 'Name',
                        'saswp_book_description'       => 'Description',
                        'saswp_book_url'               => 'URL',
                        'saswp_book_image'             => 'Image',
                        'saswp_book_author'            => 'Author',  
                        'saswp_book_isbn'              => 'Isbn',
                        'saswp_book_no_of_page'        => 'Number Of Page', 
                        'saswp_book_publisher'         => 'Publisher',
                        'saswp_book_published_date'    => 'Published Date',
                        'saswp_book_availability'      => 'Availability',
                        'saswp_book_price'             => 'Price',
                        'saswp_book_price_currency'    => 'Price Currency',
                        'saswp_book_rating_value'      => 'Rating Value',
                        'saswp_book_rating_count'      => 'Rating Count',                        
                    );                    
                    break;
                                
                case 'House':
                    $meta_field = array(
                        
                        'saswp_house_schema_name'          => 'Name',
                        'saswp_house_schema_url'           => 'URL',
                        'saswp_house_schema_image'         => 'Image',                        
                        'saswp_house_schema_description'   => 'Description',
                        'saswp_house_schema_pets_allowed'  => 'Pets Allowed',
                        'saswp_house_schema_country'       => 'Country',
                        'saswp_house_schema_locality'      => 'Locality',
                        'saswp_house_schema_region'        => 'Region',
                        'saswp_house_schema_postalcode'    => 'PostalCode',
                        'saswp_house_schema_latitude'      => 'Latitude',
                        'saswp_house_schema_longitude'     => 'Longitude',     
                        'saswp_house_schema_telephone'     => 'Telephone',
                        'saswp_house_schema_hasmap'        => 'HasMap',
                        'saswp_house_schema_floor_size'    => 'FloorSize',
                        'saswp_house_schema_no_of_rooms'   => 'No. Of Rooms'
                    );                    
                    break;
                
                case 'SingleFamilyResidence':
                    
                    $meta_field = array(                        
                        'saswp_sfr_schema_name'          => 'Name',
                        'saswp_sfr_schema_url'           => 'URL',
                        'saswp_sfr_schema_image'         => 'Image',                        
                        'saswp_sfr_schema_description'   => 'Description',
                        'saswp_sfr_schema_numberofrooms' => 'Number Of Rooms',
                        'saswp_sfr_schema_pets_allowed'  => 'Pets Allowed',
                        'saswp_sfr_schema_country'       => 'Country',
                        'saswp_sfr_schema_locality'      => 'Locality',
                        'saswp_sfr_schema_region'        => 'Region',
                        'saswp_sfr_schema_postalcode'    => 'PostalCode',
                        'saswp_sfr_schema_latitude'      => 'Latitude',
                        'saswp_sfr_schema_longitude'     => 'Longitude', 
                        'saswp_sfr_schema_telephone'     => 'Telephone',
                        'saswp_sfr_schema_hasmap'        => 'HasMap',
                        'saswp_sfr_schema_floor_size'    => 'FloorSize',
                        'saswp_sfr_schema_no_of_rooms'   => 'No. Of Rooms'
                    );                    
                    break;
                
                case 'VideoGame':
                    
                    $meta_field = array(                        
                        'saswp_vg_schema_name'                   => 'Name',
                        'saswp_vg_schema_url'                    => 'URL',
                        'saswp_vg_schema_image'                  => 'Image',                        
                        'saswp_vg_schema_description'            => 'Description',
                        'saswp_vg_schema_operating_system'       => 'Operating System',
                        'saswp_vg_schema_application_category'   => 'Application Category',
                        'saswp_vg_schema_author_name'            => 'Author Name',
                        'saswp_vg_schema_price'                  => 'Price',
                        'saswp_vg_schema_price_currency'         => 'Price Currency',
                        'saswp_vg_schema_price_availability'     => 'Availability',
                        'saswp_vg_schema_publisher'              => 'Publisher',
                        'saswp_vg_schema_genre'                  => 'Genre',
                        'saswp_vg_schema_processor_requirements' => 'Processor Requirements',
                        'saswp_vg_schema_memory_requirements'    => 'Memory Requirements',
                        'saswp_vg_schema_storage_requirements'   => 'Storage Requirements',
                        'saswp_vg_schema_game_platform'          => 'Game Platform',
                        'saswp_vg_schema_cheat_code'             => 'Cheat Code'
                    );                    
                    break;
                
                case 'JobPosting':
                    
                    $meta_field = array(                        
                        'saswp_jobposting_schema_title'             => 'Title',
                        'saswp_jobposting_schema_description'       => 'Description',
                        'saswp_jobposting_schema_url'               => 'URL',                        
                        'saswp_jobposting_schema_dateposted'        => 'Date Posted',
                        'saswp_jobposting_schema_validthrough'      => 'Valid Through',
                        'saswp_jobposting_schema_employment_type'   => 'Employment Type',
                        'saswp_jobposting_schema_ho_name'           => 'Hiring Organization Name',
                        'saswp_jobposting_schema_ho_url'            => 'Hiring Organization URL',
                        'saswp_jobposting_schema_ho_logo'           => 'Hiring Organization Logo',
                        'saswp_jobposting_schema_street_address'    => 'Street Address',
                        'saswp_jobposting_schema_locality'          => 'Address Locality',
                        'saswp_jobposting_schema_region'            => 'Address Region',
                        'saswp_jobposting_schema_postalcode'        => 'Address Postal Code',
                        'saswp_jobposting_schema_country'           => 'Address Country',
                        'saswp_jobposting_schema_bs_currency'       => 'Base Salary Currency',
                        'saswp_jobposting_schema_bs_value'          => 'Base Salary Value',
                        'saswp_jobposting_schema_bs_unittext'       => 'Base Salary Unit Text'
                    );                    
                    break;
                
                case 'Trip':
                    
                    $meta_field = array(                        
                        'saswp_trip_schema_name'             => 'Name',
                        'saswp_trip_schema_description'      => 'Description',
                        'saswp_trip_schema_url'              => 'URL',                        
                        'saswp_trip_schema_image'            => 'Image'                        
                    );                    
                    break;
                
                case 'MedicalCondition':
                    
                    $meta_field = array(                        
                        'saswp_mc_schema_name'             => 'Name',
                        'saswp_mc_schema_alternate_name'   => 'Alternate Name',
                        'saswp_mc_schema_description'      => 'Description',                        
                        'saswp_mc_schema_image'            => 'Image',
                        'saswp_mc_schema_anatomy_name'     => 'Associated Anatomy Name',
                        'saswp_mc_schema_medical_code'     => 'Medical Code',
                        'saswp_mc_schema_coding_system'    => 'Coding System',
                        'saswp_mc_schema_diagnosis_name'   => 'Diagnosis Name'                        
                    );                    
                    break;
                
                case 'DataFeed':
                    
                    $meta_field = array(                        
                        'saswp_data_feed_schema_name'                      => 'Name',
                        'saswp_data_feed_schema_description'               => 'Description',
                        'saswp_data_feed_schema_date_modified'             => 'DateModified',
                        'saswp_data_feed_schema_date_license'              => 'License',
                    );                    
                    break;
                
                case 'HowTo':
                    
                    $meta_field = array(                        
                        'saswp_howto_schema_name'                      => 'Name',
                        'saswp_howto_schema_description'               => 'Description',
                        'saswp_howto_schema_image'                     => 'Image',
                        'saswp_howto_ec_schema_currency'               => 'Estimated Cost Currency',
                        'saswp_howto_ec_schema_value'                  => 'Estimated Cost Value',
                        'saswp_howto_schema_totaltime'                 => 'Total Time',
                        'saswp_howto_ec_schema_date_published'         => 'Date Published',
                        'saswp_howto_ec_schema_date_modified'          => 'Date Modified',
                    );                    
                    break;

                default:
                    break;
            }                                    
            return $meta_field;
        }
                        
        /**
         * This function generate the schema markup by passed schema type
         * @global type $sd_data
         * @param type $schema_type
         * @return array
         */
        public function saswp_schema_markup_generator($schema_type){
            
                        global $post;                        
                        global $sd_data;
            
                        $logo         = ''; 
                        $height       = '';
                        $width        = '';
                        $site_name    = '';
                                                
                        $default_logo = $this->saswp_get_publisher(true);
                        
                        if(!empty($default_logo)){
            
                            $logo   = $default_logo['url'];
                            $height = $default_logo['height'];
                            $width  = $default_logo['width'];
            
                        }
                        
                        if(isset($sd_data['sd_name']) && $sd_data['sd_name'] !=''){
                            
                            $site_name = $sd_data['sd_name'];  
                          
                        }else{
                            
                            $site_name = get_bloginfo();    
                            
                        }
                        
                        $input1         = array();
                                                            
                        $image_id 	= get_post_thumbnail_id();
			$image_details 	= wp_get_attachment_image_src($image_id, 'full');                       			
			$date 		= get_the_date("c");
			$modified_date 	= get_the_modified_date("c");                        
			                                                
            switch ($schema_type) {
                
                case 'TechArticle':
                    
                    $input1 = array(
					'@context'			=> saswp_context_url(),
					'@type'				=> 'TechArticle',
                                        '@id'				=> trailingslashit(saswp_get_permalink()).'#techarticle',
                                        'url'				=> saswp_get_permalink(),
					'mainEntityOfPage'              => saswp_get_permalink(),					
					'headline'			=> saswp_get_the_title(),
					'description'                   => saswp_get_the_excerpt(),
                                        'articleBody'                   => saswp_get_the_content(),
                                        'keywords'                      => saswp_get_the_tags(),
					'datePublished'                 => esc_html($date),
					'dateModified'                  => esc_html($modified_date),
					'author'			=> saswp_get_author_details(),
					'publisher'			=> array(
						'@type'			=> 'Organization',
						'logo' 			=> array(
							'@type'		=> 'ImageObject',
							'url'		=> esc_url($logo),
							'width'		=> esc_attr($width),
							'height'	=> esc_attr($height),
							),
						'name'			=> esc_attr($site_name),
					),
                                    
				);

                    break;
                
                case 'Article':                   
                    $input1 = array(
					'@context'			=> saswp_context_url(),
					'@type'				=> 'Article',
                                        '@id'				=> trailingslashit(saswp_get_permalink()).'#article',
                                        'url'				=> saswp_get_permalink(),
					'mainEntityOfPage'              => saswp_get_permalink(),					
					'headline'			=> saswp_get_the_title(),
					'description'                   => saswp_get_the_excerpt(),
                                        'articleBody'                   => saswp_get_the_content(),
                                        'keywords'                      => saswp_get_the_tags(),
					'datePublished'                 => esc_html($date),
					'dateModified'                  => esc_html($modified_date),
					'author'			=> saswp_get_author_details(),
					'publisher'			=> array(
						'@type'			=> 'Organization',
						'logo' 			=> array(
							'@type'		=> 'ImageObject',
							'url'		=> esc_url($logo),
							'width'		=> esc_attr($width),
							'height'	=> esc_attr($height),
							),
						'name'			=> esc_attr($site_name),
					),
                                    
				);

                    break;
                
                case 'WebPage':
                    
                    if(empty($image_details[0]) || $image_details[0] === NULL ){
					$image_details[0] = $logo;
                    }
                    
                    $input1 = array(
				'@context'			=> saswp_context_url(),
				'@type'				=> 'WebPage' ,
                                '@id'				=> trailingslashit(saswp_get_permalink()).'#webpage',
				'name'				=> saswp_get_the_title(),
				'url'				=> saswp_get_permalink(),
				'description'                   => saswp_get_the_excerpt(),
				'mainEntity'                    => array(
						'@type'			=> 'Article',
						'mainEntityOfPage'	=> saswp_get_permalink(),
						'image'			=> esc_url($image_details[0]),
						'headline'		=> saswp_get_the_title(),
						'description'		=> saswp_get_the_excerpt(),
                                                'articleBody'           => saswp_get_the_content(),
                                                'keywords'              => saswp_get_the_tags(),
						'datePublished' 	=> esc_html($date),
						'dateModified'		=> esc_html($modified_date),
						'author'			=> saswp_get_author_details(),
						'publisher'			=> array(
							'@type'			=> 'Organization',
							'logo' 			=> array(
								'@type'		=> 'ImageObject',
								'url'		=> esc_url($logo),
								'width'		=> esc_attr($width),
								'height'	=> esc_attr($height),
								),
							'name'			=> esc_attr($site_name),
						),
                                               
					),
					
				
				);
                    
                    break;
                    
                case 'Product':
                                                                        
                        $product_details = $this->saswp_woocommerce_product_details(get_the_ID());  

                        if((isset($sd_data['saswp-woocommerce']) && $sd_data['saswp-woocommerce'] == 1) && !empty($product_details)){

                            $input1 = array(
                            '@context'			        => saswp_context_url(),
                            '@type'				=> 'Product',
                            '@id'				=> trailingslashit(saswp_get_permalink()).'#product',     
                            'url'				=> trailingslashit(saswp_get_permalink()),
                            'name'                              => saswp_remove_warnings($product_details, 'product_name', 'saswp_string'),
                            'sku'                               => saswp_remove_warnings($product_details, 'product_sku', 'saswp_string'),    
                            'description'                       => saswp_remove_warnings($product_details, 'product_description', 'saswp_string')                                                               
                          );
                            
                          if(isset($product_details['product_price']) && $product_details['product_price'] ){
							
                                    $input1['offers'] = array(
                                                    '@type'	        => 'Offer',
                                                    'availability'      => saswp_remove_warnings($product_details, 'product_availability', 'saswp_string'),
                                                    'price'             => saswp_remove_warnings($product_details, 'product_price', 'saswp_string'),
                                                    'priceCurrency'     => saswp_remove_warnings($product_details, 'product_currency', 'saswp_string'),
                                                    'url'               => trailingslashit(saswp_get_permalink()),
                                                    'priceValidUntil'   => saswp_remove_warnings($product_details, 'product_priceValidUntil', 'saswp_string')
                                                 );

							
                            }else{

                            if(isset($product_details['product_varible_price']) && is_array($product_details['product_varible_price'])){

                            $input1['offers']['@type']         = 'AggregateOffer';
                            $input1['offers']['lowPrice']      = min($product_details['product_varible_price']);
                            $input1['offers']['highPrice']     = max($product_details['product_varible_price']);
                            $input1['offers']['priceCurrency'] = saswp_remove_warnings($product_details, 'product_currency', 'saswp_string');
                            $input1['offers']['availability']  = saswp_remove_warnings($product_details, 'product_availability', 'saswp_string');
                            $input1['offers']['offerCount']    = count($product_details['product_varible_price']);

                            }

                           }                              

                          if(isset($product_details['product_image'])){
                            $input1 = array_merge($input1, $product_details['product_image']);
                          }  

                          if(isset($product_details['product_gtin8']) && $product_details['product_gtin8'] !=''){
                            $input1['gtin8'] = esc_attr($product_details['product_gtin8']);  
                          }
                          if(isset($product_details['product_mpn']) && $product_details['product_mpn'] !=''){
                            $input1['mpn'] = esc_attr($product_details['product_mpn']);  
                          }
                          if(isset($product_details['product_isbn']) && $product_details['product_isbn'] !=''){
                            $input1['isbn'] = esc_attr($product_details['product_isbn']);  
                          }
                          if(isset($product_details['product_brand']) && $product_details['product_brand'] !=''){
                            $input1['brand'] =  array('@type'=>'Thing','name'=> esc_attr($product_details['product_brand']));  
                          }                                     
                          if(isset($product_details['product_review_count']) && $product_details['product_review_count'] >0 && isset($product_details['product_average_rating']) && $product_details['product_average_rating'] >0){
                               $input1['aggregateRating'] =  array(
                                                                '@type'         => 'AggregateRating',
                                                                'ratingValue'	=> esc_attr($product_details['product_average_rating']),
                                                                'reviewCount'   => (int)esc_attr($product_details['product_review_count']),       
                               );
                          }                                      
                          if(!empty($product_details['product_reviews'])){

                              $reviews = array();

                              foreach ($product_details['product_reviews'] as $review){

                                  $reviews[] = array(
                                                                '@type'	=> 'Review',
                                                                'author'	=> $review['author'] ? esc_attr($review['author']) : 'Anonymous',
                                                                'datePublished'	=> esc_html($review['datePublished']),
                                                                'description'	=> $review['description'],  
                                                                'reviewRating'  => array(
                                                                        '@type'	=> 'Rating',
                                                                        'bestRating'	=> '5',
                                                                        'ratingValue'	=> $review['reviewRating'] ? esc_attr($review['reviewRating']) : '5',
                                                                        'worstRating'	=> '1',
                                                                )  
                                  );

                              }
                              $input1['review'] =  $reviews;
                          }                                                                                                    
                        }else{

                            $input1['@context']              = saswp_context_url();
                            $input1['@type']                 = 'Product';
                            $input1['@id']                   = trailingslashit(saswp_get_permalink()).'#Product';                                                                                                                                                                                                                                                                                        
                        }                                                                
                    
                    break;

                default:
                    break;
            }
            
            if( !empty($input1) && !isset($input1['image'])){
                                                          
                    $input2 = $this->saswp_get_fetaure_image();
                    if(!empty($input2)){

                      $input1 = array_merge($input1,$input2);                                
                    }                                                                    
            }
                        
            return $input1;
            
        }
        
        /**
         * This function returns the featured image for the current post.
         * If featured image is not set than it gets default schema image from MISC settings tab
         * @global type $sd_data
         * @return type array
         */
        public function saswp_get_fetaure_image(){
            
            global $sd_data;
            global $post;
            $input2          = array();
            $image_id 	     = get_post_thumbnail_id();
	    $image_details   = wp_get_attachment_image_src($image_id, 'full'); 
                        
            if( is_array($image_details) ){                                
                                                                                                                    
                                        if(isset($image_details[1]) && ($image_details[1] < 1200) && function_exists('aq_resize')){
                                            
                                            $width  = array(1280, 640, 300);
                                            $height = array(720, 480, 300);
                                            
                                            for($i = 0; $i<3; $i++){
                                                
                                                $resize_image = saswp_aq_resize( $image_details[0], $width[$i], $height[$i], true, false, true );
                                                
                                                if(isset($resize_image[0]) && isset($resize_image[1]) && isset($resize_image[2]) ){
                                                
                                                                                                        
                                                    $input2['image'][$i]['@type']  = 'ImageObject';
                                                    
                                                    if($i == 0){
                                                        
                                                    $input2['image'][$i]['@id']    = saswp_get_permalink().'#primaryimage';    
                                                    
                                                    }
                                                    
                                                    $input2['image'][$i]['url']    = esc_url($resize_image[0]);
                                                    $input2['image'][$i]['width']  = esc_attr($resize_image[1]);
                                                    $input2['image'][$i]['height'] = esc_attr($resize_image[2]);  
                                                    
                                                }
                                                
                                                                                                                                                
                                            }
                                            
                                            if(!empty($input2)){
                                                foreach($input2 as $arr){
                                                    $input2['image'] = array_values($arr);
                                                }
                                            }
                                                                                                                                                                                                                            
                                        }else{
                                                     
                                                $size_array = array('full', 'large', 'medium', 'thumbnail');
                                                
                                                for($i =0; $i< count($size_array); $i++){
                                                    
                                                    $image_details   = wp_get_attachment_image_src($image_id, $size_array[$i]); 
													
                                                        if(!empty($image_details)){

                                                                $input2['image'][$i]['@type']  = 'ImageObject';
                                                                
                                                                if($i == 0){
                                                        
                                                                $input2['image'][$i]['@id']    = saswp_get_permalink().'#primaryimage'; 
                                                                
                                                                }
                                                                
                                                                $input2['image'][$i]['url']    = esc_url($image_details[0]);
                                                                $input2['image'][$i]['width']  = esc_attr($image_details[1]);
                                                                $input2['image'][$i]['height'] = esc_attr($image_details[2]);

                                                        }
                                                    
                                                    
                                                }                                                                                                                                                                                        
                                            
                                        } 
                                        
                                        if(empty($input2)){
                                            
                                                $input2['image']['@type']  = 'ImageObject';
                                                $input2['image']['@id']    = saswp_get_permalink().'#primaryimage';
                                                $input2['image']['url']    = esc_url($image_details[0]);
                                                $input2['image']['width']  = esc_attr($image_details[1]);
                                                $input2['image']['height'] = esc_attr($image_details[2]);
                                            
                                        }
                                        
                                                                                                                                                                                                 
                             }
                                                       
                          //Get All the images available on post   
                             
                          $content = @get_the_content();   
                          
                          if($content){
                              
                          $regex   = '/<img(.*?)src="(.*?)"(.*?)>/';                          
                          @preg_match_all( $regex, $content, $attachments ); 
                                                                                                                                                                                      
                          $attach_images = array();
                          
                          if(!empty($attachments)){
                              
                              $attach_details   = saswp_get_attachment_details($attachments[2], $post->ID);
                              
                              $k = 0;
                              
                              foreach ($attachments[2] as $attachment) {
                                                                                                                                       
                                  if(!empty($attach_details)){
                                                                            
                                                $attach_images['image'][$k]['@type']  = 'ImageObject';                                                
                                                $attach_images['image'][$k]['url']    = esc_url($attachment);
                                                $attach_images['image'][$k]['width']  = esc_attr($attach_details[$k][0]);
                                                $attach_images['image'][$k]['height'] = esc_attr($attach_details[$k][1]);
                                      
                                  }
                                  
                                  $k++;
                              }
                              
                          }
                          
                          if(!empty($attach_images) && is_array($attach_images)){
                                                            
                              if(isset($input2['image'])){
                                                                
                                   $featured_image = $input2['image'];
                                   $content_images = $attach_images['image'];
                                  
                                   if($featured_image && $content_images){
                                       $input2['image'] = array_merge($featured_image, $content_images);
                                   }
                                                                                                                                   
                              }else{
                                  
                                  if($attach_images){
                                      
                                      foreach($attach_images['image'] as $key => $image){
                                               
                                          if($key == 0){
                                              
                                            if($image['width'] < 1200){
                                                
                                                $resized_image = saswp_aq_resize( $image['url'], 1280, 720, true, false, true );                                                                                                
                                                $attach_images['image'][$key]['url']    =   $resized_image[0];
                                                $attach_images['image'][$key]['width']  =   $resized_image[1];
                                                $attach_images['image'][$key]['height'] =   $resized_image[2];                                                
                                            }                                             
                                            $attach_images['image'][$key]['@id']    =   saswp_get_permalink().'#primaryimage';                                            
                                          }                                                                                         
                                      }
                                      
                                  }  
                                  
                                  $input2 = $attach_images;
                              }
                                                            
                          }
                          
                          }
                          
                          if(empty($input2)){
                              
                            if(isset($sd_data['sd_default_image']['url']) && $sd_data['sd_default_image']['url'] !=''){
                                        
                                    $input2['image']['@type']  = 'ImageObject';
                                    $input2['image']['@id']    = saswp_get_permalink().'#primaryimage';
                                    $input2['image']['url']    = esc_url($sd_data['sd_default_image']['url']);
                                    $input2['image']['width']  = esc_attr($sd_data['sd_default_image_width']);
                                    $input2['image']['height'] = esc_attr($sd_data['sd_default_image_height']);                                                                 
                                            
                            }
                              
                              
                          }
                                                    
                          return $input2;
        }
        /**
         * This function gets the publisher from schema settings panel 
         * @global type $sd_data
         * @param type $d_logo
         * @return type array
         */
        public function saswp_get_publisher($d_logo = null){
                
                        global $sd_data;  
                                                                        
                        $publisher    = array();
                        $default_logo = array();
                        $custom_logo  = array();
                                      
                        $logo      = isset($sd_data['sd_logo']['url']) ?     $sd_data['sd_logo']['url']:'';	
			$height    = isset($sd_data['sd_logo']['height']) ?  $sd_data['sd_logo']['height']:'';
			$width     = isset($sd_data['sd_logo']['width']) ?   $sd_data['sd_logo']['width']:'';
                        $site_name = isset($sd_data['sd_name']) && $sd_data['sd_name'] !='' ? $sd_data['sd_name']:get_bloginfo();
                                                                                                                       
                        if($logo =='' && $height =='' && $width ==''){
                            
                            $sizes = array(
					'width'  => 600,
					'height' => 60,
					'crop'   => false,
				); 
                            
                            $custom_logo_id = get_theme_mod( 'custom_logo' );     
                            
                            if($custom_logo_id){
                                
                                $custom_logo    = wp_get_attachment_image_src( $custom_logo_id, $sizes);
                                
                            }
                            
                            if(isset($custom_logo) && is_array($custom_logo)){
                                
                                $logo           = array_key_exists(0, $custom_logo)? $custom_logo[0]:'';
                                $height         = array_key_exists(1, $custom_logo)? $custom_logo[1]:'';
                                $width          = array_key_exists(2, $custom_logo)? $custom_logo[2]:'';
                            
                            }
                                                        
                        }                            
                        
                        if($site_name){
                                                    
                            $publisher['publisher']['@type']         = 'Organization';
                            $publisher['publisher']['name']          = esc_attr($site_name);                            
                            
                            if($logo !='' && $height !='' && $width !=''){
                                                                             
                            $publisher['publisher']['logo']['@type'] = 'ImageObject';
                            $publisher['publisher']['logo']['url']   = esc_url($logo);
                            $publisher['publisher']['logo']['width'] = esc_attr($width);
                            $publisher['publisher']['logo']['height']= esc_attr($height);                        
                             
                            $default_logo['url']    = esc_url($logo);
                            $default_logo['height'] = esc_attr($height);
                            $default_logo['width']  = esc_attr($width);
                            
                          }
                                                        
                        }
                                                                          
                        if($d_logo){
                            return $default_logo;
                        }else{
                            return $publisher;
                        }                        
                        
        }
                
}
if (class_exists('saswp_output_service')) {
	$object = new saswp_output_service();
        $object->saswp_service_hooks();
};
