<?php
// add_action( 'wp_ajax_lava_map_info_window_content'			, 'lava_map_info_window_content' );
// add_action( 'wp_ajax_nopriv_lava_map_info_window_content'	, 'lava_map_info_window_content' );

function lava_map_info_window_content()
{
	header( 'Content-Type: application/json; charset=utf-8' );

	$lava_query					= new lava_Array( $_POST );
	$lava_result				= Array( "state" => "fail" );

	if( false !== ( $post_id = $lava_query->get( "post_id", false ) ) )
	{
		$post					= get_post( $post_id );

		//
		if( false == ( $lava_this_author		= get_userdata( $post->post_author ) ) )
		{
			$lava_this_author					= new stdClass();
			$lava_this_author->display_name		= '';
			$lava_this_author->avatar			= 0;
		}


		// Post Thumbnail
		if( '' !== ( $lava_this_thumb_id		= $lava_this_author->avatar ) )
			{
				$lava_this_thumb_url			= wp_get_attachment_image_src( $lava_this_thumb_id , 'lava-box-v' );

				if( isset( $lava_this_thumb_url ) ) {
					$lava_this_thumb			= $lava_this_thumb_url[0];
				}
			}


			// If not found this post a thaumbnail
			if( empty( $lava_this_thumb ) )
			{
				$lava_this_thumb		= ''; //$lava_tso->get( 'no_image', LAVA_IMG_DIR.'/no-image.png' );

			}
			$lava_this_thumb		= apply_filters( 'lava_map_list_thumbnail', $lava_this_thumb, $post );
			$lava_this_thumb	= "<div class=\"lava-thb\" style=\"background-image:url({$lava_this_thumb});\"></div>";

		// Other Informations
		$lava_result			= Array(
			'state'				=> 'success'
			, 'post_id'			=> $post->ID
			, 'post_title'		=> $post->post_title
			, 'permalink'		=> get_permalink( $post->ID )
			, 'thumbnail'		=> $lava_this_thumb
			, 'category'		=> current( wp_get_object_terms( $post->ID, 'item_category', Array( 'fields' => 'names' ) ) )
			, 'location'		=> current( wp_get_object_terms( $post->ID, 'item_category', Array( 'fields' => 'names' ) ) )
			, 'author_name'		=> $lava_this_author->display_name
		);
	}
	die( json_encode( $lava_result ) );
}



