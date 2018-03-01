<?php

require_once 'Connector.php';

/**
 * ActiveCampgin Campaign class
 *
 * @since 2.0.3
 * 
 * @package Optin_Monster
 * @author Garry Gonzales
 */
class AC_Campaign extends AC_Connector {

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
	 * Create Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be created
	 *
	 * @return array @response response from the server
	 */
	public function create($params, $post_data) {
		$request_url = "{$this->url}&api_action=campaign_create&api_output={$this->output}";
		$response = parent::curl($request_url, $post_data);
		return $response;
	}

	/**
	 * Create Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 * @param array @post_data data to be added
	 *
	 * @return array @response response from the server
	 */
	public function delete_list($params) {
		$request_url = "{$this->url}&api_action=campaign_delete_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Delete Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function delete($params) {
		$request_url = "{$this->url}&api_action=campaign_delete&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * List Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function list_($params) {
		$request_url = "{$this->url}&api_action=campaign_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Paginator Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function paginator($params) {	
		$request_url = "{$this->url}&api_action=campaign_paginator&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Report Bounce List Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function report_bounce_list($params) {
		$request_url = "{$this->url}&api_action=campaign_report_bounce_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Report Bounce Total Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function report_bounce_totals($params) {
		$request_url = "{$this->url}&api_action=campaign_report_bounce_totals&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Report Forward List Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function report_forward_list($params) {
		$request_url = "{$this->url}&api_action=campaign_report_forward_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Report Forward Totals Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function report_forward_totals($params) {
		$request_url = "{$this->url}&api_action=campaign_report_forward_totals&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Report Link List Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function report_link_list($params) {
		$request_url = "{$this->url}&api_action=campaign_report_link_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Report Link Totals Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function report_link_totals($params) {
		$request_url = "{$this->url}&api_action=campaign_report_link_totals&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Report Open List Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function report_open_list($params) {
		$request_url = "{$this->url}&api_action=campaign_report_open_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Report Open Totals Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function report_open_totals($params) {
		$request_url = "{$this->url}&api_action=campaign_report_open_totals&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Report Totals Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function report_totals($params) {
		$request_url = "{$this->url}&api_action=campaign_report_totals&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Report Unopen List Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function report_unopen_list($params) {
		$request_url = "{$this->url}&api_action=campaign_report_unopen_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Report Unsubscription List Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function report_unsubscription_list($params) {
		$request_url = "{$this->url}&api_action=campaign_report_unsubscription_list&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Report Unsubscription Totals Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function report_unsubscription_totals($params) {
		$request_url = "{$this->url}&api_action=campaign_report_unsubscription_totals&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

	/**
	 * Send Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function send($params) {
		$request_url = "{$this->url}&api_action=campaign_send&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}
	
	/**
	 * Status Method for ActiveCampaign campaign
	 *
	 * @since 2.0.3
	 *
	 * @param array @params paramters for the method
	 *
	 * @return array @response response from the server
	 */
	public function status($params) {
		$request_url = "{$this->url}&api_action=campaign_status&api_output={$this->output}&{$params}";
		$response = parent::curl($request_url);
		return $response;
	}

}

?>