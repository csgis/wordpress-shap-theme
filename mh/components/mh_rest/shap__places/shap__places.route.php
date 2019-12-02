<?php 

/**
 *  author: Mario Helbing, CSGIS
 */

return [
	'route' => '/'.basename(__DIR__),
	'args' => [
		'methods'  => 'GET',
		'args' => [],
		'callback' => function( $request ){
			
			# set wpml lang 
			# global $sitepress;
			# $sitepress->switch_lang('en', true);
			$terms = (array) get_terms([
				'taxonomy' => 'shap_places',
				'hide_empty' => false,
			]);
			
			/*
			header("Content-Type: text/html");
			print_r2( $terms, '$terms' );
			die;
			*/
			
			foreach ($terms as $term) {		
				$termId    = $term->term_id;
				$termMetas = get_term_meta( $term->term_id, '' );
				
				// map to single meta values
				foreach( $termMetas as $key => $value ) { 
					$termMetas[$key] = $value[0];
				}

				$term->metas = $termMetas;
			}
			
			return $terms;			
			
		},
	],
]
	
 ?>
