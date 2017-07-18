jQuery( function( $ ) {

	var lava_realestate_manager_listing = function( form, output, template ) {
		this.form			= form;
		this.output			= output;
		this.template		= template;

		if( ! window.__lava_realestate_manager_listing_instance )
			this.init();
	}

	lava_realestate_manager_listing.prototype = {

		constructor : lava_realestate_manager_listing

		, init : function()
		{
			var obj = this;
			window.__lava_realestate_manager_listing_instance = 1;

			obj.filter();

			$( document )
				.on( 'filter'	, obj.filter() ).trigger( 'filter', 1 )
				.on( 'submit'	, obj.form.selector, function(e){ e.preventDefault(); $( document ).trigger( 'filter', 1 ); })
				.on( 'change'	, $( "select[data-filter]", obj.form ).selector, obj.filter_trigger() )
				.on( 'change'	, $( "input[data-filter]", obj.form ).selector, obj.filter_trigger() )
				.on( 'click'	, $( ".page-numbers", obj.output ).selector, obj.pagination() )

				/*
				.on( 'click'	, obj.output.selector + '>', obj.pagination() )
				.on( 'change'	, obj.form.selector + ' ' + 'select[data-filter]', obj.filter_trigger() )
				.on( 'change'	, obj.form.selector + ' ' + 'input[type="checkbox"][data-filter]', obj.filter_trigger() )
				.on( 'keydown'	, obj.form.selector + ' ' + '[data-filter-location]', obj.filter_trigger_input() )
				.on( 'keydown'	, obj.form.selector + ' ' + '[data-filter-keyword]', obj.filter_trigger_input() )
				*/
		}

		, filter_trigger : function()
		{
			var obj			= this;
			return function( e ) {
				e.preventDefault();
				$( document ).trigger( 'filter', 1 );
			}
		}

		, filter_trigger_input : function()
		{
			var obj			= this;
			return function( e ) {
				if( e.keyCode == 13 ) {
					$( document ).trigger( 'filter', 1 );
				}
			}
		}

		, filter : function()
		{
			var obj				= this;

			return function( e, idx )
			{
				var
					param
					,  form		= obj.form;

				$.ajaxSetup({
					beforeSend : function() {
						$( '*', obj.form ).prop( 'disabled', true ).addClass( 'disabled' );
					}
					, complete : function() {
						$( '*', obj.form ).prop( 'disabled', false ).removeClass( 'disabled' );
					}
				});

				$( "[name='paged']", form ).val( idx );
				params			= form.serialize();

				$.getJSON(
					ajaxurl
					, params
					, function( json )
					{
						var output = '';
						if( json.data ) {
							$.each( json.data, function( i, data ) {
								output += obj.replace( data );
							} );
						}

						if( '' == json.data )
							output = _jb_not_results

						obj.output.html( output );
						obj.output.append( json.pagination );

					}
				)
				.fail( function( xhr ){
					console.log( xhr.responseText );
				});
			}
		}

		, replace : function( data )
		{
			var
				obj			= this
				, thumbnail	= ''
				, str		= obj.template.html() || '';

			if( data.thumbnail )
				thumbnail	= "background-image:url('" + data.thumbnail + "');";

			data.meta		= data.meta || {};
			data.term		= data.term || {};

			str = str.replace( /{post_id}/g				, data.post_id || 0 );
			str = str.replace( /{post-title}/g			, data.post_title || '' );
			str = str.replace( /{permalink}/g			, data.permalink || '' );
			str = str.replace( /{author-name}/g			, data.author_name || '' );
			str = str.replace( /{thumbnail}/g			, thumbnail || '' );

			str = str.replace( /{property_type}/g		, data.term.property_type || '' );
			str = str.replace( /{property_status}/g		, data.term.property_status || '' );
			str = str.replace( /{property_city}/g		, data.term.property_city || '' );

			str = str.replace( /{posted-date}/g			, data.post_date || '' );

			str = str.replace( /{bedrooms}/g			, data.meta._bedrooms || 0 );
			str = str.replace( /{bathrooms}/g			, data.meta._bathrooms || 0 );
			str = str.replace( /{garages}/g				, data.meta._garages || 0 );

			str = str.replace( /{price}/g				, data.meta._price || 'Not set' );
			str = str.replace( /{currency}/g			, data.meta._price_prefix || '' );

			str = str.replace( /{area}/g				, data.meta._area || 'Not set' );
			str = str.replace( /{unit}/g				, data.meta._area_prefix || '' );
			return str;
		}

		, pagination : function()
		{
			var obj		= this;

			return function( e )
			{
				e.preventDefault();

				var page_number	= $( this ).attr( 'href' ).toString().split( '|' );

				$( document ).trigger( 'filter', page_number[1] );

			}
		}



	}
	new lava_realestate_manager_listing(
		$( "#lava-realstate-manager-listing" )
		, $( "#lava-realstate-manager-output" )
		, $( "#lava-realstate-manager-listing-template" )
	);


} );
