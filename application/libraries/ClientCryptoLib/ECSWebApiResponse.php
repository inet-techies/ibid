<?php
class ECSWebApiResponse {
	public $clientId;
	public $data;
	public $signature;
	public $skey;

	public function __construct( $clientId, $data, $signature, $skey)
    {
        $this->clientId = $clientId;
        $this->data = $data;
        $this->signature = $signature;
        $this->skey = $skey;
    }

    public static function createFromJson( $jsonString )
    {
        $object = json_decode( $jsonString );
        return new self( $object->clientId, $object->data, $object->signature, $object->skey);
    }
};
?>