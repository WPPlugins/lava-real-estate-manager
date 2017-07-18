<?php
add_filter( 'lava_' . self::getSlug() . '_admin_save_button', Array( $this, 'hide_saveButton' ) );
echo "<ol>";
foreach( lava_realestate()->addons->getAddons() as $slug => $addon  ) {
	$has_licensekey = !empty( $addon->license );
	$is_active			= isset( $addon->active );
	echo "<li>";
		echo "Addon Name => " . $addon->label . "<br>";
		echo "Addon Status => " . ( $is_active ? __( 'Active', 'Lavacode' ) : __( 'Deactive', 'Lavacode' ) ) . "<br>";

		if( $is_active ) {
			echo "Addon Version => " . $addon->version . "<br>";
			echo "Addon Description => " . $addon->describe . "<br>";
		}

		echo "Addon Lastest Version => " . $addon->lastest_version . "<br>";
		echo "Addon License Status => " . ( $addon->license_active ? 'Registered' : 'Deregistered' ) . "<br>";
		if( $has_licensekey && $addon->license_active ) {
			echo "Addon Registered License Key => " . $addon->license ."<br>";

			if( $is_active ) {
				if( version_compare( $addon->lastest_version, $addon->version, '>' ) ) {
					printf( "
						<a href=\"%s\"class=\"button button-primary\">
							%s
						</a>"
						, esc_url( network_admin_url( 'update-core.php' ) )
						, __( "Update Plugin Page", 'Lavacode' )
					);
				}else{
					_e( "Lastest Version", 'Lavacode' );
				}
			}else{
				printf( "<button type=\"button\" class=\"button button-primary\">%s</button>", __( "Go Download", 'Lavacode' ) );
			}
			printf( "&nbsp;<button type=\"button\" class=\"lava-addon-deactive-license button button-primary\" data-slug=\"{$slug}\">%s</button>", __( "Deactivate License", 'Lavacode' ) );
		}else{
			printf( "
				<div class=\"lava-addons-license-field\">
					<p>
						<label>
							%s : <br><input type=\"email\" name=\"lavaLicense[$slug]\" value=\"%s\" size=30>
						</label>
					</p>
					<p>
						<label>
							%s : <br><input type=\"text\" name=\"lavaLicense[$slug]\" size=30	>
						</label>
					</p>
					<p>
						<button type=\"button\" class=\"lava-addon-input-license button button-primary\" data-slug=\"{$slug}\">
							%s
						</button>
					</p>
				</dv>"
				, __( "Lavacode account email", 'Lavacode' )
				, esc_attr( get_bloginfo( 'admin_email' ) )
				, __( "License Key", 'Lavacode' )
				, __( "Register", 'Lavacode' )
			);
		}
		echo "<hr>";
	echo "</li>";
}
echo "</ol>";







echo join( "\n", Array(
	"\n<script type=\"text/javascript\">",
	"\tvar lavaAddonsVariable=" . json_encode(
		Array(
			'ajaxurl'						=> admin_url( 'admin-ajax.php' ),
			'post_type'					=> self::SLUG,
			'strEmailEmpty'			=> __( "000 Please type to account email", 'Lavacode' ),
			'strLicenseEmpty'		=> __( "000 Please type to license key", 'Lavacode' ),
			'strLicenseRegErr'		=> __( "000 Addon license register failed.", 'Lavacode' ),
		)
	) . ';',
	"</script>\n",
) );
?>

<script type="text/javascript">
jQuery( function( $ ) {

	var lava_adminAddons = function()
	{
		if( ! $.__lava_admin_addons )
			this.init();
	}

	lava_adminAddons.prototype = {

		constrcutor: lava_adminAddons

		, init : function()
		{
			var
				obj			= this;

			$__lava_admin_addons = true;

			$( document )
				.on( 'click' , '.lava-addon-input-license', obj.input_license() )
				.on( 'click' , '.lava-addon-deactive-license', obj.deactive_license() );
		}

		, input_license : function()
		{

			var
				obj				= this;

			return function( e ) {
				e.preventDefault();

				var
					parent			= $( this ).closest( '.lava-addons-license-field' )
					, txtLicense	= $( "input[type='text']", parent )
					, txtEmail		= $( "input[type='email']", parent )
					, addon			= $( this ).data( 'slug' );

				if( !txtEmail.val() ) {
					alert( lavaAddonsVariable.strEmailEmpty );
					txtEmail.focus();
					return false;
				}

				if( !txtLicense.val() ) {
					alert( lavaAddonsVariable.strLicenseEmpty );
					txtLicense.focus();
					return false;
				}

				obj.enable( txtLicense, false );
				obj.enable( txtEmail, false );

				$.post(
					lavaAddonsVariable.ajaxurl,
					{
						action			: 'lava' + '_' + lavaAddonsVariable.post_type + '_register_licensekey'
						, 'addon'		: addon
						, email			: txtEmail.val()
						, license_key	: txtLicense.val()
					},
					function( xhr ) {

						if( 'OK' === xhr.state ) {
							document.location.reload();
							return;
						}else{
							alert( lavaAddonsVariable.strLicenseRegErr );
						}
						obj.enable( txtEmail );
						obj.enable( txtLicense );
					},
					'json'
				)
				.fail( function( xhr ) {
					alert( lavaAddonsVariable.strLicenseRegErr );
					obj.enable( txtEmail );
					obj.enable( txtLicense );
				} );
			}
		}

		, deactive_license : function( control, onoff )
		{
			return function( e ) {
				e.preventDefault();

				var addon = $( this ).data( 'slug' );

				$.post(
					lavaAddonsVariable.ajaxurl,
					{
						action			: 'lava' + '_' + lavaAddonsVariable.post_type + '_deactive_licensekey'
						, 'addon'		: addon
					},
					function( xhr ) {
						document.location.reload();
					},
					'json'
				);
			}
		}
		, enable : function( control, onoff )
		{
			var onoff = typeof onoff == 'undefined' ? true : onoff;

			control.removeClass( 'disabled' );
			control.prop( 'disabled', false );

			if( !onoff ) {
				control.addClass( 'disabled' );
				control.prop( 'disabled', 'disabled' );
			}
		}
	}

	new lava_adminAddons;
} );
</script>