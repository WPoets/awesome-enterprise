<?php
require_once realpath(dirname(__FILE__)."/../../common/APIConstants.php");
require_once 'ResponseInfo.php';
require_once 'EntityResponse.php';
require_once 'CommonAPIResponse.php';
require_once realpath(dirname(__FILE__)."/../../exception/ZCRMException.php");

class BulkAPIResponse extends CommonAPIResponse
{
    /**
     * the bulk data
     * @var array
     */
	private $bulkData=null;
	/**
	 * response status of the api
	 * @var string
	 */
	private $status=null;
	/**
	 * the response information
	 * @var ResponseInfo
	 */
	private $info=null;
	/**
	 * bulk entities response
	 * @var array array of EntityResponse instances
	 */
	private $bulkEntitiesResponse=null;
	/**
	 * constructor to set the http response and http status code
	 * @param string $httpResponse  the http response
	 * @param int $httpStatusCode  http status code
	 */
	public function __construct($httpResponse,$httpStatusCode)
	{
		parent::__construct($httpResponse,$httpStatusCode);
		$this->setInfo();
	}
	/**
	 * 
	 * {@inheritDoc}
	 * @see CommonAPIResponse::handleForFaultyResponses()
	 */
	
	public function handleForFaultyResponses()
	{
		$statusCode=self::getHttpStatusCode();
		if(in_array($statusCode,APIExceptionHandler::getFaultyResponseCodes()))
		{
			if($statusCode==APIConstants::RESPONSECODE_NO_CONTENT)
			{
				$exception=new ZCRMException("No Content",$statusCode);
				$exception->setExceptionCode("NO CONTENT");
				throw $exception;
			}
			else
			{
				$responseJSON=$this->getResponseJSON();
				$exception=new ZCRMException($responseJSON['message'],$statusCode);
				$exception->setExceptionCode($responseJSON['code']);
				$exception->setExceptionDetails($responseJSON['details']);
				throw $exception;
			}
		}
	}
	/**
	 * 
	 * {@inheritDoc}
	 * @see CommonAPIResponse::processResponseData()
	 */
	public function processResponseData()
	{
		$this->bulkEntitiesResponse =array();
		$bulkResponseJSON=$this->getResponseJSON();
		if(array_key_exists(APIConstants::DATA,$bulkResponseJSON))
		{
			$recordsArray = $bulkResponseJSON[APIConstants::DATA];
			foreach ($recordsArray as $record)
			{
			    if($record!=null && array_key_exists(APIConstants::STATUS,$record))
			    {
			        array_push($this->bulkEntitiesResponse,new EntityResponse($record));
			    }
			}
		}
		if(array_key_exists(APIConstants::TAGS,$bulkResponseJSON))
		{
		    $recordsArray = $bulkResponseJSON[APIConstants::TAGS];
		    foreach ($recordsArray as $record)
		    {
		        if($record!=null && array_key_exists(APIConstants::STATUS,$record))
		        {
		            array_push($this->bulkEntitiesResponse,new EntityResponse($record));
		        }
		    }
		}
		
	}

    /**
     * method to get the bulk data
     * @return array array of data instances
     */
    public function getData(){
        return $this->bulkData;
    }

    /**
     * method to set the bulk data
     * @param array $bulkData array of data instances
     */
    public function setData($bulkData){
        $this->bulkData = $bulkData;
    }

    /**
     * method to Get the response status
     * @return String the response status
     */
    public function getStatus(){
        return $this->status;
    }

    /**
     *  method to Set the response status
     * @param String $status the response status
     */
    public function setStatus($status){
        $this->status = $status;
    }

    /**
     * method to get the response information
     * @return ResponseInfo instance of the ResponseInfo class
     */
    public function getInfo(){
        return $this->info;
    }

    /**
     * method to set the response information
     * @param  ResponseInfo $info instance of the ResponseInfo class
     */
    public function setInfo(){
    	if(array_key_exists(APIConstants::INFO,$this->getResponseJSON()))
    	{
    		$this->info = new ResponseInfo($this->getResponseJSON()[APIConstants::INFO]);
    	}
    }

    /**
     * method to get the bulk entity responses 
     * @return array array of the instances of EntityResponse class
     */
    public function getEntityResponses(){
        return $this->bulkEntitiesResponse;
    }


}
?>