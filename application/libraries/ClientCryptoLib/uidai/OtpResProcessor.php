<?php
require_once(__DIR__ . '/kyc/OtpRes.php');

class OtpResProcessor {

	public function parse($xml) {
		$decryptedOtpResponse = simplexml_load_string($xml); 
		$otpRes = new OtpRes();
		$otpRes->ret = (string)$decryptedOtpResponse['ret'];
		$otpRes->code = (string)$decryptedOtpResponse['code'];
		$otpRes->txn = (string)$decryptedOtpResponse['txn'];
		$otpRes->err = (string)$decryptedOtpResponse['err'];
		$otpRes->info = (string)$decryptedOtpResponse['info'];
		$otpRes->ts = (string)$decryptedOtpResponse['ts'];

		return $otpRes;
	}
}
?>