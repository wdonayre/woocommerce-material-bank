<?php 

namespace WooMaterialBank\Modules;

use Carbon_Fields\Field;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\ResponseInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;

use WooMaterialBank\Modules;
use WooMaterialBank\Interfaces\InterfaceModule;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class MaterialBank extends Modules{

  private static $instance;

  private $inventory;
    
  private $plugin;


  private function __construct() {
      // $this->plugin = \WooMaterialBank\Plugin::instance();
      $this->inventory = null;
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
  }

  public function getInventory($sku){
    if($sku === '' || empty($sku)) return false;

    if(empty($this->inventory)){
      $this->inventory = (array)json_decode( get_option('_crb_materialbank_inventory') );
    }
    $ret = $this->inventory[$sku];

    return $ret ? $ret : false;
  }

  public function getShortcodeName(){
    return "";
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

      $container->add_tab(
        __('Material Bank Inventory'),
        [
          Field::make( 'textarea', 'crb_materialbank_inventory', __( 'Inventory XML Data' ) )
          ->set_rows( 50 ),
          Field::make( 'text', 'crb_materialbank_inventory_last_updated', __( 'Last Updated' ) )
        ]
      );
  }
  
  /**
   * Retrieve MaterialBank ID from wp_options table
   */
  public function get_mbid(){
    return get_option('_crb_material_mbid'); 
  }

  /**
   * Retrieve MaterialBank Consignee company name from wp_options table
   */
  public function get_company_name(){
    return get_option('_crb_material_company'); 
  }

  /**
   * Retrieve MaterialBank Company type from wp_options
   */
  public function get_company_type(){
    return get_option('_crb_material_company_type'); 
  }

  /**
   * Retrieve API Key value
   */
  public function get_api_key(){
    $sandboxMode = get_option('_crb_sandbox_mode')==='yes'?true:false;
    if($sandboxMode){
      return get_option('_crb_material_api_key_sandbox');
    }
    return get_option('_crb_material_api_key');
  }

  /**
   * Check Line Item Fulfillment status from Material Bank
   */
  public function get_status($sku){
    return "";
  }

  /**
   * Count Pending MB submitted line item by Order
   */
  public function count_pending_orders($order_id){
    global $wpdb;

    $sqlStatement = 'select * from '.$wpdb->prefix.'woocommerce_order_items as ot
    left join '.$wpdb->prefix.'woocommerce_order_itemmeta as otm
    on ot.order_item_id = otm.order_item_id
    where ot.order_id = '.$order_id.'
    and otm.meta_key="_material_bank_fulfillment"
    and ot.order_item_type = "line_item"
    and otm.meta_value = "processing"';

		$tables = $wpdb->get_results( $sqlStatement );
    
    tclogger("PENDING ORDER >> ");
    tclogger(json_encode($tables));

    return 999;
  }

  /* ========================================= */
  public function sendOrder($payload){
    tclogger('TO MATERIAL BANK | PAYLOAD >> ');
    tclogger(json_encode($payload));

    ////$base_api_uri = 'https://reqres.in/api';

    $base_api_uri = WCMB_API_HOST;
    $api_key = $this->get_api_key();
    if(carbon_get_theme_option('crb_sandbox_mode')){
      $base_api_uri = WCMB_API_HOST_SANDBOX;
      // $api_key = WCMB_SANDBOX_API_KEY;
    }

    ////$base_api_uri = 'https://reqres.in/api';
    $client = new Client();
    // $response = $client->request('GET', 'https://api.github.com/repos/guzzle/guzzle');

    tclogger('API KEY >> '.$api_key);
    tclogger('API URL >> '.$base_api_uri.'/order/sendOrder');

    // $ch = curl_init( $base_api_uri.'/order/sendOrder' );
    $ch = curl_init();
    # Setup request to send json via POST.
    // $payload = json_encode( $payload );
    $curlData = [
      CURLOPT_URL => "https://apistage.materialbank.com/order/sendOrder",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "x-api-key: $api_key"
      ],
      CURLOPT_POSTFIELDS => json_encode($payload)
    ];
    curl_setopt_array($ch, $curlData);
    tclogger(PHP_EOL.'CURL DATA >> '); tclogger(json_encode($curlData));

    $response = curl_exec($ch);
    $err = curl_error($ch);

    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    tclogger(PHP_EOL.'RESPONSE >> '); tclogger($result);
    tclogger(PHP_EOL.'HTTP RESPONSE CODE >> '); tclogger($httpcode);

    curl_close($ch);

    if ($err) {
      tclogger(PHP_EOL.'CURL ERROR >> ');
      tclogger($err);
      return false;
    } else {
      tclogger(PHP_EOL.'CURL RESPONSE >> ');
      tclogger($response);
      return true;
    }
  }



}