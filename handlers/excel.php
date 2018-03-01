<?php

aw2_library::add_shortcode('excel','write_bulk', 'excel_write_bulk');

function excel_write_bulk($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'file_name'    =>'',
		'folder'		=>'',
		'file_format'    =>'Excel2007',
		'data' => '',
		'template_file'=>'',
		'template_folder'=>''
		
		), $atts) );
		
	$xlsdata=aw2_library::get($data);	

	/** Include PHPExcel */
	require_once ("wp-content/plugins/awesome-studio/monoframe/PHPExcel/PHPExcel.php");

	$file_path=$folder . $file_name;
			
	if(!array_key_exists('pageno',$xlsdata))
		$pageno=1;
	else
		$pageno=$xlsdata['pageno'];
	
	if($pageno==1){
		if($template_file){
			$template_path=$template_folder . $template_file;
			$objPHPExcel = PHPExcel_IOFactory::load($template_path);
		}
		else{
			$objPHPExcel = new PHPExcel();
		// Set document properties
			//$objPHPExcel->getProperties()->setCreator("Excel by Awesome")
			// ->setLastModifiedBy("Excel by Awesome")
			// ->setTitle($file_name);
		}
		 
		//Add Header
		$objPHPExcel->setActiveSheetIndex(0);
		if(array_key_exists('header',$xlsdata)){
			$objPHPExcel->getActiveSheet()->fromArray($xlsdata['header'], null, 'A1');
		}
	}
	else{
		$objPHPExcel = PHPExcel_IOFactory::load($file_path);
		$objPHPExcel->setActiveSheetIndex(0);
	}
	
	// Add data
	if(array_key_exists('rows',$xlsdata)){
		$row= $objPHPExcel->setActiveSheetIndex(0)->getHighestRow()+1;
		
		
		$objPHPExcel->getActiveSheet()->fromArray($xlsdata['rows'], null, 'A' . $row);
	}	
				
	$objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->getFont()->setBold(true);

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $file_format);
	$objWriter->save($file_path);
}


aw2_library::add_shortcode('excel','read', 'excel_file_reader');

function excel_file_reader($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'file_path'    =>'',
		'folder'		=>'',
		'file_format'    =>'Excel2007',
		'start_from' => '2',
		'limit'=>''		
		), $atts) );

	if (!is_readable($file_path)){
		aw2_library::set_error('File '.$file_path.' is not readable'); 
		return;
	}

	$plugin_path=dirname(plugin_dir_path( __DIR__ ));
	require_once ($plugin_path."/monoframe/PHPExcel/PHPExcel.php");
	
	/**  Identify the type of $inputFileName  **/
	$inputFileType = PHPExcel_IOFactory::identify($file_path);
	/**  Create a new Reader of the type that has been identified  **/
	
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);

	$fileObj = $objReader->load( $file_path );
	$sheetObj = $fileObj->getActiveSheet();
	$return_value = array();

	foreach( $sheetObj->getRowIterator($start_from, $limit) as $key => $row ){

		if(isExcelRowEmpty($row))
			continue;
		
		foreach( $row->getCellIterator() as $cell ){
			$return_value[$key ][] = $cell->getCalculatedValue();
		}
	}
	
	$return_value=aw2_library::post_actions('all',$return_value,$atts);
	
	return $return_value;
}

function isExcelRowEmpty($row)
{
    foreach ($row->getCellIterator() as $cell) {
        if ($cell->getValue()) {
            return false;
        }
    }

    return true;
}

aw2_library::add_shortcode('excel','info', 'excel_file_info');

