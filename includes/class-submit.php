<?php

class Lava_RealEstate_Manager_Submit
{
	public function __construct()
	{
		$this->post_type															= constant( 'Lava_RealEstate_Manager_Func::SLUG' );

		add_action( 'wp_head'														, Array( $this, 'debug' ) );

		add_action( "wp_ajax_lava_{$this->post_type}_manager_submit_item"			, Array( $this, 'submit' ) );
		add_action( "wp_ajax_nopriv_lava_{$this->post_type}_manager_submit_item"	, Array( $this, 'submit' ) );

		add_action( "wp_ajax_lava_{$this->post_type}_manager_upload_detail"			, Array( $this, 'upload_detail' ) );
		add_action( "wp_ajax_nopriv_lava_{$this->post_type}_manager_upload_detail"	, Array( $this, 'upload_detail' ) );

		add_action( "wp_ajax_lava_{$this->post_type}_manager_img"					, Array( $this, 'img' ) );
		add_action( "wp_ajax_nopriv_lava_{$this->post_type}_manager_img"			, Array( $this, 'img' ) );

		if(
			isset( $_POST[ 'lava_realestate_manager_mypage_delete' ] ) &&
			wp_verify_nonce( $_POST['lava_realestate_manager_mypage_delete'], 'security' )
		) $this->remove();
	}

	public function edit_button( $post_content )
	{
		global
			$lava_realestate_manager_func;

		if( ! is_singular( $this->post_type ) )
			return $post_content;

		if( get_current_user_ID() !== get_the_author_meta( 'ID' ) )
			return $post_content;

		if( ! $edit_page_id = intVal( lava_realestate_manager_get_option( "page_add_{$this->post_type}" ) ) )
			return $post_content;

		if( ! $edit_page_link = get_permalink( $edit_page_id ) )
			return $post_content;

		$output_content		= Array();
		$output_content[]	= sprintf(
			"<div><a href=\"%s\">%s</a></div>"
			, esc_url( add_query_arg( Array( 'edit' => get_the_ID() ), $edit_page_link ) )
			, __( "Edit", 'Lavacode' )
		);

		$output_content[]	= $post_content;
		return @implode( false, $output_content);
	}


	public function upload_detail()
	{
		$response		= Array( 'state' => 'fail' );

		//check_ajax_referer( 'lava_realestate_manager_submit_' . $this->post_type, 'security' );

		if( isset( $_FILES[ 'lava_detail_uploader' ] ) )
		{
			$fileFeaturedImage			= wp_handle_upload( $_FILES[ 'lava_detail_uploader' ], Array( 'test_form' => 0 ) );
			$detailID					= wp_insert_attachment(
				Array(
					'post_title'		=> sanitize_title( basename( $_FILES[ 'lava_detail_uploader' ][ 'name' ] ) )
					, 'post_mime_type'	=> $fileFeaturedImage[ 'type' ]
					, 'guid'			=> $fileFeaturedImage[ 'url' ]
				)
				, $fileFeaturedImage[ 'file']
			);
			$strFeaturedImageMeta		= wp_generate_attachment_metadata( $detailID, $fileFeaturedImage[ 'file' ] );
			wp_update_attachment_metadata( $detailID, $strFeaturedImageMeta );
		}

		die( json_encode( Array( 'dID' => $detailID ) ) );
	}

	public function img() {

		$attachID		= isset( $_GET[ 'id' ] ) ? intVal( $_GET[ 'id' ] ) : 0;
		die( json_encode( Array( 'dID' => $attachID, 'output' => wp_get_attachment_image( $attachID, 'thumbnail' ) ) ) );

	}

