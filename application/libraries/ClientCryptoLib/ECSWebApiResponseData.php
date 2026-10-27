<?php
class ECSWebApiResponseData {
	public $clientId;
	public $id;
	public $uid;
	public $action;
	public $actionType;
	public $timeStamp;
	public $udf1;
	public $udf2;
	public $udf3;
	public $error;
	public $errorMessage;
	public $uidaiData;
	public $status;

	public function __construct( $clientId, $id, $uid, $action, $actionType, $timeStamp, $udf1, $udf2, $udf3, $error, $errorMessage, $uidaiData, $status)
    {
        $this->clientId = $clientId;
        $this->id = $id;
        $this->uid = $uid;
        $this->action = $action;
        $this->actionType = $actionType;
        $this->timeStamp = $timeStamp;
        $this->udf1 = $udf1;
        $this->udf2 = $udf2;
        $this->udf3 = $udf3;
        $this->error = $error;
        $this->errorMessage = $errorMessage;
        $this->uidaiData = $uidaiData;
        $this->status = $status;
    }

    public static function createFromJson( $jsonString )
    {
        $object = json_decode( $jsonString );
        return new self( $object->clientId, $object->id, $object->uid, $object->action, $object->actionType, $object->timestamp, $object->udf1, $object->udf2, $object->udf3, $object->error, $object->errorMessage, $object->uidaiData,  $object->status);
    }
};
?>