add_action( 'wp_ajax_nopriv_lava_' . self::SLUG . '_map_list'	, 'lava_map_listings_contents' );
add_action( 'wp_ajax_lava_' . self::SLUG . '_map_list'			, 'lava_map_listings_contents' );
function lava_map_listings_contents()
{
	global $post, $lava_favorite;

	header( 'Content-Type: application/json; charset=utf-8' );

	$post_ids					= isset( $_REQUEST['post_ids'] ) ? (Array)$_REQUEST['post_ids'] : Array();
	$lava_result				= Array();

	foreach( $post_ids as $post_id )
	{

		if( null !== ( $post = get_post( $post_id ) ) )
		{

			// Get Ratings
			// $lava_rating					= new lava_RATING( $post->ID );

			$lava_author					= get_userdata( $post->post_author );
			$lava_author_name				= isset( $lava_author->display_name ) ? $lava_author->display_name : null;
			$lava_has_author				= isset( $post->post_author );

			$lv_property_city = lava_realestate_featured_terms( 'property_city', $post->ID, false )!= '' ? lava_realestate_featured_terms( 'property_city', $post->ID, false ) : 'No City';
			$lv_property_type = lava_realestate_featured_terms( 'property_type', $post->ID, false )!='' ? lava_realestate_featured_terms( 'property_type', $post->ID, false ) : 'No Type';
			$lv_property_status = lava_realestate_featured_terms( 'property_status', $post->ID, false )!='' ? lava_realestate_featured_terms( 'property_status', $post->ID, false ) : 'No Status';

			$attachment_noimage				= apply_filters( 'lava_realestate_listing_featured_no_image', lava_realestate()->image_url . 'no-image.png' );

			/* Post Thumbnail */ {
				$lava_this_thumb			= '';
				if( '' !== ( $lava_this_thumb_id = get_post_thumbnail_id( $post->ID ) ) ) {
					$lava_this_thumb_url	= wp_get_attachment_image_src( $lava_this_thumb_id , 'thumbnail' );
				}
				$lava_this_thumb			= isset( $lava_this_thumb_url[0] ) ? $lava_this_thumb_url[0] : $attachment_noimage ;

				$lava_this_thumb			= apply_filters( 'lava_map_list_thumbnail', $lava_this_thumb, $post );

				// If not found this post a thaumbnail
				if( empty( $lava_this_thumb ) ) {
					$lava_this_thumb		= null;
				}
				$lava_this_thumb_large		= "<div class=\"lava-thb\" style=\"background-image:url({$lava_this_thumb});\"></div>";
			}

			/* Near place search */{

				$lava_place_results		= Array();
				$lava_lat = $lava_lng	= null;

				if(
					( $lava_lat = get_post_meta( $post->ID, 'lv_item_lat', true ) ) &&
					( $lava_lng = get_post_meta( $post->ID, 'lv_item_lng', true ) ) &&
					isset( $_REQUEST['place'] )
				) {
					$lava_place_query			= Array(
						// 'key'					=> $lava_tso->get( 'google_api', 'AIzaSyDqixDRi7EBUxcbE0cLjYIw-NlB6RFKqyI' )
						'key'					=> 'AIzaSyDqixDRi7EBUxcbE0cLjYIw-NlB6RFKqyI'
						, 'sensor'				=> 'false'
						, 'radius'				=> 5000
						, 'location'			=> "{$lava_lat},{$lava_lng}"
						, 'types'				=> 'airport|bus_station|train_station'
					);

					foreach( Array( 'commute', 'location' ) as $type )
					{

						if( $type == 'location' )
							$lava_place_query['types'] = "bank";

						$lava_place_query_url		= "https://maps.googleapis.com/maps/api/place/nearbysearch/json?";
						foreach( $lava_place_query as $key => $value )
							$lava_place_query_url	.= "{$key}={$value}&";

						$lava_place_query_url		= substr( $lava_place_query_url, 0, -1 );
						$lava_place_response		= wp_remote_get( $lava_place_query_url, Array( 'header' => Array( 'Content-type' => 'application/json' ) ) );
						$lava_place_result			= wp_remote_retrieve_body( $lava_place_response );

						$lava_place_results[$type]	= json_decode( $lava_place_result );
					}
				}
			}

			// Other Informations
			$additional_item		= Array(
				'post_id'			=> $post->ID
				, 'post_title'		=> $post->post_title
				, 'post_content'	=> $post->post_content
				, 'post_date'		=>
					sprintf(
						__( "%s ago", 'Lavacode' )
						, human_time_diff(
							date( 'U', strtotime( $post->post_date ) )
							, current_time( 'timestamp' )
						)
					)
				, 'excerpt'			=> $post->post_excerpt
				, 'thumbnail_large'	=> $lava_this_thumb_large
				, 'thumbnail_url'	=> $lava_this_thumb
				, 'permalink'		=> get_permalink( $post->ID )
				, 'author_name'		=> $lava_author_name
				, 'f'				=> get_post_meta( $post->ID, '_featured_item', true )
				, 'place'			=> $lava_place_results
				, 'lat'				=> $lava_lat
				, 'lng'				=> $lava_lng
				, 'property_city' => $lv_property_city
				, 'property_type' => $lv_property_type
				, 'property_status' => $lv_property_status
			);

			if( 'use' === get_post_meta( $post->ID, '_featured_item', true ) )
				$additional_item[ 'featured' ] = 'yes';

			$lava_result[]			= apply_filters( 'lava_multiple_listing_contents', $additional_item, $post->ID );
		} // End If
	} // End foreach
	die( json_encode( $lava_result ) );
}