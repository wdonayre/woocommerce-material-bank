<?php 

namespace WooMaterialBank;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

use WooMaterialBank\AdminOptions;
use WooMaterialBank\Shortcodes;

use WooMaterialBank\Interfaces\InterfaceModule;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class Modules implements InterfaceModule {

  private static $instance;
    
  private $plugin;

  private $container;

  private $modules;

  private $includeModules = [
    'MaterialBank',
    'PipeDrive',
    'CartButtons',
    'TalkCarpet'
  ];


    function __construct() {
      // $this->plugin = \WooMaterialBank\Plugin::instance();

      $this->modules = [];
  }
  

  public static function instance()
  {
      if (!self::$instance) {
          self::$instance = new self();
      }
      
      return self::$instance;
  }

  public function registerModule($module){
      if(!empty($module)){
          array_push(AdminOptions::instance()->register($module));
          array_push(Shortcodes::instance()->register($module));
      }
  }

  public static function init() {
    //register modules
    foreach(self::instance()->includeModules as $module){
      
      $strClass = 'WooMaterialBank\Modules\\'.$module;
      //error_log(get_class($module));
      AdminOptions::instance()->register($strClass::instance());
      //error_log('SHORTCODE>>>');
      Shortcodes::instance()->register($strClass::instance());
    }
    //call init from all modules
    foreach(self::instance()->includeModules as $module){
        $strClass = 'WooMaterialBank\Modules\\'.$module;
        $strClass::init();
    }
  }

  public function shortcode($attr){}
  public function getShortcodeName(){}
  public function renderAdminOption($container){}


}