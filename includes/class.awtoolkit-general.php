<?php

class AW_Toolkit {

	public function __construct() {
	}

	public function enableFlexibleSSL() {
	    //check not on SSL, if full ssl ths isn't needed
		if ( !(function_exists( 'is_ssl' ) && is_ssl()) ) {
		    $headers = array( 'HTTP_CF_VISITOR', 'HTTP_X_FORWARDED_PROTO' );
		    foreach ( $headers as $key ) {
		        //check if CF origin is SSL, if so need to tell WP we are on SSL
			    if ( isset( $_SERVER[ $key ] ) && ( strpos( $_SERVER[ $key ], 'https' ) !== false ) ) {
				    $_SERVER[ 'HTTPS' ] = 'on';
			        add_action( 'shutdown', array( $this, 'forceLoadFirst' ) );
				    break;
			    }
		    }
		}
	}

	public function forceLoadFirst() {
		$active_plugins = get_option( is_multisite() ? 'active_sitewide_plugins' : 'active_plugins' );
		$pos = -1;
		if ( is_array( $active_plugins ) ) {
			$pos = array_search( 'awcode-toolkit/aw-toolkit.php', $active_plugins );
			if ( $pos === false ) {
				$pos = -1;
			}
		}
		if ( $pos > 1 ) {
			$active_plugins = $this->moveToStartofArray( get_option( 'active_plugins' ), 'awcode-toolkit/aw-toolkit.php' );
		    update_option( 'active_plugins', $active_plugins );

		    if ( is_multisite() ) {
			    $active_plugins = $this->moveToStartofArray( get_option( 'active_sitewide_plugins' ), 'awcode-toolkit/aw-toolkit.php');
			    update_option( 'active_sitewide_plugins', $active_plugins );
		    }
		}
	}


	private function moveToStartofArray($array, $val) {
		if ( !is_array( $array ) ) {return $array;}

		$maxpos = count( $array ) - 1;
		
		$pos = array_search( $val, $array );
		if ( $pos !== false && $pos != 0 ) {

			unset( $array[ $pos ] );
			$array = array_values( $array );

			array_splice( $array, 0, 0, $val );
		}

		return $array;
	}
}

