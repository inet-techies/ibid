<?php
class Session_encryption
{
	public function get_ses($get_prm)
	{
		$inps = base64_decode($get_prm);
		//echo "<pre>"; print_r($inps); exit;
		$splt_ar = explode('@$',$inps);
		$key = $splt_ar[1];
		
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
		$encrypted = openssl_encrypt($inps, 'aes-256-cbc', $key, 0, $iv);
  		return base64_encode($encrypted . '::' . $iv);
  
		/*$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB); 
		
		$input = $this->pkcs5_pad($inps, $size);
		
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, ''); 
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND); 
		mcrypt_generic_init($td, $key, $iv);
		$data = mcrypt_generic($td, $input); 
		mcrypt_generic_deinit($td); 
		mcrypt_module_close($td); 
		$result = base64_encode($data);
		return $result;*/
	}
	
	public function unset_ses($get_v,$s_rndm)
	{
		//echo "<pre>"; print_r($get_v); exit;
		list($encrypted_data, $iv) = explode('::', base64_decode($get_v), 2);
    	return openssl_decrypt($encrypted_data, 'aes-256-cbc', $s_rndm, 0, $iv);
	
		/*$payload = base64_decode($get_v);
		$key = $s_rndm;
		//echo "<pre>"; print_r($key);
		
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, ''); 
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND); 
		mcrypt_generic_init($td, $key, $iv); 
		$decrypted = mdecrypt_generic($td, $payload); 
		mcrypt_generic_deinit($td); 
		mcrypt_module_close($td); 
		
		$inputt = $this->pkcs5_unpad($decrypted); 
		return $inputt;*/
	}
	
	function pkcs5_pad($text, $blocksize) 
	{ 
		$pad = $blocksize - (strlen($text) % $blocksize); 
		return $text . str_repeat(chr($pad), $pad); 
	}
	
	function pkcs5_unpad($text) 
	{
		$block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $pad = ord($text[($len = strlen($text)) - 1]);
        $len = strlen($text);
        $pad = ord($text[$len-1]);
        return substr($text, 0, strlen($text) - $pad);
	}
}
?>