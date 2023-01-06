<?php 

namespace WooMaterialBank\Modules;

use Carbon_Fields\Field;

use WooMaterialBank\Modules;
use WooMaterialBank\Interfaces\InterfaceModule;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class TalkCarpet extends Modules{

  private static $instance;
    
  private $plugin;


  private function __construct() {
      // $this->plugin = \WooMaterialBank\Plugin::instance();
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

  public function getShortcodeName(){
    return "";
  }

  public function renderAdminOption($container){
       $container->add_tab(
        __('Development'),
        [
          Field::make( 'checkbox', 'crb_enable_debug', 'Enable Debug Log' ),
        ]
   );
  }

  



}