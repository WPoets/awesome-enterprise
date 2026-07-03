<?php

namespace aw2\arr;

// arr.to.json - Convert array to JSON string
\aw2_library::add_service('arr.to.json', 'Convert array to JSON string', ['func'=>'_to_json', 'namespace'=>__NAMESPACE__]);
function _to_json($atts, $content=null, $shortcode=null) {
   $main = \aw2\common\build_array($atts, $content);
   
   // Set default options
   $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
   
   // Add pretty print if requested
   if(isset($atts['pretty']) && $atts['pretty'] === 'yes') {
       $options |= JSON_PRETTY_PRINT;
   }
   
   return json_encode($main, $options);
}

// arr.to.csv - Convert array to CSV string
\aw2_library::add_service('arr.to.csv', 'Convert array to CSV string', ['func'=>'_to_csv', 'namespace'=>__NAMESPACE__]);
function _to_csv($atts, $content=null, $shortcode=null) {
   $main = \aw2\common\build_array($atts, $content);
   
   $output = fopen('php://temp', 'r+');
   
   // If headers are specified, use them
   if(isset($atts['headers'])) {
       $headers = explode(',', $atts['headers']);
       fputcsv($output, $headers);
   }
   // Otherwise use first row keys as headers
   else if(!empty($main)) {
       $first_row = reset($main);
       if(is_array($first_row)) {
           fputcsv($output, array_keys($first_row));
       }
   }
   
   // Write data rows
   foreach($main as $row) {
       if(is_array($row)) {
           fputcsv($output, array_values($row));
       } else {
           fputcsv($output, [$row]);
       }
   }
   
   rewind($output);
   $csv = stream_get_contents($output);
   fclose($output);
   
   return $csv;
}

// arr.to.query_string - Convert array to URL query string
\aw2_library::add_service('arr.to.query_string', 'Convert array to URL query string', ['func'=>'_to_query_string', 'namespace'=>__NAMESPACE__]);
function _to_query_string($atts, $content=null, $shortcode=null) {
   $main = \aw2\common\build_array($atts, $content);
   
   // Set encoding options
   $encoding = PHP_QUERY_RFC3986; // Strict RFC compliance
   if(isset($atts['encoding']) && $atts['encoding'] === 'php') {
       $encoding = PHP_QUERY_RFC1738; // PHP style encoding
   }
   
   return http_build_query($main, '', '&', $encoding);
}



// arr.to.nested - Transform flat array with dot notation keys to nested array structure
\aw2_library::add_service('arr.to.nested', 'Transform flat array with dot notation to nested structure', ['func'=>'to_nested', 'namespace'=>__NAMESPACE__]);

function to_nested($atts, $content=null, $shortcode=null){
    if(!isset($atts['main']))
        throw new \Exception('No input array provided');
    
    $input = $atts['main'];
    
    // Validate input is array
    if(!is_array($input))
        throw new \Exception('Input must be an array');
    
    $result = array();
    
    // Process each item in input array
    foreach($input as $item){
        if(!is_array($item)) continue;
        
        $temp = array();
        foreach($item as $key => $value){
            // Skip if key doesn't contain dots
            if(strpos($key, '.') === false){
                $temp[$key] = $value;
                continue;
            }
            
            // Split key by dots
            $parts = explode('.', $key);
            
            // Build nested structure
            $current = &$temp;
            $last_key = array_pop($parts);
            
            foreach($parts as $part){
                if(!isset($current[$part])){
                    $current[$part] = array();
                }
                $current = &$current[$part];
            }
            
            // Set final value
            $current[$last_key] = $value;
        }
        
        $result[] = $temp;
    }
    
    return $result;
}