<?php do_action( 'lava_' . get_post_type() . '_single_container_before' ); ?>

<div class="property-single">

	<div id="post-<?php the_ID(); ?>" <?php post_class( Array( 'lava-single-content' ) ); ?>>

		<h2 class="property-subtitle"><?php _e( "Summary", 'Lavacode' ); ?></h2>

		<ul class="meta-summary">

			<li class="meta-author">
				<?php lava_get_author_avatar(); ?>
				<strong><?php the_author_meta( 'display_name' ); ?></strong>
			</li>

			<?php if( $strType = lava_realestate_featured_terms( 'property_type', get_the_ID(), false ) ) : ?>
				<li class="meta-type"><strong><?php _e( "Type", 'Lavacode' ); ?></strong><span><?php echo $strType; ?></span></li>
			<?php endif; ?>

			<?php if( $strCity = lava_realestate_featured_terms( 'property_city', get_the_ID(), false ) ) : ?>
			<li class="meta-category">
				<strong><?php _e("City", 'Lavacode' ); ?></strong>
				<span><?php echo $strCity; ?></span>
			</li>
			<?php endif; ?>

			<?php if( $strStatus = lava_realestate_featured_terms( 'property_status', get_the_ID(), false ) ) : ?>
			<li class="meta-location">
				<strong><?php _e( "Status", 'Lavacode' ); ?></strong>
				<span><?php echo $strStatus; ?></span>
			</li>
			<?php endif; ?>

			<?php do_action( "lava_single_meta_append_contents", get_post() ); ?>

		</ul><!--/.meta-summary-->

		<?php
		lava_realestate_attach(
			Array(
				'type'				=> 'ul'
				, 'title'			=> ''
				, 'size'			=> 'large'
				, 'container_class'	=> 'lava-detail-images flexslider hidden'
				, 'wrap_class'		=> 'slides '
				, 'featured_image'	=> true
			)
		); ?>

		<ul class="meta-condition">
			<h2 class="property-subtitle"><?php _e( "Condition", 'Lavacode' ); ?></h2>
			<?php
			foreach(
				Array(
					'_bedrooms'		=> __(  "Bedrooms", 'Lavacode' )
					, '_bathrooms'	=> __(  "Bathrooms", 'Lavacode' )
					, '_garages'	=> __(  "Garages", 'Lavacode' )
				) as $key => $label ) :
				printf( "<li class=\"{$key}\"><strong>{$label}</strong> &#58; <span>%s</span></li>", get_post_meta( get_the_ID(), $key, true ) );
			endforeach;
			?>

			<li class="meta-price">
				<strong><?php _e("Price", 'Lavacode' ); ?></strong> &#58;
				<span><?php echo lava_realestate_get_price(); ?></span>
			</li>

			<li class="meta-area">
				<strong><?php _e("Area", 'Lavacode' ); ?></strong> &#58;
				<span><?php echo lava_realestate_get_area(); ?></span>
			</li>

		</ul><!--/.meta-condition-->
		<div class="description">
			<h2 class="property-subtitle"><?php _e( "Amenities", 'Lavacode' ); ?></h2>
			<?php lava_realestate_amenities(); ?>
		</div><!--/.description-->
		<div class="description">
			<h2 class="property-subtitle"><?php _e( "Description", 'Lavacode' ); ?></h2>
			<?php the_content(); ?>
		</div><!--/.description-->
		<div class="location">
			<h2 class="property-subtitle"><?php _e( "Location", 'Lavacode' ); ?></h2>
			<div id="lava-single-map-area"></div>

			<h2 class="property-subtitle"><?php _e( "StreetView", 'Lavacode' ); ?></h2>
			<div id="lava-single-streetview-area"></div>
		</div><!--/.description-->
	</div> <!-- content -->
</div><!--/.property-single -->

<?php do_action( 'lava_' . get_post_type() . '_single_container_after' ); ?>