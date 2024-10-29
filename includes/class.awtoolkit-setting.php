<?php

function awtoolbox_control_menu() {
    add_submenu_page( 'options-general.php', 'AW Toolbox', 'AW Toolbox', 'manage_options', 'awtoolbox', 'awtoolbox_dashboard');
}
add_action( 'admin_menu', 'awtoolbox_control_menu' );



function awtoolbox_dashboard(){
	if(isset($_POST['action']) && $_POST['action'] == 'updating-awtoolkit'){
	    update_option('aw_woo_prod_sales_count', isset($_POST['aw_woo_prod_sales_count']) ? $_POST['aw_woo_prod_sales_count'] : '', 'yes');
	    update_option('aw_woo_prod_dimensions', isset($_POST['aw_woo_prod_dimensions']) ? $_POST['aw_woo_prod_dimensions'] : '', 'yes');
	    update_option('aw_woo_past_order_count', isset($_POST['aw_woo_past_order_count']) ? $_POST['aw_woo_past_order_count'] : '', 'yes');
	    update_option('aw_woo_utm', isset($_POST['aw_woo_utm']) ? $_POST['aw_woo_utm'] : '', 'yes');
	    update_option('aw_woo_msp', isset($_POST['aw_woo_msp']) ? $_POST['aw_woo_msp'] : '', 'yes');
	    update_option('aw_maint_mode', isset($_POST['aw_maint_mode']) ? $_POST['aw_maint_mode'] : '', 'yes');
	    update_option('aw_maint_title', isset($_POST['aw_maint_title']) ? $_POST['aw_maint_title'] : 'Website coming soon!', 'yes');
	    update_option('aw_maint_message', isset($_POST['aw_maint_message']) ? $_POST['aw_maint_message'] : '', 'yes');
	    update_option('aw_maint_exceptions', isset($_POST['aw_maint_exceptions']) ? $_POST['aw_maint_exceptions'] : '', 'yes');
	    if(isset($_POST['replace_find']) && $_POST['replace_find']){
	        if(isset($_POST['replace_accept']) && $_POST['replace_accept']){
	            $replace_msg = awDbFindReplace($_POST['replace_table'], $_POST['replace_find'], $_POST['replace_replace']);
	        }else{
	            $replace_msg = 'Acknowledge warning before running a database replace';
	        }
	    }
	}
	
	global $wpdb;
	$db_tables = $wpdb->get_results(
            "show tables" , ARRAY_A
        );
	
	include('settings-template.php');
}


