<?php do_action( "lava_{$post_type}_listings_before" ); ?>
<form id="lava-realstate-manager-listing">
	<div class="search-type">
		<div class="search-type-keywords">
			<input type="text" name="keyword" placeholder="<?php _e( "Keywords", 'Lavacode' ); ?>" data-filter-keyword>
		</div>
		<div class="search-type-location">

			<?php if( $optAddressField ) : ?>
				<input type="text" name="location" placeholder="<?php _e( "Location", 'Lavacode' ); ?>" data-filter-location>
			<?php else: ?>
				<select name="lava_filter[property_city]" data-filter="property_city">
					<option value=""><?php _e( "Any Location", 'Lavacode' );?></option>
					<?php echo apply_filters('lava_get_selbox_child_term_lists', 'property_city', null, 'select', false, 0, 0, "-");?>
				</select>
			<?php endif; ?>

		</div>

		<div class="search-type-category">
			<select name="lava_filter[property_status]" data-filter="property_status">
				<option value=""><?php _e( "Any Category", 'Lavacode' );?></option>
				<?php echo apply_filters('lava_get_selbox_child_term_lists', 'property_status', null, 'select', false, 0, 0, "-");?>
			</select>
		</div>
	</div>
	<div class="select-type">
		<ul>
			<?php
			$lava_filterMultiple	= 'property_type';
			if( $arrType_terms = get_terms( $lava_filterMultiple, Array( 'hide_empty' => false, 'fields' => 'id=>name' ) ) ) {
				foreach( $arrType_terms as $term_id => $name ) {
					echo "
						<li>
							<label>
								<input type=\"checkbox\" name=\"lava_filter[{$lava_filterMultiple}][]\" data-filter=\"{$lava_filterMultiple}\" value=\"{$term_id}\" checked>{$name}
							</label>
						</li>
						";
				}
			} ?>
		</ul>
	</div>

	<button type="submit">
		<?php _e( "Search", 'Lavacode' ); ?>
	</button>

	<fieldset class="hidden">
		<input type="hidden" name="paged" value="1">
		<input type="hidden" name="action" value="lava_realestate_listing">
	</fieldset>

</form>

<div id="lava-realstate-manager-output"></div>
<?php do_action( "lava_{$post_type}_listings_after" ); ?>