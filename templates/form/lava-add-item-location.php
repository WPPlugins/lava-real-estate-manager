<h5><?php _e('Map', 'Lavacode');?></h5>

<div class="form-inner">
	<label><?php _e( "Address Search", 'Lavacode' ); ?></label>
	<div class="form-content">
		<input class="form-control lava-add-item-map-search" placeholder="<?php _e("Address","Lavacode");?>">
		<input type="button" value="<?php _e('Find','Lavacode'); ?>" class="lava-add-item-map-search-find">
	</div>
</div>

<div class="map_area"></div>
<input type="hidden" name="lava_location[country]" value="<?php echo $edit->country; ?>" readonly>
<input type="hidden" name="lava_location[locality]" value="<?php echo $edit->locality; ?>" readonly>
<input type="hidden" name="lava_location[address]" value="<?php echo $edit->address; ?>" readonly>

<div class="form-inner">
	<label><?php _e( "Latitude", 'Lavacode' ); ?></label>
	<input type="text" name="lava_location[lat]" class="only-number form-control" value="<?php echo $edit->lat; ?>" readonly>
</div>

<div class="form-inner">
	<label><?php _e( "Longitude", 'Lavacode' ); ?></label>
	<input type="text" name="lava_location[lng]" class="only-number form-control" value="<?php echo $edit->lng; ?>" readonly>
</div>

<div class="form-inner">
	<label><?php _e( "Used StreetView", 'Lavacode' ); ?></label>
	<input type="hidden" name="lava_location[street_visible]" value="0">
	<input type="checkbox" name="lava_location[street_visible]" class='lava-add-item-set-streetview' value="1" <?php checked( $edit->street_visible == 1 ); ?>>
</div>

<div class="form-inner lava_map_advenced hidden">

	<h5><?php _e('StreetView', 'Lavacode');?></h5>

	<div class="form-inner">
		<label><?php _e( "Latitude", 'Lavacode' ); ?></label>
		<input type="text" name="lava_location[street_lat]" value="<?php echo $edit->street_lat; ?>" class="form-control">
	</div>

	<div class="form-inner">
		<label><?php _e( "Longitude", 'Lavacode' ); ?></label>
		<input type="text" name="lava_location[street_lng]" value="<?php echo $edit->street_lng; ?>" class="form-control">
	</div>

	<div class="form-inner">
		<label><?php _e( "Heading", 'Lavacode' ); ?></label>
		<input type="text" name="lava_location[street_heading]" value="<?php echo $edit->street_heading; ?>" class="form-control">
	</div>

	<div class="form-inner">
		<label><?php _e( "Pitch", 'Lavacode' ); ?></label>
		<input type="text" name="lava_location[street_pitch]" value="<?php echo $edit->street_pitch; ?>" class="form-control">
	</div>

	<div class="form-inner">
		<label><?php _e( "Zoom", 'Lavacode' ); ?></label>
		<input type="text" name="lava_location[street_zoom]" value="<?php echo $edit->street_zoom; ?>" class="form-control">
	</div>

</div>
<script type="text/javascript">

