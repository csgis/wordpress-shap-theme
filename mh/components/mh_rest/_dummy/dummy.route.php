<?php 

/**
 *  author: Mario Helbing, CSGIS
 */

	return [
		'route' => '/'.basename(__DIR__),
		'args' => [
			'methods'  => 'GET',
			#'permissions_callback' => 'is_user_logged_in',
			'args' => [
				'sampleArg' => [
					'required' => false,
					'type' => 'string',
					'description' => 'Specify something',
					'enum' => array( 
						 'foo',
						 'bar',
					),
					#'validate_callback' => 'my_function',
					#'sanitize_callback' => 'my_function',
					#'default' => 'mario',
				]
			],
			'callback' => function( $request ){
				$queryArgs = [
					'posts_per_page' => -1,
					'post_type'      => '',
					'orderby'        => 'menu_order',
					'order'          => 'asc',
					#'orderby'        => 'post__in',
					#'post__in'       => [],
				];
				$posts = get_posts($queryArgs);
				return $posts;			
			},
		],
	]