function excel_file_info($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'file_path'    =>'',
		'file_format'    =>'Excel2007'	
		), $atts) );
	if (!is_readable($file_path)){
		aw2_library::set_error('File '.$file_path.' is not readable'); 
		return;
	}
	
	$plugin_path=dirname(plugin_dir_path( __DIR__ ));
	require_once ($plugin_path."/monoframe/PHPExcel/PHPExcel.php");
	
	/**  Identify the type of $inputFileName  **/
	$inputFileType = PHPExcel_IOFactory::identify($file_path);
	/**  Create a new Reader of the type that has been identified  **/
	
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	$worksheetData = $objReader->listWorksheetInfo($file_path);
	aw2_library::set('worksheets',$worksheetData);
	$total_rows=$worksheetData[0]['totalRows'];
	$total_rows=aw2_library::post_actions('all',$total_rows,$atts);
	return $total_rows;
	
}


aw2_library::add_shortcode('excel','dataset_write', 'excel_dataset_write');

function excel_dataset_write($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'file_name'    =>'',
		'folder'		=>'',
		'file_format'  =>'Excel2007',
		'dataset' => '',
		'template_file'=>'',
		'template_folder'=>''
		
		), $atts) );
		
	/** Include PHPExcel */
	require_once ("wp-content/plugins/awesome-studio/monoframe/PHPExcel/PHPExcel.php");

	$file_path=$folder . $file_name;
	
	if(empty($file_format))$file_format='Excel2007';
	if(!array_key_exists('pageno',$dataset))
		$pageno=1;
	else
		$pageno=$dataset['pageno'];
	
	if($pageno==1){
		if($template_file){
			$template_path=$template_folder . $template_file;
			$objPHPExcel = PHPExcel_IOFactory::load($template_path);
		}
		else{
			$objPHPExcel = new PHPExcel();
		// Set document properties
			//$objPHPExcel->getProperties()->setCreator("Excel by Awesome")
			// ->setLastModifiedBy("Excel by Awesome")
			// ->setTitle($file_name);
		}
		 
		//Add Header
		$objPHPExcel->setActiveSheetIndex(0);
		if(array_key_exists('header',$dataset)){
			$objPHPExcel->getActiveSheet()->fromArray($dataset['header'], null, 'A1');
		}
	}
	else{
		$objPHPExcel = PHPExcel_IOFactory::load($file_path);
		$objPHPExcel->setActiveSheetIndex(0);
	}
	
	// Add data
	if(array_key_exists('rows',$dataset)){
		$row= $objPHPExcel->setActiveSheetIndex(0)->getHighestRow()+1;
		
		
		$objPHPExcel->getActiveSheet()->fromArray($dataset['rows'], null, 'A' . $row);
	}	
				
	$objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->getFont()->setBold(true);

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $file_format);
	$objWriter->save($file_path);
}

aw2_library::add_shortcode('excel','write_bulk_csv', 'excel_write_bulk_csv');
function excel_write_bulk_csv($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'file_name'    =>'',
		'folder'		=>'',
		'data' => '',
		), $atts) );
		
	$xlsdata=aw2_library::get($data);	
	$file_path=$folder . $file_name;

	if(!array_key_exists('pageno',$xlsdata))
		$pageno=1;
	else
		$pageno=$xlsdata['pageno'];

	if($pageno==1){
		$fp = fopen($file_path, 'w');
		if(array_key_exists('header',$xlsdata)){
			fputcsv($fp, $xlsdata['header']);
		}
	}
	else{
		$fp = fopen($file_path, 'a');
	}
	
	if(array_key_exists('rows',$xlsdata)){
		foreach ($xlsdata['rows'] as $fields) {
				fputcsv($fp, $fields);
		}
	}	
	fclose($fp);
}


aw2_library::add_shortcode('excel','read_header', 'excel_read_header');

function excel_read_header($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'filename'    =>'',
		'folder'		=>''
		), $atts) );
		
	/** Include PHPExcel */
	require_once ("wp-content/plugins/awesome-studio/monoframe/PHPExcel/PHPExcel.php");

	$file_path=$folder . $filename;
	$objReader = PHPExcel_IOFactory::createReaderForFile($file_path);
	$objReader->setReadDataOnly(true);

	$objPHPExcel = $objReader->load($file_path);

	$highestColumm = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
	$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumm);


	$arr=array();

	for($col = 0; $col < $highestColumnIndex; $col++)
	{
		$row=1;
		$cell = $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($col, $row);
		$arr[]=$cell->getValue();	
	}
	$return_value=aw2_library::post_actions('all',$arr,$atts);
	return $return_value;
}


