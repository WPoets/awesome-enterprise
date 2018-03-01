<?php

/**
 * ActiveCampgin Connector class
 *
 * @since 2.0.3
 * 
 * @package Optin_Monster
 * @author Garry Gonzales
 */
class AC_Connector {

	/**
	 * URL
	 *
	 * @since 2.0.3
	 *
	 * @var string
	 */
	public $url;

	/**
	 * API Key
	 *
	 * @since 2.0.3
	 *
	 * @var string
	 */
	public $api_key;

	/**
	 * JSON Output
	 *
	 * @since 2.0.3
	 *
	 * @var string
	 */
	public $output = "serialize";

	/**
	 * Primary class constructor
	 *
	 * @since 2.0.3
	 *
	 * @param string @url 		URL
	 * @param string @api_key 	API Key
	 * @param string @api_user 	API User
	 * @param string @api_pass 	API Password
	 */
	public function __construct($url, $api_key, $api_user = "", $api_pass = "") {
		// $api_pass should be md5() already
		$base = "";
		if (!preg_match("/https:\/\/www.activecampaign.com/", $url)) {
			// not a reseller
			$base = "/admin";
		}
		if (preg_match("/\/$/", $url)) {
			// remove trailing slash
			$url = substr($url, 0, strlen($url) - 1);
		}
		if ($api_key) {
			$this->url = "{$url}{$base}/api.php?api_key={$api_key}";
		}
		elseif ($api_user && $api_pass) {
			$this->url = "{$url}{$base}/api.php?api_user={$api_user}&api_pass={$api_pass}";
		}
		$this->api_key = $api_key;
	}

	/**
     * Credentials Test Method
     *
     * @since 2.0.3
     *
     * @return bool @checked returns the result of the test.
     */
    public function credentials_test() {
        $test_url = "{$this->url}&api_action=user_me&api_output={$this->output}";
        $checked = $this->curl($test_url);
        if (is_object($checked) && (int)$checked->result_code) {
            // successful
            $checked = true;
        }
        else {
            // failed
            $checked = false;
        }
        
        if(!$checked) :
            throw new Exception('Invalid Credentials, Try Again.');
        endif;

        return $checked;

    }

	/**
	 * Debug Method
	 *
	 * @since 2.0.3
	 *
	 * @param array  @var       holds the variables for the debugging
	 * @param int    @continue  holds the response if debugging want to be continued.
	 * @param string @element 	holds the elements
	 * @param string @extra 	holds the extra	
	 */
	public function dbg($var, $continue = 0, $element = "pre", $extra = "") {
	  echo "<" . $element . ">";
	  echo "Vartype: " . gettype($var) . "\n";
	  if ( is_array($var) ) echo "Elements: " . count($var) . "\n";
	  elseif ( is_string($var) ) echo "Length: " . strlen($var) . "\n";
	  if ($extra) {
	  	echo $extra . "\n";
	  }
	  echo "\n";
	  print_r($var);
	  echo "</" . $element . ">";
		if (!$continue) exit();
	}

