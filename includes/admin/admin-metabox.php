<table class="form-table">

	<tr>
		<th><?php _e( "Featured Item", 'Lavacode' ); ?></th>
		<td><input type="checkbox" name="lava_pt[featured]" value="1" <?php checked( '1' === get_post_meta( $post->ID, '_featured_item', true ) ); ?>></td>
	</tr>
	<?php
	if( !empty( $lava_property_fields ) && is_Array( $lava_property_fields ) ) : foreach( $lava_property_fields as $fID => $meta ) {

		$this_value			= get_post_meta( $post->ID, $fID, true );

		echo "<tr>";
			echo "<td>{$meta['label']}</td>";
			echo "<td>";
				switch( $meta[ 'element'] ) {
					case 'input' :
						echo "<input type=\"{$meta['type']}\" name=\"lava_additem_meta[{$fID}]\" value=\"{$this_value}\" class=\"{$meta[ 'class']}\">";
					break;
				}
			echo "</td>";
		echo "</tr>";

	} endif; ?>

	<?php do_action( 'lava_admin_additem_other_field', $post ); ?>

	<tr>
		<th><?php _e('Address on map', 'Lavacode');?></th>
		<td>
			<input class="lava_txt_find_address" type="text"><a class="button lava_btn_find_address"><?php _e('Find', 'Lavacode');?></a>
			<div class="lava-item-map-container"></div>
			<?php
			echo "Latitude : <input name='lava_pt[map][lat]' value='{$post->lat}' type='text' class='only-number'>" . ', ';
			echo "Longitude : <input name='lava_pt[map][lng]' value='{$post->lng}' type='text' class='only-number'>"; ?>
		</td>
	</tr>

	<tr>
		<th><?php _e('StreetView', 'Lavacode');?></th>
		<td>
			<label>
				<input type="hidden" name="lava_pt[map][street_visible]" value="0">
				<input type="checkbox" name="lava_pt[map][street_visible]" value="1" <?php checked( 1 == $post->street_visible );?>>
				<?php _e("Use StreetView", 'Lavacode');?>
			</labeL>
			<div class="lava-item-streetview-container<?php echo $post->street_visible == 0? ' hidden': '';?>"></div>
			<fieldset class="hidden">
				<?php
				echo "Latitude : <input name='lava_pt[map][street_lat]' value='{$post->street_lat}' type='text'>";
				echo "Longitude : <input name='lava_pt[map][street_lng]' value='{$post->street_lng}' type='text'>";
				echo "Heading : <input name='lava_pt[map][street_heading]' value='{$post->street_heading}' type='text'>";
				echo "pitch: <input name='lava_pt[map][street_pitch]' value='{$post->street_pitch}' type='text'>";
				echo "zoom : <input name='lava_pt[map][street_zoom]' value='{$post->street_zoom}' type='text'>"; ?>
			</fieldset>
		</td>
	</tr>

	<tr>
		<th><?php _e('Description Images', 'Lavacode');?></th>
		<td>
			<div class="">
				<a href="javascript:" class="button button-primary lava_pt_detail_add"><?php _e('Add Images', 'Lavacode');?></a>
			</div>
			<div class="lava_pt_images">
				<?php
				$images = get_post_meta( $post->ID, "detail_images", true );
				if(is_Array($images)){
					foreach($images as $iamge=>$src){
						$url = wp_get_attachment_image_src($src, 'thumbnail');
						printf("
						<div class='lava_pt_field' style='float:left;'>
							<img src='%s'><input name='lava_attach[]' value='%s' type='hidden'>
							<div class='' align='center'>
								<input class='lava_pt_detail_del button' type='button' value=\"" . __( "Delete", 'Lavacode' ) . "\">
							</div>
						</div>
						", $url[0], $src);
					};
				};?>
			</div>
		</td>
	</tr>
</table>