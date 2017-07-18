<?php
/**
 *	Map template loop
 *
 */

?>

<div id="lavaPost-{post_id}" class="lava-list-item">
	<div class="lv-property-thumbnail">
	
		<img src="{thumbnail_url}" width="80" height="80">

	</div><!-- lv-property-thumbnail -->

	<div class="lv-property-name-wrap">

		<div class="property-name">
			<a href="{permalink}">{post_title}</a>
		</div><!-- property-name -->

		<div class="lv-property-info">
			<span>{property-type}</span>
			<span>{property-city}</span>
			<span>{property-status}</span>
		</div>

	</div><!-- lv-property-name-wrap -->

	<div class="property-author">{post_author}</div>
</div>