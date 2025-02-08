<?php
namespace aw2\regex;

function common_setup($atts, $content) {
    $items = \aw2\common\build_array($atts, $content);
    if(empty($items['pattern']))
        throw new \Exception('Pattern is required for regex split');
        
    if(!isset($items['text']))
        throw new \Exception('Text is required for regex split');
        
    if(trim($items['pattern']) === '') {
        throw new \Exception('Empty pattern is not allowed');
    }
    
    $flags = isset($items['flags']) ? $items['flags'] : '';
    // Validate flags
    $valid_flags = 'imsxADSUXJu';
    if($flags !== '' && !preg_match("/^[$valid_flags]+$/", $flags)) {
        throw new \Exception('Flags are Invalid');
    }
    return $items;
}

function handle_regex_error($error_code) {
    if($error_code === PREG_NO_ERROR) return null;
    
    $error_messages = array(
        PREG_INTERNAL_ERROR => 'Internal PCRE error',
        PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit exceeded',
        PREG_RECURSION_LIMIT_ERROR => 'Recursion limit exceeded',
        PREG_BAD_UTF8_ERROR => 'Invalid UTF-8 data',
        PREG_BAD_UTF8_OFFSET_ERROR => 'Invalid UTF-8 offset',
        PREG_JIT_STACKLIMIT_ERROR => 'PCRE JIT stack limit exceeded'
    );
    
    $error_message = isset($error_messages[$error_code]) 
        ? $error_messages[$error_code] 
        : "Unknown regex error (code: $error_code)";
        
    return array(
        'status' => false,
        'error' => $error_message,
        'error_code' => $error_code
    );
}

function handle_exceptions($e, $empty_result = null) {
    if($e instanceof \ValueError) {
        $error_code = -1;
        $error_prefix = 'Malformed pattern: ';
    }
    else if($e instanceof \Error) {
        $error_code = -2;
        $error_prefix = 'PHP Error: ';
    }
    else {
        $error_code = -3;
        $error_prefix = 'Exception: ';
    }
    
    $result = array(
        'status' => false,
        'error' => $error_prefix . $e->getMessage(),
        'error_code' => $error_code
    );
    
    if($empty_result !== null) {
        $result = array_merge($result, $empty_result);
    }
    
    return $result;
}

\aw2_library::add_service('regex.match', 'Match regex pattern against text', ['func'=>'regex_match', 'namespace'=>__NAMESPACE__]);

function regex_match($atts, $content=null, $shortcode=null) {
    $items = common_setup($atts, $content);
    try {
        $pattern = $items['pattern'];
        $text = $items['text'];
        $flags = isset($items['flags']) ? $items['flags'] : '';
        
        preg_last_error(); // Clear any previous regex errors
        $result = preg_match("/{$pattern}/{$flags}", $text);
        
        $error = handle_regex_error(preg_last_error());
        if($error) return $error;
        
        return array(
            'status' => (bool)$result,
            'error' => '',
            'error_code' => 0
        );
        
    } catch(\Throwable $e) {
        return handle_exceptions($e);
    }
}

\aw2_library::add_service('regex.extract', 'Extract all matches from text', ['func'=>'regex_extract', 'namespace'=>__NAMESPACE__]);

function regex_extract($atts, $content=null, $shortcode=null) {
    $items = common_setup($atts, $content);
    try {
        $pattern = $items['pattern'];
        $text = $items['text'];
        $flags = isset($items['flags']) ? $items['flags'] : '';
        
        $matches = array();
        preg_last_error(); // Clear any previous regex errors
        $result = preg_match_all("/{$pattern}/{$flags}", $text, $matches, PREG_PATTERN_ORDER);
        
        $error = handle_regex_error(preg_last_error());
        if($error) return $error;
        
        return array(
            'status' => true,
            'matches' => $matches[0],
            'error' => '',
            'error_code' => 0
        );
        
    } catch(\Throwable $e) {
        return handle_exceptions($e, array('matches' => array()));
    }
}

\aw2_library::add_service('regex.replace', 'Replace pattern matches in text', ['func'=>'regex_replace', 'namespace'=>__NAMESPACE__]);

function regex_replace($atts, $content=null, $shortcode=null) {
    $items = common_setup($atts, $content);
    if(!isset($items['replacement']))
    throw new \Exception('Replacement is required for regex replace');

$replacement = $items['replacement'];

try {
        $pattern = $items['pattern'];
        $text = $items['text'];
        $flags = isset($items['flags']) ? $items['flags'] : '';
        
        
        preg_last_error(); // Clear any previous regex errors
        $result = preg_replace("/{$pattern}/{$flags}", $replacement, $text);
        
        $error = handle_regex_error(preg_last_error());
        if($error) return $error;
        
        return array(
            'status' => true,
            'result' => $result,
            'error' => '',
            'error_code' => 0
        );
        
    } catch(\Throwable $e) {
        return handle_exceptions($e, array('result' => ''));
    }
}

\aw2_library::add_service('regex.split', 'Split text using pattern', ['func'=>'regex_split', 'namespace'=>__NAMESPACE__]);

function regex_split($atts, $content=null, $shortcode=null) {
    $items = common_setup($atts, $content);
    try {
        $pattern = $items['pattern'];
        $text = $items['text'];
        $flags = isset($items['flags']) ? $items['flags'] : '';
        
        preg_last_error(); // Clear any previous regex errors
        $result = preg_split("/{$pattern}/{$flags}", $text);
        
        $error = handle_regex_error(preg_last_error());
        if($error) return $error;
        
        return array(
            'status' => true,
            'parts' => $result,
            'error' => '',
            'error_code' => 0
        );
        
    } catch(\Throwable $e) {
        return handle_exceptions($e, array('parts' => array()));
    }
}

\aw2_library::add_service('regex.count', 'Count number of regex matches', ['func'=>'regex_count','namespace'=>__NAMESPACE__]);

function regex_count($atts, $content=null, $shortcode=null) {
    $items = common_setup($atts, $content);
    try {
        $pattern = $items['pattern'];
        $text = $items['text'];
        $flags = isset($items['flags']) ? $items['flags'] : '';
        
        preg_last_error(); // Clear any previous regex errors
        $count = preg_match_all("/{$pattern}/{$flags}", $text);
        
        $error = handle_regex_error(preg_last_error());
        if($error) return $error;
        
        return array(
            'status' => true,
            'count' => $count,
            'error' => '',
            'error_code' => 0
        );
        
    } catch(\Throwable $e) {
        return handle_exceptions($e, array('count' => 0));
    }
}