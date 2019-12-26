<?php

    \aw2_library::add_service('docx.parse_template','Parse the docx template',['namespace'=>__NAMESPACE__]);
    function parse_template($atts,$content=null,$shortcode){
        if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
        
        extract( shortcode_atts( array(
            'data'=>null,
            'path' => null,
            'filename' => 'Report.docx'
        ), $atts) );
        if(is_null($path)) return;
        
        $lib_path = plugin_dir_path( __DIR__ ) . 'libraries/docx-creator/vendor/autoload.php';
        require_once $lib_path;
        
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($path);
        foreach( $data['flat'] as $item=> $value ){
            $templateProcessor->setValue($item, $value);
        }
        
        foreach( $data['rows'] as $item=> $value ){
            $templateProcessor->cloneRowAndSetValues($item, $value);
        }
        
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        $templateProcessor->saveAs('php://output');
    }
?>