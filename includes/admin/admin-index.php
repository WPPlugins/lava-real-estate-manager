<table class="form-table">
	<tbody>

		<tr valign="top">
			<th scope="row"><?php _e( "Page Settings", 'Lavacode' ); ?></th>
			<td>
				<table class="widefat">
					<tbody>
						<tr valign="top">
							<td width="1%"></td>
							<th><?php _e( "Add Property", 'Lavacode' ); ?></th>
							<td>
								<select name="lava_realestate_manager_settings[page_add_<?php echo $this->post_type; ?>]">
									<option value><?php _e( "Select a Page", 'Lavacode' ); ?></option>
									<?php echo getOptionsPagesLists( lava_realestate_manager_get_option( "page_add_{$this->post_type}" ) ); ?>
								</select>
							</td>
						</tr>
						<tr><td colspan="3" style="padding:0;"><hr style='margin:0;'></td></tr>
						<tr valign="top">
							<td width="1%"></td>
							<th><?php _e( "My Page", 'Lavacode' ); ?></th>
							<td>
								<select name="lava_realestate_manager_settings[page_my_page]">
									<option value><?php _e( "Select a Page", 'Lavacode' ); ?></option>
									<?php echo getOptionsPagesLists( lava_realestate_manager_get_option( 'page_my_page' ) ); ?>
								</select>
							</td>
						</tr>
						<tr><td colspan="3" style="padding:0;"><hr style='margin:0;'></td></tr>
						<tr valign="top">
							<td width="1%"></td>
							<th><?php _e( "Login Page", 'Lavacode' ); ?></th>
							<td>
								<fieldset>
									<select name="lava_realestate_manager_settings[login_page]">
										<option value><?php _e( "Wordpress Login Page", 'Lavacode' ); ?></option>
										<optgroup label="<?php _e( "Custom Login Page", 'Lavacode' ); ?>">
											<?php echo getOptionsPagesLists( lava_realestate_manager_get_option( 'login_page' ) ); ?>
										</optgroup>
									</select>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e( "Add item Settings", 'Lavacode' ); ?></th>
			<td>
				<table class="widefat">
					<tbody>
						<tr valign="top">
							<td width="1%"></td>
							<th><?php _e( "New Property Status", 'Lavacode' ); ?></th>
							<td>
								<label>
									<input
										type="radio"
										name="lava_realestate_manager_settings[new_<?php echo $this->post_type; ?>_status]"
										value=""
										<?php checked( '' == lava_realestate_manager_get_option( "new_{$this->post_type}_status" ) ); ?>
									>
									<?php _e( "Publish", 'Lavacode' ); ?>
								</label>
								<label>
									<input
										type="radio"
										name="lava_realestate_manager_settings[new_<?php echo $this->post_type; ?>_status]"
										value="pending"
										<?php checked( 'pending' == lava_realestate_manager_get_option( "new_{$this->post_type}_status" ) ); ?>
									>
									<?php _e( "Pending", 'Lavacode' ); ?>
								</label>
							</td>
						</tr>
						<tr><td colspan="3" style="padding:0;"><hr style='margin:0;'></td></tr>
						<tr valign="top">
							<td width="1%"></td>
							<th><?php _e( "Post new listing permit", 'Lavacode' ); ?></th>
							<td>
								<label>
									<input type="radio" name="lava_realestate_manager_settings[add_capability]" value="" <?php checked( '' == lava_realestate_manager_get_option( 'add_capability' ) ); ?>>
									<?php _e( "Anyone without login (it will generate an account automatically)", 'Lavacode' ); ?>
								</label>
								<br>
								<label>
									<input type="radio" name="lava_realestate_manager_settings[add_capability]" value="member" <?php checked( 'member' == lava_realestate_manager_get_option( "add_capability" ) ); ?>>
									<?php _e( "Only login members", 'Lavacode' ); ?>
								</label>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e( "Map Settings", 'Lavacode' ); ?></th>
			<td>
				<table class="widefat">
					<tbody>
						<tr valign="top">
							<td width="1%"></td>
							<th>&nbsp;<?php _e( "Google Map API KEY", 'Lavacode' ); ?></th>
							<td>
								<input type="text" name="<?php echo $this->getOptionFieldName( 'google_map_api' ); ?>" value="<?php echo $this->get_settings( 'google_map_api' );?>" style="width:30%;">
								<?php
								printf(
									'<span class="description"><a href="%1$s" target="_blank">%2$s</a></span>',
									esc_url_raw( 'developers.google.com/maps/documentation/javascript/get-api-key' ),
									esc_html__( "More Detail", 'Lavacode' )
								); ?>
							</td>
						</tr>
						<tr><td colspan="3" style="padding:0;"><hr style='margin:0;'></td></tr>
						<tr valign="top">
							<td width="1%"></td>
							<th>&nbsp;<?php _e( "Data Refresh", 'Lavacode' ); ?></th>
							<td>
								<?php
								if(
									function_exists('icl_get_languages' ) &&
									false !== (bool)( $lava_wpml_langs = icl_get_languages('skip_missing=0') )
								){
									foreach( $lava_wpml_langs as $lang )
									{
										printf(
											"<button class='button button-primary lava-data-refresh-trigger' data-lang='%s'>\n\t
												<img src='%s'> %s %s\n\t
											</button>\n\t"
											, $lang['language_code']
											, $lang['country_flag_url']
											, $lang['native_name']
											, __("Refresh", 'Lavacode')
										);
									}
								}else{
									?>
									<button type="button" class="button button-primary lava-data-refresh-trigger" data-loading="<?php _e( "Processing", 'Lavacode' ); ?>...">
										<?php _e("JSON Generator", 'Lavacode');?>
									</button>
									<?php
								} ?>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e( "Single Page Settings", 'Lavacode' ); ?></th>
			<td>
				<table class="widefat">
					<tbody>
						<tr valign="top">
							<td width="1%"></td>
							<th>&nbsp;<?php _e( "Amenities display type", 'Lavacode' ); ?></th>
							<td>
								<label>
									<input type="radio" name="lava_realestate_manager_settings[display_amenities]" value="" <?php checked( '' == lava_realestate_manager_get_option( "display_amenities" ) ); ?>>
									<?php _e( "List only selected", 'Lavacode' ); ?>
								</label>
								<br>
								<label>
									<input type="radio" name="lava_realestate_manager_settings[display_amenities]" value="showall" <?php checked( 'showall' == lava_realestate_manager_get_option( 'display_amenities' ) ); ?>>
									<?php _e( "List all (unselected & selected)", 'Lavacode' ); ?>
								</label>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e( "General Settings", 'Lavacode' ); ?></th>
			<td>
				<table class="widefat">
					<tbody>
						<tr valign="top">
							<td width="1%"></td>
							<th>&nbsp;<?php _e( "Blank Image", 'Lavacode' ); ?></th>
							<td>
								<input type="text" name="lava_realestate_manager_settings[blank_image]" value="<?php echo lava_realestate_manager_get_option( 'blank_image' ); ?>" tar="lava-blank-image">
								<input type="button" class="button button-primary fileupload" value="<?php _e('Select an Image', 'Lavacode');?>" tar="lava-blank-image">
								<input class="fileuploadcancel button" tar="lava-blank-image" value="<?php _e('Delete', 'Lavacode');?>" type="button">
								<p>
									<?php
									_e("Preview","Lavacode");
									if( false === (boolean)( $strBlankImage = lava_realestate_manager_get_option( 'blank_image' ) ) )
										$strBlankImage = lava_realestate()->image_url . 'no-image.png';
									echo "<p><img src=\"{$strBlankImage}\" tar=\"lava-blank-image\" style=\"max-width:300px;\"></p>"; ?>
								</p>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>

	</tbody>
</table>
<?php do_action( 'lava_realestate_manager_settings_after' ); ?>