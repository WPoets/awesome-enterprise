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
            $response['msg'] = "Zoho access token updated successfully!";
            
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }
        return $response;
    }

    public function getZohoInstance(){        
        try{
        
//        $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance("Leads"); //To get record instance
//        $response = $moduleIns->getRecord("3876186000000204391"); 
        
        
        $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance("Products"); //To get record instance
        $response = $moduleIns->getRecord("3876186000000243005");
            
        $record = $response->getData();  //To get response data
        
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
       
        $response['information'] = $record->getData(); //To get lead all information
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
        
        
        $response['tax_list'] =  $tax_temp;
        
//        $lineItems = $record->getLineItems();  //To get line_items as map
//        $pricedetails = $record->getPriceDetails();  //To get the price_details array
//        $participants = $record-> getParticipants();  //To get Event record's participants
        }catch (ZCRMException $ex){
            $response = $ex->getMessage();  //To get ZCRMException error message
        }
        print_r($response);
    }


}

class zohoPage extends zohoMain{
    
    public function registerZohoSubPage(){
        add_submenu_page( 'tools.php', 'Zoho', 'Zoho', 'manage_options', 'zoho', array('zohoPage','zohoTestFun' ));
    }
    
    public function zohoTestFun(){   
        echo "<pre>";
            parent::getZohoInstance();
        echo "</pre>";
    }
}

$_SERVER['user_email_id'] = $GLOBALS['zoho_config']['zoho_userIdentifier_email'];
new zohoPage();