jQuery(function($){
	"use strict";

	var is_drag		= false;
	window.lava_add_item_func = {

		allow_transmission: false

		, options:{

			map_container:{
				map:{
					latLng		: new google.maps.LatLng(40.7143528, -74.0059731)
					, options	: {
						zoom				: 8
						, mapTypeControl	: false
						, panControl		: false
						, scrollwheel		: true
						, streetViewControl	: true
						, zoomControl		: true
					}
					, events	: {
						click:function(m, l){
							$(this)
								.gmap3({
									get:{
										name:"marker"
										, callback:function(m){
											m.setPosition( l.latLng );
										}
									}
								});
						}
					}
				}
				, marker:{
					latLng: new google.maps.LatLng(40.7143528, -74.0059731)
					, options:{ draggable:true }
					, events:{
						dragstart			: function(){ is_drag = true; }
						, dragend			: function(){ is_drag = false; }
						, position_changed	: function( m )
						{
							var
								country			= $( "[name='lava_location[country]']" )
								, locality		= $( "[name='lava_location[locality]']" )
								, address		= $( "[name='lava_location[address]']" );

							$('input[name="lava_location[lat]"]').val( m.getPosition().lat() );
							$('input[name="lava_location[lng]"]').val( m.getPosition().lng() );
							$('input[name="lava_location[street_lat]"]').val( m.getPosition().lat() );
							$('input[name="lava_location[street_lng]"]').val( m.getPosition().lng() );

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
								, getaddress		: {
									latLng		: m.getPosition()
									, callback	: function( response ) {

										if( ! response || is_drag )
											return;

										// Full address strings
										address.val( response[0].formatted_address );

										// Country, locality strings
										$.each( response[0].address_components, function( iResult, arrAddress ) {

											if( arrAddress.types[0] === 'country' ) {
												country.val( arrAddress.long_name );
											}

											if( arrAddress.types[0] === 'locality' ) {
												locality.val( arrAddress.long_name );
											}

										} );
									}
								}
							});
						}
					}
				}
			}
		}

		, init:function()
		{

			;$(document)

				// Allow Field
				.on('keydown', 'input, textarea', this.allow_field )

				// Only Number
				.on('keypress keyup blur', '.only-number', this.only_number )

				// Form Submit
				.on('submit', 'form', this.transmission )

				// Keyword Search
				.on('keyup keypress', '.lava-add-item-map-search', this.trigger_geokeyword )
				.on('click', '.lava-add-item-map-search-find', this.geolocation_keyword )

				// Street View Setup
				.on( 'change', '.lava-add-item-set-streetview', this.street_setup() )
				.on( 'keyup', '[name="lava_location[lat]"], [name="lava_location[lng]"]', this.type_latLgn )

			;$(window)
				.on('beforeunload', this.block_move_page )

			;this.map_setup()

			if( $('[name="lava_location[street_visible]"]').is( ':checked' ) )
				$('.lava-add-item-set-streetview').trigger('change');
		}

		, trigger_geokeyword: function(e)
		{
			var keyCode		= e.keyCode || e.which;

			if( keyCode	== 13 ) {
				e.preventDefault();
				$('.lava-add-item-map-search-find').trigger('click');
				return false;
			}
		}

		, geolocation_keyword: function(e)
		{
			var $object = lava_add_item_func;

			e.preventDefault();

			$object.el.gmap3({
				getlatlng:{
					address: $('.lava-add-item-map-search').val()
					, callback:function(result){
						if( !result ) return;

						$(this)
							.gmap3({
								get:{
									name:"marker"
									, callback:function(marker){
										var $map = $(this).gmap3('get');
										marker.setPosition( result[0].geometry.location );
										$map.setCenter( result[0].geometry.location );
									}
								}
							});
					}
				}
			});
		}

		, map_setup: function()
		{
			var $object = this;

			if(
				$('input[name="lava_location[lat]"]').val() &&
				$('input[name="lava_location[lng]"]').val()
			){
				var thisLat = $('input[name="lava_location[lat]"]').val();
				var thisLng = $('input[name="lava_location[lng]"]').val();
				this.options.map_container.map.latLng		= new google.maps.LatLng( thisLat, thisLng );
				this.options.map_container.marker.latLng	= new google.maps.LatLng( thisLat, thisLng );
			}

			this.el = $('.map_area');

			this.el
				.height( 500 )
				.gmap3( this.options.map_container );

			this.map = this.el.gmap3('get');

			$( 'a[data-toggle="tab"]:last-child' ).on( 'shown.bs.tab', function(){
				var current_center  = $object.map.getCenter();

				$object.el.gmap3({ trigger:'resize' });
				$object.map.setCenter( current_center );
			});

			this.kw_el = $('.lava-add-item-map-search');



			var lava_ac = new google.maps.places.Autocomplete( this.kw_el.get(0) );

			google.maps.event.addListener( lava_ac, 'place_changed', function(){

				var lava_place = lava_ac.getPlace();

				if( typeof lava_place.geometry == 'undefined' ) return false;

				if( lava_place.geometry.viewport){
					$object.map.fitBounds( lava_place.geometry.viewport );
				}else{
					$object.map.setCenter( lava_place.geometry.location );
					$object.map.setZoom( 17 );
				}
				$object.el.gmap3({
					get:{
						name: 'marker'
						, callback: function( marker ){
							marker.setPosition( lava_place.geometry.location );
						}
					}
				});

			// End Event Listener
			});

		// End map_setup
		}

		, type_latLgn: function(e)
		{
			var _this		= this;
			var obj			= window.lava_add_item_func;
			this.lat		= parseFloat( $('[name="lava_location[lat]"]').val() );
			this.lng		= parseFloat( $('[name="lava_location[lng]"]').val() );

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

		, street_setup: function()
		{
			var $object			= this;
			return function (e)
			{
				if( ! $( this ).is( ':checked' ) ) {
					$('.lava_map_advenced').addClass('hidden');
					$( '.map_area_streetview' ).remove();
					return false;
				}

				// Set Container
				$object.st_el = $(document.createElement('div')).addClass('map_area_streetview').insertAfter( $(this) );

				// Use StreetView
				$('.lava_map_advenced').removeClass('hidden');

				// Set Height
				$object.st_el.height(350);

				$object.el.gmap3({
					streetviewpanorama:{
						options:{
							container: $object.st_el
							, opts:{
								position: new google.maps.LatLng(
									parseFloat( $('[name="lava_location[street_lat]"]').val() )
									, parseFloat( $('[name="lava_location[street_lng]"]').val() )
								)
								, pov:{
									heading			: parseFloat( $('[name="lava_location[street_heading]"]').val() )
									, pitch			: parseFloat( $('[name="lava_location[street_pitch]"]').val() )
									, zoom			: parseFloat( $('[name="lava_location[street_zoom]"]').val() )
								}
								, addressControl	: false
								, clickToGo			: true
								, panControl		: true
								, linksControl		: true
							}
						}
						, events:{
							pov_changed:function( pano ){
								$('[name="lava_location[street_heading]"]').val( parseFloat( pano.pov.heading ) );
								$('[name="lava_location[street_pitch]"]').val( parseFloat( pano.pov.pitch ) );
								$('[name="lava_location[street_zoom]"]').val( parseFloat( pano.pov.zoom ) );
								$( window ).trigger( 'update_location' );
							}
							, position_changed: function( pano ){
								$('[name="lava_location[street_lat]"]').val( parseFloat( pano.getPosition().lat() ) );
								$('[name="lava_location[street_lng]"]').val( parseFloat(  pano.getPosition().lng() ) );
							}
						}
					}
				});
			}

			var $object = lava_add_item_func;

		// StreetView Setup
		}

		, empty_field:function( obj, msg )
		{
			var lava_error = true;

			$(obj).each( function(){

				if( $(this).val() == "" )
				{
					$(this).addClass('isNull').focus();
					$.lava_msg({ content: msg, delay:10000 });
					lava_error = false;
				}
			} );
			return lava_error;
		}

		, allow_field		: function(e){ $(this).removeClass('isNull'); }
		, only_number		: function(e){

			$(this).val($(this).val().replace(/[^0-9\.-]/g,''));

			if(
				(e.which != 45 || $(this).val().indexOf('-') != -1) &&
				(event.which != 46 || $(this).val().indexOf('.') != -1) &&
				(event.which < 48 || event.which > 57)
			){
				event.preventDefault();
			}
		}
		, transmission		: function(e){ lava_add_item_func.allow_transmission = true; }
		, block_move_page	: function(e){ if(!lava_add_item_func.allow_transmission) return ""; }
	}
	window.lava_add_item_func.init();

});
</script>