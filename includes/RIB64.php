<?php
if(!defined('HVZ')) die(-1);
/**
 * RIB64 Encoding
 * Copyright (c) 2009-2010 Ryan Schmidt
 */

class RIB64 {
	var $str, $enc, $charset;
	const STR_ROTATE_RIGHT = 1;
	const STR_ROTATE_LEFT = 2;

	function __construct() {
		$this->str = '';
		$this->enc = '';
		$this->charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
	}

	function encode( $str ) {
		$bits = str_split( $str, 3 );
		$encoded = '';
		// work on 3 bytes at a time
		foreach( $bits as $bit ) {
			$hex = '';
			$pieces = str_split( $bit );
			foreach( $pieces as $piece ) {
				if( $piece === '' ) {
					continue;
				}
				// convert decimal ascii values to hex
				$dec = dechex( ord( $piece ) );
				if( strlen( $dec ) === 1 ) {
					$dec = '0' . $dec;
				}
				$hex .= $dec;
			}
			// reverse the string
			$rev = strrev( $hex );
			// split every 2 characters
			$chunks = str_split( $rev, 2 );
			$bin = '';
			foreach( $chunks as $chunk ) {
				if( $chunk === '' ) {
					$encoded .= substr( $this->charset, 0, 1 ) . '===';
					$bin = '';
					continue;
				}
				// convert hex to binary, going through decimal first
				$dec = str_pad( hexdec( $chunk ), 3, '0', STR_PAD_LEFT );
				$bin .= str_pad( decbin( $dec ), 8, '0', STR_PAD_LEFT );
				if( strlen( $bin ) == 24 ) {
					$encoded .= $this->getChar( $bin );
					$bin = '';
				}
			}
			$encoded .= $this->getChar( $bin );
		}
		return $encoded;
	}

	function decode( $enc ) {
		$bits = str_split( $enc );
		$bitsr = array_reverse( $bits );
		$decoded = '';
		if( substr( $enc, -3 ) == '===' ) {
			$numeq = 3;
			array_shift( $bitsr );
			array_shift( $bitsr );
			array_shift( $bitsr );
			array_pop( $bits );
			array_pop( $bits );
			array_pop( $bits );
		} elseif( substr( $enc, -2 ) == '==' ) {
			$numeq = 2;
			array_shift( $bitsr );
			array_shift( $bitsr );
			array_pop( $bits );
			array_pop( $bits );
		} elseif( substr( $enc, -1 ) == '=' ) {
			$numeq = 1;
			array_shift( $bitsr );
			array_pop( $bits );
		} else {
			$numeq = 0;
		}
		$bits[] = '=';
		$i = 0;
		$bin = '';
		$hex = '';
		foreach( $bits as $bit ) {
			if( $bit != '=' ) {
				$padded = str_pad( decbin( strpos( $this->charset, $bit ) ), 6, '0', STR_PAD_LEFT );
				$bin .= $this->rotate( $padded, ++$i, self::STR_ROTATE_LEFT );
				if( strlen( $bin ) == 24 ) {
					$i = 0;
					$chunks = str_split( $bin, 8 );
					foreach( $chunks as $chunk ) {
						$hex .= str_pad( dechex( bindec( $chunk ) ), 2, '0', STR_PAD_LEFT );
					}
					$bin = '';
				}
			} else {
				if( strlen( $bin ) == 24 && $numeq === 0 ) {
					$i = 0;
					$chunks = str_split( $bin, 8 );
					foreach( $chunks as $chunk ) {
						$hex .= str_pad( dechex( bindec( $chunk ) ), 2, '0', STR_PAD_LEFT );
					}
					$bin = '';
				} elseif( strlen( $bin ) == 18 && $numeq === 1 ) {
					$i = 0;
					$chunks = str_split( $bin, 8 );
					foreach( $chunks as $chunk ) {
						if( strlen( $chunk ) == 2 )
							continue;
						$hex .= str_pad( dechex( bindec( $chunk ) ), 2, '0', STR_PAD_LEFT );
					}
					$bin = '';
				} elseif( strlen( $bin ) == 12 && $numeq === 2 ) {
					$i = 0;
					$chunks = str_split( $bin, 8 );
					foreach( $chunks as $chunk ) {
						if( strlen( $chunk ) == 4 )
							continue;
						$hex .= str_pad( dechex( bindec( $chunk ) ), 2, '0', STR_PAD_LEFT );
					}
					$bin = '';
				} elseif( strlen( $bin ) == 6 && $numeq === 3 ) {
					// empty string
					$hex .= '';
					$bin = '';
				}
			}
		}
		$revarr = str_split( $hex, 6 );
		$rev = '';
		foreach( $revarr as $revstr ) {
			$rev .= strrev( $revstr );
		}
		$chars = str_split( $rev, 2 );
		foreach( $chars as $char ) {
			if( $char === '' ) {
				continue;
			}
			$decoded .= chr( hexdec( $char ) );
		}
		return $decoded;
	}

	function rotate( $str, $num, $type = self::STR_ROTATE_RIGHT ) {
		while( $num > strlen( $str ) ) {
			$num = $num - strlen( $str );
		}
		if( $num < 1 || !is_int( $num ) ) {
			return $str;
		}
		if( $type == self::STR_ROTATE_RIGHT ) {
			return substr( $str, -$num ) . substr( $str, 0, strlen( $str ) - $num );
		} elseif( $type == self::STR_ROTATE_LEFT ) {
			return substr( $str, $num ) . substr( $str, 0, $num );
		} else {
			return $str;
		}
	}

	function getChar( $bin ) {
		switch( strlen( $bin ) ) {
			case 0:
				return '';
			case 8:
				$bin = str_pad( $bin, 12, '0', STR_PAD_RIGHT );
				$extra = '==';
				break;
			case 16:
				$bin = str_pad( $bin, 18, '0', STR_PAD_RIGHT );
				$extra = '=';
				break;
			case 24:
				$extra = '';
				break;
			default:
				return ''; // invalid string size
		}
		$binarr = str_split( $bin, 6 );
		$ret = '';
		$i = 0;
		foreach( $binarr as $binstr ) {
			$ret .=  substr( $this->charset, bindec( $this->rotate( $binstr, ++$i, self::STR_ROTATE_RIGHT ) ), 1 );
		}
		return $ret . $extra;
	}
}

$rib64_cache = null;

function rib64_encode( $str ) {
	global $rib64_cache;
	if( !$rib64_cache instanceOf RIB64 ) {
		$rib64_cache = new RIB64();
	}
	return $rib64_cache->encode( $str );
}

function rib64_decode( $str ) {
	global $rib64_cache;
	if( !$rib64_cache instanceOf RIB64 ) {
		$rib64_cache = new RIB64();
	}
	return $rib64_cache->decode( $str );
}
