<?php
//namespace aw2\zoho;

\aw2_library::add_service('zoho','Zoho Library.',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('zoho.crm','Runs Zoho.com CRM API Actions',['namespace'=>__NAMESPACE__]);

$path = \aw2_library::$plugin_path . "libraries/zoho/";
require_once $path . 'zoho.php';

function crm($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts) );
	unset($atts['main']);

        /*
         * Check Zoho requied setting here
        */
//	if(empty(\aw2_library::get('site_settings.zoho-crm-authcode')))
//		return 'Zoho.com CRM Authcode not set.';
	
	$return_value='';
	$pieces=explode('.',$main);

	$zoho=new aw2_zoho_crm($pieces['0'],$pieces['1'],$atts,$content);
	$return_value=$zoho->run();

	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
        unset($pieces);
	return $return_value;
}


class aw2_zoho_crm{
	public $module=null;
	public $action=null;
	public $atts=null;
	public $content=null;
	public $zoho_crm=null;
	
	function __construct($module,$action,$atts,$content=null){
                
            $this->module=$module;
            $this->action=$action;
            $this->atts=$atts;
            $this->content=trim($content);
            
            $_SERVER['user_email_id'] = $GLOBALS['zoho_config']['zoho_userIdentifier_email'];
            $this->zoho_crm = new \zohoMain();
	}
        
        public function run(){
            $return_value='';
            if (method_exists($this, $this->action)){
                return call_user_func(array($this, $this->action));
            }else {
                return "invalied function call...";
    //                $xml=$this->zoho_crm->request($this->module, $this->action, $this->args());
    //                if($xml){
    //                    $return_value = array();
    //                    foreach ($xml->result->{$this->module}->row as $row) {
    //                        $return_value[(string) $row['no']] = $this->row_to_record($row);
    //                    }
    //                }
            }
            return $return_value;	
	}
        
        private function getRecord(){
            $response = array();
            if(!empty($this->module) && !empty($this->atts['id'])){
                $result =  $this->zoho_crm->getRecord($this->module,$this->atts['id']);
                if($result['aws_status'] === 1){
                    unset($result['aws_status']);
                    $response = array('status'=>'success','response'=>$result);
                }else{
                    $response = array('status'=>'error','response'=>$result);
                }
            }else{
                $response = array('status'=>'error','message'=>'Please zoho module and id is required fields!');
            }
            return $response;
        }
        
        private function getModuleFields(){
            $response = array();
            $result =  $this->zoho_crm->getModuleFieldsName($this->module);
            if($result['aws_status'] === 1){
                    unset($result['aws_status']);
                    $response = array('status'=>'success','response'=>$result);
            }else{
                $response = array('status'=>'error','response'=>$result);
            }
                
            return $response;
        }
        
        private function getAllCustomViews(){
            $response = array();
            $result =  $this->zoho_crm->getAllCustomViews($this->module);
            if($result['aws_status'] === 1){
                    unset($result['aws_status']);
                    $response = array('status'=>'success','response'=>$result);
            }else{
                $response = array('status'=>'error','response'=>$result);
            }
                
            return $response;
        }
        
        private function deleteRecords(){
            $response = array();
            if(!empty($this->module) && !empty($this->atts['ids'])){
                $result =  $this->zoho_crm->deleteRecords($this->module,$this->atts['ids']);
                if($result['aws_status'] === 1){
                    unset($result['aws_status']);
                    $response = array('status'=>'success','response'=>$result);
                }else{
                    $response = array('status'=>'error','response'=>$result);
                }
            }else{
                $response = array('status'=>'error','message'=>'Comma separated ids is required fields.Example ids="1234567890,9876543210" or Invalid shortcode format.');
            }
            return $response;
        }
        
        private function createRecord(){
            $args = $this->args();
            $response = array();
            
            if(!empty($this->module) && !empty($args)){
                $result =  $this->zoho_crm->createRecords($this->module,$args);
                if($result['aws_status'] === 1){
                    unset($result['aws_status']);
                    $response = array('status'=>'success','response'=>$result);
                }else{
                    $response = array('status'=>'error','response'=>$result);
                }
            }else{
                $response = array('status'=>'error','message'=>'Comma separated ids is required fields.Example ids="1234567890,9876543210" or Invalid shortcode format.');
            }
            return $response;
        }
        private function createRecords(){
            $args = $this->args();
            $response = array();
            
            if(!empty($this->module) && !empty($args)){
                foreach ($args['rows'] as $value) {
                    $result =  $this->zoho_crm->createRecords($this->module,$value);
                    if($result['aws_status'] === 1){
                        unset($result['aws_status']);
                        $response[] = array('status'=>'success','response'=>$result);
                    }else{
                        $response[] = array('status'=>'error','response'=>$result);
                    }
                }
            }else{
                $response = array('status'=>'error','message'=>'Comma separated ids is required fields.Example ids="1234567890,9876543210" or Invalid shortcode format.');
            }
            return $response;
        }
        
