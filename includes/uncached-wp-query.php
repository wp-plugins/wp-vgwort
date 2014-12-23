<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_Uncached_WP_Query {

	
	const POST_COUNT_PER_QUERY = 200;


	
	private $queryParameters;

	
	private $postQuery = null;

	
	private $currentOffset = 0;

	
	private $currentPost = null;


	
	public function __construct( array $query ) {
		
		$this->queryParameters = array_merge(
			$query,
			array(
				'posts_per_page'         => self::POST_COUNT_PER_QUERY,
				'cache_results'          => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'no_found_rows'          => true,
			)
		);


		
		$this->postQuery = new WP_Query(
			array_merge(
				$this->queryParameters,
				array( 'offset' => $this->currentOffset )
			)
		);
	}


	
	public function has_post() {
		return $this->postQuery->have_posts();
	}

	
	public function get_post() {
		
		if ( $this->currentPost !== null ) {
			wp_cache_delete( $this->currentPost->ID, 'posts' );
			wp_cache_delete( $this->currentPost->ID, 'post_meta' );
		}

		
		$post = $this->postQuery->next_post();
		$this->currentPost = $post;

		
		if ( !$this->postQuery->have_posts() ) {
			
			wp_reset_query();

			
			$this->currentOffset += self::POST_COUNT_PER_QUERY;

			
			$this->postQuery = new WP_Query(
				array_merge(
					$this->queryParameters,
					array( 'offset' => $this->currentOffset )
				)
			);
		}

		return $post;
	}

}