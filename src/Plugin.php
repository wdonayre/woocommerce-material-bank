<?php 

namespace WooMaterialBank;

use WooMaterialBank\Module;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class Plugin {

  private $config;

  private static $instance;

  private $settings = null;

  private $modules;
  

  private function __construct() {
    $this->config = array();
    $this->modules = [];
  }
  

  public static function instance() {
      if( !self::$instance ) {
          self::$instance = new Plugin();
          self::$instance->loadTextdomain();
          self::$instance->hooks();
          self::$instance->loadModules();
          self::$instance->loadAdminOptions();
          self::$instance->initSettings();
          self::$instance->loadUpdater();
          self::$instance->postLoad();
          self::$instance->loadShortcodes();
      }

      return self::$instance;
  }

  
  public static function load() {
    return Plugin::instance();
  } 


  public function loadTextdomain() {
    \load_plugin_textdomain( 'wcmb', false, dirname( \plugin_basename( __FILE__  ) ) . '/languages/' );
  }


  public function configure(array $config) {
    $this->config = array_merge($this->config, $config);
  }


  private function hooks() {
    \register_activation_hook( __FILE__, array($this, 'activate_wcmb') );
    \register_deactivation_hook( __FILE__, array($this, 'deactivate_wcmb') );

    \add_filter('setup_theme',function(){
      \Carbon_Fields\Carbon_Fields::boot();
    });
  }


  private function initSettings() {
    Settings::init();
  }

  /**
   * Initialize Admin Options
   */
  private function loadAdminOptions(){
    AdminOptions::init();
  }

  /**
   * Initialize Shortcodes
   */
  private function loadShortcodes(){
    Shortcodes::init();
  }

   
  private function activate_wcmb(){
    global $wp_rewrite; 
		$wp_rewrite->flush_rules( true );
  }


  private function deactivate_wcmb(){
    
  }

  private function loadModules(){
    Modules::init();
  }

  private function loadUpdater(){
    //if ((string) get_option('access_hash') !== '') {
      
      $updater = new Updater(WCMB_PLUGIN);
      $updater->set_username(WCMB_GITHUB_USER);
      $updater->set_repository(WCMB_PLUGIN_NAME);
      $updater->authorize(get_option('access_hash'));
      $updater->initialize();

    //}
  }

  private function postLoad(){
    include WCMB_PLUGIN_FUNCTIONS_DIR."/woocommerce/woocommerce.php";
  }


}