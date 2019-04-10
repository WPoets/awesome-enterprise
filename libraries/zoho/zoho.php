<?php    
include( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php');

define("ZOHO_TOKEN_FOLDER_NAME", "zoho-token");
define("ZOHO_TOKEN_TXT_FILE_NAME", "zcrm_oauthtokens.txt");
$GLOBALS['aws_zoho_config_meta_keys'] = array('zoho_code','zoho_client_id','zoho_client_secret','zoho_refresh_token','zoho_redirect_uri','zoho_accounts_url','zoho_userIdentifier_email');

class awsZohoConfig {
    
    public function __construct(){
    }
    
    public function getZohoTokenTxtFilePath(){
        return dirname(getcwd(), 1) .'/'. ZOHO_TOKEN_FOLDER_NAME.'/';
    }
    
    public function makeZohoTokenFolder(){
        $folder=dirname(getcwd(), 1) .'/' .ZOHO_TOKEN_FOLDER_NAME;
        if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
                $temp = $folder.'/'.ZOHO_TOKEN_TXT_FILE_NAME;
                fopen($temp, "w");
        }
		
        if(is_dir($folder)){
            return "Zoho token folder created successfully.";
        }else{
            return "Some error occurred please try again or check root folder access permission";
        }
        
    }
    
    public function makeZohoAttachmentFolder(){
        $folder=dirname(getcwd(), 1) . '/zoho-attachment';
        
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
		
        if(is_dir($folder)){
            return "Zoho Attachment folder created successfully.";
        }else{
            return "Some error occurred please try again or check root folder access permission";
        }
    }
    
    
    public function getSetZohoConfig(){
        $setting_post = get_page_by_path('settings',OBJECT,'awesome_core');
        
        $zoho_config = array();
        foreach ($GLOBALS['aws_zoho_config_meta_keys'] as $meta_key) {
            $zoho_config[$meta_key] = trim(get_post_meta($setting_post->ID,$meta_key,true));
        }      
        $zoho_config['post_id'] = $setting_post->ID;
        $zoho_config['zoho_token_persistence_path'] = $this->getZohoTokenTxtFilePath();
        
        return $zoho_config;
    }
    
    function __destruct() {
        $GLOBALS['zoho_config'] = $this->getSetZohoConfig();
    }
}

$awsZohoConfig = new awsZohoConfig();


class zohoMain{
    
    public function __construct() {
        ZCRMRestClient::initialize();
    }      
    
