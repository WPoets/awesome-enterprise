<?php

/**
 * load Associated class
 *
 * @since 2.0.3
 */
require_once 'ActiveCampaign/Connector.php';
require_once 'ActiveCampaign/Account.php';
require_once 'ActiveCampaign/Auth.php';
require_once 'ActiveCampaign/Campaign.php';
require_once 'ActiveCampaign/Contact.php';
require_once 'ActiveCampaign/Design.php';
require_once 'ActiveCampaign/Form.php';
require_once 'ActiveCampaign/Group.php';
require_once 'ActiveCampaign/List.php';
require_once 'ActiveCampaign/Message.php';
require_once 'ActiveCampaign/Subscriber.php';
require_once 'ActiveCampaign/Tracking.php';
require_once 'ActiveCampaign/User.php';
require_once 'ActiveCampaign/Webhook.php';

/**
 * Main Class for ActiveCampaign API
 * 
 * @since 2.0.3
 * 
 * @package Optin_Monster
 * @author Garry Gonzales
 */
class ActiveCampaign {

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
     * API key
     *
     * @since 2.0.3
     *
     * @var string
     */
    public $api_key;

    /**
     * Tracker Email
     *
     * @since 2.0.3
     *
     * @var string
     */
    public $track_email;

    /**
     * Tracker account ID
     *
     * @since 2.0.3
     *
     * @var string
     */
    public $track_actid;

    /**
     * Tacker key
     *
     * @since 2.0.3
     *
     * @var string
     */
    public $track_key;

    /**
     * Version
     *
     * @since 2.0.3
     *
     * @var int
     */
    public $version = 1;

    /**
     * Debug
     *
     * @since 2.0.3
     *
     * @var bool
     */
    public $debug = false;

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
     */
    public function __construct($url, $api_key, $api_user = "", $api_pass = ""){

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
     * Method for version
     *
     * @since 2.0.3
     */
    public function version($version) {
        $this->version = (int)$version;
        if ($version == 2) {
            $this->url_base = $this->url_base . "/2";
        }
    }

    /**
     * Method for api call
     * 
     * @since 2.0.3
     *
     * @param string @path       call for api path.
     * @param array  @post_data  data for the api call
     *
     * @return array @response   response from the api call
     */
    function api($path, $post_data = array()) {
        // IE: "contact/view"
        $components = explode("/", $path);
        $component = $components[0];

        if (count($components) > 2) {
            // IE: "contact/tag/add?whatever"
            // shift off the first item (the component, IE: "contact").
            array_shift($components);
            // IE: convert to "tag_add?whatever"
            $method_str = implode("_", $components);
            $components = array($component, $method_str);
        }

        if (preg_match("/\?/", $components[1])) {
            // query params appended to method
            // IE: contact/edit?overwrite=0
            $method_arr = explode("?", $components[1]);
            $method = $method_arr[0];
            $params = $method_arr[1];
        }
        else {
            // just a method provided
            // IE: "contact/view
            if ( isset($components[1]) ) {
                $method = $components[1];
                $params = "";
            }
            else {
                return "Invalid method.";
            }
        }

        // adjustments
        if ($component == "list") {
            // reserved word
            $component = "list_";
        }
        elseif ($component == "branding") {
            $component = "design";
        }
        elseif ($component == "sync") {
            $component = "contact";
            $method = "sync";
        }
        elseif ($component == "singlesignon") {
            $component = "auth";
        }

        $class = ucwords($component); // IE: "contact" becomes "Contact"
        $class = "AC_" . $class;
        // IE: new Contact();

        $add_tracking = false;
        if ($class == "AC_Tracking") $add_tracking = true;

        $class = new $class($this->version, $this->url_base, $this->url, $this->api_key);
        // IE: $contact->view()

        if ($add_tracking) {
            $class->track_email = $this->track_email;
            $class->track_actid = $this->track_actid;
            $class->track_key = $this->track_key;
        }

        if ($method == "list") {
            // reserved word
            $method = "list_";
        }

        $class->debug = $this->debug;

        $response = $class->$method($params, $post_data);
        return $response;
    }
}

?>