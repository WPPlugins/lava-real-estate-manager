<?php

class Lava_RealEstate_Manager_Shortcodes
{

	const ACCEPT		= true;
	const EXCEPT		= false;

	public function __construct()
	{
		$this->post_type										= constant( 'Lava_RealEstate_Manager_Func::SLUG' );

		add_shortcode( 'lava_realestate_listing'				, Array( $this, 'listings' ) );
		add_shortcode( 'lava_realestate_form'					, Array( $this, 'form' ) );
		add_shortcode( 'lava_realestate_mypage'					, Array( $this, 'dashboard' ) );

		require_once "functions-ajaxListings.php";

	}

	public function _array( &$arrTax = Array(), $strPosition ) {

		if( ! is_Array( $arrTax ) || empty( $arrTax ) )
			return $arrTax;

		switch( $strPosition ) {
			case 'current'	: current( $arrTax ); break;
			case 'next'		: next( $arrTax ); break;
			case 'prev'		: prev( $arrTax ); break;
			case 'first'	: reset( $arrTax ); break;
			case 'last'		:
			default			: end( $arrTax ); break;
		}
		return key( $arrTax );
	}

	public function listings( $attr, $content='' )
	{
		$post_type			= $this->post_type;

		// Variables initialize
		$output_template	= trailingslashit( lava_realestate()->template_path );
		$optAddressField	= false;

		add_action( 'wp_footer', Array( $this, '_listings_enqueues' ) );
		ob_start();
		require_once $output_template . 'template-listing.php';
		return ob_get_clean();
	}

	public function _listings_enqueues() {
		wp_enqueue_script( 'lava-listing-js' );
	}

	public function form( $attr, $content='' )
	{
		global $post, $wpdb;

		if( is_object( $post ) )
			$post->comment_close		= true;

		// If logged User ?
		if('member' ==  lava_realestate_manager_get_option( 'add_capability' ) )
			if( self::ACCEPT !== ( $cReturn = self::is_available_shortcode() ) ) return $cReturn;

		// If current user has modify permission ?
		if( $is_edit = intVal( get_query_var( 'edit' ) ) )
			if( self::ACCEPT !== ( $cReturn = self::is_can_modify( $is_edit ) ) ) return $cReturn;

		// Get Request variables
		$lava_query			= new lava_Array( $_POST );

		if( ! $wpdb->get_var( "select ID from {$wpdb->posts} where ID={$is_edit}" ) ) {
			// Initialze for edit variable.
			$edit					= new stdClass();
			$edit->ID				=
			$edit->post_title		=
			$edit->post_content		=
			$edit->post_author		= false;
		}else{
			$edit = get_post( $is_edit );
		}

		$latlng						= Array();
		foreach(
			Array( 'lat', 'lng', 'street_lat', 'street_lng', 'street_pitch', 'street_heading', 'street_zoom', 'street_visible', 'country', 'locality', 'address' )
			as $index
		) $edit->$index					= floatVal( get_post_meta( $edit->ID, 'lv_item_' . $index, true ) );

		$edit->arrAttach				= get_post_meta( $edit->ID, 'detail_images', true );

		$GLOBALS[ 'edit' ]				= $edit;
		add_action( 'wp_footer', Array( $this, '_form_enqueues' ) );

		ob_start();

		$strFormFile						= apply_filters(
			"lava_{$this->post_type}_form_loadFile"
			, trailingslashit( lava_realestate()->template_path ) . 'template-addItem.php'
		);
		if( file_exists( $strFormFile ) )
			require_once $strFormFile;

		return ob_get_clean();
	}

	public function _form_enqueues() {
		wp_enqueue_script( 'jquery-form' );
		wp_enqueue_script( 'scripts-js' );
		wp_enqueue_script( 'lava-submit-script-js' );
	}

	public function dashboard( $attr, $content='' )
	{
		$attr											= shortcode_atts(
			Array(
				'hide_pending'							=> 0
			), $attr
		);

		if( self::ACCEPT !== ( $cReturn = self::is_available_shortcode() ) ) return $cReturn;

		$GLOBALS[ 'lava_realestate_stc_mmypage' ]		= new lava_Array( $attr );
		$output_template								= trailingslashit( lava_realestate()->template_path );

		ob_start();
		require_once $output_template . 'template-dashboard.php';
		return ob_get_clean();
	}

	public function is_available_shortcode() {
		$lava_loginURL		= apply_filters( "lava_{$this->post_type}_login_url", wp_login_url() );

		if( ! is_user_logged_in() ) {
			return sprintf(
				"<div class='notice' align='center'>
					<a href=\"%s\">%s</a>
				</div>"
				, $lava_loginURL
				,__( "Please login", 'Lavacode' )
			);
		}
		return self::ACCEPT;
	}

	public function is_can_modify ( $tID = 0 )
	{
		$post	= get_post( $tID );

		if( ! is_object( $post ) ){
			return sprintf( "<div class='notice'>%s</div>", __( "Invaild Post ID.", 'Lavacode' ) );
		}

		if( $post->post_author != get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
			return sprintf( "<div class='notice'>%s</div>", __( "You are not the author.", 'Lavacode' ) );
		}
		return self::ACCEPT;
	}
}

new Lava_RealEstate_Manager_Shortcodes;