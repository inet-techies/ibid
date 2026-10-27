<?php
require_once(__DIR__ . '/kyc/Resp.php');
require_once(__DIR__ . '/kyc/KycRes.php');
require_once(__DIR__ . '/kyc/UidDataType.php');
require_once(__DIR__ . '/kyc/PoaType.php');
require_once(__DIR__ . '/kyc/PoiType.php');
require_once(__DIR__ . '/kyc/PrnType.php');
require_once(__DIR__ . '/kyc/LDataType.php');

class KycResProcessor {
	public function parse($xml) {
		$decryptedKycResponse = simplexml_load_string($xml); 
		$lResp = new Resp();

		$lResp->status = (string)$decryptedKycResponse['status'];
		$lResp->ko = (string)$decryptedKycResponse['ko'];
		$lResp->ret = (string)$decryptedKycResponse['ret'];
		$lResp->code = (string)$decryptedKycResponse['code'];
		$lResp->txn = (string)$decryptedKycResponse['txn'];
		$lResp->ts = (string)$decryptedKycResponse['ts'];
		$lResp->err = (string)$decryptedKycResponse['err'];

		if(array_key_exists('kycRes', $decryptedKycResponse)) {
			
			$decryptedKycRes = simplexml_load_string(base64_decode($decryptedKycResponse->kycRes));

			$lResp->kycRes = new KycRes();
			$lResp->kycRes->rar = $decryptedKycRes->Rar;

			if(array_key_exists('UidData', $decryptedKycRes)) {

				$uidData = $decryptedKycRes->UidData;
				$lResp->kycRes->uidData = new UidDataType();

				$poi = $uidData->Poi;

				$lResp->kycRes->uidData->poi = new PoiType();
				$lResp->kycRes->uidData->poi->name = (string)$poi['name'];
				$lResp->kycRes->uidData->poi->dob = (string)$poi['dob'];
				$lResp->kycRes->uidData->poi->gender = (string)$poi['gender'];

				$poa = $uidData->Poa;
				$lResp->kycRes->uidData->poa = new PoaType();
				$lResp->kycRes->uidData->poa->co = (string)$poa['co'];
				$lResp->kycRes->uidData->poa->house = (string)$poa['house'];
				$lResp->kycRes->uidData->poa->street = (string)$poa['street'];
				$lResp->kycRes->uidData->poa->lm = (string)$poa['lm'];
				$lResp->kycRes->uidData->poa->loc = (string)$poa['loc'];
				$lResp->kycRes->uidData->poa->vtc = (string)$poa['vtc'];
				$lResp->kycRes->uidData->poa->subdist = (string)$poa['subdist'];
				$lResp->kycRes->uidData->poa->dist = (string)$poa['dist'];
				$lResp->kycRes->uidData->poa->state = (string)$poa['state'];
				$lResp->kycRes->uidData->poa->country = (string)$poa['country'];
				$lResp->kycRes->uidData->poa->pc = (string)$poa['pc'];
				$lResp->kycRes->uidData->poa->po = (string)$poa['po'];
				$lResp->kycRes->uidData->poa->vtcCode = (string)$poa['vtcCode'];

				$lData = $uidData->LData;
				$lResp->kycRes->uidData->lData = new LDataType();
				$lResp->kycRes->uidData->lData->lang = (string)$lData['lang'];
				$lResp->kycRes->uidData->lData->name = (string)$lData['name'];
				$lResp->kycRes->uidData->lData->co = (string)$lData['co'];
				$lResp->kycRes->uidData->lData->house = (string)$lData['house'];
				$lResp->kycRes->uidData->lData->street = (string)$lData['street'];
				$lResp->kycRes->uidData->lData->lm = (string)$lData['lm'];
				$lResp->kycRes->uidData->lData->loc = (string)$lData['loc'];
				$lResp->kycRes->uidData->lData->vtc = (string)$lData['vtc'];
				$lResp->kycRes->uidData->lData->subdist = (string)$lData['subdist'];
				$lResp->kycRes->uidData->lData->dist = (string)$lData['dist'];
				$lResp->kycRes->uidData->lData->state = (string)$lData['state'];
				$lResp->kycRes->uidData->lData->country = (string)$lData['country'];
				$lResp->kycRes->uidData->lData->po = (string)$lData['po'];

				$lResp->kycRes->uidData->pht = (string)$uidData->Pht;

				$prn = $uidData->Prn;
				$lResp->kycRes->uidData->prn = new PrnType();
				$lResp->kycRes->uidData->prn->value = (string)$prn['value'];
				$lResp->kycRes->uidData->prn->type = (string)$prn['type'];

				$lResp->kycRes->uidData->uid = (string)$uidData['uid'];
				$lResp->kycRes->ret = (string)$decryptedKycRes['ret'];
				$lResp->kycRes->code = (string)$decryptedKycRes['code'];
				$lResp->kycRes->txn = (string)$decryptedKycRes['txn'];
				$lResp->kycRes->ts = (string)$decryptedKycRes['ts'];
				$lResp->kycRes->ttl = (string)$decryptedKycRes['ttl'];
				$lResp->kycRes->err = (string)$decryptedKycRes['err'];
				$lResp->kycRes->action = (string)$decryptedKycRes['action'];
			}
		}

		return $lResp;
	}
}
?>