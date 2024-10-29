<?php
if(!get_option('aw_woo_msp')) return ;

//add add_submenu_page suppliers to product
function awtoolkit_suppliers_per_product(  ) {
    if(!get_option('aw_woo_msp')) return ;
   	add_submenu_page(
        'edit.php?post_type=product',
        __( 'Suppliers' ),
        __( 'Suppliers' ),
        'manage_woocommerce', // Required user capability
        'manage-suppliers', // page name
        'awtoolkit_manage_suppliers'  // function 
    );
}
add_action( 'admin_menu', 'awtoolkit_suppliers_per_product', 100 );

function awtoolkit_manage_suppliers() {
    if( !isset($_GET['action']) ){
        echo supplier_get_template('templete','supplier_list');
    }elseif($_GET['action'] == 'delete'){
        $meta_value = get_post_meta( $_GET['post_id'],'_supplier_info',false)[0];
        wp_delete_post( $_GET['post_id'], true);
        delete_metadata( 'supplier', $_GET['post_id'], '_supplier_info', $meta_value );
        echo '<script>window.location.href = "edit.php?post_type=product&page=manage-suppliers&message=deleted";</script>';
    }else{
        echo supplier_get_template('templete','supplier_add');
    }
}

function awtoolkit_add_custom_supplier() {
    if(!get_option('aw_woo_msp')) return ;
    $args = array(
        'label' => __( 'Supplier', 'woocommerce' ),
        'placeholder' => __( 'Enter Supplier here', 'woocommerce' ),
        'id' => '_supplier',
        'desc_tip' => true,
        'description' => __( 'This Supplier is for internal use only.', 'woocommerce' ),
    );
    //<div style="width: 50%;">new supplier</div>
    woocommerce_wp_text_input( $args );
}
add_action( 'woocommerce_product_options_inventory_product_data', 'awtoolkit_add_custom_supplier' );

add_action( 'woocommerce_product_quick_edit_end', function(){
    if(!get_option('aw_woo_msp')) return ;
    /*
    Notes:
    Take a look at the name of the text field, '_custom_field_demo', that is the name of the custom field, basically its just a post meta
    The value of the text field is blank, it is intentional
    */

    ?>
    <div class="custom_field_supplier">
        <br class="clear">
        <label class="alignleft">
            <span class="title"><?php _e('Supplier', 'woocommerce' ); ?></span>
            <span class="input-text-wrap _supplier_field">
                <input type="text" id="_supplier" name="_supplier" class="text" autocomplete="off" placeholder="<?php _e( 'Enter Supplier here', 'woocommerce' ); ?>" value="">
            </span>
        </label>
        <br class="clear">
    </div>
    <script type="text/javascript">
        jQuery(function(){
            jQuery('#the-list').on('click', '.editinline', function(){

                /**
                 * Extract metadata and put it as the value for the custom field form
                 */
                inlineEditPost.revert();

                var post_id = jQuery(this).closest('tr').attr('id');

                post_id = post_id.replace("post-", "");

                var $cfd_inline_data = jQuery('#custom_field_supplier_inline_' + post_id),
                    $wc_inline_data = jQuery('#woocommerce_inline_' + post_id );

                jQuery('input[name="_supplier"]', '.inline-edit-row').val($cfd_inline_data.find("#_custom_field_supplier").text());


                /**
                 * Only show custom field for appropriate types of products (simple)
                 */
                var product_type = $wc_inline_data.find('.product_type').text();
                console.log(product_type);
                if (product_type=='simple' || product_type=='external') {
                    jQuery('.custom_field_demo', '.inline-edit-row').show();
                } else {
                    jQuery('.custom_field_demo', '.inline-edit-row').hide();
                }

            });
        });
    </script>
    <?php

});

