<?php

class Lava_RealEstate_Manager_template
{




	/**
	 *	Constructor
	 *
	 *
	 *	@return	void
	 */
	public function __construct()
	{
		$this->post_type											= constant( 'Lava_RealEstate_Manager_Func::SLUG' );

		/** Common hooks */ {
			add_filter( 'template_include'							, Array( $this, 'load_templates' ) );
		}

		/** Register Map Template */ {
			add_filter( 'page_attributes_dropdown_pages_args'		, Array( $this, 'register_map_tempate' ) );
			add_filter( 'wp_insert_post_data'						, Array( $this, 'register_map_tempate' ) );

		}

		/** Single page template */ {

			add_action(
				"lava_{$this->post_type}_single_container_before"
				, Array( $this, 'parse_post_object' )
			);

			add_action(
				"lava_{$this->post_type}_single_container_after"
				, Array( $this, 'single_script_params' ), 20
			);

			add_action(
				"lava_{$this->post_type}_single_container_after"
				, Array( $this, 'single_script' ), 30
			);
		}

		/** Map page template  */ {
			add_action( "lava_{$this->post_type}_map_container_after" , Array( $this, 'print_map_templates' ) );
		}

		/** Add form template */ {
			add_action( "lava_add_{$this->post_type}_form_before"	, Array( $this	, 'author_user_email' ), 20 );
			add_action( "lava_add_{$this->post_type}_form_after"	, Array( $this	, 'extend_form' ) );
			add_filter( "lava_add_{$this->post_type}_terms"			, Array( $this	, 'addItem_terms' ), 9 );

			foreach(
				Array( 'category', 'type' )
				as $key
			) add_filter( "lava_map_meta_{$key}"					, Array( $this, "map_meta_{$key}" ), 10, 2 );
		}

		/** Shortcode - listings */ {

			// Output Templates
			add_action( "lava_{$this->post_type}_listings_after"	, Array( $this, 'print_listings_templates' ) );

			// Output Variables
			add_action( "lava_{$this->post_type}_listings_after"	, Array( $this, 'print_listings_var' ) );
		}
	}




