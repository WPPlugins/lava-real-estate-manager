<?php

class Lava_RealEstate_Manager_Addons extends Lava_RealEstate_Manager_Func
{

	const urlAPI					= 'www.lava-code.com';

	private $post_type			= false;

	private $optionGroup		= false;

	public $addons				= Array();

	public function __construct()
	{
		$this->post_type		= self::SLUG;
		$this->optionGroup	= 'lava_' . $this->post_type . '_addons';
		add_filter( "lava_{$this->post_type}_admin_tab"	, Array( $this, 'add_addons_tab' ) );
		add_action( "wp_ajax_lava_{$this->post_type}_register_licensekey", Array( $this, 'register_licensekey' ) );
		add_action( "wp_ajax_lava_{$this->post_type}_deactive_licensekey", Array( $this, 'deactive_licensekey' ) );

		add_filter( 'site_transient_update_plugins', array( $this, 'check_for_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api_handler' ), 10, 3 );
	}

	public function add_addons_tab( $args )
	{
		return wp_parse_args(
			Array(
				'addons'		=> Array(
					'label'		=> __( "Addons", 'Lavocode' ),
					'group'	=> $this->optionGroup,
					'file'		=> dirname( __FILE__ ) . '/admin/admin-addons.php'
				)
			), $args
		);
	}

	public function register_licensekey()
	{
		header( "Content-Type:application/json; charset=utf-8" );

		$arrOutput	= Array( 'state' => '' );

		if(
			!empty( $_POST[ 'addon' ] ) &&
			!empty( $_POST[ 'email' ] ) &&
			!empty( $_POST[ 'license_key' ] )
		) {
			$license_actAddons		= (Array) get_option( 'lava_actived_addons' );
			$licenseResponse			=  $this->getRemotePost(
				Array(
					'action'					=> 'license_exists',
					'addon'					=> $_POST[ 'addon' ],
					'email'					=> $_POST[ 'email' ],
					'license_key'			=> $_POST[ 'license_key' ],
				)
			);
			if( 'OK' === $licenseResponse[ 'state' ] ) {
				if( !empty( $licenseResponse[ 'result' ]->result ) ){
					if( ! in_Array( $_POST[ 'addon' ], $license_actAddons ) )
						$license_actAddons[]	= $_POST[ 'addon' ];
					$arrOutput[ 'state' ] = 'OK';
				}else{
					if( false !== ( $arrOrder = array_search( $_POST[ 'addon' ], $license_actAddons ) ) ) {
						unset( $license_actAddons[ $arrOrder ] );
					}
				}
			}
			update_option( 'lava_actived_addons', $license_actAddons );
			update_option( $_POST[ 'addon' ] . '_license_key', sanitize_key( $_POST[ 'license_key' ] ) );
		}
		die( json_encode( $arrOutput ) );
	}

	public function deactive_licensekey()
	{
		header( "Content-Type:application/json; charset=utf-8" );
		if( !empty( $_POST[ 'addon'] ) ) {
			$license_actAddons		= (Array) get_option( 'lava_actived_addons' );
			if( false !== ( $arrOrder = array_search( $_POST[ 'addon' ], $license_actAddons ) ) ) {
				unset( $license_actAddons[ $arrOrder ] );
			}
			update_option( 'lava_actived_addons', $license_actAddons );
			update_option( $_POST[ 'addon' ] . '_license_key', '' );
		}
	}

