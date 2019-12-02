<?php

/**
 *  author: Mario Helbing, CSGIS
 *  rest controller class to extend the wp rest api
 *  with the routes the project needs.
 *
 *  - loads the route config files,
 *  - register the routes and handles the request.
 */

class MH_REST_Controller extends WP_REST_Controller {
	var $my_namespace = 'shap/v';
	var $my_version   = '1';
	var $my_route_files;
	
	public function __construct(){
		$this->$my_route_files = glob( dirname(__FILE__) . '/[!_]*/[!_]*.route.php' );
	}
	
	// generate response, get the results and request infos 
	private function generateResponse( $request, $callback ) {
		$_result = $callback( $request );
		$result = [];

		if( is_array($_result) ){
			foreach( $_result as $_key => $_item ) {
				# if its posts collection: remap array
				if( is_a($_item, 'wp_post') ) {
					$result[] = $this->translatePostToRestArray($_item);						
				}else{
					$result[$_key] = $_item;						
				}
			}
		}
		
		// count result
		$countResult = null;
		if( is_countable($result) ){
			$countResult = count($result);
		}
		
		// build the response array
		$return = [
			'request' => [
				'url' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
				'params' => $request->get_params(),
			],
			'info' => [
				'dbQueries' => get_num_queries(),
				'countResults' => $countResult,
			],
			'result' => $result,
		];
		
		// merge optional info pairs from request
		if( is_array( $request->info ) ) $return['info'] = array_merge( $return['info'], $request->info );
		
		return $return;
	}
	
	// remap the post array to the json format 
	private function translatePostToRestArray( $aPost ){
		$aPost = (array) $aPost;
		
		$return = [
			'id'           => $aPost['ID'],
			#'guid'         => get_the_guid( $aPost['ID'] ),
			'date'         => mysql_to_rfc3339( $aPost['post_date'] ),
			'date_gmt'     => mysql_to_rfc3339( $aPost['post_date_gmt'] ),
			'modified'     => mysql_to_rfc3339( $aPost['post_modified'] ),
			'modified_gmt' => mysql_to_rfc3339( $aPost['post_modified_gmt'] ),
			'slug'         => $aPost['post_name'],
			'status'       => $aPost['post_status'],
			'type'         => $aPost['post_type'],
			'link'         => get_permalink( $aPost['ID'] ),
			'adjacentLinks'=> $aPost['adjacentLinks'],			
			
			'title' 	   => ['rendered' => apply_filters('the_title',   $aPost['post_title'])],
			'content' 	   => ['rendered' => apply_filters('the_content', $aPost['post_content'])],
			'excerpt' 	   => ['rendered' => apply_filters('the_excerpt', $aPost['post_excerpt'])],
			
			'author' 	   => intval( $aPost['post_author'] ),
			'parent' 	   => $aPost['post_parent'],
			'menuOrder'    => $aPost['menu_order'],
			'template' 	   => $aPost['template'],
		];
		
		if( function_exists('get_fields') ) $return['acf'] = get_fields( $aPost['ID'] );
		
		return $return;
	}
	
	// register our routes
	public function registerRoutes() {
		$namespace  = $this->my_namespace.$this->my_version;
		$routeFiles = $this->$my_route_files;
		
		// register routes, set callbacks
		foreach( $routeFiles as $route ) {
			$routeFile   = basename( $route );
			$dirName     = basename( dirname( $route ) );
			$routeConfig = include( $dirName.'/'.$routeFile );
			$route       = $routeConfig['route'];
			$args        = [];

			// wrap callback function with generateResponse()
			if( is_array( $routeConfig['args'] ) ){				
				foreach( $routeConfig['args'] as $key => $value ){
					if($key == 'callback'){
						$args[$key] = function( $request ) use ($value) {
							$return = $this->generateResponse( $request, $value );
							return $return;
						};
						
					} else {
						$args[$key] = $value;
					}
					
				}
			}
			
			// register route
			register_rest_route( $namespace, $routeConfig['route'], $args );
		}
	}
	
	// register our REST server
	public function hookRestServer() {
		add_action('rest_api_init', array($this, 'registerRoutes'));
		#print_r2($this->$my_route_files, '$this->$my_route_files');
		//add_action('rest_api_init', 'my_customize_rest_cors', 15);
	}
}
$MH_REST_Controller = new MH_REST_Controller();
$MH_REST_Controller->hookRestServer();	
