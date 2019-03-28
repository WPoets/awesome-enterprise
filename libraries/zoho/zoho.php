<?php
/*
    Home,Leads,Contacts,Accounts,Deals,Activities,Reports,Dashboards,Products,Quotes,Sales_Orders,Purchase_Orders,Invoices,SalesInbox,Feeds,Campaigns,Vendors,Price_Books,Cases,Solutions,Documents,Forecasts,Visits,Social,Tasks,Events,Notes,Attachments,Calls,Actions_Performed
    Leads,Contacts,Products,Attachments
*/
    
include( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php');

define("ZOHO_TOKEN_FOLDER_NAME", "zoho-token");
define("ZOHO_TOKEN_TXT_FILE_NAME", "zcrm_oauthtokens.txt");
$GLOBALS['aws_zoho_config_meta_keys'] = array('zoho_code','zoho_client_id','zoho_client_secret','zoho_refresh_token','zoho_redirect_uri','zoho_accounts_url','zoho_userIdentifier_email');

class awsZohoConfig {
    
    public function __construct(){
        
    }
    
    public function getZohoTokenTxtFilePath(){
        return $_SERVER['DOCUMENT_ROOT'].ZOHO_TOKEN_FOLDER_NAME.'/';
    }
    
    public function makeZohoTokenFloder(){
        $mode = '0777';
        $root_dir_name = $_SERVER['DOCUMENT_ROOT'].ZOHO_TOKEN_FOLDER_NAME;

        if(!is_dir($root_dir_name)){
            mkdir($root_dir_name, $mode, TRUE);
            $temp = $root_dir_name.'/'.ZOHO_TOKEN_TXT_FILE_NAME;
            fopen($temp, "w");
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
        add_action( 'admin_menu',array('zohoPage','registerZohoSubPage' )); 
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
            $response['status'] = 1;
            $response['message'] = "Zoho access token updated successfully!";
            
        } catch (Exception $ex) {
            $response['status'] = 0;
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
            
            foreach ($fields as $field){ //each field
                $response[$field->getId()] = 
                        
                        array(
                                                "name"=>$field->getApiName(),
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
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }
    
    public function getAllCustomViews($module){
        $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module); //To get record instance
        $response = $moduleIns->getAllCustomViews(); //to get all the custom views
        $customViews = $response->getData(); //to get the custom view in form of ZCRMCustomView
        
        $response = array();
        foreach($customViews as $customView){
            $response[$customView->getSystemName()] = array('id'=>$customView->getId(),'name'=>$customView->getName()); //to get the name of the custom view
        }
        return $response;
    }
    
    //public function getRecords($module,$custom_view_id = null,$field_api_name = null,$sort_order = null,$start_index = null,$end_index = null,$customHeaders = null) {
    public function getRecords($module) {
        try{
            $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module);  //To get module instance
            //$response = $moduleIns->getRecords("3876186000000089005",$field_api_name,$sort_order,$start_index,$end_index,$customHeaders);
            $response = $moduleIns->getRecords();
            $records = $response->getData();  //To get response data
            
            $response = array();
            foreach ($records as $record){
                $response[] = self::getSingle($record);
            }
        }catch (ZCRMException $ex){
            $response = $ex->getMessage();  //To get ZCRMException error message
        }
        return $response;
    }
    
    public function getRecord($module,$id){        
        try{
            $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module); //To get record instance
            $response = $moduleIns->getRecord($id);
            $record = $response->getData();  //To get response data
            $response = self::getSingle($record);
        }catch (ZCRMException $ex){
            $response = $ex->getMessage();  //To get ZCRMException error message
        }
        return $response;
    }
    
    private function getSingle($record){
        $response = array();
        $response['entity_Id'] = $record->getEntityId();
        $response['module_api_name'] = $record->getModuleApiName();

        $createdBy = $record->getCreatedBy();
        $response['created_by']['id'] = $createdBy->getId();;  //To get user_id who created the record
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

        $response['tax'] =  $tax_temp;
        return $response;
    }
    
    public function deleteRecords($module,$recordids){
        $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module); //To get record instance
        $recordids = explode(',', $recordids);
        $responseIn = $moduleIns->deleteRecords($recordids); //to delete the records
        
        $response = array();
        foreach($responseIn->getEntityResponses() as $responseIns){
            $details = $responseIns->getDetails();
                $temp['http_status_code'] = $responseIn->getHttpStatusCode(); //To get http response code
                $temp['status'] = $responseIn->getStatus(); //To get response status
                $temp['message'] = $responseIn->getMessage(); //To get response message
                $temp['code'] = $responseIns->getCode();  //To get status code
                $temp['details'] = json_encode($responseIns->getDetails());
            $response[$details['id']] = $temp;
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
            
            /**
                //$record->setFieldValue("Account_Name","{account_id}"); //This function is for Invoices module
                //Following methods are being used only by Inventory modules
                $lineItem=ZCRMInventoryLineItem::getInstance(null);  //To get ZCRMInventoryLineItem instance
                $lineItem->setDescription("Product_description");  //To set line item description
                $lineItem ->setDiscount(5);  //To set line item discount
                $lineItem->setListPrice(100);  //To set line item list price

                $taxInstance1=ZCRMTax::getInstance("{tax_name}");  //To get ZCRMTax instance
                $taxInstance1->setPercentage(2);  //To set tax percentage
                $taxInstance1->setValue(50);  //To set tax value
                $lineItem->addLineTax($taxInstance1);  //To set line tax to line item

                $taxInstance1=ZCRMTax::getInstance("{tax_name}"); //to get the tax instance
                $taxInstance1->setPercentage(12); //to set the tax percentage
                $taxInstance1->setValue(50); //to set the tax value
                $lineItem->addLineTax($taxInstance1); //to add the tax to line item

                $lineItem->setProduct(ZCRMRecord::getInstance("{module_api_name}","{record_id}"));  //To set product to line item
                $lineItem->setQuantity(100);  //To set product quantity to this line item

                $record->addLineItem($lineItem);   //to add the line item to the record

                $record->uploadPhoto("/path/to/file");//to upload a photo of the record. photo can be uploaded only for certain modules                
            **/
            $responseIns = $record->create();
            $details = $responseIns->getDetails();
            $record_id = $details['id'];
            
            $response['http_status_code'] = $responseIns->getHttpStatusCode(); //To get http response code
            $response['status'] = $responseIns->getStatus(); //To get response status
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
            $response['status'] = $responseIns->getStatus();  //To get response status;
            $response['message'] = $responseIns->getMessage();  //To get status code;
            $response['code'] = $responseIns->getCode();
            $response['details'] = $responseIns->getDetails();
        return $response;
    }
    
    public function uploadPhoto($module,$record_id,$path){
        $record=ZCRMRestClient::getInstance()->getRecordInstance($module, $record_id); //To get record instance
        $responseIns=$record->uploadPhoto($path); // $photoPath - absolute path of the photo to be uploaded.
        echo "HTTP Status Code:".$responseIns->getHttpStatusCode(); //To get http response code
        echo "Status:".$responseIns->getStatus(); //To get response status
        echo "Message:".$responseIns->getMessage(); //To get response message
        echo "Code:".$responseIns->getCode(); //To get status code
        echo "Details:".$responseIns->getDetails()['id'];
    }
}

class zohoPage extends zohoMain{
    
    public function registerZohoSubPage(){
        add_submenu_page( 'tools.php', 'Zoho', 'Zoho', 'manage_options', 'zoho', array('zohoPage','zohoTestFun' ));
    }
    
    public function zohoTestFun(){   
        
        echo "<pre>";
//            $m = parent::getModuleFieldsName('Products');
//            print_r($m);
            //parent::getAllCustomViews('Leads');
//            $l = parent::getRecord('Products','3876186000000243005');
//            print_r($l);
//            exit;
            //$module= "Leads";
            //$custom_view_id = "3876186000000089005";
            //$field_api_name = "Email";
            //$sort_order = "DESC";
            //$start_index=0;
            //$end_index=5;
            //$customHeaders = array();
            //print_r(parent::getRecords($module,$custom_view_id ,$field_api_name,$sort_order,$start_index,$end_index,$customHeaders));
            //print_r(parent::getRecords($module));

            //$ids = "3876186000000204202,3876186000000204203";
            //print_r(parent::deleteRecords('Contacts',$ids));
        
        /*
            if (count($fiels) == count($fiels, COUNT_RECURSIVE)){

            }else{

            }
        */
  
//         $fiels = array("Lead_Source" => "Web Download",
//                        "Lead_Status" => "Lost Lead",
//                        "Company" => "Wpoets",
//                        "Salutation" => "Ms.",
//                        "First_Name" => "Dev",
//                        "Last_Name" => "Danidhariya",
//                        "Designation" => "Tech",
//                        "Email" => "devidas@amiworks.com",
//                        "Email_Opt_Out" => true,
//                        "Phone" => "9033240723",
//                        "Fax" => "1234567890",
//                        "Mobile" => "0987654321",
//                        "Website" => "devidas.in",
//                        "Industry" => "Industry",
//                        "No_of_Employees" => "27",
//                        "Annual_Revenue" => "8000000",
//                        "Rating" => "5",
//                        "Tag" => "Tag_Test",
//                        "Skype_ID" => "devidas",
//                        "Full_Name" => "Devidas Danidhariya",
//                        "Secondary_Email" => "devidas+2@amiworks.com",
//                        "Twitter" => "devtwitter",
//                        "Street" => "Line number 13",
//                        "City" => "pune",
//                        "State" => "maharashtra",
//                        "Zip_Code" => "123456",
//                        "Country" => "India",
//                        "Description" => "This is test Description",
//                        "Record_Image" => "https://rukminim1.flixcart.com/image/832/832/jr6o13k0/watch/w/q/z/1164-br-fogg-original-imaf9stmmgg2eq2f.jpeg"
//             );
//            $c = parent::createRecords('Leads',$fiels);
//            print_r($c);

            
            $fiels = array(
                            "information" =>    array(
                                                    "Product_Name" => "Fogg 1164-BR Brown Day and Date Unique New Watch - For Men",
                                                    "Product_Code" => "WATF9VDHRUQTGWQZ",
                                                    "Product_Active" => true,
                                                    "Manufacturer" => "Flip Kart",
                                                    "Product_Category" => "Watch",
                                                    "Sales_Start_Date" => "2019-03-28",
                                                    "Sales_End_Date" => "2019-10-28",
                                                    "Support_Start_Date" => "2019-03-28",
                                                    "Support_Expiry_Date" => "2019-09-28",
                                                    "Unit_Price" => 2830,
                                                    "Commission_Rate" => 28,
                                                    "Taxable" => true,
                                                    "Usage_Unit" => "Box",
                                                    "Qty_Ordered" => 10,
                                                    "Qty_in_Stock" => 500,
                                                    "Reorder_Level" => 3,
                                                    "Qty_in_Demand" => 8,
                                                    "Description" => "A classy and sophisticated timepiece for modern men is this brown coloured round watch from Fogg Fashion. Highlighted with a brown bold dial and a brown bezel, this watch looks appealing. The number markings at 6 and 12 o clock positions ensure ease of time viewing. Styled with a unique brown strap, this watch fits well on your wrist. Durable and classy, this watch will complement your formal as well as semi-formal look."
                                                ),
                            "tags" => array("Tea,Coffe,Test")
                            );

            $c = parent::createRecords('Products',$fiels);
            print_r($c);
        
        echo "</pre>";
    }
}

$_SERVER['user_email_id'] = $GLOBALS['zoho_config']['zoho_userIdentifier_email'];
new zohoPage();