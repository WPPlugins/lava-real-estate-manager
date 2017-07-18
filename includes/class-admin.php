<?php

class Lava_RealEstate_Manager_Admin extends Lava_RealEstate_Manager_Func
{
	const __OPTION_GROUP__					= 'lava_realestate_manager_group';

	private $admin_dir;

	public $featured_term							= false;

	public $options;

	private static $form_loaded					= false;
	private static $is_wpml_actived;
	private static $item_refresh_message;


	public function __construct()
	{
		$this->admin_dir									= trailingslashit( dirname( __FILE__ ) . '/admin' );
		$this->post_type									= self::SLUG;
		$this->featured_term							= lava_realestate()->core->featured_term;

		$this->options = get_option( $this->getOptionFieldName() );

		self::$is_wpml_actived							= function_exists( 'icl_object_id' );

		// Admin Initialize
		add_action( 'admin_init'													, Array( $this, 'register_options' ) );
		add_action( 'admin_menu'											, Array( $this, 'register_setting_page' ) );
		add_action( 'admin_footer'											, Array( $this, 'admin_form_scripts' ) );
		add_action( 'save_post'												, Array( $this, 'save_post' ) );
		add_action( 'add_meta_boxes'										, Array( $this, 'reigster_meta_box' ), 0 );
		add_action( 'admin_enqueue_scripts'							, Array( $this, 'load_admin_page' ) );

		add_filter( "lava_{$this->post_type}_json_addition"		, Array( $this, 'json_addition' ), 10, 3 );
		add_filter( "lava_{$this->post_type}_categories"			, Array( $this, 'json_categories' ) );
		add_filter( 'lava_realestate_listing_featured_no_image'	, Array( $this, 'noimage' ) );
		add_filter( "lava_{$this->post_type}_login_url"				, Array( $this, 'login_url' ) );

		// Custom Category Marker
		add_action( 'admin_enqueue_scripts'								, Array($this, "admin_enqueue_callback"));
		add_action( "{$this->featured_term}_edit_form_fields"	, Array($this,'edit_featured_term'), 10, 2);
		add_action( "{$this->featured_term}_add_form_fields"	, Array($this, 'add_featured_term'));
		add_action( "created_{$this->featured_term}"				, Array($this, 'save_featured_term'), 10, 2);
		add_action( "edited_{$this->featured_term}"					, Array($this, 'save_featured_term'), 10, 2);
		add_action( 'deleted_term_taxonomy'								, Array($this, 'remove_featured_term'));
		add_action( 'lava_file_script'											, Array($this, 'lava_file_script_callback'));
		add_filter( "manage_edit-{$this->featured_term}_columns" , Array($this, 'featured_term_columns'));
		add_filter( "manage_{$this->featured_term}_custom_column" , Array($this, 'manage_featured_term_columns'), 10, 3);


		require_once 'functions-admin.php';

		if( isset( $_POST[ 'lava_' . self::SLUG . '_refresh' ] ) )
			self::item_refresh();
	}

	public function load_admin_page() {
		wp_enqueue_script( 'gmap-v3' );
	}

	public function reigster_meta_box()
	{
		foreach(
			Array( 'postexcerpt', 'commentstatusdiv', 'commentsdiv')
			as $keyMetaBox
		) remove_meta_box( $keyMetaBox, self::SLUG, 'normal' );

		add_meta_box(
			'lava_realestate_manager_metas'
			, __( "Property Additional Meta", 'Lavacode' )
			, Array( $this, 'lava_realestate_manager_addition_meta' )
			, self::SLUG
			, 'advanced'
			, 'high'
		);

		/*
		add_meta_box(
			'lava_realestate_manager_map_metas'
			, __( "Map settings", 'Lavacode' )
			, Array( $this, 'lava_realestate_manager_map_meta' )
			, 'page'
			, 'side'
			, 'high'
		);
		*/

	}

