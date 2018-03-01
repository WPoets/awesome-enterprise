<?php

require_once 'Connector.php';

/**
 * ActiveCampgin Message class
 *
 * @since 2.0.3
 * 
 * @package Optin_Monster
 * @author Garry Gonzales
 */

class AC_Message extends AC_Connector {

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
	 * @param string @version   Version for ActiveCampaign
	 * @param string @url_base 	Base URL
	 * @param string @url 		URL
	 * @param string @api_key 	API Key
	 */
	public function __construct($version, $url_base, $url, $api_key) {
		$this->version = $version;
		$this->url_base = $url_base;
		$this->url = $url;
		$this->api_key = $api_key;
	}

	/**
	 * Add Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be added
	 *
	 * @return array @response response from the server
	 */	
	public function add($params, $post_data) {
		$request_url = "{$this->url}&api_action=message_add&api_output={$this->output}";
		$response = parent::curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Delete List Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */	
	public function delete_list($params) {
		$request_url = "{$this->url}&api_action=message_delete_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Delete Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */	
	public function delete($params) {
		$request_url = "{$this->url}&api_action=message_delete&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Edit Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be edited
	 *
	 * @return array @response response from the server
	 */	
	public function edit($params, $post_data) {
		$request_url = "{$this->url}&api_action=message_edit&api_output={$this->output}";
		$response = parent::curl($request_url, $post_data);
		return $response;
	}

	/**
	 * List Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */	
	public function list_($params) {
		$request_url = "{$this->url}&api_action=message_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}
	
	/**
	 * Tempalte Add Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be added
	 *
	 * @return array @response response from the server
	 */	
	public function template_add($params, $post_data) {
		$request_url = "{$this->url}&api_action=message_template_add&api_output={$this->output}";
		$response = parent::curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Template Delete List Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */	
	public function template_delete_list($params) {
		$request_url = "{$this->url}&api_action=message_template_delete_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Template Delete Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be added
	 *
	 * @return array @response response from the server
	 */	
	public function template_delete($params) {
		$request_url = "{$this->url}&api_action=message_template_delete&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Template Edit Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be edited
	 *
	 * @return array @response response from the server
	 */	
	public function template_edit($params, $post_data) {
		$request_url = "{$this->url}&api_action=message_template_edit&api_output={$this->output}";
		$response = parent::curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Template Export Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */	
	public function template_export($params) {
		$request_url = "{$this->url}&api_action=message_template_export&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Template Import Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be imported
	 *
	 * @return array @response response from the server
	 */	
	public function template_import($params, $post_data) {
		$request_url = "{$this->url}&api_action=message_template_import&api_output={$this->output}";
		$response = parent::curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Template List Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */	
	public function template_list($params) {
		$request_url = "{$this->url}&api_action=message_template_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Template View Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */	
	public function template_view($params) {
		$request_url = "{$this->url}&api_action=message_template_view&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Add Method for ActiveCampaign message
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */	
	public function view($params) {
		$request_url = "{$this->url}&api_action=message_view&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

}

?>