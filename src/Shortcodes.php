<?php 

namespace WooMaterialBank;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class Shortcodes {

  private static $instance;
  private $modules;


  private function __construct() {
      $this->modules = [];
  }
  

  public static function instance()
  {
      if (!self::$instance) {
          self::$instance = new self();
      }
      return self::$instance;
  }

  public function register($module){
    if(!empty($module)){
      array_push($this->modules, $module);
    }
  }

  public static function init() {

    foreach(self::$instance->modules as $module){
      var_dump("Shortcode name: ".$module->getShortcodeName());
      // var_dump($module);
        \add_shortcode( $module->getShortcodeName(), [$module, 'shortcode'] );
    }
    
  }


}