        private	function args(){
            if($this->content==null || $this->content==''){
                    $return_value = array();	
            }
            else{
                    $json=\aw2_library::clean_specialchars($this->content);
                    $json=\aw2_library::parse_shortcode($json);		
                    $return_value=json_decode($json, true);
                    if(is_null($return_value)){
                            \aw2_library::set_error('Invalid JSON' . $content); 
                            $return_value=array();	
                    }
            }
            //util::var_dump($return_value);
            /* $arg_list = func_get_args();
            foreach($arg_list as $arg){
                    if(array_key_exists($arg,$this->atts))
                            $return_value[$arg]=$this->atts[$arg];
            } */
            return $return_value;
	}
        
        
}

 /*
         echo "<pre>";
            $get_fields = parent::getModuleFieldsName('Products');
            $get_custom_view = parent::getAllCustomViews('Leads');
            $get_record = parent::getRecord('Products','3876186000000243005');

            $module= "Leads";
            $custom_view_id = "3876186000000089005";
            $field_api_name = "Email";
            $sort_order = "DESC";
            $start_index=0;
            $end_index=5;
            $customHeaders = array();
            $get_records = parent::getRecords($module,$custom_view_id ,$field_api_name,$sort_order,$start_index,$end_index,$customHeaders);

            $ids = "3876186000000204202,3876186000000204203";
            $deleteRecords = parent::deleteRecords('Contacts',$ids);
            
            $insert_lead = array(
                           "fields" =>    array(
                                               "Lead_Source" => "Web Download",
                                               "Lead_Status" => "Lost Lead",
                                               "Company" => "Wpoets",
                                               "Salutation" => "Ms.",
                                               "First_Name" => "Dev",
                                               "Last_Name" => "Danidhariya",
                                               "Designation" => "Tech",
                                               "Email" => "devidas@amiworks.com",
                                               "Email_Opt_Out" => true,
                                               "Phone" => "9033240723",
                                               "Fax" => "1234567890",
                                               "Mobile" => "0987654321",
                                               "Website" => "devidas.in",
                                               "Industry" => "Industry",
                                               "No_of_Employees" => "27",
                                               "Annual_Revenue" => "8000000",
                                               "Rating" => "5",
                                               "Tag" => "Tag_Test",
                                               "Skype_ID" => "devidas",
                                               "Full_Name" => "Devidas Danidhariya",
                                               "Secondary_Email" => "devidas+2@amiworks.com",
                                               "Twitter" => "devtwitter",
                                               "Street" => "Line number 13",
                                               "City" => "pune",
                                               "State" => "maharashtra",
                                               "Zip_Code" => "123456",
                                               "Country" => "India",
                                               "Description" => "This is test Description"
                                           ),
                           "record_image" => "D:/laragon/www/enterprise/wp-content/uploads/2019/03/IMG_20181106_111725-768x432.jpg",
                           "tags" => array("Tea,Coffe,Test")           
                       );
            
            $insert_product =    array(
                                        "fields" => array(
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
                                        "tags" => array("Tea,Coffe,Test"),
                                        "record_image" => "D:/laragon/www/enterprise/wp-content/uploads/2019/03/IMG_20181106_111725-768x432.jpg"
                                );
            $create_records = parent::createRecords('Products',$insert_product);

            $module = 'Products';
            $id = '3876186000000263022';
            $fiels = array(
                            "fields" => array(
                                            "Product_Name" => "Fogg 1164-BR Brown Day and Date Unique New Watch - For boys",
                                            "Description" => "A As classy and sophisticated timepiece for modern men is this brown coloured round watch from Fogg Fashion. Highlighted with a brown bold dial and a brown bezel, this watch looks appealing. The number markings at 6 and 12 o clock positions ensure ease of time viewing. Styled with a unique brown strap, this watch fits well on your wrist. Durable and classy, this watch will complement your formal as well as semi-formal look."
                                        ),
                            "tags" => array("Tea-test,Coffe-test,Test-tea"),
                            "record_image" => "D:/laragon/www/enterprise/wp-content/uploads/2018/10/dev-test.jpg"
                    );
            $update_records = parent::updateRecords($module,$id,$fiels);
        echo "</pre>";
          */
