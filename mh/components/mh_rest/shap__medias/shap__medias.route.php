<?php 

/**
 *  author: Mario Helbing, CSGIS
 */

	function countAllMediaItems(){  
		$query_img_args = array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'posts_per_page' => -1,  
		);  
		$query_img = new WP_Query( $query_img_args );  
		return $query_img->post_count;
	} 

	return [
		'route' => '/'.basename(__DIR__),
		'args' => [
			'methods'  => 'GET',
			'args' => [
				'shap_places' => [
					'required' => false,
					'type' => 'integer',
					'description' => 'Specify the shap_places id to filter the results',
				],
				'perPage' => [
					'required' => false,
					'type' => 'integer',
					'description' => 'How many items per page.',
					'default' => 15,
					/*
					'sanitize_callback' => function( $value ){
						$value = absint( $value );
						if( $value > 99 ) $value = 99;
						return $value;
					},
					*/
				],
				'paged' => [
					'required' => false,
					'type' => 'integer',
					'description' => 'Which page.',
					'default' => 1,
					'sanitize_callback' => 'absint',
				],
			],
			'callback' => function( $request ){
				$params	    = $request->get_params();
				$perPage    = $request->get_param('perPage');
				$paged      = $request->get_param('paged');				
				$totalPosts = countAllMediaItems();
				$totalPages = ceil( $totalPosts/$perPage );
				$currentUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				
				// set infos about pagination
				if( $paged ){				
					$request->info['totalPosts']  = $totalPosts;
					$request->info['totalPages']  = $totalPages;
					$request->info['currentPage'] = $paged;
					
					for( $i=1; $i < $totalPages+1; $i++ ) {						
						$params['paged'] = $i;
						$request->info['paginationLinks'][] = explode("?", $currentUrl, 2)[0] . '?' . http_build_query( $params );
					}
				}
				
				/*
				header("Content-Type: text/html");
				print_r2( $totalPosts, '$totalPosts' );
				print_r2( $totalPages, '$totalPages' );
				die;
				*/
				
				// default query args
				$queryArgs = [
					'posts_per_page' => $perPage,
					'post_type'      => 'attachment',
					'orderby'        => 'menu_order',
					'order'          => 'asc',
					'paged'          => $paged,
				];
				
				// additional query args if filter
				if( $params['shap_places'] ){
					$queryArgs['tax_query'] = [
						[
							'taxonomy'         => 'shap_places',
							'field'            => 'term_id', 
							'terms'            => $params['shap_places'],
							'include_children' => false
						]
					];
				}
				
				// get posts
				$posts = get_posts($queryArgs);
				
				// add additional data
				$_posts = [];
				foreach( $posts as $post ){		
					$id         = $post->ID;
					$metas      = get_post_meta( $id, '' );
					$taxonomies = get_attachment_taxonomies( $id );
				
					#header("Content-Type: text/html");
					#print_r2( get_intermediate_image_sizes() );
					#print_r2( $_wp_additional_image_sizes );
					
					// map media sources
					$sources = [];
					if( strpos( $post->post_mime_type, "image/" ) === 0 ){ // for images only
						$wpImageSizes = get_intermediate_image_sizes();
						foreach( $wpImageSizes as $wpImageSize ){
							$source = wp_get_attachment_image_src( $id, $wpImageSize );
							$sources[$wpImageSize] = [
								'url'    => $source[0],
								'width'  => $source[1],
								'height' => $source[2],
							];
						}
					}
					
					#print_r2( $sources );
					#die;
					
					// map taxonomies
					foreach( $taxonomies as $i => $taxonomy ) { 
						$term = get_the_terms( $id, $taxonomy );
						$taxonomies[$taxonomy] = $term;
						unset( $taxonomies[$i] ); 
					}
					
					// map to single meta values
					foreach( $metas as $key => $value ) { 
						$metas[$key] = $value[0];
					}

					// add sources, meta and taxonomies
					$post->sources = $sources;
					$post->metas = $metas;
					$post->taxonomies = $taxonomies;
					$_posts[] = (array) $post;
				}
				return $_posts;		
			},
		],
	]
?>