	/**
	 *
	 *
	 *
	 *	@param	array
	 *	@return	array
	 */
	public function register_map_tempate( $attr )
	{
		$cache_key	= 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
		$templates	= wp_get_theme()->get_page_templates();
		$templates	= empty( $templates ) ? Array() : $templates;
		$templates	= wp_parse_args(
			$templates
			, Array(
				"lava_{$this->post_type}_map"	=> sprintf(
					__( "Lava %s Map Template", 'Lavacode' )
					, get_post_type_object( $this->post_type )->label
				)
			)
		);
		wp_cache_delete( $cache_key , 'themes');
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );
		return $attr;
	}




	/**
	 *
	 *
	 *
	 *	@param	array
	 *	@return	array
	 */
	public static function addItem_terms( $args )
	{
		global $lava_realestate_manager_func;

		$lava_exclude					= Array();

		$lava_taxonomies				= $lava_realestate_manager_func->lava_extend_item_taxonomies();

		if( empty( $lava_taxonomies ) || !is_Array( $lava_taxonomies ) )
			return $args;

		if( !empty( $lava_exclude ) ) : foreach( $lava_exclude as $terms ) {
			if( in_Array( $terms, $lava_taxonomies ) )
				unset( $lava_taxonomies[ $terms] );
		} endif;

		return wp_parse_args( Array_Keys( $lava_taxonomies ), $args );
	}




	/**
	 *
	 *
	 *	@param	string	template path
	 *	@return	string	template path
	 */
	public function load_templates( $template )
	{
		global $wp_query;

		$post		= $wp_query->queried_object;

		if( is_a( $post, 'WP_Post' ) ) {

			/* Single Template */ {
				if( $wp_query->is_single && $post->post_type == $this->post_type ) {

					if(  $__template = locate_template(
							Array(
								"single-{$this->post_type}.php"
								, lava_realestate()->folder . "/single-{$this->post_type}.php"
							)
						)
					) $template = $__template;
				}
			}

			/* Map Template */ {
				if( "lava_{$this->post_type}_map" == get_post_meta( $post->ID, '_wp_page_template', true ) ){
					$template = $this->get_map_template();
				}
			}
		}
		return apply_filters( "lava_{$this->post_type}_get_template", $template, $wp_query, $this );
	}




	/**
	 *
	 *
	 *	@return	string
	 */
	public function get_map_template()
	{
		add_action( 'wp_enqueue_scripts'	, Array( $this, 'map_template_enqueues' ) );
		add_action( 'body_class'					, Array( $this, 'map_template_body_class' ) );
		add_action( 'get_header'					, Array( $this, 'remove_html_margin_top' ) );
		add_action( 'wp_head'						, Array( $this, 'parse_mapdata' ) );

		$result_template	= lava_realestate()->template_path . "/template-map.php";
		if(
			$__template = locate_template(
				Array(
					"lava-map-template.php"
					, lava_realestate()->folder . "/lava-map-template.php"
				)
			)
		) $result_template = $__template;

		return $result_template;
	}





	/**
	 *
	 *
	 *	@param	Array
	 *	@return	void
	 */
	public function map_template_body_class( $classes ) {
		$classes = apply_filters( "lava_{$this->post_type}_map_classes",$classes );
		return wp_parse_args( Array( "page-template-lava_{$this->post_type}_map" ), $classes );
	}





	/**
	 *
	 *
	 *	@return	void
	 */
	public function parse_mapdata()
	{
		lava_realestate_mapdata( $post );
		$GLOBALS[ 'post' ] = $post;
	}





	/**
	 *
	 *
	 *	@param	object
	 *	@return	void
	 */
	public function extend_form( $edit )
	{
		$arrPartFiles	= apply_filters(
			'lava_realestate_manager_add_item_extends'
			, Array(
				'lava-add-item-terms.php'
				, 'lava-add-item-file.php'
				, 'lava-add-item-location.php'
				, 'lava-add-item-meta.php'
			)
		);

		if( !empty( $arrPartFiles ) ) :  foreach( $arrPartFiles as $filename ) {
			$filepath	= trailingslashit( lava_realestate()->template_path ) . "form/{$filename}";
			if( file_exists( $filepath ) ) require_once $filepath;
		} endif;
	}




	/**
	 *
	 *
	 *	@param	array
	 *	@return	void
	 */
	public function author_user_email( $edit )
	{

		$lava_loginURL			= apply_filters( "lava_{$this->post_type}_login_url", wp_login_url() );

		if( is_user_logged_in() )
			return;

		$filepath				= trailingslashit( lava_realestate()->template_path ) . "form/lava-add-item-user.php";
		if( file_exists( $filepath ) ) require_once $filepath;
	}




	/**
	 *
	 *
	 *	@return	void
	 */
	public function parse_post_object()
	{
		lava_realestate_setupdata();
	}




	/**
	 *
	 *
	 *	@param	array
	 *	@return	void
	 */
	public function single_script_params()
	{
		$post		= get_post();
		$options	= Array(
			'strNotLocation'			=> __( "There is no location information on this property.", 'Lavacode' )
			, 'strNotStreetview'		=> __( "This location is not supported by google StreetView or the location did not add.", 'Lavacode' )
		);

		echo "<fieldset class=\"lava-single-map-param hidden\">";

		echo "
			<!-- parameters -->
			<input type=\"hidden\" value=\"disable\" data-cummute-panel>
			<input type=\"hidden\" value=\"450\" data-map-height>
			<input type=\"hidden\" value=\"500\" data-street-height>
			<!-- end parameters -->
			";

		if( ! empty( $options ) ) : foreach( $options as $key => $value ) {
			echo "<input type='hidden' key=\"{$key}\" value=\"{$value}\">";
		} endif;

		foreach(
			Array( 'lat', 'lng', 'street_lat', 'street_lng', 'street_heading', 'street_pitch', 'street_zoom', 'street_visible' )
			as $key
		) printf(
			"<input type=\"hidden\" data-item-%s value=\"%s\">\n"
			, str_replace( '_', '-', $key )
			, floatVal( get_post_meta( $post->ID, "lv_item_{$key}", true ) )
		);
		echo "</fieldset>";
	}




	/**
	 *
	 *
	 *	@param	array
	 *	@return	void
	 */
	public function single_script()
	{
		echo "
			<script type=\"text/javascript\">
			jQuery( function($){
				jQuery.lava_single({
					map			: $( '#lava-single-map-area' )
					, street	: $( '#lava-single-streetview-area' )
					, slider	: $( '.lava-detail-images' )
					, param		: $( '.lava-single-map-param' )
				});
			} );
			</script>
			";
	}




	/**
	 *
	 *
	 *	@param	array
	 *	@return	void
	 */
	public function map_template_enqueues()
	{
		wp_enqueue_script( 'google-maps' );
		wp_enqueue_script( 'gmap-v3' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'google-map-infobubble-js' );
		wp_enqueue_script( 'lava-map-js' );
		do_action( "lava_{$this->post_type}_map_box_enqueue_scripts" );
	}




	/**
	 *
	 *
	 *	@param	array
	 *	@return	void
	 */
	public function remove_html_margin_top()
	{
		remove_action('wp_head', '_admin_bar_bump_cb');
	}




	/**
	 *
	 *
	 *	@return	void
	 */
	public function print_map_templates()
	{
		$tmpDir				= lava_realestate()->template_path . '/';

		$load_map_htmls		= Array(
			'lava-map-output-template'		=> $tmpDir . 'template-map-htmls.php'
			, 'lava-map-not-found-template'	=> $tmpDir . 'template-not-list.php'
		);

		$load_map_htmls		= apply_filters( "lava_{$this->post_type}_map_htmls", $load_map_htmls, $tmpDir );

		$output_script		= Array();
		if( !empty( $load_map_htmls ) ) : foreach( $load_map_htmls as $sID => $strFilePath ) {

			$output_script[]	= "<script type='text/html' id=\"{$sID}\">";
			ob_start();

			if( file_exists( $strFilePath ) )
				require_once $strFilePath;

			$output_script[]	= ob_get_clean();
			$output_script[]	= "</script>";

		} endif;
		echo @implode( "\n", $output_script );
	}




	/**
	 *
	 *
	 *	@return	void
	 */
	public function print_listings_templates()
	{
		$load_map_htmls		= Array(
			'lava-realstate-manager-listing-template'	=> 'template-listing-list.php'
		);

		$load_map_htmls		= apply_filters( 'lava_{$this->post_type}_map_htmls', $load_map_htmls );
		$output_script		= Array();
		if( !empty( $load_map_htmls ) ) : foreach( $load_map_htmls as $sID => $strFilename ) {

			$output_script[]	= "<script type='text/html' id=\"{$sID}\">";
			ob_start();
			require_once lava_realestate()->template_path . "/{$strFilename}";
			$output_script[]	= ob_get_clean();
			$output_script[]	= "</script>";

		} endif;
		echo @implode( "\n", $output_script );
	}




	/**
	 *
	 *
	 *	@return	void
	 */
	public function print_listings_var()
	{
		$lava_script_param			= Array();
		$lava_script_param[]		= "<script type=\"text/javascript\">";
			$lava_script_param[]	= sprintf( "var ajaxurl=\"%s\";", admin_url( 'admin-ajax.php' ) );
			$lava_script_param[]	= sprintf( "var _jb_not_results=\"%s\";", __( "Not found results.", 'Lavacode' ) );
		$lava_script_param[]		= "</script>";

		echo @implode( "\n", $lava_script_param );
	}

}
new Lava_RealEstate_Manager_template;