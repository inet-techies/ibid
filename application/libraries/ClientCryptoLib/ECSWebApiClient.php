<?php
require_once(__DIR__ . '/Verhoeff.php');
require_once(__DIR__ . '/ECSWebApiResponse.php');
require_once(__DIR__ . '/ECSWebApiResponseData.php');

function prepareRequestData($id, $aadhaarNumber, $clientId, $action, $actionType, $udc, $udf1, $udf2, $udf3, $maxRetry, $gatewayCertificate, $signPfx, $signPfxPassword) {

	//Validate input parameters
	if (empty($id)) {
		throw new Exception('Id cannot be empty');
	}

	/*if (empty($aadhaarNumber)) {
		throw new Exception('AadhaarNumber cannot be empty');
	}

	if (strlen($aadhaarNumber) != 12) {
		throw new Exception('Invalid Aadhaar Number');
	}

	if(!Verhoeff::validate($aadhaarNumber)) {
		throw new Exception('Invalid Aadhaar Number');
	}*/

	if (empty($clientId)) {
		throw new Exception('ClientId cannot be empty');
	}

	if (empty($udc)) {
		throw new Exception('UDC cannot be empty');
	}

	if (strlen($udc) > 19) {
		throw new Exception('UDC is too long. It cannot be more than 19 characters');
	}

	$timeStamp = date('Y-m-d H:i:s');

	//Create the JSON Data
	$postData = json_encode(array('id' => $id,
    	'uid' => $aadhaarNumber, //$aadhaarNumber
    	'clientId' => $clientId,
    	'action' => $action,
    	'actionType' => $actionType,
    	'udc' => $udc,
    	'udf1' => $udf1,
    	'udf2' => $udf2,
    	'udf3' => $udf3,
    	'maxRetry' => $maxRetry,
    	'timestamp' => $timeStamp));

	//Sign the JSON Data
	$signPfxContens = file_get_contents($signPfx);
	$signKey = openssl_get_privatekey($signPfxContens, $signPfxPassword);

	openssl_sign($postData, $bySignature, $signKey, "SHA256");

	$signature = base64_encode($bySignature);

	// Generate SessionKey & encrypt data
	$key = md5(uniqid());
	$sessionKey = $key;

	$byEncryptedData = aes256_cbc_encrypt($key, $postData);
	$encryptedData = base64_encode($byEncryptedData); 

	//Open the WEBAPI file for encryption
	$fp = file_get_contents($gatewayCertificate);
	$pub_key = openssl_pkey_get_public($fp);

	//Encrypt session key
	openssl_public_encrypt($sessionKey, $encryptedSessionKey, $pub_key, OPENSSL_PKCS1_PADDING);
	$encryptedSessionKey = base64_encode($encryptedSessionKey);

	$finalData = json_encode(array('clientId' => $clientId,
									'data' => $encryptedData,
									'signature' => $signature,
									'skey' => $encryptedSessionKey));

	return $finalData;
}

function getResponseData($res, $gatewayCertificate, $signPfx, $signPfxPassword) {
	$encryptedSessionKey = $res->skey;

	$signPfxContens = file_get_contents($signPfx);
	$signKey = openssl_get_privatekey($signPfxContens, $signPfxPassword);

	//Decrypt the Session Key
	openssl_private_decrypt(base64_decode($encryptedSessionKey), $sessionKey, $signKey, OPENSSL_PKCS1_PADDING);
	//$sessionKey = aes256_cbc_decrypt($signKey, $encryptedSessionKey);

	//Decrypt the Data
	//openssl_public_decrypt($res->data, $decryptedData, $sessionKey, OPENSSL_PKCS1_PADDING);
	$decryptedData = aes256_cbc_decrypt($sessionKey, base64_decode($res->data));

	//Load the Gateway Key File
	$fp = file_get_contents($gatewayCertificate);
	$pub_key = openssl_pkey_get_public($fp);

	//Verify the Signature of the Data
	if(openssl_verify($decryptedData, base64_decode($res->signature), $pub_key, "SHA256") != 1) {
		print 'Signature Verification Failed.';
		return null;
	} 

	$webApiResponseData = ECSWebApiResponseData::createFromJson($decryptedData);

	return $webApiResponseData;
}

function getSimpleResponseData($res, $gatewayCertificate, $signPfx, $signPfxPassword) {
	$encryptedSessionKey = $res->skey;

	$signPfxContens = file_get_contents($signPfx);
	$signKey = openssl_get_privatekey($signPfxContens, $signPfxPassword);

	//Decrypt the Session Key
	openssl_private_decrypt(base64_decode($encryptedSessionKey), $sessionKey, $signKey, OPENSSL_PKCS1_PADDING);
	//$sessionKey = aes256_cbc_decrypt($signKey, $encryptedSessionKey);

	//Decrypt the Data
	//openssl_public_decrypt($res->data, $decryptedData, $sessionKey, OPENSSL_PKCS1_PADDING);
	$decryptedData = aes256_cbc_decrypt($sessionKey, base64_decode($res->data));

	//Load the Gateway Key File
	$fp = file_get_contents($gatewayCertificate);
	$pub_key = openssl_pkey_get_public($fp);

	//Verify the Signature of the Data
	if(openssl_verify($decryptedData, base64_decode($res->signature), $pub_key, "SHA256") != 1) {
		print 'Signature Verification Failed.';
		return null;
	} 

	$webApiSimpleResponseData = ECSWebApiSimpleResponseData::createFromJson($decryptedData);

	return $webApiSimpleResponseData;
}

function parseResponse($webapiRes) {
	$response = ECSWebApiResponse::createFromJson($webapiRes);
	return $response;
}

function aes256_cbc_encrypt($key, $data) {

	if(32 !== strlen($key)) $key = hash('SHA256', $key, true);
	$padding = 16 - (strlen($data) % 16);
	$data .= str_repeat(chr($padding), $padding);

	//return openssl_encrypt($data, "aes-256-ecb", $key);
	return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_ECB);
}

function aes256_cbc_decrypt($key, $data) {

  if(32 !== strlen($key)) $key = hash('SHA256', $key, true);
  $data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_ECB);
  $padding = ord($data[strlen($data) - 1]);

  return substr($data, 0, -$padding);

}

?>