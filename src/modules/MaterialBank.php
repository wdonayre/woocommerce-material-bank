<?php 

namespace WooMaterialBank\Modules;

use WooMaterialBank\Interfaces\InterfaceAdminOption;
use WooMaterialBank\AdminOptions;

use Carbon_Fields\Field;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\ResponseInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class MaterialBank implements InterfaceAdminOption{

  private static $instance;
    
  private $plugin;


  private function __construct() {
      $this->plugin = \WooMaterialBank\Plugin::instance();
  }
  

  public static function instance()
  {
      if (!self::$instance) {
          self::$instance = new self();
      }
      
      return self::$instance;
  }

  public static function init() {
    $instance = self::instance();
    AdminOptions::instance()->register(self::instance());
  }

  public function renderAdminOption($container){
       $container->add_tab(
        __('Material Bank Integration'),
        [
          Field::make( 'checkbox', 'crb_sandbox_mode', 'Sandbox Mode' ),
          Field::make( 'text', 'crb_material_api_key', __( 'Production API Key' ) ),
          Field::make( 'text', 'crb_material_api_key_sandbox', __( 'Sandbox API Key' ) ),
          
          Field::make( 'separator', 'crb_separator', __( 'MaterialBank Account Details' ) ),
          Field::make( 'text', 'crb_material_mbid', __( 'MB ID' ) )->set_width(30),
          Field::make( 'text', 'crb_material_company', __( 'Company Name' ) )->set_width(40),

          Field::make( 'select', 'crb_material_company_type', __( 'Company Type' ) )
          ->set_options( array(
            'CS' => 'Consumer Orders',
            'TO' => 'Trade Orders'
          ) )
          ->set_width(30),

          //OPTIONS
          Field::make( 'separator', 'crb_separator_options', __( 'Options' ) ),
          Field::make( 'checkbox', 'crb_disable_line_items_action', __( 'Disable Line Items Action' ) )->set_width(30),


        ]
   );
  }

  /* ========================================= */
  public function sendOrder($payload){
    error_log('send order >>');
    error_log(json_encode($payload));


    // return true; //TEMP

    ////$base_api_uri = 'https://reqres.in/api';

    $base_api_uri = WCMB_API_HOST;
    $api_key = WCMB_PRODUCTION_API_KEY;
    if(carbon_get_theme_option('crb_sandbox_mode')){
      $base_api_uri = WCMB_API_HOST_SANDBOX;
      $api_key = WCMB_SANDBOX_API_KEY;
    }

    ////$base_api_uri = 'https://reqres.in/api';

    $client = new Client([
      'base_uri' => $base_api_uri,
      'timeout'  => 50.0
    ]);

    error_log('API KEY >> '.$api_key);
    error_log('API URL >> '.$base_api_uri.'/order/sendOrder');

    try{
      $response = $client->request('POST', '/order/sendOrder', [
        'headers' => [
          'Content-Type' => 'application/json', 
          'x-api-key' => $api_key
        ],
        'json' => $payload,
        'debug' => true
      ]) ;

      $body = $response->getBody();
      error_log('RESPONSE >>>>>>>>>>>>>>>>>>>>>');
      error_log((string)$body);
      
      return true;

    } catch (ClientException $e) {
        error_log("MATERIALBANK ERROR:: REQUEST = ".Psr7\Message::toString($e->getRequest()));
        error_log("MATERIALBANK ERROR:: RESPPONSE = ".Psr7\Message::toString($e->getResponse()));
        return false;
    }
  }

  public function getShortcodeName(){
    return "";
  }

}