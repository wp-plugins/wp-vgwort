<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */


/**
 * Encapsulates the uncached usage of WP_Query. Posts are retrieved partially.
 * It uses significant less memory, because the post data cache will be deleted after usage.
 */
class WPVGW_Uncached_WP_Query {

	/**
	 * Number of posts that will be retrieved by WP_Query (limit).
	 */
	const POST_COUNT_PER_QUERY = 200;


	/**
	 * @var array An array of WP_Query parameters. Same as {@link WP_Query}.
	 */
	private $queryParameters;

	/**
	 * @var WP_Query|null The current WP_Query.
	 */
	private $postQuery = null;

	/**
	 * @var int The current offset for WP_Query.
	 */
	private $currentOffset = 0;

	/**
	 * @var WP_Post
	 */
	private $currentPost = null;


	/**
	 * Creates a new instance of {@link WPVGW_Uncached_WP_Query}.
	 *
	 * @param array $query An array if query parameter. Same as {@link WP_Query}.
	 * Some parameters are overwritten to ensure non caching.
	 * A limit ('posts_per_page') cannot be set, all posts are retrieved.
	 */
	public function __construct( array $query ) {
		// merge parameters and set non caching and paged retrieval
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


		// create initial WP_Query with the offset 0
		$this->postQuery = new WP_Query(
			array_merge(
				$this->queryParameters,
				array( 'offset' => $this->currentOffset )
			)
		);
	}


	/**
	 * Test if there is a next post to be retrieved.
	 *
	 * @return bool True if there is a next post that can be retrieved, otherwise false.
	 */
	public function has_post() {
		return $this->postQuery->have_posts();
	}

	/**
	 * Retrieves the current post and sets pointer to the next post.
	 *
	 * @return WP_Post The current post.
	 */
	public function get_post() {
		// clear cached data (WP_Query caches data) of the previous post
		if ( $this->currentPost !== null ) {
			wp_cache_delete( $this->currentPost->ID, 'posts' );
			wp_cache_delete( $this->currentPost->ID, 'post_meta' );
		}

		// get next post
		$post = $this->postQuery->next_post();
		$this->currentPost = $post;

		// are there no more posts?
		if ( !$this->postQuery->have_posts() ) {
			// restore global post data stomped by the_post()
			wp_reset_query();

			// iterate the offset
			$this->currentOffset += self::POST_COUNT_PER_QUERY;

			// create new WP_Query with the new offset
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