add_action('woocommerce_product_quick_edit_save', function($product){
    if(!get_option('aw_woo_msp')) return ;
    /*
    Notes:
    $_REQUEST['_custom_field_demo'] -> the custom field we added above
    Only save custom fields on quick edit option on appropriate product types (simple, etc..)
    Custom fields are just post meta
    */

    if ( $product->is_type('simple') || $product->is_type('external') ) {

        $post_id = $product->id;

        if ( isset( $_REQUEST['_supplier_id'] ) && $_REQUEST['_supplier_id'] != '') {
            $supplier_id = trim(esc_attr( $_REQUEST['_supplier_id'] ));
            $_supplier = trim(esc_attr( $_REQUEST['_supplier'] ));
            update_post_meta( $post_id, '_supplier_id', wc_clean( $supplier_id ) ); // Do sanitation and Validation here
            update_post_meta( $post_id, '_supplier', wc_clean( $_supplier ) ); // Do sanitation and Validation here
        }else if( !empty($_REQUEST['_supplier']) ){
            global $wpdb;
            $query = "SELECT ID , post_title FROM $wpdb->posts WHERE post_type='supplier' AND post_title='".$_REQUEST['_supplier']."'";
            $result = $wpdb->get_results( $query, ARRAY_A );
            if( count($result) == 0 ){
                $supplier_id = wp_insert_post(array (
                   'post_type' => 'supplier',
                   'post_title' => $_REQUEST['_supplier'],
                   'post_content' => '',
                   'post_status' => 'publish',
                   'comment_status' => 'closed',   // if you prefer
                   'ping_status' => 'closed',      // if you prefer
                ));
                if ($supplier_id) {
                   add_post_meta($supplier_id, '_supplier_info', ''); // insert post meta
                }
            }else{
                $supplier_id = $result[0]['ID'];
            }
            
            $_supplier_id = trim(esc_attr( $supplier_id ));
            $_supplier = trim(esc_attr( $_REQUEST['_supplier'] ));
            update_post_meta( $post_id, '_supplier_id', wc_clean( $_supplier_id ) ); // Do sanitation and Validation here
            update_post_meta( $post_id, '_supplier', wc_clean( $_supplier ) ); // Do sanitation and Validation here
        }
    }

}, 10, 1);

add_action( 'manage_product_posts_custom_column', function($column,$post_id){
    if(!get_option('aw_woo_msp')) return ;
    /*
    Notes:
    The 99 is just my OCD in action, I just want to make sure this callback gets executed after WooCommerce's
    */

    switch ( $column ) {
        case 'name' :

            ?>
            <div class="hidden custom_field_supplier_inline" id="custom_field_supplier_inline_<?php echo $post_id; ?>">
                <div id="_custom_field_supplier"><?php echo get_post_meta($post_id,'_supplier',true); ?></div>
            </div>
            <?php

            break;

        default :
            break;
    }

}, 99, 2);

add_action( 'admin_footer', '_supplier_quick_edit_javascript' );

/**
 * Write javascript function to set checked to headline news checkbox
 *
 * @return void
 */
