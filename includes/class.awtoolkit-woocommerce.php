<?php


//add order count on products
add_action( 'manage_product_posts_custom_column', 'awtoolkit_product_order_count_column', 10, 2 );
function awtoolkit_product_order_count_column( $column, $postid ) {
    if(!get_option('aw_woo_prod_sales_count')) return ;
    if ( $column == 'lifesales' ) {
        $sales = awtoolkit_get_total_sales_per_product( $postid );
	    echo $sales;
    }
}

add_filter( 'manage_edit-product_columns', 'awtoolkit_add_order_count_columns_to_product_grid', 15, 1 );
function awtoolkit_add_order_count_columns_to_product_grid( $aColumns ) {
    if(get_option('aw_woo_prod_sales_count')){
        $aColumns['lifesales'] = '# Sold';
    }
    return $aColumns;
}

add_filter( 'manage_edit-product_sortable_columns', 'awtoolkit_order_count_sortable_column' );
function awtoolkit_order_count_sortable_column( $columns ) {
    if(get_option('aw_woo_prod_sales_count')){
        $columns['lifesales'] = 'lifesales';
    }

    return $columns;
}

//add dimensions on products
add_action( 'manage_product_posts_custom_column', 'awtoolkit_product_dimensions_column', 10, 2 );
function awtoolkit_product_dimensions_column( $column, $postid ) {
    if(!get_option('aw_woo_prod_dimensions')) return ;
    if ( $column == 'dimensions' ) {
        $sales = awtoolkit_get_dimensions_per_product( $postid );
	    echo $sales;
    }
}

add_filter( 'manage_edit-product_columns', 'awtoolkit_add_dimensions_columns_to_product_grid', 15, 1 );
function awtoolkit_add_dimensions_columns_to_product_grid( $aColumns ) {
    if(get_option('aw_woo_prod_dimensions')){
        $aColumns['dimensions'] = 'Size';
    }
    return $aColumns;
}

add_filter( 'manage_edit-product_sortable_columns', 'awtoolkit_dimensions_sortable_column' );
function awtoolkit_dimensions_sortable_column( $columns ) {
    if(get_option('aw_woo_prod_dimensions')){
        $columns['dimensions'] = 'dimensions';
    }

    return $columns;
}



//orders show count and UTM
add_action( 'manage_shop_order_posts_custom_column', 'awtoolkit_order_count_column',  20, 2 );
function awtoolkit_order_count_column( $column, $order_id ) {
    if($column == 'order_number'){
        if(get_option('aw_woo_past_order_count')){
            // $order = new WC_Order( $order_id );
            $user_id = get_post_meta($order_id, '_customer_user', true);
            if($user_id){
                $customer_orders = wc_get_customer_order_count($user_id);
                echo(' <span title="'.$customer_orders.' Total orders">('.$customer_orders.')</span>');
            }
        }
        if(get_option('aw_woo_utm')){
            $utm = @json_decode(get_post_meta($order_id, '_utm', true), true);
            if(is_array($utm)){
                foreach($utm as $k=>$v){
                    if($v && $k !='landing'){
                        echo('<br><small title="'.str_replace(array('"',"'"),'',$v).'" style="max-height:2em;display:inline-block;overflow:hidden;max-width: 100%;word-break: break-all;">UTM '.$k.': '.$v.'</small>');
                    }
                }
            }
        }
    }
}



add_filter( 'posts_orderby', 'awtoolkit_order_count_orderby', 25, 2 );
function awtoolkit_order_count_orderby($orderby_statement, $wp_query ) {
    global $wpdb;
    if( ! is_admin() ){return $orderby_statement;}
    if(!get_option('aw_woo_prod_sales_count') && !get_option('aw_woo_prod_dimensions')) return ;
    
    $orderby = $wp_query->get( 'orderby');

    if($wp_query->get("post_type") == 'product' && 'lifesales' == $orderby) {
        //$query->set('meta_key','lifesales');
        //$query->set('orderby','meta_value_num');
	    return "(SELECT SUM( order_item_meta.meta_value ) FROM {$wpdb->prefix}woocommerce_order_items as order_items 
		    LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
		    LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id
	        LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID

	        WHERE posts.post_type = 'shop_order'
	        AND posts.post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold' )
	        AND order_items.order_item_type = 'line_item'
	        AND order_item_meta.meta_key = '_qty'
	        AND order_item_meta_2.meta_key = '_product_id'
	        AND order_item_meta_2.meta_value = {$wpdb->prefix}posts.ID
		    GROUP BY order_item_meta_2.meta_value) ".$wp_query->get('order');
    }elseif($wp_query->get("post_type") == 'product' && 'dimensions' == $orderby) {
	    return "(SELECT meta_value FROM {$wpdb->prefix}postmeta  as meta
	        WHERE meta.meta_key = '_length'
	        AND  meta.post_id = {$wpdb->prefix}posts.ID) ".$wp_query->get('order');
    }else{
	    return $orderby_statement;
    }
}