	public function lava_realestate_manager_addition_meta( $post )
	{
		global $post;

		self::$form_loaded		= 1;

		foreach(
			Array( 'lat', 'lng', 'street_lat', 'street_lng', 'street_heading', 'street_pitch', 'street_zoom', 'street_visible' )
			as $key
		) $post->$key	= floatVal( get_post_meta( $post->ID, 'lv_item_' . $key, true ) );

		$lava_property_fields	= apply_filters( "lava_{$this->post_type}_more_meta", Array() );

		ob_start();
			do_action( "lava_{$this->post_type}_admin_metabox_before" , $post );
			require_once dirname( __FILE__) . '/admin/admin-metabox.php';
			do_action( "lava_{$this->post_type}_admin_metabox_after" , $post );
		ob_end_flush();
	}

	public function lava_realestate_manager_map_meta( $post )
	{
		global $post;

		ob_start();
			do_action( "lava_{$this->post_type}_admin_map_meta_before" , $post );
			require_once dirname( __FILE__) . '/admin/admin-mapmeta.php';
			do_action( "lava_{$this->post_type}_admin_map_meta_after" , $post );
		ob_end_flush();
	}

	public function admin_form_scripts()
	{
		if( ! self::$form_loaded )
			return;

		$output_variable	= Array();
		$output_variable[]	= "<script type=\"text/javascript\">";
		$output_variable[]	= sprintf( "var %s=\"%s\";", 'fail_find_address', __( "You are not the author.", 'Lavacode' ) );
		$output_variable[]	= "</script>";

		echo @implode( "\n", $output_variable ); ?>
		<script type="text/javascript">

		jQuery( function( $ ){

			var lava_realestate_form_script = function() { if( ! this.loaded ) this.init(); }

			lava_realestate_form_script.prototype = {

				constructor : lava_realestate_form_script

				, init : function()
				{
					var obj			= this;

					obj.loaded		= 1;

					$( window )
						.on( 'load'	, obj.item_meta() )

					$( document )
						.on( 'keyup', '[name="lava_pt[map][lat]"], [name="lava_pt[map][lng]"]', obj.type_latLng() )
						.on( 'click', '[name="lava_pt[map][street_visible]"]', obj.toggle_streetview() )

				}

				, item_meta: function()
				{
					var obj			= this;

					return function( e, undef )
					{

						e.preventDefault();

						obj.el			= $(".lava-item-map-container");
						obj.st_el		= $(".lava-item-streetview-container");
						obj.streetView = $("[name='lava_pt[map][street_visible]']").is(":checked");

						obj.st_el.height(350);

						// This Item get Location
						obj.latLng = $("input[name='lava_pt[map][lat]']").val() != "" && $("input[name='lava_pt[map][lng]']").val() != "" ?
							new google.maps.LatLng($("input[name='lava_pt[map][lat]']").val(), $("input[name='lava_pt[map][lng]']").val()) :
							new google.maps.LatLng( 40.7143528, -74.0059731 );

						// Initialize Map Options
						obj.map_options = {
							map:{ options:{ zoom:10, center: obj.latLng } }
							, marker:{
								latLng		: obj.latLng
								, options:{
									draggable	: true
								}
								, events:{
									position_changed: function( m )
									{
										$('input[name="lava_pt[map][lat]"]').val( m.getPosition().lat() );
										$('input[name="lava_pt[map][lng]"]').val( m.getPosition().lng() );
										$('input[name="lava_pt[map][street_lat]"]').val( m.getPosition().lat() );
										$('input[name="lava_pt[map][street_lng]"]').val( m.getPosition().lng() );

										if( $("[name='lava_pt[map][street_visible]']").is(":checked") )
										{

											$(this).gmap3({
												get:{
													name:'streetviewpanorama'
													, callback: function( streetView )
													{
														if( typeof streetView != 'undefined' )
														{
															streetView.setPosition( m.getPosition() );
															streetView.setVisible();
														}
													}
												}
											});
										}
									}

								}
							}, streetviewpanorama:{
								options:{
									container				: obj.st_el.get(0)
									, opts:{
										position			: new google.maps.LatLng(
											$('[name="lava_pt[map][street_lat]"]').val()
											, $('[name="lava_pt[map][street_lng]"]').val()
										)
										, pov				: {
											heading			: parseFloat( $('[name="lava_pt[map][street_heading]"]').val() )
											, pitch			: parseFloat( $('[name="lava_pt[map][street_pitch]"]').val() )
											, zoom			: parseFloat( $('[name="lava_pt[map][street_zoom]"]').val() )
										}
										, addressControl	: false
										, clickToGo			: true
										, panControl		: true
										, linksControl		: true
									}
								}
								, events:{
									pov_changed:function( pano ){
										$('[name="lava_pt[map][street_heading]"]').val( parseFloat( pano.pov.heading ) );
										$('[name="lava_pt[map][street_pitch]"]').val( parseFloat( pano.pov.pitch ) );
										$('[name="lava_pt[map][street_zoom]"]').val( parseFloat( pano.pov.zoom ) );
									}
									, position_changed: function( pano ){
										$('[name="lava_pt[map][street_lat]"]').val( parseFloat( pano.getPosition().lat() ) );
										$('[name="lava_pt[map][street_lng]"]').val( parseFloat(  pano.getPosition().lng() ) );
									}
								}
							}
						}

						obj.el.css("height", 300).gmap3( obj.map_options );
						obj.map = obj.el.gmap3('get');

						if( !obj.streetView && obj.el.length > 0 ){
							obj.map.getStreetView().setVisible( false );
						}

						$( document )
							.on("click", ".lava_pt_detail_del", function(){
								var t = $(this);
								t.parents(".lava_pt_field").remove();
							})
							.on("click", ".lava_pt_detail_add", function(e){
								e.preventDefault();
								var
									attachment
									, output_image
									, file_frame
									, t = $(this)

								if(file_frame){ file_frame.open(); return; }
								file_frame = wp.media.frames.file_frame = wp.media({
									title: jQuery( this ).data( 'uploader_title' ),
									button: {
										text: jQuery( this ).data( 'uploader_button_text' ),
									},
									multiple: false
								});
								file_frame.on( 'select', function(){
									var str ="";
									attachment			= file_frame.state().get('selection').first().toJSON();
									output_image		= attachment.url;

									if( attachment.sizes.thumbnail !== undef )
										output_image	= attachment.sizes.thumbnail.url;

									str += "<div class='lava_pt_field' style='float:left;'>";
									str += "<img src='" + output_image + "'> <div align='center'>";
									str += "<input name='lava_attach[]' value='" + attachment.id + "' type='hidden'>";
									str += "<input class='lava_pt_detail_del button' type='button' value='Delete'>";
									str += "</div></div>";
									t.parents("td").find(".lava_pt_images").append(str);
								});
								file_frame.open();
							})
							.on("keyup keypress", ".lava_txt_find_address", function ( e ){

								var keyCode		= e.keyCode || e.which;

								if(e.keyCode == 13){
									e.preventDefault();
									$(".lava_btn_find_address").trigger("click");
									return false;
								}

							})
							.on("click", ".lava_btn_find_address", function(){

								var _addr = $(".lava_txt_find_address").val();
								$(".lava-item-map-container").gmap3({
									getlatlng:{
										address:_addr,
										callback:function(r){
											if(!r){
												alert( fail_find_address );
												return false;
											}
											var _find = r[0].geometry.location;
											$("input[name='lava_pt[map][lat]']").val(_find.lat());
											$("input[name='lava_pt[map][lng]']").val(_find.lng());
											$(".lava-item-map-container").gmap3({
												get:{
													name:"marker",
													callback:function(m){
														m.setPosition(_find);
														$(".lava-item-map-container").gmap3({map:{options:{center:_find}}});
													}
												}
											});
										}
									}
								});
							});
					}
				}

				, toggle_streetview: function()
				{
					var obj		= this;

					return function()
					{
						if( $(this).is(":checked") )
						{
							obj.st_el.removeClass('hidden');
							obj.map.getStreetView().setVisible( true );
						}else{
							obj.st_el.addClass('hidden');
							obj.map.getStreetView().setVisible( false );
						}
					}
				}

				, type_latLng: function()
				{
					var obj				= this;
					return function( e )
					{
						e.preventDefault();

						var _this		= this;

						this.lat		= parseFloat( $('[name="lava_pt[map][lat]"]').val() );
						this.lng		= parseFloat( $('[name="lava_pt[map][lng]"]').val() );

						if( isNaN( this.lat ) || isNaN( this.lng ) ){ return; }

						this.latLng		= new google.maps.LatLng( this.lat, this.lng );

						obj.el.gmap3({
							get:{
								name: "marker"
								, callback: function( marker )
								{
									if( typeof window.nTimeID != "undefiend" ){
										clearInterval( window.nTimeID );
									};
									window.nTimeID = setInterval( function(){
										marker.setPosition( _this.latLng );
										obj.el.gmap3('get').setCenter( _this.latLng );
										clearInterval( window.nTimeID );
									}, 1000 );
								}
							}
						});
					}
				}
			}

			new lava_realestate_form_script;
		} );
		</script>
		<?php
	}