function _supplier_quick_edit_javascript() {
    global $current_screen;
 
    if ( 'product' != $current_screen->post_type ) {
        return;
    }
?>
    <script type="text/javascript">
        jQuery(function(){
            if(jQuery('#_supplier').length > 0){
                jQuery('#_supplier').attr('autocomplete','off');

                jQuery("#_supplier").focusout(function(){
                    if(jQuery('._supplier_field .auto-complete').length > 0){
                        // jQuery('._supplier_field .auto-complete').hide();
                    }
                });
                jQuery("#_supplier_bulk").focusout(function(){
                    if(jQuery('._supplier_field_bulk .auto-complete').length > 0){
                        // jQuery('._supplier_field_bulk .auto-complete').hide();
                    }
                });
            }
        });
        jQuery('#_supplier').on('keyup',function(){
            
            jQuery.ajax({
                url: '<?php echo site_url() ?>/wp-admin/admin-ajax.php',
                data:{
                    'action': 'check_supplier',
                    '_supplier': jQuery(this).val()
                },
                type: 'POST', // POST
                dataType: 'json',
                beforeSend:function(xhr){},
                success:function(data){
                    // console.log(jQuery('._supplier_field .auto-complete').length);
                    if(jQuery('._supplier_field .auto-complete').length > 0){
                        jQuery('._supplier_field .auto-complete').remove();
                    }
                    if(jQuery('._supplier_field .alert-new-sup').length > 0){
                        jQuery('._supplier_field .alert-new-sup').remove();
                    }
                    if(jQuery('._supplier_field ._supplier_id').length > 0){
                        jQuery('._supplier_field ._supplier_id').val('');
                    }
                    if(data == 'no_data'){
                        if(jQuery('._supplier_field .auto-complete').length > 0){
                            jQuery('._supplier_field .auto-complete').remove();
                        }
                        if(jQuery('._supplier_field .alert-new-sup').length > 0){
                            jQuery('._supplier_field .alert-new-sup').remove();
                        }
                        if(jQuery('._supplier_field ._supplier_id').length > 0){
                            jQuery('._supplier_field ._supplier_id').val('');
                        }
                    }else if(data != '' && data != 'no_data'){
                        let list = '';
                        list += '<ul class="auto-complete" style="width: 50%;max-height: 200px;overflow: auto;margin-block-start: 6px;clear: left;">';
                        for (var i = 0; i < data.length; i++) {
                            list += '<li style="padding: 5px;border: 1px solid #7e8993;padding: 0px 5px;margin-bottom: 0;cursor: pointer;" onclick="selectSupplier('+data[i].ID+',\''+data[i].post_title+'\')">'+data[i].post_title+'</li>';
                        }
                        list += '</ul>';

                        jQuery('._supplier_field').append(list);
                        if(jQuery('.custom_field_supplier ._supplier_field .auto-complete').length > 0){
                            jQuery('.custom_field_supplier ._supplier_field .auto-complete').css('width','99%');
                        }
                    }else{
                        jQuery('._supplier_field').append('<div class="alert-new-sup" style="width: 50%;color: green;">New supplier</div>');
                    }
                }
            });
        });

            jQuery('#_supplier_bulk').on('keyup',function(){
            
            jQuery.ajax({
                url: '<?php echo site_url() ?>/wp-admin/admin-ajax.php',
                data:{
                    'action': 'check_supplier',
                    '_supplier': jQuery(this).val()
                },
                type: 'POST', // POST
                dataType: 'json',
                beforeSend:function(xhr){},
                success:function(data){
                    // console.log(jQuery('._supplier_field .auto-complete').length);
                    if(jQuery('._supplier_field_bulk .auto-complete').length > 0){
                        jQuery('._supplier_field_bulk .auto-complete').remove();
                    }
                    if(jQuery('._supplier_field_bulk .alert-new-sup').length > 0){
                        jQuery('._supplier_field_bulk .alert-new-sup').remove();
                    }
                    if(jQuery('._supplier_field_bulk ._supplier_id').length > 0){
                        jQuery('._supplier_field_bulk ._supplier_id').val('');
                    }
                    if(data == 'no_data'){
                        if(jQuery('._supplier_field_bulk .auto-complete').length > 0){
                            jQuery('._supplier_field_bulk .auto-complete').remove();
                        }
                        if(jQuery('._supplier_field_bulk .alert-new-sup').length > 0){
                            jQuery('._supplier_field_bulk .alert-new-sup').remove();
                        }
                        if(jQuery('._supplier_field_bulk ._supplier_id').length > 0){
                            jQuery('._supplier_field_bulk ._supplier_id').val('');
                        }
                    }else if(data != '' && data != 'no_data'){
                        let list = '';
                        list += '<ul class="auto-complete" style="width: 50%;max-height: 200px;overflow: auto;margin-block-start: 6px;clear: left;margin-top: 0;">';
                        for (var i = 0; i < data.length; i++) {
                            list += '<li style="padding: 5px;border: 1px solid #7e8993;padding: 0px 5px;margin-bottom: 0;cursor: pointer;" onclick="selectSupplierBulk('+data[i].ID+',\''+data[i].post_title+'\')">'+data[i].post_title+'</li>';
                        }
                        list += '</ul>';

                        jQuery('._supplier_field_bulk').append(list);
                        if(jQuery('._supplier_field_bulk .auto-complete').length > 0){
                            jQuery('._supplier_field_bulk .auto-complete').css('width','99%');
                        }
                    }else{
                        jQuery('._supplier_field_bulk').append('<div class="alert-new-sup" style="width: 50%;color: green;">New supplier</div>');
                    }
                }
            });

            
        });

        function selectSupplier(id, name){
            jQuery('#_supplier').val(name);
            if(jQuery('._supplier_field ._supplier_id').length > 0){
                jQuery('._supplier_field ._supplier_id').val(id);
            }else{
                jQuery('._supplier_field').append('<input type="hidden" class="_supplier_id" name="_supplier_id" value="'+id+'">');
            }
            jQuery('._supplier_field .auto-complete').remove();
        }
        function selectSupplierBulk(id, name){
            jQuery('#_supplier_bulk').val(name);
            if(jQuery('._supplier_field_bulk ._supplier_id').length > 0){
                jQuery('._supplier_field_bulk ._supplier_id').val(id);
            }else{
                jQuery('._supplier_field_bulk').append('<input type="hidden" class="_supplier_id" name="_supplier_id" value="'+id+'">');
            }
            jQuery('._supplier_field_bulk .auto-complete').remove();
        }

    </script>
<?php
}