function awtoolkit_get_total_sales_per_product($product_id ='', $sumNotCount=false) { 
    global $wpdb;

    $post_status = array( 'wc-completed', 'wc-processing', 'wc-on-hold' );
    if($sumNotCount){$sel = 'SUM( order_item_meta_3.meta_value )';}else{$sel = 'SUM( order_item_meta.meta_value )';}

    $order_items = $wpdb->get_row( $wpdb->prepare(" SELECT ".$sel." as val FROM {$wpdb->prefix}woocommerce_order_items as order_items

	LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
	LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id
	LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_3 ON order_items.order_item_id = order_item_meta_3.order_item_id
	LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID

	WHERE posts.post_type = 'shop_order'                   
	AND posts.post_status IN ( '".implode( "','",  $post_status  )."' )
	AND order_items.order_item_type = 'line_item'
	AND order_item_meta.meta_key = '_qty'
	AND order_item_meta_2.meta_key = '_product_id'
	AND order_item_meta_2.meta_value = %s
	AND order_item_meta_3.meta_key = '_line_total'

	GROUP BY order_item_meta_2.meta_value
	", $product_id));

    return $order_items->val;
}

function awtoolkit_get_dimensions_per_product($product_id ='', $sumNotCount=false) { 
    global $wpdb;

    $meta = get_post_meta($product_id);
    $width = '';
    $length = '';
    $height = '';
    $weight = '';
    foreach($meta as $k=>$v){
        if($k == '_length'){$length = $v[0];}
        elseif($k == '_width'){$width = $v[0];}
        elseif($k == '_height'){$height = $v[0];}
        elseif($k == '_weight'){$weight = $v[0];}
    }
    if($length || $width){
        return "<strong>L</strong> ".$length." <strong>W</strong> ".$width." <strong>H</strong> ".$height." <strong>KG</strong> ".$weight." ";
    }elseif($weight){
    
    }
    return '';
}

add_action( 'init', 'awtoolkit_utm_tracking' );
function awtoolkit_utm_tracking() {
    if(!get_option('aw_woo_utm')) return ;
    if(isset($_COOKIE['wp_utm']) && isset($_GET['utm_source'])) {//safety check if utm cookie set but blank. Debugging why not always saved
        $old_utm = awextract_utm_array();
        if(isset($old_utm['source']) && !$old_utm['source']){
            unset($_COOKIE['wp_utm']);
        }
    }
    if(!isset($_COOKIE['wp_utm'])) {
        if(isset($_GET['utm_source']) || isset($_GET['utm_medium']) || isset($_GET['utm_campaign']) || isset($_GET['utm_term']) || isset($_GET['utm_content']) || isset($_SERVER['HTTP_REFERER'])){
            $utm = [];
            $utm['source'] = isset($_GET['utm_source']) ? $_GET['utm_source'] : '';
            $utm['medium'] = isset($_GET['utm_medium']) ? $_GET['utm_medium'] : '';
            $utm['campaign'] = isset($_GET['utm_campaign']) ? $_GET['utm_campaign'] : '';
            $utm['term'] = isset($_GET['utm_term']) ? $_GET['utm_term'] : '';
            $utm['content'] = isset($_GET['utm_content']) ? $_GET['utm_content'] : '';
            $utm['referer'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $utm['landing'] = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $utm_str = base64_encode(json_encode($utm));
     
            setrawcookie('wp_utm', $utm_str , time()+(3600*24*90) );
        }
    }
}

add_filter('woocommerce_checkout_create_order','awtoolkit_add_utm_data', 20, 2);
function awtoolkit_add_utm_data($order, $data)
{
    if(isset($_COOKIE['wp_utm']))
    {
//        if(!get_option('aw_woo_utm')) return ;
        
        $utm = awextract_utm_array();
        $order->update_meta_data( '_utm',  base64_decode( str_replace('%3D', '=', $_COOKIE['wp_utm'])));
        if(is_array($utm)){
            if(isset($utm['source']) && $utm['source']){ $order->update_meta_data( '_utm_source',  $utm['source']);}
            if(isset($utm['medium']) && $utm['medium']){ $order->update_meta_data( '_utm_medium',  $utm['medium']);}
            if(isset($utm['campaign']) && $utm['campaign']){ $order->update_meta_data( '_utm_campaign',  $utm['campaign']);}
            if(isset($utm['term']) && $utm['term']){ $order->update_meta_data( '_utm_term',  $utm['term']);}
            if(isset($utm['content']) && $utm['content']){ $order->update_meta_data( '_utm_content',  $utm['content']);}
            if(isset($utm['referer']) && $utm['referer']){ $order->update_meta_data( '_utm_referer',  $utm['referer']);}
            if(isset($utm['landing']) && $utm['landing']){ $order->update_meta_data( '_utm_landing',  $utm['landing']);}
        }
    }else{
	    $order->update_meta_data( '_utm',  serialize($_COOKIE));
    }
}

function awextract_utm_array(){
    $str = str_replace('%3D', '=', $_COOKIE['wp_utm']);//had issue with url encoding making base64 fail
    return @json_decode(base64_decode($_COOKIE['wp_utm']), true);
}
