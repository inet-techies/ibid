<?php
require_once(__DIR__ . '/auth/AuthRes.php');

class AuthResProcessor {

	public function parse($xml) {
		$decryptedAuthResponse = simplexml_load_string($xml); 
		$authRes = new AuthRes();
		$authRes->ret = (string)$decryptedAuthResponse['ret'];
		$authRes->code = (string)$decryptedAuthResponse['code'];
		$authRes->txn = (string)$decryptedAuthResponse['txn'];
		$authRes->err = (string)$decryptedAuthResponse['err'];
		$authRes->info = (string)$decryptedAuthResponse['info'];
		$authRes->ts = (string)$decryptedAuthResponse['ts'];
		$authRes->actn = (string)$decryptedAuthResponse['actn'];

		return $authRes;
	}
}
?>