	public function save_post( $post_id )
	{
		$lava_query		= new lava_Array( $_POST );
		$lava_PT		= new lava_Array( $lava_query->get( 'lava_pt', Array() ) );
		$lava_mapMETA	= $lava_query->get( 'lava_map_param' );
		$lava_moreMETA	= $lava_query->get( 'lava_additem_meta' );

		// More informations
		if( !empty( $lava_moreMETA ) ) : foreach( $lava_moreMETA as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		} endif;

		// Map informations
		if( !empty( $lava_mapMETA ) ) : foreach( $lava_mapMETA as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		} endif;

		// More detail picture or image ids meta
		update_post_meta( $post_id, 'detail_images', $lava_query->get( 'lava_attach' ) );

		// Google Map position meta
		if( false !== (boolean)( $meta = $lava_PT->get( 'map', false ) ) ) {
			foreach( $meta as $key => $value ) {
				update_post_meta( $post_id, "lv_item_{$key}", $value );
			}
		}

		// Featured item meta
		update_post_meta( $post_id, '_featured_item', $lava_PT->get( 'featured', 0 ) );

		// Upldate Json
		do_action( "lava_{$this->post_type}_json_update", $post_id, get_post( $post_id ), null );

	}

	public function register_options() {
		register_setting( self::__OPTION_GROUP__ , $this->getOptionFieldName() );
	}