	/**
	 * Curl Method
	 *
	 * @since 2.0.3
	 *
	 * @param string @url           holds the url for the curl method
	 * @param array  @params_data   holds the data for the curl
	 * @param string @verb 		    holds the verb
	 * @param string @custom_method holds the custom method
	 *
	 * @return object @object returns an object to the curl
	 */
	public function curl($url, $params_data = array(), $verb = "", $custom_method = "") {
    	if ($this->version == 1) {
			// find the method from the URL.
			$method = preg_match("/api_action=[^&]*/i", $url, $matches);
			if ($matches) {
				$method = preg_match("/[^=]*$/i", $matches[0], $matches2);
				$method = $matches2[0];
			} elseif ($custom_method) {
				$method = $custom_method;
			}
		} elseif ($this->version == 2) {
			$method = $custom_method;
			$url .= "?api_key=" . $this->api_key;
		}
		$debug_str1 = "";
		$request = curl_init();
		$debug_str1 .= "\$ch = curl_init();\n";
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		$debug_str1 .= "curl_setopt(\$ch, CURLOPT_HEADER, 0);\n";
		$debug_str1 .= "curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);\n";
		if ($params_data && $verb == "GET") {
			if ($this->version == 2) {
				$url .= "&" . $params_data;
				curl_setopt($request, CURLOPT_URL, $url);
			}
		}
		else {
			curl_setopt($request, CURLOPT_URL, $url);
			if ($params_data && !$verb) {
				// if no verb passed but there IS params data, it's likely POST.
				$verb = "POST";
			} elseif ($params_data && $verb) {
				// $verb is likely "POST" or "PUT".
			} else {
				$verb = "GET";
			}
		}
		$debug_str1 .= "curl_setopt(\$ch, CURLOPT_URL, \"" . $url . "\");\n";
		if ($this->debug) {
			$this->dbg($url, 1, "pre", "Description: Request URL");
		}
		if ($verb == "POST" || $verb == "PUT" || $verb == "DELETE") {
			if ($verb == "PUT") {
				curl_setopt($request, CURLOPT_CUSTOMREQUEST, "PUT");
				$debug_str1 .= "curl_setopt(\$ch, CURLOPT_CUSTOMREQUEST, \"PUT\");\n";
			} elseif ($verb == "DELETE") {
				curl_setopt($request, CURLOPT_CUSTOMREQUEST, "DELETE");
				$debug_str1 .= "curl_setopt(\$ch, CURLOPT_CUSTOMREQUEST, \"DELETE\");\n";
			} else {
				$verb = "POST";
				curl_setopt($request, CURLOPT_POST, 1);
				$debug_str1 .= "curl_setopt(\$ch, CURLOPT_POST, 1);\n";
			}
			$data = "";
			if (is_array($params_data)) {
				foreach($params_data as $key => $value) {
					if (is_array($value)) {

						if (is_int($key)) {
							// array two levels deep
							foreach ($value as $key_ => $value_) {
								if (is_array($value_)) {
									foreach ($value_ as $k => $v) {
										$k = urlencode($k);
										$data .= "{$key_}[{$key}][{$k}]=" . urlencode($v) . "&";
									}
								}
								else {
									$data .= "{$key_}[{$key}]=" . urlencode($value_) . "&";
								}
							}
						}
						else {
							// IE: [group] => array(2 => 2, 3 => 3)
							// normally we just want the key to be a string, IE: ["group[2]"] => 2
							// but we want to allow passing both formats
							foreach ($value as $k => $v) {
								if (!is_array($v)) {
									$k = urlencode($k);
									$data .= "{$key}[{$k}]=" . urlencode($v) . "&";
								}
							}
						}

					}
					else {
						$data .= "{$key}=" . urlencode($value) . "&";
					}
				}
			}
			else {
				// not an array - perhaps serialized or JSON string?
				// just pass it as data
				$data = "data={$params_data}";
			}

			$data = rtrim($data, "& ");
			curl_setopt($request, CURLOPT_HTTPHEADER, array("Expect:"));
			$debug_str1 .= "curl_setopt(\$ch, CURLOPT_HTTPHEADER, array(\"Expect:\"));\n";
			if ($this->debug) {
				$this->dbg($data, 1, "pre", "Description: POST data");
			}
			curl_setopt($request, CURLOPT_POSTFIELDS, $data);
			$debug_str1 .= "curl_setopt(\$ch, CURLOPT_POSTFIELDS, \"" . $data . "\");\n";
		}
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
		$debug_str1 .= "curl_setopt(\$ch, CURLOPT_SSL_VERIFYPEER, false);\n";
		$debug_str1 .= "curl_setopt(\$ch, CURLOPT_SSL_VERIFYHOST, 0);\n";
		$debug_str1 .= "curl_setopt(\$ch, CURLOPT_FOLLOWLOCATION, true);\n";
		$response = curl_exec($request);
		$debug_str1 .= "curl_exec(\$ch);\n";
		
		$object = unserialize($response);

		return $object;
    }
}

?>