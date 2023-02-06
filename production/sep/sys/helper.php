<?php

// defined('BASEPATH') OR exit('No direct script access allowed');

// ------------------------------------------------------------------------

if ( ! function_exists('createBarcode'))
{
	function createBarcode($value)
	{
		$CI =& get_instance();
		//load library
		$CI->load->library('zend');
		//load in folder Zend
		$CI->zend->load('Zend/Barcode');
		//generate barcode
		Zend_Barcode::render('code128', 'image', 
			array('text' => $value, 
				'drawText' => false, 
				'barHeight' => 40, 
				'withQuietZones' => false,
				//'barThickWidth' => 4.9,
				'barThinWidth' => 2
			), 
			array('imageType' => 'png')
		);
	}
}

if ( ! function_exists('loginData'))
{
	function loginData($x)
	{
		session_start();
		$a = $_SESSION['SIKITA'];
		$aa = unserialize($a);
		return $aa[1]['Silahkan Menunggu'][$x];
	}
}

if ( ! function_exists('nice_date'))
{
	function nice_date($date, $format )
	{
		$new_date = date_create($date);
		return date_format($new_date, $format);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('date_db'))
{
	function date_db($bad_date = null, $format = FALSE)
	{
		if (!empty($bad_date)){
			$b = date($bad_date);		
			return date('Y-m-d', strtotime($b));
		}
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('lenght'))
{
	function lenght($val)
	{
		return strlen((string) $val);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('create_id'))
{
  // $this->load->helper('string');
	function create_id()
	{	
		$unix = now().random_string('alnum', 16);
		return md5($unix);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('durasi'))
{
	function durasi($prev , $start)
	{	
		$prev = new DateTime($prev,new DateTimeZone('+7'));
		$start = new DateTime($start,new DateTimeZone('+7'));
		$since_start = $start->diff($prev);
		$since_start->days.' days total<br>';
		$tahun = $since_start->y.' years';
		$bulan = $since_start->m.' months';
		$hari = $since_start->d.' days';
		$jam = $since_start->h.' Jam ';
		$menit = $since_start->i.' Mnt ';
		$detik = $since_start->s.' Dtk ';

		return $jam.$menit.$detik; 
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('durasiPerjalanan'))
{
	function durasiPerjalanan($prev , $start)
	{	
		$prev = new DateTime($prev,new DateTimeZone('+7'));
		$start = new DateTime($start,new DateTimeZone('+7'));
		$since_start = $start->diff($prev);
		$since_start->days.' days total';
		$tahun = $since_start->y.' Thn ';
		$bulan = $since_start->m.' Bln ';
		$hari = $since_start->d.' Hari ';
		$jam = $since_start->h.' Jam ';
		$menit = $since_start->i.' Mnt ';
		$detik = $since_start->s.' Dtk ';

		return $tahun.$bulan.$hari; 
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('durasiDetik'))
{
	function durasiDetik($prev , $start)
	{	
		$prev = new DateTime($prev,new DateTimeZone('+7'));
		$start = new DateTime($start,new DateTimeZone('+7'));
		$since_start = $start->diff($prev);
		$since_start->days.' days total<br>';
		$tahun = $since_start->y.' years<br>';
		$bulan = $since_start->m.' months<br>';
		$hari = $since_start->d.' days<br>';
		$jam = $since_start->h.' Jam ';
		$menit = $since_start->i.' Mnt ';
		//$detik = $since_start->s.' Dtk ';

		$detik = $since_start->days * 24 * 60;
		$detik += $since_start->h * 60;
		$detik += $since_start->i * 60;
		$detik += $since_start->s;

		return $detik; 
	}
}

// ------------------------------------------------------------------------

// Encryption
function mc_encrypt($data, $key) {
	/// make binary representasion of $key
	$key = hex2bin($key);
	/// check key length, must be 256 bit or 32 bytes
	if (mb_strlen($key, "8bit") !== 32) {
		throw new Exception("Needs a 256-bit key!");
	}
	/// create initialization vector
	$iv_size = openssl_cipher_iv_length("aes-256-cbc");
	$iv = openssl_random_pseudo_bytes($iv_size); // dengan catatan dibawah
	/// encrypt
	$encrypted = openssl_encrypt($data,"aes-256-cbc",$key,OPENSSL_RAW_DATA,$iv);
	/// create signature, against padding oracle attacks
	$signature = mb_substr(hash_hmac("sha256",$encrypted,$key,true),0,10,"8bit"); 
	/// combine all, encode, and format
	$encoded = chunk_split(base64_encode($signature.$iv.$encrypted));
	return $encoded;
}

// Decryption
function mc_decrypt($str, $strkey){
	/// make binary representation of $key
	$key = hex2bin($strkey);
	/// check key length, must be 256 bit or 32 bytes
	if (mb_strlen($key, "8bit") !== 32) {
		throw new Exception("Needs a 256-bit key!");
	}
	/// calculate iv size
	$iv_size = openssl_cipher_iv_length("aes-256-cbc");
	/// breakdown parts
	$decoded = base64_decode($str);
	$signature = mb_substr($decoded,0,10,"8bit");
	$iv = mb_substr($decoded,10,$iv_size,"8bit");
	$encrypted = mb_substr($decoded,$iv_size+10,NULL,"8bit");
	/// check signature, against padding oracle attack
	$calc_signature = mb_substr(hash_hmac("sha256",$encrypted,$key,true),0,10,"8bit"); 
	if(!mc_compare($signature,$calc_signature)) {
		return "SIGNATURE_NOT_MATCH"; /// signature doesn't match
	}
	$decrypted = openssl_decrypt($encrypted,"aes-256-cbc",$key,OPENSSL_RAW_DATA,$iv);
	return $decrypted;
}

/// Compare Function
function mc_compare($a, $b) {
	/// compare individually to prevent timing attacks
	/// compare length
	if (strlen($a) !== strlen($b)) return false;
	/// compare individual
	$result = 0;
	for($i = 0; $i < strlen($a); $i ++) {
		$result |= ord($a[$i]) ^ ord($b[$i]);
	}
	return $result == 0;
}