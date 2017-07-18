<?php
/**
 *	Listings template loop
 *
 */
?>
<div class="lava-realstate-manager-listing-item item-{post_id}">
	<div class="lava-image">
		<a href="{permalink}">
			<div class="lava-thb" style="width:100%;height:100px;{thumbnail}"></div>
			<strong>{author-name}</strong>
		</a>
	</div>
	<div class="description">

		<a href="{permalink}"> <h1>{post-title}</h1> </a>

		<ul>

			<li class="meta-type"><span>{property_type}</span></li>
			<li class="meta-status"><span>{property_status}</span></li>
			<li class="meta-location"><span>{property_city}</span></li>

			<li class=""><strong>{bedrooms}</strong> <span><?php _e( "Bed", 'Lavacode' ); ?></span></li>
			<li class=""><strong>{bathrooms}</strong> <span><?php _e( "Bath", 'Lavacode' ); ?></span></li>
			<li class=""><strong>{garages}</strong> <span><?php _e( "Garage", 'Lavacode' ); ?></span></li>
			<li class=""><strong>{area}</strong> <span>{unit}</span></li>
		</ul>

		<div class="">
			<strong><?php _e( "Price", 'Lavacode' );?></strong> <span>{currency} {price}</span>
		</div>
	</div>
</div>