	public function getOptionFieldName( $option_name=false ){    // option field name

		$strFieldName = 'lava_realestate_manager_settings';

		if( $option_name )
			$strFieldName = sprintf( '%1$s[%2$s]', $strFieldName, $option_name );

		return $strFieldName;
	}

	public function getOptionsPagesLists( $default )
	{
		$pages_output = Array();

		if( ! $pages = get_posts( Array( 'post_type' => 'page', 'posts_per_page' => -1 ) ) )
			return false;

		foreach( $pages as $page )
		{
			$pages_output[]	= "<option value=\"{$page->ID}\"";
			$pages_output[]	= selected( $default == $page->ID, true, false );
			$pages_output[]	= ">{$page->post_title}</option>";
		}

		return @implode( false, $pages_output );
	}

	public function register_setting_page()
	{
		add_submenu_page(
			'edit.php?post_type=' . self::SLUG
			, __( "Lava Real Estate Manager Settings", 'Lavacode' )
			, __( "Settings", 'Lavacode' )
			, 'manage_options'
			, 'lava-' . self::SLUG . '-settings'
			, Array( $this, 'admin_page_template' )
		);
		/*
		add_submenu_page(
			'edit.php?post_type=' . self::SLUG
			, __( "Welcome Lava Real Estate Manager", 'Lavacode' )
			, __( "Lava Real Estate Manager Information", 'Lavacode' )
			, 'manage_options'
			, 'lava-' . self::SLUG . '-welcome'
			, Array( &$this, 'admin_welcome_template')
		);
		*/
	}

