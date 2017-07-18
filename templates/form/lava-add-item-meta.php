<?php

$lava_property_fields	= apply_filters( "lava_{$lava_post_type}_more_meta", Array() );

if( !empty( $lava_property_fields ) && is_Array( $lava_property_fields ) ) : foreach( $lava_property_fields as $fID => $meta ) {

	$post_id			= intVal( get_query_var( 'edit' ) );
	$this_value			= get_post_meta( $post_id, $fID, true );

	echo "<div class=\"form-inner\">";
		echo "<label>{$meta['label']}</label>";
	switch( $meta[ 'element'] ) {

		case 'input' :
			echo "<input type=\"{$meta['type']}\" name=\"lava_additem_meta[{$fID}]\" value=\"{$this_value}\" class=\"form-control\">";
		break;
	}
	echo "</div>";

} endif;