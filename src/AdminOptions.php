<?php 

namespace WooMaterialBank;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AdminOptions {

  private static $instance;
    
  private $plugin;

  private $container;

  private $modules;


  private function __construct() {
      $this->plugin = \WooMaterialBank\Plugin::instance();

      $this->modules = [];
  }
  

  public static function instance()
  {
      if (!self::$instance) {
          self::$instance = new self();
      }
      
      return self::$instance;
  }

  public function setContainer($container){
    $this->container = $container;
    // var_dump($this->container);
  } 
  public function getContainer(){
    // var_dump($this->container);
    return $this->container;
  }
  public function register($module){
    if(!empty($module)){
      array_push($this->modules, $module);
    }
  }

  public static function init() {
    // $instance = self::instance();

    \add_action( 'carbon_fields_register_fields', function(){
      
      $instance = \WooMaterialBank\AdminOptions::instance();
      $instance->setContainer(Container::make( 'theme_options', 'WooMaterialBank Options')  );

      foreach($instance->modules as $module){
        $module->renderAdminOption($instance->getContainer());
      }

    });
  }


}