	public function admin_page_template()
	{
		$arrTabs_args		= Array(
			''				=>	Array(
				'label'		=> __( "index", 'Lavocode' )
				, 'group'	=> self::__OPTION_GROUP__
				, 'file'	=> $this->admin_dir . 'admin-index.php'
			)
		);

		$arrTabs		= apply_filters( "lava_{$this->post_type}_admin_tab", $arrTabs_args );

		echo self::$item_refresh_message;
		echo "<div class=\"wrap\">";
			printf( "<h2>%s</h2>", __( "Lava Real Estate Manager Settings", 'Lavacode' ) );
			echo "<form method=\"post\" action=\"options.php\">";
			echo "<h2 class=\"nav-tab-wrapper\">";
			$strCurrentPage	= isset( $_GET[ 'index' ] ) && $_GET[ 'index' ] != '' ? $_GET[ 'index' ] : '';
			if( !empty( $arrTabs ) ) : foreach( $arrTabs as $key => $meta ) {
					printf(
						"<a href=\"%s\" class=\"nav-tab %s\">%s</a>"
						, esc_url(
								add_query_arg(
									Array(
										'post_type' => self::SLUG
										, 'page' => 'lava-' . self::SLUG . '-settings'
										, 'index' => $key
									)
									, admin_url( 'edit.php' )
								)
							)
						, ( $strCurrentPage == $key ? 'nav-tab-active' : '' )
						, $meta[ 'label' ]
					);

				}
				echo "</h2>";
				if( $strTabMeta = $arrTabs[ $strCurrentPage ] ) {
					settings_fields( $strTabMeta[ 'group' ] );
					if( file_exists( $strTabMeta[ 'file' ] ) )
						require_once $strTabMeta[ 'file' ];
				}
			endif;

			if( apply_filters( "lava_{$this->post_type}_admin_save_button", true ) )
				printf( "<button type=\"\" class=\"button button-primary\">%s</button>", __( "Save", 'Lavacode' ) );

			echo "</form>";
			echo "<form id=\"lava_common_item_refresh\" method=\"post\">";
			wp_nonce_field( "lava_{$this->post_type}_items", "lava_{$this->post_type}_refresh" );
			echo "<input type=\"hidden\" name=\"lang\">";
			echo "</form>";
		echo "</div>";
		wp_enqueue_media();
		add_action( 'admin_footer'			, Array( $this, 'script_json' ) );
	}

	public function hide_saveButton( $showButton ){
		return false;
	}

	public function admin_welcome_template()
	{
		if( file_exists( $this->admin_dir . 'admin-welcome.php' ) )
			require_once $this->admin_dir . 'admin-welcome.php';
	}

