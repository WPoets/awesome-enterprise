<?php

require_once 'Connector.php';

/**
 * ActiveCampgin Tacking class
 *
 * @since 2.0.3
 * 
 * @package Optin_Monster
 * @author Garry Gonzales
 */
class AC_Tracking extends AC_Connector {

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
	 * Site Status Method for ActiveCampaign tracking
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be updated
	 *
	 * @return array @response response from the server
	 */	
	public function site_status($params, $post_data) {
		// version 2 only.
		$request_url = "{$this->url_base}/track/site";
		$response = parent::curl($request_url, $post_data, "POST", "tracking_site_status");
		return $response;
	}

	/**
	 * Event Status Method for ActiveCampaign tracking
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be updated
	 *
	 * @return array @response response from the server
	 */	
	public function event_status($params, $post_data) {
		// version 2 only.
		$request_url = "{$this->url_base}/track/event";
		$response = parent::curl($request_url, $post_data, "POST", "tracking_event_status");
		return $response;
	}

	/**
	 * Site List Method for ActiveCampaign tracking
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be updated
	 *
	 * @return array @response response from the server, holds the whitelisted domains.
	 */	
	public function site_list($params) {
		if ($this->version == 1) {
			// not supported currently.
			//$request_url = "{$this->url}&api_action=contact_delete_list&api_output={$this->output}&{$params}";
		} elseif ($this->version == 2) {
			$request_url = "{$this->url_base}/track/site";
		}
		$response = parent::curl($request_url, array(), "GET", "tracking_site_list");
		return $response;
	}

	/**
	 * Event List Method for ActiveCampaign tracking
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be updated
	 *
	 * @return array @response response from the server, holds the existing tracked events.
	 */	
	public function event_list($params) {
		if ($this->version == 1) {
			// not supported currently.
			//$request_url = "{$this->url}&api_action=contact_delete_list&api_output={$this->output}&{$params}";
		} elseif ($this->version == 2) {
			$request_url = "{$this->url_base}/track/event";
		}
		$response = parent::curl($request_url, array(), "GET", "tracking_event_list");
		return $response;
	}

	/**
	 * Whitelist Method for ActiveCampaign tracking, adds a domain to the site tracking whitelist
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be added
	 *
	 * @return array @response response from the server
	 */	
	public function whitelist($params, $post_data) {
		// version 2 only.
		$request_url = "{$this->url_base}/track/site";
		$response = parent::curl($request_url, $post_data, "PUT", "tracking_whitelist");
		return $response;
	}

	/**
	 * Whitelist Remove Method for ActiveCampaign tracking, remove a domain to the site tracking whitelist
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be removed
	 *
	 * @return array @response response from the server
	 */	
	public function whitelist_remove($params, $post_data) {
		// version 2 only.
		$request_url = "{$this->url_base}/track/site";
		$response = parent::curl($request_url, $post_data, "DELETE", "tracking_whitelist");
		return $response;
	}

	/**
	 * Event Remove Remove Method for ActiveCampaign tracking, removes an event
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be removed
	 *
	 * @return array @response response from the server
	 */	
	public function event_remove($params, $post_data) {
		// version 2 only.
		$request_url = "{$this->url_base}/track/event";
		$response = parent::curl($request_url, $post_data, "DELETE", "tracking_event_remove");
		return $response;
	}

	/**
	 * Event Remove Remove Method for ActiveCampaign tracking, adds new event
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be added
	 *
	 * @return array @response response from the server
	 */	
	public function log($params, $post_data) {
		$request_url = "https://trackcmp.net/event";
		$post_data["actid"] = $this->track_actid;
		$post_data["key"] = $this->track_key;
		$visit_data = array();
		if ($this->track_email) {
			$visit_data["email"] = $this->track_email;
		}
		if (isset($post_data["visit"])) {
			$visit_data = array_merge($visit_data, $post_data["visit"]);
		}
		if ($visit_data) {
			$post_data["visit"] = json_encode($visit_data);
		}
		$response = parent::curl($request_url, $post_data, "POST", "tracking_log");
		return $response;
	}

}

?>