add_action('wp_ajax_check_supplier', '_supplier_filter_function'); // wp_ajax_{ACTION HERE}
add_action('wp_ajax_nopriv_check_supplier', '_supplier_filter_function');

function _supplier_filter_function(){
    if( $_POST['_supplier'] != ''){
        global $wpdb;
        $query = "SELECT ID , post_title FROM $wpdb->posts WHERE post_type='supplier' AND post_title like '%".$_POST['_supplier']."%'";
        
        $result = $wpdb->get_results( $query, ARRAY_A );
        if(count($result) == 0){
            echo json_encode('');
        }else{
            echo json_encode($result);
        }
       
    }else{
        echo json_encode('no_data');
    }
    
    die();
};

function supplier_get_template( $subfolder, $file ) {
    $real_file = $file . '.php';

    $theme_template = plugin_dir_path( __DIR__ ). '/' . $subfolder . '/' . $real_file;
    if( $theme_template ) {
        require $theme_template;
    }
}

function supplier_add_new(){
    $verify_nonce = wp_verify_nonce( $_POST['_wpnonce'], "add-new-supplier" );
    if(!$verify_nonce){
        echo "0";
        die();
    }
    if( !isset($_POST['post_id']) ){
        $post_id = wp_insert_post(array (
           'post_type' => 'supplier',
           'post_title' => $_POST['name'],
           'post_content' => '',
           'post_status' => 'publish',
           'comment_status' => 'closed',   // if you prefer
           'ping_status' => 'closed',      // if you prefer
        ));
        if ($post_id) {
           // insert post meta
           add_post_meta($post_id, '_supplier_info', $_POST['info']);
        }
    }else{
        $my_post = array(
          'ID'           => $_POST['post_id'],
          'post_title'   => $_POST['name'],
        );
        // Update the post into the database
        wp_update_post( $my_post );
        update_post_meta( $_POST['post_id'], '_supplier_info', $_POST['info'] );
    }

    echo 'success';
    die();
}

add_action('wp_ajax_supplierAddNew', 'supplier_add_new' ); // executed when logged in
add_action('wp_ajax_nopriv_supplierAddNew', 'supplier_add_new' ); // executed when logged out

function mp_sync_on_product_save( $post_id, $post, $update ) {
    if( isset($_REQUEST['_supplier_id']) && $_REQUEST['_supplier_id'] != '' ){
        $supplier_id = trim(esc_attr( $_REQUEST['_supplier_id'] ));
        $_supplier = trim(esc_attr( $_REQUEST['_supplier'] ));
        update_post_meta( $post_id, '_supplier_id', wc_clean( $supplier_id ) ); // Do sanitation and Validation here
        update_post_meta( $post_id, '_supplier', wc_clean( $_supplier ) ); // Do sanitation and Validation here
    }else if( !empty($_REQUEST['_supplier']) ){
        global $wpdb;
        $query = "SELECT ID , post_title FROM $wpdb->posts WHERE post_type='supplier' AND post_title='".$_REQUEST['_supplier']."'";
        $result = $wpdb->get_results( $query, ARRAY_A );
        if(count($result) == 0){
            $supplier_id = wp_insert_post(array (
               'post_type' => 'supplier',
               'post_title' => $_REQUEST['_supplier'],
               'post_content' => '',
               'post_status' => 'publish',
               'comment_status' => 'closed',   // if you prefer
               'ping_status' => 'closed',      // if you prefer
            ));
            if ($supplier_id) {
               add_post_meta($supplier_id, '_supplier_info', ''); // insert post meta
            }
        }else{
            $supplier_id = $result[0]['ID'];
        }
        
        $_supplier_id = trim(esc_attr( $supplier_id ));
        $_supplier = trim(esc_attr( $_REQUEST['_supplier'] ));
        update_post_meta( $post_id, '_supplier_id', wc_clean( $_supplier_id ) ); // Do sanitation and Validation here
        update_post_meta( $post_id, '_supplier', wc_clean( $_supplier ) ); // Do sanitation and Validation here
    }else if( empty($_REQUEST['_supplier']) ){
        update_post_meta( $post_id, '_supplier_id', '' ); // Do sanitation and Validation here
        update_post_meta( $post_id, '_supplier', '' ); // Do sanitation and Validation here
    }
}
add_action('save_post_product', 'mp_sync_on_product_save', 10, 3);

