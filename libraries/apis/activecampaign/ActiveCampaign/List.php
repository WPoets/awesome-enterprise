<?php

require_once 'Connector.php';

/**
 * ActiveCampgin List class
 *
 * @since 2.0.3
 * 
 * @package Optin_Monster
 * @author Garry Gonzales
 */
class AC_List_ extends AC_Connector {

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
	 * Add Method for ActiveCampaign list
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be added
	 *
	 * @return array @response response from the server
	 */	
	public function add($params, $post_data) {
		$request_url = "{$this->url}&api_action=list_add&api_output={$this->output}";
		$response = parent::curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Add Method for ActiveCampaign list
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */	
	public function delete_list($params) {
		$request_url = "{$this->url}&api_action=list_delete_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Delete Method for ActiveCampaign list
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */	
	public function delete($params) {
		$request_url = "{$this->url}&api_action=list_delete&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Edit Method for ActiveCampaign list
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be edited
	 *
	 * @return array @response response from the server
	 */	
	public function edit($params, $post_data) {
		$request_url = "{$this->url}&api_action=list_edit&api_output={$this->output}";
		$response = parent::curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Field Add Method for ActiveCampaign list
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be added
	 *
	 * @return array @response response from the server
	 */	
	public function field_add($params, $post_data) {
		$request_url = "{$this->url}&api_action=list_field_add&api_output={$this->output}";
		$response = parent::curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Field Delete Method for ActiveCampaign list
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */	
	public function field_delete($params) {
		$request_url = "{$this->url}&api_action=list_field_delete&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Field Edit Method for ActiveCampaign list
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be edited
	 *
	 * @return array @response response from the server
	 */
	public function field_edit($params, $post_data) {
		$request_url = "{$this->url}&api_action=list_field_edit&api_output={$this->output}";
		$response = parent::curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Field View Method for ActiveCampaign list
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function field_view($params) {
		$request_url = "{$this->url}&api_action=list_field_view&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * List Method for ActiveCampaign list
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be listed
	 *
	 * @return array @response response from the server
	 */
	public function list_($params, $post_data) {
		if ($post_data) {
			if (isset($post_data["ids"]) && is_array($post_data["ids"])) {
				// make them comma-separated.
				$post_data["ids"] = implode(",", $post_data["ids"]);
			}
		}else{
			$post_data["ids"] = "all";
		}
		$request_url = "{$this->url}&api_action=list_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Paginator Method for ActiveCampaign list
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function paginator($params) {
		$request_url = "{$this->url}&api_action=list_paginator&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * View Method for ActiveCampaign list
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function view($params) {
		$request_url = "{$this->url}&api_action=list_view&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

}

?>