	public function script_json()
	{
		?>
		<script type="text/javascript">
		jQuery( function($) {

			var lava_realestate_admin_settings = function () {

				this.init();
			}

			lava_realestate_admin_settings.prototype = {

				constructor : lava_realestate_admin_settings

				, init : function()
				{
					$( document )
						.on( 'click', '.lava-data-refresh-trigger'		, this.onGenerator() )
						.on( 'click', '.fileupload'					, this.image_upload() )
						.on( 'click', '.fileuploadcancel'			, this.image_remove() )

				}

				, onGenerator : function ()
				{
					var obj		= this;
					return function ( e )
					{
						var
							$this			= $( this )
							, frm			= $( document ).find( "form#lava_common_item_refresh" )
							, loading		= '<span class="spinner" style="display: block; float:left;"></span>'
							, strLoading	= $( this ).data( 'loading' ) || "Processing"
							, parent		= $( this ).parent();

						parent.html( loading + ' ' + strLoading );
						frm.find( "[name='lang']" ).val( $( this ).data( 'lang' ) );
						frm.submit();
					}
				}

				, image_upload : function ()
				{
					var file_frame;

					return function ( e, undef )
					{
						e.preventDefault();

						var
							attahment
							, output_image
							, t				= $( this ).attr( 'tar' )
							, bxTitle		= $( this ).data( 'uploader_title' ) || "Upload"
							, bxOK			= $( this ).data( 'uploader_button_text' ) || "Apply"
							, bxMultiple	= false;

						if( file_frame ){
							file_frame.open();
							return;
						}

						file_frame = wp.media.frames.file_frame = wp.media({
							title				: bxTitle
							, button			: { text : bxOK }
							, multiple			: bxMultiple
						});

						file_frame.on( 'select', function(){
							attachment			= file_frame.state().get('selection').first().toJSON();
							output_image		= attachment.url;

							if( attachment.sizes.thumbnail !== undef )
								output_image	= attachment.sizes.thumbnail.url;

							$("input[type='text'][tar='" + t + "']").val(attachment.url);
							$("img[tar='" + t + "']").prop("src", output_image );
						});

						file_frame.open();
					}
				}

				,image_remove : function ()
				{
					return function ( e )
					{
						var t = $(this).attr("tar");
						$("input[type='text'][tar='" + t + "']").val("");
						$("img[tar='" + t + "']").prop("src", "");
					}
				}
			}

			new lava_realestate_admin_settings;

		} );
		</script>
		<?php
	}


	public function json_categories( $args )
	{
		global $lava_realestate_manager_func;

		$lava_exclude					= Array();

		$lava_taxonomies				= $lava_realestate_manager_func->lava_extend_item_taxonomies();

		if( empty( $lava_taxonomies ) || !is_Array( $lava_taxonomies ) )
			return $args;

		if( !empty( $lava_exclude ) ) : foreach( $lava_exclude as $terms ) {
			if( in_Array( $terms, Array_keys( $lava_taxonomies ) ) )
				unset( $lava_taxonomies[ $terms] );
		} endif;

		return wp_parse_args( Array_Keys( $lava_taxonomies ), $args );
	}

	public function json_addition( $args, $post_id, $tax )
	{
		$lava_exclude		= Array( $this->post_type . '_keyword' );
		$lava_taxonomies	= $this->json_categories( Array() );

		if( !empty( $lava_taxonomies ) ) : foreach( $lava_taxonomies as $term ) {
			$args[ $term ]	= $tax->get( $term );
		} endif;

		if( !empty( $lava_exclude ) ) : foreach( $lava_exclude as $terms ) {
			if( in_Array( $terms, Array_keys( $args ) ) )
				unset( $args[ $terms] );
		} endif;

		return $args;
	}