add_filter( 'manage_edit-product_columns', '_admin_products_supplier_column' );
 
function _admin_products_supplier_column( $columns ){
   $columns['supplier'] = 'Supplier';
   return $columns;
}
 
add_action( 'manage_product_posts_custom_column', '_admin_products_supplier_column_content', 10, 2 );
 
function _admin_products_supplier_column_content( $column, $product_id ){
    if ( $column == 'supplier' ) {
        $value = get_post_meta( $product_id, '_supplier', true );
        $text = !empty( $value ) ? esc_attr( $value ) : '';
        echo ucfirst($text);
    }
}

// Add meta box
add_action( 'add_meta_boxes', '_supplier_order_box' );
function _supplier_order_box() {
    add_meta_box(
        '_supplier-modal',
        'Suppliers',
        '_supplier_order_box_callback',
        'shop_order',
        'side'
    );
}

// Callback
function _supplier_order_box_callback( $post )
{
    $order = wc_get_order( $post->ID );
    $supplier = [];
    $supplier_item = [];
    foreach ( $order->get_items() as $item_id => $item ) {
        $value = get_post_meta( $item->get_product_id(), '_supplier', true );
        $text = !empty( $value ) ? ucfirst(esc_attr( $value )) : 'Unknown';
        if(!in_array($text, $supplier)){
            $supplier[] = $text;
        }
        $supplier_item[$text][] = $item->get_quantity() . ' x ' . $item->get_name();
    }

    foreach ($supplier as $key => $value) {
        echo "<h4 style='margin-bottom: 5px;'>".$value."</h4>";
        echo '<ul style="list-style: none;padding-left: 15px;margin-top: 5px;">';
        foreach ($supplier_item[$value] as $k => $val) {
            echo '<li>'.$val.'</li>';
        }
         echo '</ul>';
    }
    
}

// Add a custom field to product bulk edit special page
add_action( 'woocommerce_product_bulk_edit_start', '_supplier_field_product_bulk_edit', 10, 0 );
function _supplier_field_product_bulk_edit() {
    ?>
        <div class="inline-edit-group">
            <label class="alignleft">
                <span class="title"><?php _e('Supplier', 'woocommerce'); ?></span>
                <span class="input-text-wrap">
                    <select class="change_supplier change_to" name="change_supplier">
                    <?php
                        $options = array(
                            ''  => __( '— No change —', 'woocommerce' ),
                            '1' => __( 'Change to:', 'woocommerce' ),
                        );
                        foreach ( $options as $key => $value ) {
                            echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                        }
                    ?>
                    </select>
                </span>
            </label>
            <label class="change-input">
                <span class="input-text-wrap _supplier_field_bulk">
                    <input type="text" name="_supplier" id="_supplier_bulk" class="text _supplier" autocomplete="off" placeholder="<?php _e( 'Enter Supplier here', 'woocommerce' ); ?>" value="" />
                </span>
            </label>
        </div>
    <?php
}

