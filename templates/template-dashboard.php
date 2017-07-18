<?php
global
	$lava_realestate_stc_mmypage
	, $lava_realestate_manager_func;

$lava_realestate_status			= Array( 'publish' );

if( ! $lava_realestate_stc_mmypage->get( 'hide_pending', false ) || current_user_can( 'manage_options' ) )
	$lava_realestate_status[]		= 'pending';


$lava_user_posts						= new WP_Query(
	Array(
		'post_type'						=> $this->post_type
		, 'author'							=> get_current_user_id()
		, 'post_status'					=> $lava_realestate_status
		, 'posts_per_page'			=> 10
		, 'paged'							=> max( 1, get_query_var( 'paged' ) )
	)
); ?>

<div class="lava-my-item-list">

	<?php
	global $lava_dashboard_message;

	if( !empty( $lava_dashboard_message ) )
	{
		echo $lava_dashboard_message;
	} ?>

	<h2><?php _e( "My Realestate", 'Lavacode' ); ?></h2>

	<table cellPadding="0" cellSpacing=="0" width="100%">
		<thead>
			<tr>
				<th class="text-center"><?php _e( "Title", 'Lavacode' ); ?></th>
				<th class="text-center"><?php _e( "Posted date", 'Lavacode' ); ?></th>
				<th class="text-center"><?php _e( "Status", 'Lavacode' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if( $lava_user_posts->have_posts() )
			{
				while( $lava_user_posts->have_posts() )
				{
					$lava_user_posts->the_post();
					?>
					<tr class="lava-realstate-<?php the_ID(); ?>">
						<td>
							<div class="lava-title">
								<a href="<?php the_permalink(); ?>">
									<?php the_title(); ?>
								</a>
							</div>
							<?php if( get_current_user_id() == get_the_author_meta( 'ID' ) ) : ?>
								<div class="lava-action">

									<?php
									do_action(
										"lava_{$this->post_type}_dashboard_actions_before"
										, get_the_ID()
										, lava_realestate_manager_get_option( 'page_add_' . $this->post_type )
									) ; ?>

									<a href="<?php lava_realestate_edit_page(); ?>">
										<?php _e( "Edit", 'Lavacode' ); ?>
									</a>
									<a href="javascript:" data-lava-realstate-manager-trash="<?php the_ID();?>">
										<?php _e( "Remove", 'Lavacode' ); ?>
									</a>

									<?php
									do_action(
										"lava_{$this->post_type}_dashboard_actions_after"
										, get_the_ID()
										, lava_realestate_manager_get_option( 'page_add_' . $this->post_type )
									) ; ?>

								</div>
							<?php endif; ?>
						</td>
						<td class="text-center"><?php echo get_the_date(); ?></td>
						<td class="text-center">
							<?php 
								$jv_this_post_status = get_post_status();
								if($jv_this_post_status=='publish'){
									 _e('PUBLISH','Lavacode') ;
								}else if($jv_this_post_status=='pending'){
									_e('PENDING','Lavacode');
								}
								?>
						</td>
					</tr>
					<?php
				}
			}
			wp_reset_query();
			?>
		</tbody>
	</table>
	<p class="lava-pagination">
		<?php
		$big						= 999999999;
		echo paginate_links(
			Array(
				'base'			=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) )
				, 'format'		=> '?paged=%#%'
				, 'current'		=> max( 1, get_query_var('paged') )
				, 'total'			=> $lava_user_posts->max_num_pages
			)
		); ?>
	</p>
</div>

<form method="post" id="lava-realstate-manager-myapge-form">
	<?php wp_nonce_field( 'security', 'lava_realestate_manager_mypage_delete' ); ?>
	<input type="hidden"name="post_id" value="">
</form>

<?php
$lava_output_variable	= Array();
$lava_output_variable[]	= "<script type=\"text/javascript\">";
$lava_output_variable[]	= sprintf( "var strLavaTrashConfirm = '%s'", __( "Do you want to delete this item?", 'Lavacode') );
$lava_output_variable[]	= "</script>";

echo @implode( "\n", $lava_output_variable );




?>

<script type="text/javascript">
jQuery( function( $ ){

	var lava_realestate_manager_mypage = function( el ) {

		this.el	= el;
		if( typeof this.instance === 'undefined' )
			this.init();
	}

	lava_realestate_manager_mypage.prototype = {

		constructor : lava_realestate_manager_mypage
		, init : function(){

			var obj			= this;
			obj.instance	= 1;

			$( document )
				.on( 'click', '[data-lava-realstate-manager-trash]', obj.trash() );
		}

		, trash : function()
		{
			var obj = this;

			return function( e )
			{
				e.preventDefault();

				var post_id		= $( this ).data( 'lava-realstate-manager-trash' );

				if( confirm( strLavaTrashConfirm ) ) {
					obj.el.find( "[name='post_id']").val( post_id );
					obj.el.submit();
				}
			}
		}
	}
	new lava_realestate_manager_mypage( $( "#lava-realstate-manager-myapge-form" ) );

} );
</script>