	public static function item_refresh()
	{
		global $wpdb;
		if( empty( $_POST ) || !check_admin_referer( 'lava_' . self::SLUG . '_items', 'lava_' . self::SLUG . '_refresh' ) )
			return;

		$lava_query	= new lava_array( $_POST );

		$lang		= $lava_query->get('lang', '');

		$lava_this_response		= Array();

		/* wpml */
		{
			$wpml_join			= "";
			$wpml_where			= "";
			$wpml_req_language	= "";
			if( self::$is_wpml_actived && $lang != '' )
			{
				if(
					function_exists( 'icl_get_languages' ) &&
					false !== (bool)( $lava_wpml_langs = icl_get_languages( 'skip_missing=0' ) )
				){
					if( !empty( $lava_wpml_langs[ $lang ][ 'translated_name' ] ) ) {
						$wpml_req_language = $lava_wpml_langs[ $lang ][ 'translated_name' ];
					}
				}

				$wpml_join	= "INNER JOIN {$wpdb->prefix}icl_translations as w ON p.ID = w.element_id";
				$wpml_where	= $wpdb->prepare( "AND w.language_code=%s" , $lang);
			}
		}

		// wpml > Multilingual Content Setup > custom post > select use posts
		$lava_refresh_items = apply_filters( 'lava_json_to_use_types', Array( self::SLUG ) );

		$lava_all_posts = Array();
		$lava_all_items = $wpdb->get_results(
			$wpdb->prepare("SELECT DISTINCT ID, post_title FROM $wpdb->posts as p {$wpml_join} WHERE p.post_type=%s AND p.post_status=%s {$wpml_where} ORDER BY p.post_date ASC"
				, self::SLUG, 'publish'
			)
			, OBJECT
		);

		foreach( $lava_all_items as $item )
		{
			// Google Map LatLng Values
			$latlng = Array(
				'lat'			=> get_post_meta( $item->ID, 'lv_item_lat', true )
				, 'lng'			=> get_post_meta( $item->ID, 'lv_item_lng', true )
			);

			$category			= Array();
			$category_label		= Array();

			/* Taxonomies */
			{

				$lava_all_taxonomies					= apply_filters( 'lava_' . self::SLUG . '_categories', Array() );

				foreach( $lava_all_taxonomies as $taxonomy )
				{

					$results = $wpdb->get_results(
						$wpdb->prepare("
							SELECT t.term_id, t.name FROM $wpdb->terms AS t
							INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id
							INNER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
							WHERE tt.taxonomy IN (%s) AND tr.object_id IN ($item->ID)
							ORDER BY t.name ASC"
							, $taxonomy
						)
					);
					//$category[ $taxonomy ] = $results;
					foreach( $results as $result )
					{
						$category[ $taxonomy ][]		= $result->term_id;
						$category_label[ $taxonomy ][]	= $result->name;
					}
				}
			}

			$lava_categories			= new lava_ARRAY( $category );
			$lava_categories_label	= new lava_ARRAY( $category_label );

			/* Marker Icon */ {
				$arrCategories = $lava_categories->get( 'property_status', Array() );
				$category_icon			= reset	( $arrCategories );
				$lava_set_icon			= get_option( "lava_property_status_{$category_icon}_marker", '' );
			}

			if( !empty( $latlng['lat'] ) && !empty( $latlng['lng'] ) )
			{
				$lava_all_posts_args	= Array(
					'post_id'			=> $item->ID
					, 'post_title'		=> $item->post_title
					, 'lat'				=> $latlng['lat']
					, 'lng'				=> $latlng['lng']
					, 'icon'			=> $lava_set_icon
					, 'tags'			=> $lava_categories_label->get( self::SLUG . '_keyword' )
				);
				$lava_all_posts[]		= apply_filters( 'lava_' . self::SLUG . '_json_addition', $lava_all_posts_args, $item->ID, $lava_categories );
			}
		}

		$upload_folder					= wp_upload_dir();
		$blog_id							= get_current_blog_id();
		$lava_item_type				= self::SLUG;

		$json_file = "{$upload_folder['basedir']}/lava_all_{$lava_item_type}_{$blog_id}_{$lang}.json";

		$file_handle = @fopen( $json_file, 'w' );
		@fwrite( $file_handle, json_encode( $lava_all_posts ) );
		@fclose( $file_handle );

		ob_start();
		?>
		<div class="updated">
			<?php
			if( '' !== $wpml_req_language ) {
				echo "<h3>{$wpml_req_language}: </h3>";
			} ?>
			<strong><?php _e( "Successfully Generated!", 'Lavacode' ); ?></strong>
			<u><?php echo $json_file; ?></u>
		</div>
		<?php
		self::$item_refresh_message = ob_get_clean();
	}

	public function get_settings( $option_key, $default=false )
	{
		if( array_key_exists( $option_key, (Array) $this->options ) )
			if( $value = $this->options[ $option_key ] )
				$default = $value;
		return $default;
	}

	public function noimage( $image_url ) {
		if( $noimage = $this->get_settings( 'blank_image' ) )
			return $noimage;
		return $image_url;
	}

	public function login_url( $login_url ) {
		if( $redirect = $this->get_settings( 'login_page' ) )
			return get_permalink( $redirect );
		return $login_url;
	}

