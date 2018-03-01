<?php

aw2_library::add_shortcode('aw2','pdf', 'awesome2_generate_pdf','Generate PDF form templates');

function awesome2_generate_pdf($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	
	extract( shortcode_atts( array(
		'part'=>null
		), $atts) );

	$aw2_pdf=new awesome2_pdf_handler($atts,$content);
	if($aw2_pdf->status==false){
		return aw2_library::get('errors');
	}
	if(is_null($part)){
		aw2_library::set_error('Part attribute is required for aw2.pdf'); 
		return aw2_library::get('errors');
	}
	
	 if(!method_exists($aw2_pdf, $part)){
		 aw2_library::set_error('Part type does not exist'); 
	 }
		
	
	$return_value =call_user_func(array($aw2_pdf, $part));
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	
	return $return_value;
}

class awesome2_pdf_handler{
	public $action=null;
	public $atts=null;
	public $content=null;
	public $status=false;
	
	function __construct($atts,$content=null){
    
		$this->atts=$atts;
		$this->content=trim($content);
		$this->status=true;
	
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
			$json=aw2_library::clean_specialchars($this->content);
			$json=aw2_library::parse_shortcode($json);		
			$return_value=json_decode($json, true);
			if(is_null($return_value)){
				aw2_library::set_error('Invalid JSON' . $content); 
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
	

	
	function setup(){
		$pdf=&aw2_library::get_array_ref('pdf_setup');
		$args = $this->args();
		//set defaults

		$args = shortcode_atts( array(
					"page_orientation" => "p",
					"pdf_unit" => "mm",
					"page_format" => "A4",
					"unicode" => true,
					"encoding" => "UTF-8",
					"diskcache" => false,
					"pdfa" =>false,
					"author" => "Awesome Studio",
					"creator" => "awesome_pdf",
					"left_margin" => "5",
					"top_margin" => "5",
					"right_margin" => "5",
					"footer_margin" => "5",
					"header_margin" => "2",
					"font_name" => "helvetica"
				), $args);
		
		$pdf['setup']=$args;
		

		return;
	}
	
	function header(){
		$pdf=&aw2_library::get_array_ref('pdf_setup');
		$args = $this->args();
				
		$pdf['header']=$args;
		

		return;
	}
	
	function footer(){
		$pdf=&aw2_library::get_array_ref('pdf_setup');
		$args = $this->args();
				
		$pdf['footer']=$args;
		

		return;
	}
	
	function content(){
		$pdf=&aw2_library::get_array_ref('pdf_setup');
		
		$content=aw2_library::clean_specialchars($this->content);
		$content=aw2_library::parse_shortcode($content);
		
		$pdf['content']=$content;
		

		return;
	}

	
	function download(){
		$pdf=&aw2_library::get_array_ref('pdf_setup');
		
		$pdf['filename']=$this->att('filename');
		if(empty($pdf['filename'])){
			aw2_library::set_error('filename is required for part=download aw2.pdf'); 
			return aw2_library::get('errors');
		}
		$return_value = $this->generate_pdf($pdf,true);
		
		return $return_value;
	}

	
	function save(){
		$pdf=&aw2_library::get_array_ref('pdf_setup');
		
		$pdf['filename']=$this->att('filename');
		$pdf['dir_path']=$this->att('dir_path');
		
		if(empty($pdf['filename'])){
			aw2_library::set_error('filename is required for part=save aw2.pdf'); 
			return aw2_library::get('errors');
		}
		
		$return_value = $this->generate_pdf($pdf,false);
		
		return $return_value;
	}
	
	private function generate_pdf($pdf_data, $download=false){
		require_once dirname(plugin_dir_path( __DIR__ )).'/monoframe/pdf-helper.php';
		
		$aw_pdf = new awesome_pdf($pdf_data);
		$aw_pdf->SetPDF();
		
		$return_value='';
		if($download) {
			$aw_pdf->Download($pdf_data['filename']);
		}
		else {
			$filename=$pdf_data['dir_path'] .'/'.$pdf_data['filename'];
			$return_value = $aw_pdf->Save($filename);
		}
		
		return $return_value;		
	}
	
	function app_option(){
		$app_setting_sections=&aw2_library::get_array_ref('app_setting_sections');
		$part = $this->att('part');
		$args = $this->args();
		$id = $args['id'];
		if($part=='start' || ($part==null && $args!='')) 
			$app_setting_sections[$id]=$args;
		
		
		if($part=='field'){
			$key = util::array_last_key( $app_setting_sections );
			
			if(!array_key_exists('fields', $app_setting_sections[$key]))
				$app_setting_sections[$key]['fields']=array();
			
			$app_setting_sections[$key]['fields'][]=$args;				
		}
		return;
	}

}