<?php
$edit_id				= isset( $edit ) ? $edit->ID : 0;
$lava_post_type			= constant( 'Lava_RealEstate_Manager_Func::SLUG' );
$addition_terms			= apply_filters( "lava_add_{$lava_post_type}_terms", Array() );

$limit_terms			= Array(
	'property_status'	=> 1
	, 'property_type'	=> 1
);

if( !empty( $addition_terms ) ) : foreach( $addition_terms as $taxonomy ) {
	$this_value			= wp_get_object_terms( $edit_id, $taxonomy, Array( 'fields' => 'ids' ) );
	if( is_wp_error( $this_value ) )
		continue;

	printf( "
		<div class=\"form-inner\">
			<label>%s</label>
			<select name=\"lava_additem_terms[{$taxonomy}][]\" multiple=\"multiple\" class=\"lava-add-item-selectize\" data-limit=\"%s\" data-create=\"%s\">
				<option value=\"\">%s</option>%s
			</select>
		</div>"
		, get_taxonomy( $taxonomy )->label
		, ( Array_Key_Exists( $taxonomy, $limit_terms ) ? $limit_terms[ $taxonomy ] : 0 )
		, ( $taxonomy === $lava_post_type . '_keyword' )
		, get_taxonomy( $taxonomy )->label
		, apply_filters('lava_get_selbox_child_term_lists', $taxonomy, null, 'select', $this_value, 0, 0, "-")
	);
} endif;
?>

<script type="text/javascript">
jQuery( function( $ ){

	var lava_Ai_update_extend = function()
	{
		if( ! window.__LAVA_AI__EXTEND__ )
			this._init();
	}

	lava_Ai_update_extend.prototype = {

		constrcutor : lava_Ai_update_extend

		, _init : function()
		{
			var obj						= this;
			window.__LAVA_AI__EXTEND__	= 1;
			obj.setCategory();
		}

		, setCategory : function()
		{
			var obj						= this;

			$( '.lava-add-item-selectize' ).each( function() {

				var
					limit		= parseInt( $( this ).data( 'limit' ) || 0 )
					, isCreate	= parseInt( $( this ).data( 'create' ) || 0 )
					options		= { plugins : [ 'remove_button' ], create : isCreate };

				if( limit > 0 )
					options.maxItems	= limit;

				$( this ).selectize( options );

			} );
		}
	};
	new lava_Ai_update_extend;
} );
</script>