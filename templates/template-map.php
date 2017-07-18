<?php
global $post;
get_header(); ?>

<?php do_action( "lava_{$post->lava_type}_map_container_before" ); ?>

<div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div id="lava-map-container"></div>

	<div id="lava-map-filter">

		<div class="filter-group">
			<div class="filter-group-column">
				<label><?php _e( "Type", 'Lavacode'); ?></label>
				<select name="lava_filter[property_type]" data-filter="property_type">
					<option value=""><?php _e( "Type", 'Lavacode' );?></option>
					<?php echo apply_filters('lava_get_selbox_child_term_lists', 'property_type', null, 'select', false, 0, 0, "-");?>
				</select>
			</div>
			<div class="filter-group-column">
				<label><?php _e( "Status", 'Lavacode'); ?></label>
				<select name="lava_filter[property_status]" data-filter="property_status">
					<option value=""><?php _e( "Status", 'Lavacode' );?></option>
					<?php echo apply_filters('lava_get_selbox_child_term_lists', 'property_status', null, 'select', false, 0, 0, "-");?>
				</select>
			</div>
		</div>

		<div class="filter-group">

			<div class="filter-group-column">
				<label><?php _e( "City", 'Lavacode'); ?></label>
				<select name="lava_filter[property_city]" data-filter="property_city">
					<option value=""><?php _e( "Location (city)", 'Lavacode' );?></option>
					<?php echo apply_filters('lava_get_selbox_child_term_lists', 'property_city', null, 'select', false, 0, 0, "-");?>
				</select>
			</div>
			<div class="filter-group-column">
				<label><?php _e( "Amenities", 'Lavacode'); ?></label>
				<select name="lava_filter[property_amenities]" data-filter="property_amenities">
					<option value=""><?php _e( "Amenities", 'Lavacode' );?></option>
					<?php echo apply_filters('lava_get_selbox_child_term_lists', 'property_amenities', null, 'select', false, 0, 0, "-");?>
				</select>
			</div>
		</div>

		<div class="filter-group submit">
			<label><?php _e( "Keyword", 'Lavacode'); ?></label>
			<input type="text" name="keyword" data-filter="tags" placeholder="<?php _e( "Keyword", 'Lavacode' ); ?>">
			<button type="button" id="lava-map-search">
				<?php _e( "Search Now", 'Lavacode' ); ?>
			</button>
		</div>

	</div>

	<p>
		<strong><?php _e( "Listings", 'Lavacode' );?></strong>
		<div id="lava-map-output"><?php _e( "Loading", 'Lavacode' ); ?>....</div>
	</p>

<div>


<fieldset class="hidden" id="lava-map-parameter">
	<input type="hidden" key="ajaxurl" value="<?php echo admin_url( 'admin-ajax.php' ); ?>">
	<input type="hidden" key="crossdomain" value="<?php echo $post->crossdomain; ?>">
	<input type="hidden" key="json_file" value="<?php echo $post->json_file; ?>">
	<input type="hidden" key="prefix" value="<?php echo $post->lava_type; ?>">
	<input type="hidden" key="filter" value="#lava-map-filter">
	<input type="hidden" key="output" value="#lava-map-output">
	<input type="hidden" key="output-template" value="#lava-map-output-template">
	<input type="hidden" key="output-not-found" value="#lava-map-not-found-template">
</fieldset>

<script type="text/javascript">
jQuery( function( $ ) {
	$.lava_boxMap({
		map			: '#lava-map-container'
		, params	: '#lava-map-parameter'
	});
} );
</script>


<?php
do_action( "lava_{$post->lava_type}_map_container_after" );
wp_footer();