// Save the custom fields data when submitted for product bulk edit
add_action('woocommerce_product_bulk_edit_save', 'save_supplier_field_product_bulk_edit', 10, 1);
function save_supplier_field_product_bulk_edit( $product ){
    if ( $product->is_type('simple') || $product->is_type('external') ){
        $product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : $product->id;

        if ( isset( $_REQUEST['_supplier_id'] ) ){
            // update_post_meta( $product_id, '_supplier', sanitize_text_field( $_REQUEST['_supplier'] ) );
            $supplier_id = trim(esc_attr( $_REQUEST['_supplier_id'] ));
            $_supplier = trim(esc_attr( $_REQUEST['_supplier'] ));
            update_post_meta( $product_id, '_supplier_id', wc_clean( $supplier_id ) ); // Do sanitation and Validation here
            update_post_meta( $product_id, '_supplier', wc_clean( $_supplier ) ); // Do sanitation and Validation here

        }else if( !empty($_REQUEST['_supplier']) ){
            global $wpdb;
            $query = "SELECT ID , post_title FROM $wpdb->posts WHERE post_type='supplier' AND post_title='".$_REQUEST['_supplier']."'";
            $result = $wpdb->get_results( $query, ARRAY_A );
            if(count($result) == 0){
                $supplier_id = wp_insert_post(array (
                   'post_type' => 'supplier',
                   'post_title' => $_REQUEST['_supplier'],
                   'post_content' => '',
                   'post_status' => 'publish',
                   'comment_status' => 'closed',   // if you prefer
                   'ping_status' => 'closed',      // if you prefer
                ));
                if ($supplier_id) {
                   add_post_meta($supplier_id, '_supplier_info', ''); // insert post meta
                }
            }else{
                $supplier_id = $result[0]['ID'];
            }
            
            $_supplier_id = trim(esc_attr( $supplier_id ));
            $_supplier = trim(esc_attr( $_REQUEST['_supplier'] ));
            update_post_meta( $product_id, '_supplier_id', wc_clean( $_supplier_id ) ); // Do sanitation and Validation here
            update_post_meta( $product_id, '_supplier', wc_clean( $_supplier ) ); // Do sanitation and Validation here
        }else if( empty($_REQUEST['_supplier']) ){
            update_post_meta( $post_id, '_supplier_id', '' ); // Do sanitation and Validation here
            update_post_meta( $post_id, '_supplier', '' ); // Do sanitation and Validation here
        }
    }
}

add_action('restrict_manage_posts','location_filtering',10);

function location_filtering($post_type){

    if('product' !== $post_type){
      return; //filter your post
    }

    $selected = '';
    $request_attr = 'supplier';
    if ( isset($_REQUEST[$request_attr]) ) {
      $selected = $_REQUEST[$request_attr];
    }
    //get unique values of the meta field to filer by.
    $meta_key = '_supplier';
    global $wpdb;
    $results = $wpdb->get_col( 
        $wpdb->prepare( "
            SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = '%s'
            AND p.post_status IN ('publish', 'draft')
            ORDER BY pm.meta_value", 
            $meta_key
        ) 
    );

   //build a custom dropdown list of values to filter by
    echo '<select id="supplier-filter" name="supplier">';
    echo '<option value="0">' . __( 'Filter by supplier', 'supplier-plugin' ) . ' </option>';
    foreach($results as $supplier){
        if(!empty($supplier)){
          $select = ($supplier == $selected) ? ' selected':'';
          echo '<option value="'.$supplier.'"'.$select.'>' . $supplier . ' </option>';
        }
    }
    $select = ('unknown' == $selected) ? ' selected':'';
    echo '<option '.$select.' value="unknown">' . __( 'Unknown', 'supplier-plugin' ) . ' </option>';
    echo '</select>';
}

add_filter( 'parse_query', 'supplier_filter_request_query' , 10);
function supplier_filter_request_query($query){

    //we want to modify the query for the targeted custom post and filter option
    if( (!isset($query->query['post_type']) || 'product' != $query->query['post_type']) && !isset($_GET['supplier']) ){
        return $query;
    }

    //modify the query only if it admin and main query.
    if( !is_admin() && !$query->is_main_query() ){ 
        return $query;
    }

    //for the default value of our filter no modification is required
    if( isset($_GET['supplier']) && $_GET['supplier'] == '0' ){
        return $query;
    }

    if( isset($_GET['supplier']) ){
       //modify the query_vars.
        if( $_GET['supplier'] != 'unknown' ){
            $query->query_vars['meta_key'] = '_supplier';
            $query->query_vars['meta_value'] = $_GET['supplier'];
            $query->query_vars['meta_compare'] = '=';
        }else{
            $meta_query = array(
                array(
                    'key' => '_supplier',
                    'value' => '',
                    'compare' => '='
                ),
                array(
                    'key' => '_supplier',
                    'compare' => 'NOT EXISTS'
                ),
                'relation'    => 'OR',
            );
            $query->set( 'meta_query', $meta_query );
            // $query->query_vars['meta_key'] = '_supplier';
            // $query->query_vars['meta_value'] = '';
            // $query->query_vars['meta_compare'] = 'NOT EXISTS';
        }
    }
// die(print_r($query->query_vars));
    return $query;
}