aw2_library::add_shortcode('excel','read_post_data', 'excel_read_post_data');

function excel_read_post_data($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'filename'    =>'',
		'folder'		=>'',
		'posts_per_page'		=>'',
		'offset'		=>0
		), $atts) );
		
	/** Include PHPExcel */
	require_once ("wp-content/plugins/awesome-studio/monoframe/PHPExcel/PHPExcel.php");

	$file_path=$folder . $filename;
	$objReader = PHPExcel_IOFactory::createReaderForFile($file_path);
	$objReader->setReadDataOnly(true);

	$objPHPExcel = $objReader->load($file_path);

	$highestColumm = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
	$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumm);
	$highestRow = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
	
	$arr=array();
	$arr['found_posts']=$highestRow - 1;
	
	for($col = 0; $col < $highestColumnIndex; $col++)
	{
		$row=1;
		$cell = $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($col, $row);
		$pieces = explode(":", $cell->getValue());
		$new=array();
		$new['table']=$pieces[0];
		$new['field']=$pieces[1];
		$arr['header'][]=$new;
	}
	
	$start_row=2 + $offset;
	$end_row=$start_row + $posts_per_page - 1 ;
	if($end_row>$highestRow)$end_row=$highestRow;
		


	$sheetObj = $objPHPExcel->setActiveSheetIndex(0);

/*	
	foreach( $sheetObj->getRowIterator($start_row, $end_row) as $row ){
			$col=0;
			foreach( $row->getCellIterator() as $cell ){
				$col++;
				$value = $cell->getCalculatedValue();
				$new=array();
				$new['table']=$arr['header'][$col]['table'];
				$new['field']=$arr['header'][$col]['field'];
				$new['value']=$value;
				if(is_null($new['value']))$new['value']='';
				$cols[]=$new;
			}
		$arr['data'][]=$cols;
	}
*/

	for($row = $start_row; $row <= $end_row; $row++)
	{
		$cols=array();  
		for($col = 0; $col < $highestColumnIndex; $col++)
		{
			$cell = $sheetObj->getCellByColumnAndRow($col, $row);
			$new=array();
			$new['table']=$arr['header'][$col]['table'];
			$new['field']=$arr['header'][$col]['field'];
			$new['value']=$cell->getValue();
			if(is_null($new['value']))$new['value']='';
			$cols[]=$new;
		}
		$arr['data'][]=$cols;
	}
	
	$return_value=aw2_library::post_actions('all',$arr,$atts);
	return $return_value;
}



aw2_library::add_shortcode('excel','read_bulk', 'excel_read_bulk');

function excel_read_bulk($atts,$content=null,$shortcode){
	if(aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
	extract( shortcode_atts( array(
		'file_name'    =>'',
		'folder'		=>'',
		'file_format'    =>'Excel2007',
		'data' => '',
		'template_file'=>'',
		'template_folder'=>''
		
		), $atts) );
		
	/** Include PHPExcel */
	require_once ("wp-content/plugins/awesome-studio/monoframe/PHPExcel/PHPExcel.php");

	$objReader = PHPExcel_IOFactory::createReader('Excel2007');
	$inputFileType = 'Excel2007';
	//$inputFileName = 'c://temp//bs1.xlsx';
	$sheetname = 'Page 1'; // I DON'T WANT TO USE SHEET NAME HERE
	
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	$objReader->setLoadSheetsOnly($sheetname);
	$objPHPExcel = $objReader->load($file_name);
	$sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
	echo ' Highest Column ' . $getHighestColumn = $objPHPExcel->setActiveSheetIndex()->getHighestColumn(); // Get Highest Column
	echo ' Get Highest Row ' . $getHighestRow = $objPHPExcel->setActiveSheetIndex()->getHighestRow(); // Get Highest Row

	util::var_dump($sheetData);

}
