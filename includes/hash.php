<?php
if(!defined('HVZ')) die(-1);
/**
 * Hash Library v3.0hvz3
 * Copyright (c) 2009-2010 Ryan Schmidt
 */

class Password {
	static function crypt( $password, $uid ) {
		global $keys;
		$hash = ':';
		
		$salt = self::salt( 16 );
		$a = self::hashOrder( $uid ); 
		
		$hash_algos = hash_algos();
		$algos = array();
		//only use algorithms deemed "secure"
		foreach( $hash_algos as $algo ) {
			switch( $algo ) {
				case 'sha512':
					$algos[] = array( $a[0], 'sha512' );
					break;
				case 'ripemd160':
					$algos[] = array( $a[1], 'ripemd160' );
					break;
				case 'ripemd320':
					$algos[] = array( $a[2], 'ripemd320' );
					break;
				case 'whirlpool':
					$algos[] = array( $a[3], 'whirlpool' );
					break;
				case 'gost':
					$algos[] = array( $a[4], 'gost' );
					break;
				case 'tiger192,4':
					$algos[] = array( $a[5], 'tiger192,4' );
					break;
				case 'haval256,5':
					$algos[] = array( $a[6], 'haval256,5' );
					break;
				case 'sha256':
					$algos[] = array( $a[7], 'sha256' );
					break;
				case 'sha384':
					$algos[] = array( $a[8], 'sha384' );
					break;
				case 'ripemd128':
					$algos[] = array( $a[9], 'ripemd128' );
					break;
				case 'ripemd256':
					$algos[] = array( $a[10], 'ripemd256' );
					break;
			}
		}
		
		$r1 = rand( 0, count( $algos ) - 1 );
		$r2 = rand( 0, count( $algos ) - 1 );
		$type = $algos[$r1][0] . $algos[$r2][0];
		$pw1 = hash_hmac( $algos[$r2][1], $salt . '-' . hash_hmac( $algos[$r1][1], $password, $keys[0] ), $keys[1] );
		$size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC );
		$ksize = mcrypt_get_key_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC );
		$iv = mcrypt_create_iv( $size, MCRYPT_RAND );
		$key = substr( $keys[2], 0, $ksize - 1 );
		$pw2 = mcrypt_encrypt( MCRYPT_RIJNDAEL_256, $key, $pw1, MCRYPT_MODE_CBC, $iv );
		$pwf = rib64_encode( gzcompress( rib64_encode( $pw2 ) . '|' . rib64_encode( $iv ) ) );
		$hash .= $type . ':' . $salt . ':' . $pwf;
		
		// sometimes the mcrypt is invalid, so we need to do a quick check to make sure that comparing will work in the future
		// otherwise the password won't work... and that would suck
		$hash = self::recursiveCryptCheck( $hash, $password, $uid );
		return $hash;
	}

	static function compare( $hash, $password, $uid ) {
		global $keys;
		$bits = explode( ':', $hash, 4 );
		
		$type1 = substr( $bits[1], 0, 1 );
		$type2 = substr( $bits[1], 1, 1 );
		$salt = $bits[2];
		$hash1 = $bits[3];
		
		$a = self::hashOrder( $uid );
		$algos = array(
			$a[0] => 'sha512',
			$a[1] => 'ripemd160',
			$a[2] => 'ripemd320',
			$a[3] => 'whirlpool',
			$a[4] => 'gost',
			$a[5] => 'tiger192,4',
			$a[6] => 'haval256,5',
			$a[7] => 'sha256',
			$a[8] => 'sha384',
			$a[9] => 'ripemd128',
			$a[10] => 'ripemd256',
		);
		//check for b/c to update hash keys
		$alpha = array( 'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5, 'G' => 6, 'H' => 7, 'I' => 8, 'K' => 9, 'L' => 10 );
		$oldalgos = array(
			'A' => 'sha512',
			'B' => 'ripemd160',
			'C' => 'ripemd320',
			'D' => 'whirlpool',
			'E' => 'gost',
			'F' => 'tiger192,4',
			'G' => 'haval256,5',
			'H' => 'sha256',
			'I' => 'sha384',
			'K' => 'ripemd128',
			'L' => 'ripemd256',
		);
		$pw = hash_hmac( $algos[$type2], $salt . '-' . hash_hmac( $algos[$type1], $password, $keys[0] ), $keys[1] );
		$pw2 = @hash_hmac( $oldalgos[$type2], $salt . '-' . @hash_hmac( $oldalgos[$type1], $password, $keys[0] ), $keys[1] );
		$h1 = gzuncompress( rib64_decode( $hash1 ) );
		$ksize = mcrypt_get_key_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC );
		$key = substr( $keys[2], 0, $ksize - 1 );
		$bits = explode( '|', $h1 );
		$iv = rib64_decode( $bits[1] );
		$h2 = rib64_decode( $bits[0] );
		$hf = mcrypt_decrypt( MCRYPT_RIJNDAEL_256, $key, $h2, MCRYPT_MODE_CBC, $iv );
		
		if( $pw2 === $hf ) {
			//update the db
			global $db;
			$hash = self::crypt($password, $uid);
			$db->update( 'users', array( 'password' => $hash ), array( 'uin' => $uid ) );
			return true;
		}
		
		return ( $pw === $hf );
	}

	static function recursiveCryptCheck( $hash, $password, $uid = 7 ) {
		if( !@self::compare( $hash, $password, $uid ) ) {
			$hash = self::crypt( $password, $uid );
			return self::recursiveCryptCheck( $hash, $password, $uid );
		}
		return $hash;
	}

	static function salt( $len = 8 ) {
		$salt = '';
		while( strlen( $salt ) < $len ) {
			$salt .= chr( mt_rand( 65, 126 ) );
		}
		return $salt;
	}
	
	static function hashOrder( $id ) {
		if( $id > 999999 ) {
			$id = substr( $id, 0, 6 );
		}
		$o = ( $id ^ 3 ) * 573 + 9001;
		$s = str_split( $o );
		//don't let stuff be 0, and don't let [3] be 9
		if( $s[1] == 0 ) $s[1] = 1;
		if( $s[2] == 0 ) $s[2] = 3;
		if( $s[3] == 0 ) $s[3] = 5;
		if( $s[3] == 9 ) $s[3] = 7;
		$r = array();
		$c = 0;
		$n = 64;
		//generate the arrays
		while( true ) {
			$n += $s[0];
			if( $n > 90 ) $n -= 26;
			while( in_array( chr( $n ), $r) ) $n = ( $n == 90 ) ? 65 : $n + 1;
			$r[] = chr( $n );
			if( ++$c == 26 ) break;
			$n -= $s[1];
			if( $n < 65 ) $n += 26;
			while( in_array( chr( $n ), $r) ) $n = ( $n == 90 ) ? 65 : $n + 1;
			$r[] = chr( $n );
			if( ++$c == 26 ) break;
			$n += 2 * $s[2];
			if( $n > 90 ) $n -= 26;
			while( in_array( chr( $n ), $r) ) $n = ( $n == 90 ) ? 65 : $n + 1;
			$r[] = chr( $n );
			if( ++$c == 26 ) break;
			$n -= $s[0];
			if( $n < 65 ) $n += 26;
			while( in_array( chr( $n ), $r) ) $n = ( $n == 90 ) ? 65 : $n + 1;
			$r[] = chr( $n );
			if( ++$c == 26 ) break;
			$n += $s[1];
			if( $n > 90 ) $n -= 26;
			while( in_array( chr( $n ), $r) ) $n = ( $n == 90 ) ? 65 : $n + 1;
			$r[] = chr( $n );
			if( ++$c == 26 ) break;
			$n -= 3 * $s[3];
			if( $n < 65 ) $n += 26;
			while( in_array( chr( $n ), $r) ) $n = ( $n == 90 ) ? 65 : $n + 1;
			$r[] = chr( $n );
			if( ++$c == 26 ) break;
			$n -= 2 * $s[1];
			if( $n < 65 ) $n += 26;
			while( in_array( chr( $n ), $r) ) $n = ( $n == 90 ) ? 65 : $n + 1;
			$r[] = chr( $n );
			if( ++$c == 26 ) break;
		}
		return $r;
	}
}
