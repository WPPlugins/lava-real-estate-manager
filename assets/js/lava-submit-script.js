;( function( $, window, undef ) {

	var lava_submit_func = function( option ) {

		var opt = $.extend( true, {}, {
			el				: null
			, form			: null
			, params		: null
			, notice		: null
			, detail_image	: false
		}, option );

		this.attr		= opt;

		this.form		= $( opt.form, opt.el );
		this.param		= $( opt.param, opt.el );
		this.notice		= $( opt.notice, opt.el );
		this.detailIMG	= opt.detail_image || {};

		if( ! this.instance )
			this.init();

	}
	lava_submit_func.prototype = {

		constructor : lava_submit_func

		, init : function ()
		{
			var obj			= this;

			obj.instnace	= 1;

			if( obj.form )
				obj.form.on( 'submit', obj.submit() );

			if( obj.detailIMG )
				$.each( obj.detailIMG, function( i, j ){ $( "#lava-detail-image-input-wrap" ).append( obj.parse_detail_images( j ) ) });


			$( document )
				.on( 'click', '.lava-additem-uploadfile'			, obj.file_upload() )
				.on( 'click', '.lava-additem-clearfile'				, obj.file_clear() )
				.on( 'click', '.lava-additem-removefile'			, obj.file_remove() )

				// Input[type='file']
				.on( 'change', '[name="lava_detail_uploader"]'		, obj.input_file_add() )
				.on( 'click', '[name="lava_detail_file_remove"]'	, obj.input_file_remove() )

			// scripts.js / numeric function
			$( "input[type='number']" ).numeric();
		}

		, message : function ( str ){

			var
				obj			= this;

			obj.notice
				.css({
					'backgroundColor'	: 'red'
					, 'color'			: '#fff'
					, padding			: '15px'
				});

			obj.notice.removeClass( 'hidden' );
			obj.notice.html( str );
			$( document ).scrollTop( 0 );
		}

		, file_clear : function()
		{
			return function (e) {
				e.preventDefault();
				$($(this).data('preview')).prop('src', '');
				$($(this).data('input')).prop('value', '');


			}
		}

		, file_remove : function()
		{
			return function (e) {
				e.preventDefault();

				var parent				= $( this ).closest( '.lava-detail-images-wrap' );
				parent.remove();
			}
		}

		, file_upload : function ()
		{
			var
				file_frame
				, output_image
				, obj		= this

			return function (e)
			{
				e.preventDefault();

				var
					$this				= $(this)
					, wndTitle			= $this.data( 'title' ) || "Upload"
					, wndMultipleUpload	= $this.data( 'multiple' ) || false
					, field				= $( $this.data( 'input' ), obj.form )
					, preview			= $( $this.data( 'preview' ), obj.form ) ;

				if( undef === wp.media ) {
					obj.message( "WP media load fail" );
					return false;
				}

				file_frame = wp.media.frames.file_frame = wp.media({ title : wndTitle, multiple : wndMultipleUpload });

				file_frame.on( 'select', function(){
					var attachment;
					if( wndMultipleUpload ){
						var selection = file_frame.state().get('selection');
						selection.map(
							function( attachment )
							{
								var output			= "";

								attachment			= attachment.toJSON();
								output_image		= attachment.url;

								if( attachment.sizes.thumbnail !== undef )
									output_image	= attachment.sizes.thumbnail.url;

								if( $this.hasClass( 'other' ) ){

									output += "<li class=\"list-group-item\">";
									output += attachment.filename
									output += "<input type='hidden' name='lava_attach_other[]' value='" + attachment.id + "'>";
									output += "<input type='button' value='Delete' class='lava_detail_image_del'>";
									output += "</li>";
									$( $this.data('preview') ).append( output );

								}else{
									output += "<div class='lava-detail-images-wrap'>";
									output += "		<img src='" + output_image + "'>";
									output += "		<input type='hidden' name='lava_attach[]' value='" + attachment.id + "'>";
									output += "		<input type='button' value='Delete' class='lava-additem-removefile'>";
									output += "</div>";
									$( $this.data('preview') ).append( output );
								}
							}
						);
					}else{
						attachment = file_frame.state().get('selection').first().toJSON();
						output_image = attachment.url;

						if( attachment.sizes.thumbnail !== undef )
							output_image	= attachment.sizes.thumbnail.url;

						field.val(attachment.id);
						preview.prop("src", output_image );
					};
				});
				file_frame.open();
			}
		}

		, submit : function ()
		{
			var
				obj			= this
				, param		= obj.param;

			return function (e)
			{
				e.preventDefault();

				var
					arrFields		= obj.form.serialize()
					, ajaxURL		= $( "[key='responseURL']", param ).val()
					, strSuccess	= $( "[key='str_msg_success']", param ).val()
					, strSecurity	= $( "[name='security']", obj.form ).val();

				obj.form.ajaxSubmit({

					dataType		: 'json'
					, url			: ajaxURL
					, contentType	: 'application/json'
					, type			: 'post'
					, success		: function( xhr ) {

						if( xhr.err ) {
							obj.message( xhr.err );
							return;
						}
						alert( strSuccess );
						window.onbeforeunload	= function(){};
						window.location.href	= xhr.link;
					}
					, error			: function( xhr ){ console.log( xhr.responseText ); }
				});

				return false;
			}
		}
		, parse_detail_images : function( data ) {
			var str		= $( "#lava-detail-image-input-template" ).html();

			if( !data )
				return data;

			str			= str.replace( /{filename}/		, data.output || '' );
			str			= str.replace( /{attachID}/		, data.dID || '' );
			return str;
		}

		, input_file_add : function() {
			var
				obj			= this
				, param		= obj.param
				, ajaxURL	= $( "[key='responseURL']", param ).val()
				, container	= $( "#lava-detail-image-input-wrap" )
				, form		= $( "#lava-detail-image-uploader" )
				, pAction	= $( "[key='preview_action']", param ).val()
				, loading	= $( "#lava-additem-upload-loading" );

			return function( e ){

				var
					thisObject		= $( this ).clone()
					, thisParent	= $( this ).parent();
				e.preventDefault();

				if( ! $( this ).val() )
					return;

				$( "[name='lava_detail_uploader']", form ).remove();
				$( this ).appendTo( form );

				form
					.ajaxSubmit({
						dataType		: 'json'
						, url			: ajaxURL
						, contentType	: 'application/json'
						, type			: 'post'
						, beforeSend	: function() {
							loading.removeClass( 'hidden' );
						}
						, uploadProgress: function( e, pos, total, loaded ) {
							console.log( loaded, total );
						}
						, complete		: function() {
							thisObject.appendTo( thisParent );
							loading.addClass('hidden');
						}
						, success		: function( xhr )
						{


							if( xhr.err ) {
								obj.message( xhr.err );
								return;
							}


							$.get( ajaxURL, { id: xhr.dID, action : pAction }, function( xhr ) {
								container.append( obj.parse_detail_images( xhr ) );
							}, 'json' );

						}
						, error			: function( xhr ){ console.log( xhr.responseText ); }
					});
			}

		}
		, input_file_remove : function() {
			return function( e ){
				e.preventDefault();
				$( this ).closest( 'fieldset' ).remove();
			}
		}

	}

	$.lava_add_item = function( opt ) {
		new lava_submit_func( opt );
	};

} )( jQuery, window );