<?php
//  namespace aw2\zoho;

  \aw2_library::add_service('zoho.campaigns','Zoho campaigns',['namespace'=>__NAMESPACE__]);
  function campaigns($atts,$content=null,$shortcode){
      
    $return_value='';

    if( \aw2_library::pre_actions( 'all', $atts, $content ) == false )
      return;

    extract(\aw2_library::shortcode_atts( array(
      'main' => null,
      'listkey' => null
    ), $atts) );


    if( !$main ) {
      $return_value = 'Parameter Main not defined';
      unset($atts['main']);
    } else if((
            empty( \aw2_library::get('env.settings.opt-zoho-campaigns-enabled' ) ) ||
            "yes" !== \aw2_library::get('env.settings.opt-zoho-campaigns-enabled' )
        ) ||
        empty( \aw2_library::get('env.settings.opt-zoho-campaigns-api-base-url' ) )
    ) {
      $return_value = 'Zoho campaigns is not yet activated';
    } else if ( empty( \aw2_library::get('env.settings.opt-zoho-crm-campaigns-authtoken') ) ) {
      $return_value = 'Zoho campaigns authtoken is not Configured.';
    } else if ( empty( $listkey ) && empty( \aw2_library::get('env.settings.opt-zoho-crm-campaigns-listkey') ) ) {
      $return_value = 'Zoho campaigns listkey is not set.';
    } else {

      $zoho = new awesome_zoho_campaigns_wrapper( $main, $atts, $content);
      $return_value = $zoho->run();
    }

    if( is_string( $return_value ) )
      $return_value = trim( $return_value );

    if( is_object( $return_value ) )
     $return_value = 'Object';

     $return_value = \aw2_library::post_actions('all', $return_value, $atts);
    return $return_value;
  }

class awesome_zoho_campaigns_wrapper {

    public $module=null;
    public $action=null;
    public $atts=null;
    public $content=null;

    /**
      * Constructor for this class.
     */
    function __construct( $main, $atts, $content= null ){
      $this->main = $main;
      $this->atts = $atts;
      $this->content = trim($content);
    }

    /**
     * Run the shortcode.
     */
    public function run(){

      $return_value='';
      if ( method_exists( $this, $this->main ) ) {
        // Method exists pre-intailize Rest Client.
        return call_user_func(array($this, $this->main ));
      }
      else {
        $return_value = "Called main does not exists" ;
      }
    }

  /**
   * Subscribe.
   */
  private function subscribe() {

    $return_value ="";
    $params = "";
    $resource = "listsubscribe";
    $ch = curl_init();
    $url = \aw2_library::get('env.settings.opt-zoho-campaigns-api-base-url' ) ;
    $authtoken = \aw2_library::get('env.settings.opt-zoho-crm-campaigns-authtoken' ) ;

    //Json decode is not needed. Since API needs is as string.
    $this->parse_content_json();
    $content = preg_replace( '/"([^"]+)"\s*:"([^"]+)"\s*/', '$1:$2', $this->content );
    $content = \aw2_library::parse_shortcode($content);

    $data = array (
          "authtoken" => $authtoken,
          "scope" => "CampaignsAPI",
          "listkey" => isset( $this->atts['listkey'] ) ?  $this->atts['listkey'] :  \aw2_library::get('env.settings.opt-zoho-crm-campaigns-listkey') ,
          "resfmt" => 'JSON',
          "contactinfo" => urlencode( $content ),
          "sources" => isset( $this->atts['sources'] ) ?  $this->atts['sources'] : "direct"
        );

     foreach ( $data as $key => $value) {
       $params .= $key.'='.$value.'&';
     }
    $params = rtrim( $params, "&" );
    $url .= '/' . $resource . '?' . $params;

    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30 );

    $response = curl_exec($ch);

    $response  = json_decode( $response );
    $err = curl_error($ch);
    curl_close($ch);

    if( $err ) {
      $return_value = $err;
    } else if ( empty( $response  ) ) {
      $return_value = "No response Returned. Please Checck Configuration parameters.";
    } else if ( "error" ===  $response->status ) {
      $return_value = array();
      $return_value["code"] = $response->code;
      $return_value["message"] = $response->message;
    } else {
      $return_value = $response->message;
    }

    return $return_value;
  }

  /**
   * Parse Json object passed from content.
   */
  private function parse_content_json() {

    $json = \aw2_library::clean_specialchars( $this->content );
    $json = \aw2_library::parse_shortcode( $json );
    $json_obj = json_decode( $json, true );

    if( is_null(  $json_obj )) {
      throw new \Exception( 'Invalid JSON' );
    } else {
      return $json_obj;
    }
  }

}
