<?php

require_once 'Connector.php';

/**
 * ActiveCampgin Contact class
 *
 * @since 2.0.3
 * 
 * @package Optin_Monster
 * @author Garry Gonzales
 */
class AC_Contact extends AC_Connector {

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
	 * Add Method for ActiveCampaign contact
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be added
	 *
	 * @return array @response response from the server
	 */	
	function add($params, $post_data) {
		$request_url = "{$this->url}&api_action=contact_add&api_output={$this->output}";
		if ($params) $request_url .= "&{$params}";
		$response = parent::curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Delete List Method for ActiveCampaign contact
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	function delete_list($params) {
		$request_url = "{$this->url}&api_action=contact_delete_list&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	/**
	 * Delete Method for ActiveCampaign contact
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	function delete($params) {
		$request_url = "{$this->url}&api_action=contact_delete&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	/**
	 * Edit Method for ActiveCampaign contact
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	function edit($params, $post_data) {
		$request_url = "{$this->url}&api_action=contact_edit&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url, $post_data);
		return $response;
	}

	/**
	 * List Method for ActiveCampaign contact
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	function list_($params) {
		if ($this->version == 1) {
			$request_url = "{$this->url}&api_action=contact_list&api_output={$this->output}&{$params}";
			$response = $this->curl($request_url);
		} elseif ($this->version == 2) {
			$request_url = "{$this->url_base}/contact/emails";
			// $params example: offset=0&limit=1000&listid=4
			$response = $this->curl($request_url, $params, "GET", "contact_list");
		}
		return $response;
	}

	/**
	 * Note Add Method for ActiveCampaign contact
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be added
	 *
	 * @return array @response response from the server
	 */
	function note_add($params, $post_data) {
		$request_url = "{$this->url}&api_action=contact_note_add&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Note Edit Method for ActiveCampaign contact
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be edit
	 *
	 * @return array @response response from the server
	 */
	function note_edit($params, $post_data) {
		$request_url = "{$this->url}&api_action=contact_note_edit&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Note Delete Method for ActiveCampaign contact
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	function note_delete($params) {
		$request_url = "{$this->url}&api_action=contact_note_delete&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	/**
	 * Paginator Method for ActiveCampaign contact
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	function paginator($params) {
		$request_url = "{$this->url}&api_action=contact_paginator&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	/**
	 * Sync Method for ActiveCampaign contact
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be sync
	 *
	 * @return array @response response from the server
	 */
	function sync($params, $post_data) {
		$request_url = "{$this->url}&api_action=contact_sync&api_output={$this->output}";
		if ($params) $request_url .= "&{$params}";
		$response = $this->curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Tag Add Method for ActiveCampaign contact
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be added
	 *
	 * @return array @response response from the server
	 */
	function tag_add($params, $post_data) {
		$request_url = "{$this->url}&api_action=contact_tag_add&api_output={$this->output}";
		if ($params) $request_url .= "&{$params}";
		$response = $this->curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Tag Remove Method for ActiveCampaign contact
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be remove
	 *
	 * @return array @response response from the server
	 */
	function tag_remove($params, $post_data) {
		$request_url = "{$this->url}&api_action=contact_tag_remove&api_output={$this->output}";
		if ($params) $request_url .= "&{$params}";
		$response = $this->curl($request_url, $post_data);
		return $response;
	}

	/**
	 * View Method for ActiveCampaign contact
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	function view($params) {
		// can be a contact ID, email, or hash
		if (preg_match("/^email=/", $params)) {
			$action = "contact_view_email";
		}
		elseif (preg_match("/^hash=/", $params)) {
			$action = "contact_view_hash";
		}
		elseif (preg_match("/^id=/", $params)) {
			$action = "contact_view";
		}
		else {
			// default
			$action = "contact_view";
		}
		$request_url = "{$this->url}&api_action={$action}&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

}

?>