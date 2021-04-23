<?php
/** miniOrange enables user to log in using LDAP credentials.
    Copyright (C) 2015  miniOrange

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
* @package 		miniOrange OAuth
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
/**
This class contains all the utility functions

**/
class Mo_Ldap_Local_Util{

	public static function is_customer_registered() {
		$email 			= get_option('mo_ldap_local_admin_email');
		$customerKey 	= get_option('mo_ldap_local_admin_customer_key');
		if( ! $email || ! $customerKey || ! is_numeric( trim( $customerKey ) ) ) {
			return 0;
		} else {
			return 1;
		}
	}


    public static function generateRandomString($length = 8) {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $crypto_rand_secure = function ( $min, $max ) {
            $range = $max - $min;
            if ( $range < 0 ) return $min; // not so random...
            $log    = log( $range, 2 );
            $bytes  = (int) ( $log / 8 ) + 1; // length in bytes
            $bits   = (int) $log + 1; // length in bits
            $filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1
            do {
                $rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
                $rnd = $rnd & $filter; // discard irrelevant bits
            } while ( $rnd >= $range );
            return $min + $rnd;
        };

        $token = "";
        $max   = strlen( $pool );
        for ( $i = 0; $i < $length; $i++ ) {
            $token .= $pool[$crypto_rand_secure( 0, $max )];
        }
        return $token;
    }

	public static function check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}
	
	public static function encrypt($str) {
		if(!Mo_Ldap_Local_Util::is_extension_installed('openssl')) {
			return;
		}
		$key = get_option('mo_ldap_local_customer_token');
		$method = 'AES-128-ECB';
		//$ivSize = openssl_cipher_iv_length($method);
		//$iv     = openssl_random_pseudo_bytes($ivSize);
		$strCrypt = openssl_encrypt ($str, $method, $key,OPENSSL_RAW_DATA||OPENSSL_ZERO_PADDING);
		return base64_encode($strCrypt);
	}

	public static function decrypt($value) {
		if(!Mo_Ldap_Local_Util::is_extension_installed('openssl')) {
			return;
		}
		$strIn = base64_decode($value);
		$key = get_option('mo_ldap_local_customer_token');
		$method = 'AES-128-ECB';
		$ivSize = openssl_cipher_iv_length($method);
		//$iv     = substr($strIn,0,$ivSize);
		$data   = substr($strIn,$ivSize);
		$clear  = openssl_decrypt ($data, $method, $key, OPENSSL_RAW_DATA||OPENSSL_ZERO_PADDING);

		return $clear;
	}
		
	public static function is_curl_installed() {
		if  (in_array  ('curl', get_loaded_extensions())) {
			return 1;
		} else 
			return 0;
	}
	
	public static function is_extension_installed($name) {
		if  (in_array  ($name, get_loaded_extensions())) {
			return true;
		}
		else {
			return false;
		}
	}
}
?>