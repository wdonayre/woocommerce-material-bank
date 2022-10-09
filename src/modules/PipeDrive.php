<?php 

namespace WooMaterialBank\Modules;

use WooMaterialBank\Modules;
use WooMaterialBank\Interfaces\InterfaceAdminOption;
use WooMaterialBank\Interfaces\InterfaceModule;
use WooMaterialBank\AdminOptions;


// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class PipeDrive extends Modules{

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
  }


  //override parent class
  public function renderAdminOption($container){
       $container->add_tab(
        __('PipeDrive Integration'),
        [
          \Carbon_Fields\Field::make( 'text', 'crb_pipedrive_api_key', __( 'API Key' ) )
            ->set_classes( 'wcmb_variable_object' )
        ]
   );
  }

  public function getShortcodeName(){
    return "";
  }

}