function awDbFindReplace($table, $find, $replace){
    global $wpdb;
    $table = esc_sql( $table );

	$find 	= str_replace( '#BSR_BACKSLASH#', '\\', $find );
	$replace 	= str_replace( '#BSR_BACKSLASH#', '\\', $replace );

	$primary_key = '';
	$columns = [];
	$changes = 0;
	$errors = [];
	$ci = false;
	
	$fields = $wpdb->get_results( 'DESCRIBE ' . $table );
    
	if ( is_array( $fields ) ) {
		foreach ( $fields as $column ) {
			$columns[$column->Field] = $column->Type;
			if ( $column->Key == 'PRI' ) {
				$primary_key = $column->Field;
			}
		}
	}

	if ($primary_key == '') {
	    return 'No primary key in table';
	}

    $data = $wpdb->get_results( "SELECT * FROM `$table`" , ARRAY_A );
    
	foreach ( $data as $row ) {
		$update_sql = array();
		$where_sql 	= array();
		
      
		foreach( $columns as $column => $type) {

			$text = $row[$column];

			if ( $column == $primary_key ) {
				$where_sql[] = $column . ' = "' .  aw_mysql_escape_mimic( $text ) . '"';
				continue;
			}


			if ( $wpdb->options === $table ) {

				if ( '_transient_bsr_results' === $text || 'bsr_profiles' === $text || 'aw_update_site_url' === $text || 'bsr_data' === $text ) {
					continue;
				}

				if ( $text =='siteurl'){//do this last
					$edited_data 	= aw_recursive_unserialize_replace( $find, $replace, $text, false, $ci );
					if ( $edited_data != $text ) {
						update_option( 'aw_update_site_url', $edited_data );
						continue;
					}
				}
			}

			// Run a search replace on the data that'll respect the serialisation.
			$edited_data = aw_recursive_unserialize_replace( $find, $replace, $text, false, $ci );

			// Something was changed
			if ( $edited_data != $text ) {
				$update_sql[] = $column . ' = "' . aw_mysql_escape_mimic( $edited_data ) . '"';
				$changes++;
			}

		}

		// Determine what to do with updates.
		if ( $changes && ! empty( $where_sql ) && ! empty( $update_sql ) ) {
			// If there are changes to make, run the query.
			$sql 	= 'UPDATE ' . $table . ' SET ' . implode( ', ', $update_sql ) . ' WHERE ' . implode( ' AND ', array_filter( $where_sql ) );
			$result = $wpdb->query( $sql );
            //echo($sql);die();
            
			if ( ! $result ) {
				//error handling
			}

		}

	} // end row loop

	if($site_url = get_option( 'aw_update_site_url')){
		update_option( 'siteurl', $site_url);
		delete_option( 'aw_update_site_url' );
		$changes++;
	}


	$wpdb->flush();
		
	return 'Find Replace Success, '.$changes.' changes';
}


    /**
     * Below functions adapted from listed sources
     * Adapted from https://wordpress.org/plugins/better-search-replace/
	 * Adapated from https://interconnectit.com/products/search-and-replace-for-wordpress-databases/
	 *
	 * Take a serialised array and unserialise it replacing elements as needed and
	 * unserialising any subordinate arrays and performing the replace on those too.
	 *
	 * @access private
	 * @param  string 			$from       		String we're looking to replace.
	 * @param  string 			$to         		What we want it to be replaced with
	 * @param  array  			$data       		Used to pass any subordinate arrays back to in.
	 * @param  boolean 			$serialised 		Does the array passed via $data need serialising.
	 * @param  sting|boolean 	$ci 	Set to 'on' if we should ignore case, false otherwise.
	 *
	 * @return string|array	The original array with all elements replaced as needed.
	 */
function aw_recursive_unserialize_replace( $from = '', $to = '', $data = '', $serialised = false, $ci = false ) {
	try {

		if ( is_string( $data ) && ! is_serialized_string( $data ) && ( $unserialized = awunserialize( $data ) ) !== false ) {
			$data = aw_recursive_unserialize_replace( $from, $to, $unserialized, true, $ci );
		}
        elseif ( is_array( $data ) ) {
			$_tmp = array( );
			foreach ( $data as $key => $value ) {
				$_tmp[ $key ] = aw_recursive_unserialize_replace( $from, $to, $value, false, $ci );
			}
			
			$data = $_tmp;
			unset( $_tmp );
		}
		// Submitted by Tina Matter
		elseif ( is_object( $data ) ) {
			// $data_class = get_class( $data );
			$_tmp = $data; // new $data_class( );
			$props = get_object_vars( $data );
			foreach ( $props as $key => $value ) {
				$_tmp->$key = aw_recursive_unserialize_replace( $from, $to, $value, false, $ci );
			}

			$data = $_tmp;
			unset( $_tmp );
		}
		elseif ( is_serialized_string( $data ) ) {
			if ( $data = awunserialize( $data ) !== false ) {
				$data = aw_str_replace( $from, $to, $data, $ci );
				$data = serialize( $data );
			}
		}
		else {
			if ( is_string( $data ) ) {
				$data = aw_str_replace( $from, $to, $data, $ci );
			}
		}

	    if ( $serialised ) {
		    return serialize( $data );
	    }

	} catch( Exception $ex ) {
        //echo($ex->getMessage());die();
	}

	return $data;
}


function awunserialize( $serialized_string ) {
	if ( ! is_serialized( $serialized_string ) ) {
		return false;
	}

	$serialized_string   = trim( $serialized_string );
	$unserialized_string = @unserialize( $serialized_string );

	return $unserialized_string;
}
	
function aw_str_replace( $from, $to, $text, $ci = false ) {
    if ($ci ) {
        return str_ireplace( $from, $to, $text );
    }
    return str_replace( $from, $to, $text );
}

function aw_mysql_escape_mimic( $input ) {
	if ( is_array( $input ) ) {
	    return array_map( __METHOD__, $input );
	}
	if ( ! empty( $input ) && is_string( $input ) ) {
	    return str_replace( array( '\\', "\0", "\n", "\r", "'", '"', "\x1a" ), array( '\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z' ), $input );
	}

 return $input;
}
