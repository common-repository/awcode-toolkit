<?php
/*
 * Templete name: Supplier Add
 * Version: 1.0.0
 * Templete path: /templete/supplier_add.php
 * Add Supplier for admin
 *
*/

	$wpnonce = wp_create_nonce( 'add-new-supplier' );
	$admin_ajax = site_url().'/wp-admin/admin-ajax.php';
	$title = '';
	$info = '';
	$post_id = isset($_GET['post_id']) ? preg_replace("/[^0-9]/", "", $_GET['post_id']) : null;
	if( $post_id ){
		$post = get_post( $post_id ); 
		$title = $post->post_title;
		$info = get_post_meta($post_id,'_supplier_info',false)[0];
	}
?>

<div class="wrap supplier">
	<h1 class="wp-heading-inline">Add Supplier</h1>
	<hr class="wp-header-end">
	<div style="width: 50%;margin-top: 15px;">
		<form id="supplier_addnew" action="<?php echo site_url() ?>/wp-admin/admin-ajax.php" method="POST">
			<?php if( $post_id ){ ?>
			<input type="hidden" name="post_id" value="<?=$post_id?>">
			<?php } ?>
			<input type="hidden" name="action" value="supplierAddNew">
			<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?=$wpnonce?>">
			<input type="hidden" name="_wp_http_referer" value="<?php echo site_url() ?>/wp-admin/edit.php?post_type=product&page=manage-suppliers">
			<div class="form-group">
				<label>Name</label>
				<input type="text" name="name" class="form-control" required value="<?=$title?>">
			</div>
			<div class="form-group">
				<label>Contact info</label>
				<textarea name="info" class="form-control" rows="4" cols="5"><?=$info?></textarea>
			</div>
			<div class="text-right">
				<input type="submit" class="btn btn-submit" name="submit" value="Save">
			</div>
		</form>
	</div>
</div>


<style type="text/css">
	.supplier .form-control{
		width: 100%;
		border-radius: 5px;
		border: 1px solid #d0d0d0;
	}
	.form-group{
		margin-bottom: 15px;
	}
	.text-center{
		text-align: center;
	}
	.text-right{
		text-align: right;
	}
	.text-left{
		text-align: left;
	}
	.supplier .btn.btn-submit{
		white-space: nowrap;
	    background: #007cba;
	    border: 1px solid #007cba;
	    color: #fff;
	    text-decoration: none;
	    text-shadow: none;
    	font-size: 13px;
    	cursor: pointer;
	    transition: box-shadow .1s linear;
	    height: 36px;
	    align-items: center;
	    box-sizing: border-box;
	    padding: 0 15px;
	    overflow: hidden;
	    border-radius: 3px;
	}
</style>

<script type="text/javascript">
	jQuery('#supplier_addnew').on('submit',function(e){
		e.preventDefault();
		let formData = new FormData(this);
		let url = jQuery(this).attr('action');
	    jQuery.ajax({
	         type: 'POST',
	         url: url,
	         data: formData,
	         processData: false,
	         contentType: false,
	         success: function(response) {
	         	if(response == 'success'){
	         		window.location.href = jQuery('#supplier_addnew input[name="_wp_http_referer"]').val();
	         	}else{
	         		alert("error.");
	         	}
	    		return false;
	         },
	        error: function() {
	            alert("There was an error submitting comment");
	    		return false;
	        }
	     });

	    return false;
	});
</script>
