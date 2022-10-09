<?php 

namespace WooMaterialBank\Modules;

use WooMaterialBank\Modules;
use WooMaterialBank\Interfaces\InterfaceModule;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

use WooMaterialBank\AdminOptions;
use WooMaterialBank\Shortcodes;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class CartButtons extends Modules{

  private static $instance;
    
  private $plugin;

  private $container;

  private $modules;

  private $shortcodeName = "tc-product-buttons";





public static function instance()
{
    if (!self::$instance) {
        self::$instance = new self();
    }
    
    return self::$instance;
}

  public static function init() {
    
  }

    public function renderAdminOption($container){}

    public function shortcode($attr){

      $pid = get_the_ID();
      ob_start();

      
  
      return ob_get_clean();

    }

    public function getShortcodeName(){
      return $this->shortcodeName;
    }


}