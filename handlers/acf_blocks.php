<?php
namespace aw2\acf_blocks;

\aw2_library::add_service('acf_blocks','Handles the registration of ctp, less variables etc.',['namespace'=>__NAMESPACE__]);



function unhandled($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;

	$pieces=$shortcode['tags'];

	if(count($pieces)!=2)return 'error:You must have exactly two parts to the query shortcode';
	
	$register=new awesome2_acfblock($pieces[1],$atts,$content);
	if($register->status==false){
		return \aw2_library::get('errors');
	}
	$return_value =$register->run();
	
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;
	
}

class awesome2_acfblock{
	public $action=null;
	public $atts=null;
	public $content=null;
	public $status=false;
	
	function __construct($action,$atts,$content=null){
     if (method_exists($this, $action)){
		$this->action=$action;
		$this->atts=$atts;
		$this->content=trim($content);
		$this->status=true;
	 }
	}
	function run(){
     if (method_exists($this, $this->action))
		return call_user_func(array($this, $this->action));
     else
		\aw2_library::set_error('Register Method does not exist'); 
	}
	
	function att($el,$default=null){
		if(array_key_exists($el,$this->atts))
			return $this->atts[$el];
		return $default;
	}

	function args(){
		if($this->content==null || $this->content==''){
			$return_value=array();	
		}
		else{
			$json=\aw2_library::clean_specialchars($this->content);
			$json=\aw2_library::parse_shortcode($json);		
			$return_value=json_decode($json, true);
			if(is_null($return_value)){
				\aw2_library::set_error('Invalid JSON' . $this->content); 
				$return_value=array();	
			}
		}

		$arg_list = func_get_args();
		foreach($arg_list as $arg){
			if(array_key_exists($arg,$this->atts))
				$return_value[$arg]=$this->atts[$arg];
		}
			return $return_value;
	}
	
	function register(){
		$gutenberg_blocks=&\aw2_library::get_array_ref('gutenberg_blocks');
		$new=$this->args();
		if(!isset($new['render_template'])){
			$new['render_callback'] = array($this, 'render_callback');
		}
		$new['service_handler']= $this->att('service_handler');
		
		$gutenberg_blocks[]=$new;
	}
	
	function render_callback($block, $content = '', $is_preview = false, $post_id = 0){

		$result='service handler not set';
		if(isset($block['service_handler']) && !empty($block['service_handler'])){
			// convert name ("acf/testimonial") into path friendly slug ("testimonial")
			$slug = str_replace('acf/', '', $block['name']);
			$block['slug_name']=$slug;
			$result=\aw2_library::service_run($block['service_handler'],$block,null,'service');
			
		 }
		
		echo $result;
	}
	
}	
