<?php

aw2_library::add_shortcode('zoho','crm', 'awesome2_zoho_crm','Runs Zoho.com CRM API Actions');

function awesome2_zoho_crm($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
	'main'=>null,
	), $atts) );
	
	unset($atts['main']);

	if(empty(aw2_library::get('site_settings.zoho-crm-authcode')))
		return 'Zoho.com CRM Authcode not set.';
	
	$return_value='';
	$pieces=explode('.',$main);
	
	$zoho=new aw2_zoho_crm($pieces['0'],$pieces['1'],$atts,$content);
	$return_value=$zoho->run();
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
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
		
		$this->zoho_crm = new awesome_zoho_crm(aw2_library::get('site_settings.zoho-crm-authcode'));
	}
	
	public function run(){
		
		$return_value='';
		if (method_exists($this, $this->action))
			return call_user_func(array($this, $this->action));
		else {
			
			$xml=$this->zoho_crm->request($this->module, $this->action, $this->args());
			if($xml)
			{
				$return_value = array();
				foreach ($xml->result->{$this->module}->row as $row) {
					$return_value[(string) $row['no']] = $this->row_to_record($row);
				}
			}
			
		}
		return $return_value;	
	}
	
	private function insertRecords(){
		$args = $this->args();
		
		$xmlData = $this->zoho_crm->fields_to_xml($this->module, $args['xmlData']);

		$args['xmlData']=$xmlData;
		$xml=$this->zoho_crm->request($this->module, $this->action, $args);
		
		if(isset($xml->error)){
			aw2_library::set_error($xml->error); 
			return false;
		}
			
		
		return (string) $xml->result->recorddetail->FL[0];
	}
	
	private function updateRecords(){
		$args = $this->args();
		
		if(!isset($args['rows']))
			$data['0']=$args;
		else
			$data = $args;
		
		unset($args);
		
		$xmlData = $this->zoho_crm->fields_to_xml($this->module, $data);
		$params=$this->atts;
		$params['xmlData'] = $xmlData;
		
		$xml=$this->zoho_crm->request($this->module, $this->action, $params);
		
		if(isset($xml->error)){
			aw2_library::set_error($xml->error); 
			return false;
		}
			
		
		return (string) $xml->result->recorddetail->FL[0];
	}
	private function row_to_record(SimpleXMLElement $row) {
        $data = array();
        foreach($row as $field) {
            if ($field->count() > 0) {
                foreach ($field->children() as $item) {
                    foreach ($item->children() as $subitem) {
                        $data[(string) $field['val']][(string) $item['no']][(string) $subitem['val']] = (string) $subitem;
                    }
                }
            }
            else {
                $data[(string) $field['val']] = (string) $field;
            }
        }

        return $data;
        //return new Response\Record($data, (int) $row['no']);
    }
	
	private	function args(){
		if($this->content==null || $this->content==''){
			$return_value=array();	
		}
		else{
			$json=aw2_library::clean_specialchars($this->content);
			$json=aw2_library::parse_shortcode($json);		
			$return_value=json_decode($json, true);
			if(is_null($return_value)){
				aw2_library::set_error('Invalid JSON' . $content); 
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
