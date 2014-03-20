<?php
if(!defined('HVZ')) die(-1);
/**
 * String Library v3.0
 * Copyright (c) 2009-2010 Ryan Schmidt
 */

/**
 * Encryption
 * Step 1 -- rib64 encode the string
 * Step 2 -- encyrpt the string using AES 256 and CBC mode with a random key and iv
 * Step 3 -- rib64 encode the key, iv, and encrypted string, then append them in that order seperated by |
 * Step 4 -- compress the string using zlib at maximum compression
 * Step 5 -- rib64 encode the compressed string
 */
function encodeString( $str ) {
	$size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC );
	$ksize = mcrypt_get_key_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC );
	srand();
	$iv = mcrypt_create_iv( $size, MCRYPT_RAND );
	$kh1 = hash( 'ripemd160', $str );
	$kh2 = md5( $str );
	$kh3 = sha1( $str );
	$key = substr( $kh1 . $kh2 . $kh3, 0, $ksize - 1 );
	$es1 = rib64_encode( $str );
	$es2 = mcrypt_encrypt( MCRYPT_RIJNDAEL_256, $key, $es1, MCRYPT_MODE_CBC, $iv );
	$es3 = rib64_encode( $key ) . '|' . rib64_encode( $iv ) . '|' . rib64_encode( $es2 );
	$es4 = gzcompress( $es3, 9 );
	$es5 = rib64_encode( $es4 );
	if( $str != @decodeString( $es5 ) ) {
		return encodeString( $str );
	} else {
		return $es5;
	}
}

/**
 * Decryption
 * Step 1 -- rib64 decode the string
 * Step 2 -- uncompress the string using zlib
 * Step 3 -- split the string into three parts (key, iv, encrypted string) along the | character
 * Step 4 -- rib64 decode each piece
 * Step 5 -- decrypt the string using AES 256 and mode CBC with the key and iv given
 * Step 6 -- rib64 decode the string
 */
function decodeString( $str ) {
	$ds1 = rib64_decode( $str );
	$ds2 = gzuncompress( $ds1 );
	$parts = explode( '|', $ds2, 3 );
	$key = rib64_decode( $parts[0] );
	$iv = rib64_decode( $parts[1] );
	$ds3 = rib64_decode( $parts[2] );
	$ds4 = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, $key, $ds3, MCRYPT_MODE_CBC, $iv ), "\x00" );
	$ds5 = rib64_decode( $ds4 );
	return $ds5;
}
