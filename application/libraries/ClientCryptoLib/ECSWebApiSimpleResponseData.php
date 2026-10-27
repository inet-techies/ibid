<?php
class ECSWebApiSimpleResponseData {
	public $id;
	public $timeStamp;
	public $status;

	public function __construct( $id, $timeStamp, $status)
    {
        $this->id = $id;
        $this->timeStamp = $timeStamp;
        $this->status = $status;
    }

    public static function createFromJson( $jsonString )
    {
        $object = json_decode( $jsonString );
        return new self( $object->id, $object->timestamp, $object->status);
    }
};
?>