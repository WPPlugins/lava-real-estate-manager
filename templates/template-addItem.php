<?php
global $edit;

get_currentuserinfo();
$lava_query					= new lava_Array( $_POST );
$lava_post_type				= constant( 'Lava_RealEstate_Manager_Func::SLUG' );
?>

<div class="lv-dashboard-additem" id="lava-additem-form">

	<div class="notice hidden"></div>

	<form method="post" enctype="multipart/form-data" id="lava-additem-form">

		<?php do_action( "lava_add_{$lava_post_type}_form_before", $edit ); ?>

		<div class="form-inner">
			<label><?php _e("Title", "Lavacode"); ?></label>
			<input name="txt_title" type="text" class="form-control" value="<?php echo esc_attr( isset($edit) ? $edit->post_title : NULL ); ?>" placeholder="<?php _e('Write a title','Lavacode'); ?>">
		</div>

		<div class="form-inner">
			<label><?php _e("Description", "Lavacode"); ?></label>
			<textarea name="txt_content" class="form-control" rows="10" placeholder="<?php _e( "Write a description", 'Lavacode' );?>"><?php echo !empty($edit)?$edit->post_content:'';?></textarea>
		</div>

		<?php do_action( "lava_add_{$lava_post_type}_form_after", $edit ); ?>

		<!-- Submit Button -->
		<?php lava_add_realestate_submit_button(); ?>
		<input type="hidden" name="action" value="<?php echo "lava_{$lava_post_type}_manager_submit_item";?>">

	</form>

	<form method="post" enctype="multipart/form-data" id="lava-detail-image-uploader" class="hidden">
		<input type="hidden" name="action" value="<?php echo "lava_{$lava_post_type}_manager_upload_detail"; ?>">
	</form>

	<fieldset class="params hidden">
		<input type="hidden" key="responseURL" value="<?php echo admin_url( 'admin-ajax.php' ); ?>">
		<input type="hidden" key="preview_action" value="<?php echo "lava_{$lava_post_type}_manager_img"; ?>">
		<input type="hidden" key="str_msg_success" value="<?php _e( "Saved", 'Lavacode' ); ?> !">
	</fieldset>
</div>
<?php
$lava_single_dImages	= Array();

if( !empty( $edit->arrAttach ) ) : foreach( $edit->arrAttach as $attID ) {
	$lava_single_dImages[]	= Array(
		'dID'				=> $attID
		, 'output'			=> wp_get_attachment_image( $attID )
	);
} endif;
$strSingle_dImage		= Array();
$strSingle_dImage[]		= "<script type=\"text/javascript\">";
$strSingle_dImage[]		= sprintf( 'var lava_detail = %s;', json_encode( $lava_single_dImages ) );
$strSingle_dImage[]		= "</script>";
echo @implode( "\n", $strSingle_dImage ) . "\n";

?>
<script type="text/javascript">
jQuery( function($) {
	jQuery.lava_add_item({
		el				: '#lava-additem-form'
		, form			: '#lava-additem-form'
		, param			: 'fieldset.params'
		, notice		: '.notice'
		, processbar	: '.progress'
		, detail_image	: lava_detail
	});
});
</script>

<?php
wp_enqueue_media();
do_action( "lava_add_{$lava_post_type}_edit_footer", get_query_var('edit') );

