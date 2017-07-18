<?php

if( !defined( 'ABSPATH' ) || ! class_exists( 'Lava_RealEstate_Manager' ) )
	die;

class Lava_RealEstate_Manager_Enqueues extends Lava_RealEstate_Manager
{
	private $lava_ssl = 'http://';

	public function __construct()
	{
		if( is_ssl() )
			$this->lava_ssl							=  'https://';

		add_action('wp_enqueue_scripts'				, Array( $this, 'register_styles'), 20 );
		add_action('wp_enqueue_scripts'				, Array( $this, 'register_scripts') );
		add_action('admin_enqueue_scripts'			, Array( $this, 'register_scripts') );
	}

	public function register_styles()
	{
		$lava_load_styles							=
			Array(
				'flexslider.css'					=> '2.5.0'
				, 'selectize.css'					=> '0.12.0'
				, lava_realestate()->folder . '.css'	=> '0.1.0'
			);

		if( !empty( $lava_load_styles ) )
			foreach( $lava_load_styles as $filename => $version )
			{
				wp_register_style(
					sanitize_title( $filename )
					, lava_realestate()->assets_url . "css/{$filename}"
					, false
					, $version
				);
				wp_enqueue_style( sanitize_title( $filename ) );
			}
	}

	public function register_scripts()
	{
		global $wpdb;

		$lava_google_api						= '';

		if( $lava_google_api					= false )
			$lava_google_api					.= "&key={$lava_google_api}";

		if( $lava_google_lang					= false )
			$lava_google_api					.= "&language={$lava_google_lang}";

		$lava_load_scripts						=
			Array(
				'scripts.js'					=> Array( '0.0.1', true )
				, 'less.min.js'					=> Array( '2.4.1', false )
				, 'jquery.lava.msg.js'			=> Array( '0.0.1', true )
				, 'gmap3.js'					=> Array( '0.0.1', false )
				, 'lava-submit-script.js'		=> Array( '0.0.1', false )
				, 'lava-single.js'				=> Array( '0.0.2', true )
				, 'lava-map.js'					=> Array( '0.0.2', true )
				, 'lava-listing.js'				=> Array( '0.0.2', true )
				, 'jquery.flexslider-min.js'	=> Array( '2.5.0', true )
				, 'google.map.infobubble.js'	=> Array( '1.0.0', true )
			);

		if( !empty( $lava_load_scripts ) )
			foreach( $lava_load_scripts as $filename => $args )
			{
				wp_register_script(
					sanitize_title( $filename )
					, lava_realestate()->assets_url . "js/{$filename}"
					, Array( 'jquery' )
					, $args[0], $args[1]
				);
			}

		$strAppend = '';

		if( $strAPIKEY = lava_realestate()->admin->get_settings( 'google_map_api', '' ) )
			$strAppend = '&key=' . $strAPIKEY;

		wp_enqueue_script(
			'google-maps',
			sprintf(
				'%1$smaps.googleapis.com/maps/api/js?libraries=places%2$s',
				$this->lava_ssl,
				$strAppend
			),
			Array('jquery'),
			"0.0.1",
			false
		);

		wp_enqueue_script( 'gmap3-js' );
		wp_enqueue_script( 'less-min-js' );
	}
}