	public function getRemotePost( $args=Array() )
	{
		$arrOutput				= Array( 'state' => false );
		$lavaResponser			= wp_remote_post(
			esc_url( self::urlAPI ),
			Array(
				'method'			=> 'POST',
				'timeout'			=> 15,
				'user-agent'		=> $this->getName() . '/' . $this->getVersion() . ';' . get_bloginfo('url'),
				'body'				=> wp_parse_args( Array( 'site' => home_url( '/' ) ), $args )
			)
		);

		$lavaResponse				= wp_remote_retrieve_body( $lavaResponser );
		if( is_wp_error( $lavaResponser ) ){
			$arrOutput[ 'result' ]	= sprintf( "%s : %s", __( "Error", 'Lavacode' ), $lavaResponser->get_error_message() );
		}else if( is_wp_error( $lavaResponser) ){
			$arrOutput[ 'result' ]	= sprintf( "%s : %s", __( "Error", 'Lavacode' ), $lavaResponse->get_error_message() );
		}else if( empty( $lavaResponse ) ){
			$arrOutput[ 'result' ]	= __( "Error", 'javo' );
		}else{
			$arrOutput[ 'state' ]	= 'OK';
			$arrOutput[ 'result' ]	= json_decode( $lavaResponse );
		}
		return $arrOutput;
	}

	public function getLavaAddons()
	{
		$this->addons	= null;
		$response			= $this->getRemotePost( Array( 'action' => 'getAddons' ) );

		if( 'OK' !== $response[ 'state' ] )
			return;

		if( isset( $response[ 'result' ]->state ) && 'OK' === $response[ 'result' ]->state ) {
			$this->addons = $response[ 'result' ]->addons;
		}else{
			$strMessage		= __( "Server Error", 'Lavacode' );
			if( !empty( $response[ 'result' ]->message ) )
				$strMessage = $response[ 'result' ]->message;
			echo $strMessage;
		}
	}

	public function getAddons()
	{
		$this->getLavaAddons();
		if( !empty( $this->addons ) ) : foreach( $this->addons as $slug => $addon ) {

			$addon->license			= get_option( $slug . '_license_key' );
			$addon->license_active	= in_Array( $slug, (Array) get_option( 'lava_actived_addons') );

			if( false !== ( $pluginDATA = $this->getAddonsDATA( "{$addon->slug}/{$addon->slug}.php" ) ) )
			{
				$addon->active			= true;
				$addon->version		= $pluginDATA[ 'Version' ];
				$addon->describe		= $pluginDATA[ 'Description' ];
			}

		} endif;
		return $this->addons;
	}

	public function getAddonsDATA( $slug=false )
	{
		$arrReturn		= false;
		if( is_plugin_active( $slug ) )
			$arrReturn	= get_plugin_data( $this->getPluginDir() . $slug );
		return $arrReturn;
	}

	public function check_for_update( $data )
	{
		/*
		if( empty( $data->checked ) )
			return $data;
		*/

		//$license_actAddons		= (Array) get_option( 'lava_actived_addons' );
		$license_actAddons = Array( 'lava-alert' );

		if( !empty( $license_actAddons ) ) : foreach( $license_actAddons as $slugAddon )
		{
			$addonSlug								= $slugAddon . '/' . $slugAddon . '.php';
			$addonMeta								= new stdClass();
			$addonMeta->new_version		= '1.4.5';
			$addonMeta->package	= 'http://lava-code.com/ref/addons/file/javo-home-core.zip';
			$addonMeta->slug						= $addonSlug;
			$data->response[ $addonSlug ]	= $addonMeta;
		} endif;
		return $data;
	}

	public function plugins_api_handler( $resource, $action, $args )
	{
		$license_actAddons = 'lava-alert';

		if( 'plugin_information' == $action ) :
			 if ( isset( $args->slug ) && $args->slug == plugin_basename( $license_actAddons . '/' . $license_actAddons . '.php' ) )
			{
				$addonMeta							= new stdClass();
				$addonMeta->name				= __( "Lava Real-Estate Payment", 'Lavacode' );
				$addonMeta->version			= '1.4.5';
				$addonMeta->slug					= $args->slug;
				$addonMeta->last_updated	= '2015/09/14';
				$addonMeta->sections			= Array(
					'description'						=> __( "Update !!", 'Lavacode')
				);
				$addonMeta->download_link	= 'http://lava-code.com/ref/addons/file/javo-home-core.zip';

				return $addonMeta;
			}
		endif;
		return false;
	}
}