	public function submit()
	{
		global $lava_realestate_manager_func;

		$response		= Array( 'state' => 'fail' );

		check_ajax_referer( "lava_realestate_manager_submit_{$this->post_type}", 'security' );

		$is_update			= false;
		$is_publish			= lava_realestate_manager_get_option( "new_{$this->post_type}_status" ) !== 'pending';
		$is_publish			= (boolean) apply_filters( "lava_{$this->post_type}_new_status", $is_publish );

		$lava_dashboardID	= lava_realestate()->admin->get_settings( 'page_my_page' );

		try{

			$post_args		= Array();
			$lava_query		= new lava_Array( $_POST );
			$userID			= get_current_user_id();

			if( ! is_user_logged_in() ) {

				if( ! $user_email = $lava_query->get( 'user_email', false ) )
					throw new Exception( __( "Invaild User Email.", 'Lavacode' ) );

				$user_email_meta	= explode( '@', $user_email );
				$user_login			= sanitize_user( $user_email_meta[0] );
				$user_pass			= wp_generate_password();

				wp_clear_auth_cookie();

				$userID = wp_insert_user( compact( 'user_email', 'user_login', 'user_pass' ) );

				if( is_wp_error( $userID ) )
					throw new Exception( $userID->get_error_message() );

				wp_new_user_notification( $userID, $user_pass );
				wp_set_current_user( $userID );
				wp_set_auth_cookie( $userID );
				do_action( 'wp_login', $userID );
			}

			if( intVal( $lava_query->get( 'post_id', 0 ) )  > 0 )
				$is_update	= true;

			$post_type		= $this->post_type;
			$post_title		= $lava_query->get( 'txt_title' );
			$post_content	= $lava_query->get( 'txt_content' );

			$post_args		= compact( 'post_type', 'post_title', 'post_content', 'post_status' );

			if( $is_update ) {
				$post_args['ID']			= $lava_query->get( 'post_id', 0 );
				$post_id					= wp_update_post( $post_args );
			}else{
				$post_args['post_status']	= $is_publish ? 'publish' : 'pending';
				$post_id					= wp_insert_post( $post_args );
			}

			if( intVal( $post_id ) > 0 )
			{
				$GLOBALS[ 'lava_realestate_form_current_id' ] = $post_id;

				$lava_taxonomies			= $lava_query->get( 'lava_additem_terms'	, Array() );
				$lava_metafields			= $lava_query->get( 'lava_additem_meta'		, Array() );
				$lava_locations				= $lava_query->get( 'lava_location'			, Array() );
				$arrLocation				= Array();

				if( isset( $_FILES[ 'lava_featured_file' ] ) && $_FILES[ 'lava_featured_file' ]['size'] > 0 )
				{

					$fileFeaturedImage			= wp_handle_upload( $_FILES[ 'lava_featured_file' ], Array( 'test_form' => 0 ) );
					$featuredID					= wp_insert_attachment(
						Array(
							'post_title'		=> sanitize_title( basename( $_FILES[ 'lava_featured_file' ][ 'name' ] ) )
							, 'post_mime_type'	=> $fileFeaturedImage[ 'type' ]
							, 'guid'			=> $fileFeaturedImage[ 'url' ]
						)
						, $fileFeaturedImage[ 'file']
					);
					$strFeaturedImageMeta		= wp_generate_attachment_metadata( $featuredID, $fileFeaturedImage[ 'file' ] );
					wp_update_attachment_metadata( $featuredID, $strFeaturedImageMeta );
					set_post_thumbnail( $post_id, $featuredID );
				}

				update_post_meta( $post_id, 'detail_images', $lava_query->get( 'lava_attach' ) );

				if( !empty( $lava_taxonomies ) && is_Array( $lava_taxonomies ) ) : foreach( $lava_taxonomies as $taxonomy => $values ) {
					if( $taxonomy != $this->post_type . '_keyword' )
						$values = Array_map( 'intVal', $values );
					wp_set_object_terms( $post_id, $values, $taxonomy );
				} endif;

				if( !empty( $lava_metafields ) && is_Array( $lava_metafields ) ) : foreach( $lava_metafields as $name => $values ) {
					update_post_meta( $post_id, $name, $values );
				} endif;

				if( !empty( $lava_locations ) && is_Array( $lava_locations ) ) : foreach( $lava_locations as $name => $values ) {

					update_post_meta( $post_id, "lv_item_{$name}", $values );

					if( $name == 'locality' || $name == 'country' )
						$arrLocation[] = $values;

				} endif;

				update_post_meta( $post_id, '_location', $arrLocation );

				do_action( "lava_{$this->post_type}_json_update", $post_id, get_post( $post_id ), $is_update );

				$response[ 'state' ]	= 'OK';

				if( get_post_status( $post_id ) === 'publish' ) {
					$strRedirect	= get_permalink( $post_id );
				}else{
					$strRedirect	=  intVal( $lava_dashboardID ) > 0 ? get_permalink( $lava_dashboardID ) : home_url();
				}
				$response[ 'link']	= apply_filters( "lava_{$this->post_type}_new_item_redirect", $strRedirect, $post_id );

			}else{
				throw new Exception( __( "Please try again, failure to submit", 'Lavacode' ) );
			}
		} catch( Exception $e ) {
			die( json_encode( Array( 'err' => $e->getMessage() ) ) );

		}

		die( json_encode( $response ) );
	}

	public function remove()
	{
		global $lava_dashboard_message;

		$lava_query		= new lava_Array( $_POST );
		$post			= get_post( $lava_query->get( 'post_id', 0 ) );

		if(
			$post->post_author == get_current_user_id() ||
			current_user_can( 'manage_option' )
		){
			wp_delete_post( $post->ID, true );
			$lava_dashboard_message = __( "It has been deleted", 'Lavacode' );
		}else{
			$lava_dashboard_message = __( "You are not the author.", 'Lavacode' );
		}
	}

	public function debug() {}
}