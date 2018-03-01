<?php

/**
 * ActiveCampgin Automation class
 *
 * @since 2.0.3
 * 
 * @package Optin_Monster
 * @author Garry Gonzales
 */
class AC_Automation {

	/**
	 * Version
	 *
	 * @since 2.0.3
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Base URL
	 *
	 * @since 2.0.3
	 *
	 * @var string
	 */
	public $url_base;

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
	 * Primary class constructor
	 *
	 * @since 2.0.3
	 *
	 * @param string @version   Version for ActiveCampaign
	 * @param string @url_base 	Base URL
	 * @param string @url 		URL
	 * @param string @api_key 	API Key
	 */
	function __construct($version, $url_base, $url, $api_key) {
		$this->version = $version;
		$this->url_base = $url_base;
		$this->url = $url;
		$this->api_key = $api_key;
	}

	/**
	 * List Method for ActiveCampaign Automation
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array response from the server
	 */
	function list_($params) {
		$request_url = "{$this->url}&api_action=automation_list&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	/**
	 * Add Contact Method for ActiveCampaign Automation
	 *
	 * @since 2.0.3
	 *
	 * @param array @params    paramters for the method
	 * @param array @post_data data to be added
	 *
	 * @return array @response response from the server
	 */
	function contact_add($params, $post_data) {
		$request_url = "{$this->url}&api_action=automation_contact_add&api_output={$this->output}";
		if ($params) $request_url .= "&{$params}";
		$response = $this->curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Remove Contact Method for ActiveCampaign Automation
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be remove
	 *
	 * @return array @response response from the server
	 */
	function contact_remove($params, $post_data) {
		$request_url = "{$this->url}&api_action=automation_contact_remove&api_output={$this->output}";
		if ($params) $request_url .= "&{$params}";
		$response = $this->curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Contact List Method for ActiveCampaign Automation
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	function contact_list($params) {
		$request_url = "{$this->url}&api_action=automation_contact_list&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	/**
	 * Contact View Method for ActiveCampaign Automation
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	function contact_view($params) {
		$request_url = "{$this->url}&api_action=automation_contact_view&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

}

?>