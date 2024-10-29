<?php
/*
 * Templete name: Supplier List
 * Version: 1.0.0
 * Templete path: /templete/supplier_list.php
 * Supplier List for admin
 *
*/
	if( isset($_GET['paged']) ){
		$paged = (int) $_GET['paged'];
	}else{
		$paged = 1;
	}
	// $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

	$args = array( 
		'post_type' => 'supplier',
		'posts_per_page'=> 20,
        'paged' => $paged
	);

	$the_query = new WP_Query( $args );
?>
<style type="text/css">
	
	.updated{
		padding: 10px;
	}
	.text-right{
		text-align: right;
	}
</style>
<div class="wrap supplier">
	<h1 class="wp-heading-inline">Suppliers</h1>
	<a href="edit.php?post_type=product&page=manage-suppliers&action=addnew" class="page-title-action">Add New</a>
	<?php if(isset($_GET['message'])): ?>
	<div class="updated"><?=ucfirst(esc_attr($_GET['message']))?></div>
	<?php endif; ?>
	<hr class="wp-header-end">
	<table class="wp-list-table supplier-table widefat fixed striped" style="margin-top: 15px;">
		<thead>
			<tr>
				<td>Name</td>
				<td>Info</td>
				<td></td>
			</tr>
		</thead>
		<tbody>
		<?php
			if ( $the_query->have_posts() ) {
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
		?>
			<tr>
				<td>
					<div class="title">
						<strong>
							<a href="<?php echo site_url() ?>/wp-admin/edit.php?post_type=product&page=manage-suppliers&action=edit&post_id=<?=get_the_ID()?>"><?php ucfirst(the_title()) ?></a>
						</strong>
					</div>
				</td>
				<td><?php echo get_post_meta(get_the_ID(),'_supplier_info',false)[0]; ?></td>
				<td>
					<a class="page-title-action" href="<?php echo site_url() ?>/wp-admin/edit.php?post_type=product&page=manage-suppliers&action=edit&post_id=<?=get_the_ID()?>">Edit</a> 
					<a class="page-title-action" href="<?php echo site_url() ?>/wp-admin/edit.php?post_type=product&page=manage-suppliers&action=delete&post_id=<?=get_the_ID()?>" onclick="return confirmDelete()">Delete</a>
				</td>
			</tr>
		<?php }
		
	}else{ ?>
			<tr>
				<td colspan="3">No data</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<div class="text-right">
        <?php
        	$total_page=$the_query->max_num_pages;
            if($total_page > 1){
            	echo '<ul style="list-style: none;float: right;">';
            	for ($i=1; $i <= $total_page; $i++) { 
            		$link_page = 'edit.php?post_type=product&page=manage-suppliers&paged='.$i;
            		if($paged != $i){
	            		echo '<li style="float: left;"><a href="'.$link_page.'" style="display: block;padding: 0 5px;">'.$i.'</a></li>';
            		}else{
            			echo '<li style="float: left;"><a href="#" style="display: block;padding: 0 5px;color: #000;">'.$i.'</a></li>';
            		}
            	}
            	echo "</ul>";
            }
        ?>
    </div>
</div>
<?php wp_reset_postdata();?>
<script type="text/javascript">
	function confirmDelete(){
		if(confirm('You want delete?')){
			return true;
		}
		return false;
	}
</script>