	public function admin_enqueue_callback(){
		if ( function_exists('wp_enqueue_media') ) {
			wp_enqueue_media();
		}
	}
	public function edit_featured_term($tag, $taxonomy) {
		$lava_marker = get_option( 'lava_property_status_'.$tag->term_id.'_marker', '' );
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="lava_property_status_marker"><?php _e('Map Marker', 'Lavacode');?></label>
			</th>
			<td>
				<input type="text" name="lava_property_status_marker" id="lava_property_status_marker" value="<?php echo $lava_marker; ?>">
				<button class="fileupload" data-target='#lava_property_status_marker'><?php _e('Change', 'Lavacode');?></button>
				<p class="description"><?php _e( "Category markers : you need to refresh map data after you upload or change pin (map marker). Theme settigns >> Maps", 'Lavacode');?></p>
			</td>
		</tr>
		<?php
		do_action('lava_file_script');
	}

	public function add_featured_term($tag) {
		?>
		<div class="form-field">
			<label for="lava_property_status_marker"><?php _e('Map Marker', 'Lavacode');?></label>
			<input type="text" name="lava_property_status_marker" id="lava_property_status_marker" value="" style="width: 80%;"/>
			<button class="fileupload" data-target='#lava_property_status_marker'><?php _e('Upload', 'Lavacode');?></button>
			<p class="description"><?php _e( "Category markers : you need to refresh map data after you upload or change pin (map marker). Theme settigns >> Maps", 'Lavacode');?></p>
		</div>
		<?php
		do_action('lava_file_script');
	}

	public function save_featured_term($term_id, $tt_id) {
		if (!$term_id) return;

		if (isset($_POST['lava_property_status_marker'])){
			$name = 'lava_property_status_' .$term_id. '_marker';
			update_option( $name, $_POST['lava_property_status_marker'] );
		}
	}

	public function remove_featured_term($id) {
		delete_option( 'lava_property_status_'.$id.'_marker' );
	}

	public function featured_term_columns($category_columns) {
		$new_columns = array(
			'cb'        		=> '<input type="checkbox">'
			, 'name'      		=> __('Name', 'Lavacode')
			, 'description'    	=> __('Description', 'Lavacode')
			, 'marker'			=> __('Marker Preview', 'Lavacode')
			, 'slug'      		=> __('Slug', 'Lavacode')
			, 'posts'     		=> __('Items', 'Lavacode')
			);
		return $new_columns;
	}

	public function manage_featured_term_columns($out, $column_name, $cat_id){

		$marker = get_option( 'lava_property_status_'.$cat_id.'_marker', '' );
		switch ($column_name) {
			case 'marker':
				if(!empty($marker)){
					$out .= '<img src="'.$marker.'" style="max-width:100%;" alt="">';
				}
			break;
		};
		return $out;
	}

	public function lava_file_script_callback(){
		?>
		<script type="text/javascript">
		jQuery(function($){
			"use strict";

			; $( document )
				.on("click", ".fileupload", function(e){
					e.preventDefault();

					// Variable
					var file_frame, attachment, t = $(this).data("target");
					if( file_frame ){
						file_frame.open();
						return;
					}
					file_frame = wp.media.frames.file_frame = wp.media({
						title		: "<?php _e('Select Category Featured Image', 'Lavacode');?>"
						, multiple	: false
						, button	: {
							text: "<?php _e('Apply', 'Lavacode');?>"
						}
					});
					file_frame.on( 'select', function(){
						attachment = file_frame.state().get('selection').first().toJSON();
						$(t).val(attachment.url);
						$( $( e.target ).data('featured-field') ).prop( 'value',  attachment.id );

						$( e.target ).closest('.form-field').find('img').prop( 'src', attachment.url );

					});

					file_frame.open();

					// Upload field reset button
				})
				.on("click", ".fileuploadcancel", function(){
					var t = $(this).attr("tar");
					$("input[type='text'][tar='" + t + "']").val("");
					$("img[tar='" + t + "']").prop("src", "");
				})
		});
		</script>
		<?php
	}
}