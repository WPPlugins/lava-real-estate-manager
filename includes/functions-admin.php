<?php




/**
 *
 *
 * @param	Integer	Default choise page ID
 * @return	String	Select tag options
 */
if( ! function_exists( 'getOptionsPagesLists' ) ) :

	function getOptionsPagesLists( $default=0 )
	{
		return lava_realestate()->admin->getOptionsPagesLists( $default );
	}

endif;




/**
 * Get manager setting options
 *
 * @param	String	Option Key name
 * @param	Mixed	Result value null, return
 * @return	Mixed	String or default value
 */
if( ! function_exists( 'lava_realestate_manager_get_option' ) ) :

	function lava_realestate_manager_get_option( $key, $default=false )
	{
		return lava_realestate()->admin->get_settings( $key, $default );
	}

endif;