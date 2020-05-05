<?php 

class pdf_parser {

	function __construct($filename, $data_format = NULL, $give_attachments = false, $attachment_extension) {
		$this->data = $this->parseForm($filename, $data_format, $give_attachments, $attachment_extension);
	}
	
	function parseForm($file_path, $data_format = '', $give_attachments = false, $attachment_extension)
	{
		$start = false;
	    $content = @file_get_contents($file_path, FILE_BINARY);
		
        if($give_attachments){
			preg_match_all('/Type\/Filespec\/UF\((.*?)\)>>/', $content, $match);
			
			$file_names = $match[1];
			preg_match_all("#$attachment_extension>>stream(.*)endstream#ismU", $content, $attachments); 
			$attachments = $attachments[1];
			
			for($i=0; $i < count($attachments); $i++){
				$final_attrs[$file_names[$i]] = @gzuncompress(trim($attachments[$i]));	
			}
		}
		
		/**
		* Split apart the PDF document into sections. We will address each
		* section separately.
		*/
		preg_match_all("#obj(.*)endobj#ismU", $content, $a_objs); 
		$a_obj = @$a_objs[1];
		  
        $j        = 0;
		$a_chunks = array();
		/**
		* Attempt to extract each part of the PDF document into a 'filter'
		* element and a 'data' element. This can then be used to decode the
		* data.
		*/
		foreach ($a_obj as $k => $obj) {
			$obj = ltrim($obj);
            preg_match("#<<(.*)>>#ismU", $obj, $a_filter);
			if (is_array($a_filter) && isset($a_filter[0])) {
                $a_chunks[$j]['filter'] = $a_filter[1];
			    preg_match("#stream(.*)endstream#ismU", $obj, $stream);
                $a_data = ltrim($stream[1]);
                $a_chunks[$j]['data'] = $a_data;
				$j++;
			}
		}
		$result_data = null;
		// decode the chunks
		foreach ($a_chunks as $key => $chunk) {
		// Look at each chunk decide if we can decode it by looking at the contents of the filter
			if (isset($chunk['data'])) {
				if (strpos($chunk['filter'], 'FlateDecode') !== false) {
					// Use gzuncompress but suppress error messages.
					$data =@ gzuncompress($chunk['data']);
				}
				else if (strpos($chunk['filter'], '<>') !== false) {
					$data =@ zlib_decode ($chunk['data']);
				}
				else {
					$data = $chunk['data'];
				}
				if (trim($data) != '') {
					// If we got data then attempt to extract it.
					if (strpos($data, '/CIDInit') === 0) {
						continue;
					}
					$text  = '';
					$lines = explode("\n", $data);
					foreach ($lines as $line) {
						$collected[] = trim($line);
					}
					$start = false;
				}
			}
		}

		$tags = implode('', $collected);

		/* Fetch only the needed node data */
        preg_match_all('/<xfa:data>(.*?)<\/xfa:data/', $tags, $match);
		$xmlstring = array_pop($match[1]);

		/* Sometimes its taking the closing tag from the last element to here */
		if($xmlstring{0}=='>'){ $xmlstring = substr($xmlstring, 1); }
		/* Sometimes its missing the closing tag becuase of the next stream to here */
		if($xmlstring{strlen($xmlstring)-1}!='>'){ $xmlstring .= '>'; }

		/* Remove special characters from the tags <frm:data> */
		$xmlstring = preg_replace(array('/<([a-zA-Z0-9 _-])+[@|:|,|*|!|]/', '/<\/([a-zA-Z0-9 _-])+[@|:|,|*|!|]/'), array('<','</'), $xmlstring);
        /* Load the xml data and get it parsed */
        $xml = simplexml_load_string(utf8_encode((string)$xmlstring), "SimpleXMLElement", LIBXML_NOCDATA);

		if('' == $data_format){
			return $xml;
		}
		$json = json_encode($xml);
		if('json' == $data_format){
			$json = json_encode($xml);
			return $json;
		}
		if('array' == $data_format){
			$arr = json_decode($json, true);
			if($final_attrs){
				$arr['aw2_attachments'] = $final_attrs;
			}
			return $arr;
		}

	}
	/**
	* Convert a section of data into an array, separated by the start and end words.
	*
	* @param  string $data       The data.
	* @param  string $start_word The start of each section of data.
	* @param  string $end_word   The end of each section of data.
	* @return array              The array of data.
	*/
	
	/*
    function getDataArray($data, $start_word, $end_word)
	{
		$start     = 0;
		$end       = 0;
		$a_results = array();
		while ($start !== false && $end !== false) {
			$start = strpos($data, $start_word, $end);
			$end   = strpos($data, $end_word, $start);
			if ($end !== false && $start !== false) {
				// data is between start and end
				$a_results[] = substr($data, $start, $end - $start + strlen($end_word));
			}
		}
		return $a_results;
	}
	*/
}

/*
$obj = new pdf_parser('/var/www/09-Form CHG-1-05072016.pdf', 'array');
print_r($obj->data);
*/