    public function getZohoRefreshToken(){
        $ZohoConfig = $GLOBALS['zoho_config'];
        $curl = curl_init();
        $zoho_curl = $ZohoConfig['zoho_accounts_url']."/oauth/v2/token".
                                    "?code=".$ZohoConfig['zoho_code'].
                                    "&redirect_uri=".urlencode($ZohoConfig['zoho_redirect_uri']).
                                    "&client_id=".$ZohoConfig['zoho_client_id'].
                                    "&client_secret=".$ZohoConfig['zoho_client_secret'].
                                    "&grant_type=authorization_code";
        $curl_config =  array(
                            CURLOPT_URL => "$zoho_curl",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_HTTPHEADER => array(
                              "cache-control: no-cache",
                              "content-type: application/json",
                            ),
                        );
        curl_setopt_array($curl, $curl_config);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          return $response;
        } else {
            return $response;
        }
    } 

    public function updateAccessToken(){
        /*
         * This try catch is not working 
         * Zoho generateAccessTokenFromRefreshToken nothing retunt.
         * each time return null if token is update or not
         */
        $response = array();
        try{
            $ZohoConfig = $GLOBALS['zoho_config'];
            $oAuthClient = ZohoOAuth::getClientInstance();
            $oAuthClient->generateAccessTokenFromRefreshToken($ZohoConfig['zoho_refresh_token'],$ZohoConfig['zoho_userIdentifier_email']);
            $response['status'] = 'success';
            $response['message'] = "Zoho access token updated successfully!";
            
        } catch (Exception $ex) {
            $response['status'] = 'error';
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }
    
    public function getModuleFieldsName($module){
        $response = array();
        try{
            $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module); //To get record instance
            $getAllFields = $moduleIns->getAllFields(); //to get the field
            $fields = $getAllFields->getData(); //to get the array of ZCRMField instances
            $response['aws_status'] = 1;
            foreach ($fields as $field){ //each field
                $response['data'][$field->getId()] = 
                        
                        array(
                                                "name"=>$field->getApiName(),
                                                "id" =>$field->getId(), 
                                                "FieldLabel" => $field->getFieldLabel(),
                                                "Length" => $field->getLength(),
                                                "isMandatory" => $field->isMandatory(),
                                                "dataType" => $field->getDataType(),
                                                "defaultValue" => $field->getDefaultValue(),
                                                "currencyField" => $field->isCurrencyField(),
                                                "jsonType" => $field->getJsonType()
                                                 
                                            );
//                        $field->getApiName();
            }       
        } catch (Exception $ex) {
            $response['zoho_status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }
    
    public function getAllCustomViews($module){
        $response = array();
        try{
            $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module); //To get record instance
            $customViews = $moduleIns->getAllCustomViews(); //to get all the custom views
            $customViews = $customViews->getData(); //to get the custom view in form of ZCRMCustomView

            $response['aws_status'] = 1;

            foreach($customViews as $customView){
                $response[$customView->getSystemName()] = array('id'=>$customView->getId(),'name'=>$customView->getName()); //to get the name of the custom view
            }
        } catch (Exception $ex) {
            $response['zoho_status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }
    
    public function getRecords($module,$custom_view_id = null,$field_api_name = null,$sort_order = null,$start_index = null,$end_index = null,$customHeaders = null) {
        try{
            $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module);  //To get module instance
            $response = $moduleIns->getRecords($custom_view_id,$field_api_name,$sort_order,$start_index,$end_index,$customHeaders);
            $records = $response->getData();  //To get response data
            
            $response = array();
            $response['aws_status'] = 1;
            foreach ($records as $record){
                $response['data'][] = self::getSingle($record);
            }
        }catch (ZCRMException $ex){
            $response = $ex->getMessage();  //To get ZCRMException error message
        }
        return $response;
    }
    
    public function getRecord($module,$id){
        try{
            $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module); //To get record instance
            $record = $moduleIns->getRecord($id);
            $record = $record->getData();  //To get response data         
            $response['data'] = self::getSingle($record);
            $response['aws_status'] = 1;
        }catch (ZCRMException $ex){
            $response['message'] = $ex->getMessage();  //To get ZCRMException error message
            $response['code'] = $ex->getExceptionCode();  //To get ZCRMException error code
            $response['file'] = $ex->getFile();
        }
        return $response;
    }
    
    private function getSingle($record){
        $response = array();
        $response['entity_Id'] = $record->getEntityId();
        $response['module_api_name'] = $record->getModuleApiName();

        $createdBy = $record->getCreatedBy();
        $response['created_by']['id'] = $createdBy->getId();  //To get user_id who created the record
        $response['created_by']['name'] = $createdBy->getName();  //To get user name who created the record


        $modifiedBy = $record->getModifiedBy();
        $response['modified_by']['id'] = $modifiedBy->getId();  //To get user_id who modified the record
        $response['modified_by']['namew']= $modifiedBy->getName();  //To get user name who modified the record

        $owner = $record->getOwner();
        $response['owner']['id'] = $owner->getId();  //To get record owner_id
        $response['owner']['name'] = $owner->getName();  //To get record owner name

        $response['created_time'] = $record->getCreatedTime();  //To get record created time
        $response['modified_time'] = $record->getModifiedTime();  //To get record modified time
        $response['last_activity_time'] = $record->getLastActivityTime();  //To get last activity time(latest modify/view time)

        $temp_information = array();
        $map=$record->getData();  //To get record data as map. To get lead all information

        foreach ($map as $key=>$value){
            if($value instanceof ZCRMRecord){  //If value is ZCRMRecord object
                $temp_information[$value->getModuleApiName()] = array($value->getEntityId() => $value->getLookupLabel());
            }else{  //If value is not ZCRMRecord object
                $temp_information[$key] = $value;
            }
        }
        $response['fields'] = $temp_information;
        
        
        $response['properties'] = $record->getAllProperties();  //To get record properties

        $layouts = $record->getLayout();  //To get record layout
        if($layouts){
            $response['layout']['id'] = $layouts->getId();  //To get layout_id
            $response['layout']['name'] = $layouts->getName();  //To get layout name
        }


        $taxlists = $record->getTaxList();  //To get the tax list
        $tax_temp = array();
        foreach ($taxlists as $taxlist){
            $temp['tax_name'] = $taxlist->getTaxName();
            $temp['percentage'] = $taxlist->getPercentage();
            $temp['value'] = $taxlist->getValue();
            $tax_temp[] = $temp;
        }
        
        //Check record image is set
        if(isset($response['fields']['Record_Image'])){
            $dir = "/zoho-attachment/";
            $filePath = dirname(getcwd(), 1).$dir;
            
            $fileResponseIns = $record->downloadPhoto();
            $file_name = $fileResponseIns->getFileName();
            $fp=fopen($filePath.$file_name,"w"); // $filePath - absolute path where the downloaded photo is stored.
            $stream=$fileResponseIns->getFileContent();
            fputs($fp,$stream);
            fclose($fp);
            
            $response['profile_photo'] =    array(
                                    "name" => $file_name,
                                    "url" => site_url($dir).$file_name,
                                    "dir" => $filePath.$file_name
                                );
        }

        $response['tax'] = $tax_temp;
        
        
        $notes=$record->getNotes()->getData();//to get the notes in form of ZCRMNote instances array
        $temp_notes = array();
        foreach ($notes as $note){
            $temp_notes[] =   array(
                                    "id" => $note->getId(), //To get note id
                                    "title" => $note->getTitle(), //To get note title
                                    "content" => $note->getContent() //To get note content
                                );
        }
        $response['notes'] = $temp_notes;
        
        return $response;
    }
    
    public function deleteRecords($module,$recordids){
        try {
            $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module); //To get record instance
            $recordids = explode(',', $recordids);
            $responseIn = $moduleIns->deleteRecords($recordids); //to delete the records
            
            $response = array();
            $response['aws_status'] = 1;
            foreach($responseIn->getEntityResponses() as $responseIns){
                $details = $responseIns->getDetails();
                    $temp['http_status_code'] = $responseIn->getHttpStatusCode(); //To get http response code
                    $temp['status'] = $responseIn->getStatus(); //To get response status
                    $temp['message'] = $responseIn->getMessage(); //To get response message
                    $temp['code'] = $responseIns->getCode();  //To get status code
                    $temp['details'] = json_encode($responseIns->getDetails());
                $response['data'][$details['id']] = $temp;
            }
        }catch (ZCRMException $ex){
            $response['message'] = $ex->getMessage();  //To get ZCRMException error message
            $response['code'] = $ex->getExceptionCode();  //To get ZCRMException error code
            $response['file'] = $ex->getFile();
        }    
        return $response;
     }
     
    public function createRecords($module,$fiels) {       
       $record = ZCRMRestClient::getInstance()->getRecordInstance($module, NULL); //To get record instance
       
        $response = array();                
        try{
            foreach ($fiels['fields'] as $key => $value) {
                $record->setFieldValue($key,$value); //This function use to set FieldApiName and value similar to all other FieldApis and Custom field
            }
            
            $responseIns = $record->create();
            $details = $responseIns->getDetails();
            $record_id = $details['id'];
            
            if (!empty($fiels['tags'])) { // Check tag exits or not
                $tags = self::addTagsToRecord($module,$record_id,$fiels['tags']); //to add the tags to the record
                //$response['tag_status'] = $tags;
            }
            
            $record_attachment = $fiels['attachment'];
            if (trim($record_attachment)) {
                self::uploadAttachment($module,$record_id,$record_attachment);
            }
            
            $profile_photo = $fiels['profile_photo'];
            if (trim($profile_photo)) {
                self::uploadPhoto($module,$record_id,$profile_photo);
            }
            
            $note = $fiels['note'];
            if (!empty($note)) {
                self::addNote($module,$record_id,$note);
            }
    
            $response['aws_status'] = 1;
            
            $temp['http_status_code'] = $responseIns->getHttpStatusCode(); //To get http response code
            $temp['zoho_status'] = $responseIns->getStatus(); //To get response status
            $temp['message'] = $responseIns->getMessage(); //To get response message
            $temp['code'] = $responseIns->getCode();  //To get status code
            $temp['details'] = $responseIns->getDetails();
            $response['data'] = $temp;
        }catch (ZCRMException $ex){
            $response['data']['message'] = $ex->getMessage();  //To get ZCRMException error message
        }
       return $response;
   } 
   
    public function updateRecord($module,$id,$fiels){
        $record = ZCRMRestClient::getInstance()->getRecordInstance($module,$id); //To get record instance
        
        $response = array();                
        try{
            
            $response['aws_status'] = 1;
            foreach ($fiels['fields'] as $key => $value) {
                $record->setFieldValue($key,$value); //This function use to set FieldApiName and value similar to all other FieldApis and Custom field
            }
            $responseIns = $record->update();//to update the record
            
            $details = $responseIns->getDetails();
            $record_id = $details['id'];
            
            //Tag update section
            if (!empty($fiels['tags'])) { // Check tag exits or not
                self::addTagsToRecord($module,$record_id,$fiels['tags']); //to add the tags to the record
            }
            
            //profile_photo update section
            $profile_photo = $fiels['profile_photo'];
            if ($profile_photo) {
                self::uploadPhoto($module,$record_id,$profile_photo);
            }
            
            //attachment update section
            $record_attachment = $fiels['attachment'];
            if (trim($record_attachment)) {
                self::uploadAttachment($module,$record_id,$record_attachment);
            }
            
            //notes update section
            $record_notes = $fiels['notes'];
            if (!empty($record_notes)) {
                self::updateNotes($module,$record_id,$record_notes);
            }
            
            $response['http_status_code'] = $responseIns->getHttpStatusCode(); //To get http response code
            $response['zoho_status'] = $responseIns->getStatus(); //To get response status
            $response['message'] = $responseIns->getMessage(); //To get response message
            $response['code'] = $responseIns->getCode();  //To get status code
            $response['details'] = $responseIns->getDetails();
            
        }catch (ZCRMException $ex){
            $response['message'] = $ex->getMessage();  //To get ZCRMException error message
        }
        return $response;
    }
   
    public function addTagsToRecord($module,$record_id,$tags){
        $record = ZCRMRestClient::getInstance()->getRecordInstance($module,$record_id); //to get the module instance 
        $responseIns = $record->addTags($tags); //to add the tags to the record
        //$responseIn=$moduleIns->removeTagsFromRecords($recordids,$tagnames); //to remove the tags from the records
            $response = array();
            $response['http_status_code'] = $responseIns->getHttpStatusCode(); //To get http response code
            $response['zoho_status'] = $responseIns->getStatus();  //To get response status;
            $response['message'] = $responseIns->getMessage();  //To get status code;
            $response['code'] = $responseIns->getCode();
            $response['details'] = $responseIns->getDetails();
        return $response;
    }
    
    public function uploadPhoto($module,$record_id,$path){
        
        try{
            $record=ZCRMRestClient::getInstance()->getRecordInstance($module, $record_id); //To get record instance
            $responseIns=$record->uploadPhoto($path); // $photoPath - absolute path of the photo to be uploaded.
            $response['aws_status'] = 1;
            $response['data']['http_status_code'] = $responseIns->getHttpStatusCode(); //To get http response code
            $response['data']['zoho_status'] = $responseIns->getStatus(); //To get response status
            $response['data']['message'] = $responseIns->getMessage(); //To get response message
            $response['data']['code'] = $responseIns->getCode(); //To get status code
            $response['data']['details'] = $responseIns->getDetails()['id'];
            
        } catch (Exception $ex) {
            $response['zoho_status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        return $response;

    }
    
    public function uploadAttachment($module,$record_id,$path){
        try{
            $record=ZCRMRestClient::getInstance()->getRecordInstance($module, $record_id); //To get record instance
            $responseIns=$record->uploadAttachment($path); // $photoPath - absolute path of the photo to be uploaded.
            $response['aws_status'] = 1;
            $response['data']['http_status_code'] = $responseIns->getHttpStatusCode(); //To get http response code
            $response['data']['zoho_status'] = $responseIns->getStatus(); //To get response status
            $response['data']['message'] = $responseIns->getMessage(); //To get response message
            $response['data']['code'] = $responseIns->getCode(); //To get status code
            $response['data']['details'] = $responseIns->getDetails()['id'];
        } catch (Exception $ex) {
            $response['zoho_status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }
    
    public function addNote($module,$record_id,$fiels){
        $response =  array();
        try{
            $record = ZCRMRestClient::getInstance()->getRecordInstance($module, $record_id); //To get record instance
            $noteIns = ZCRMNote::getInstance($record,NULL);//to get the note instance

            $noteIns->setTitle($fiels['title']);//to set the note title
            $noteIns->setContent($fiels['content']);//to set the note content
            $responseIns = $record->addNote($noteIns);//to add the note

            $response['aws_status'] = 1;
            $response['data']['http_status_code'] = $responseIns->getHttpStatusCode(); //To get http response code
            $response['data']['zoho_status'] = $responseIns->getStatus(); //To get response status
            $response['data']['message'] = $responseIns->getMessage(); //To get response message
            $response['data']['code'] = $responseIns->getCode(); //To get status code
            $response['data']['details'] = $responseIns->getDetails();
        } catch (Exception $ex) {
            $response['zoho_status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }
    
    public function updateNotes($module,$record_id,$fiels){
        try{
            $record = ZCRMRestClient::getInstance()->getRecordInstance($module, $record_id); //To get record instance
            $response['aws_status'] = 1;
            foreach ($fiels as $note_id => $note_fiels) {
                $noteIns = ZCRMNote::getInstance($record,$note_id);//to get the note instance
                $noteIns->setTitle($note_fiels['title']);//to set the title of the note
                $noteIns->setContent($note_fiels['content']);//to set the content of the note
                $responseIns = $record->updateNote($noteIns);//to update the note
                
                $note_id = $responseIns->getDetails()['id'];
                $response['data'][$note_id]['http_status_code'] = $responseIns->getHttpStatusCode(); //To get http response code
                $response['data'][$note_id]['zoho_status'] = $responseIns->getStatus(); //To get response status
                $response['data'][$note_id]['message'] = $responseIns->getMessage(); //To get response message
                $response['data'][$note_id]['code'] = $responseIns->getCode(); //To get status code
                $response['data'][$note_id]['details'] = $responseIns->getDetails();
            }
        } catch (Exception $ex) {
            $response['zoho_status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }

}