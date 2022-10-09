<?php 

namespace WooMaterialBank;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class Settings {

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

    //admin js
    \add_action('admin_enqueue_scripts', [&$instance, 'adminEnqueue']);

    //front-end js
    \add_action('wp_enqueue_scripts', [&$instance, 'FEEnqueue']);
  }

  /**
   * Enqueu Admin scripts in the front-end
   */
  public function FEEnqueue(){

    //handle bar
    \wp_register_script( 'wcmb-handlebar-js', \plugin_dir_url( WCMB_PLUGIN )."lib/handlebars.js", [], WCMB_VERSION );
    \wp_enqueue_script( "wcmb-handlebar-js", \plugin_dir_url( WCMB_PLUGIN )."lib/handlebars.js", [], WCMB_VERSION ); 
    // END :: handle bar 

    //Popper and Tippy
    \wp_enqueue_script( 'wcmb-poppy-js', \plugin_dir_url( WCMB_PLUGIN )."lib/popper.js", [], WCMB_VERSION );
    \wp_enqueue_script( 'wcmb-tippy-js', \plugin_dir_url( WCMB_PLUGIN )."lib/tippy.js", [], WCMB_VERSION );


    //main script
    \wp_register_script( 
      'wcmb-main-js', 
      \plugin_dir_url( WCMB_PLUGIN )."assets/public/js/wcmb-functions.js",
      [],
      WCMB_VERSION
    );

    \wp_enqueue_script(
      "wcmb-main-js",
      \plugin_dir_url( WCMB_PLUGIN )."assets/public/js/wcmb-functions.js",
      [],
      WCMB_VERSION
    ); 

    // Localize the script with new data
   $wpmcMainScript = json_decode( carbon_get_theme_option('crb_variables_object') );
   \wp_localize_script( 'wcmb-main-js', 'WPMC', $wpmcMainScript );


    // END :: handle bar 

    \wp_enqueue_style( 'wcmb-main-css', \plugin_dir_url( WCMB_PLUGIN )."assets/public/css/wcmb-style.css", [], WCMB_VERSION );

  }

  /**
   * Enqueue Admin scripts and styling
   */
  public function adminEnqueue(){

    // CodeMirror
    // \wp_enqueue_style( 'wcmb-codemirror-css', \plugin_dir_url( WCMB_PLUGIN )."lib/codemirror/lib/codemirror.css", [], WCMB_VERSION );
    // \wp_enqueue_script( 'wcmb-codemirror-js', \plugin_dir_url( WCMB_PLUGIN )."lib/codemirror/lib/codemirror.js", [], WCMB_VERSION );
    // \wp_enqueue_script( "wcmb-codemirror-addon-overlay", \plugin_dir_url( WCMB_PLUGIN )."lib/codemirror/addon/mode/overlay.js", [], WCMB_VERSION ); 
    // \wp_enqueue_script( "wcmb-codemirror-mode-xml", \plugin_dir_url( WCMB_PLUGIN )."lib/codemirror/mode/xml/xml.js", [], WCMB_VERSION ); 
    // // END :: CodeMirror

    //JSON Editor 
    // \wp_register_script( 
    //   'JSONEditor', 
    //   \plugin_dir_url( WCMB_PLUGIN )."lib/jsoneditor.min.js",
    //   [],
    //   WCMB_VERSION
    // );

    // \wp_enqueue_script(
    //   "JSONEditor",
    //   \plugin_dir_url( WCMB_PLUGIN )."lib/jsoneditor.min.js",
    //   [],
    //   WCMB_VERSION
    // ); 
    // END :: JSON Editor 

    //Font Awesome
    // \wp_enqueue_style(
    //   'font-awesome',
    //   \plugin_dir_url( WCMB_PLUGIN )."lib/font-awesome-4/css/font-awesome.min.css",
    //   [],
    //   WCMB_VERSION
    // );


    //Main Admin Script 
    \wp_register_script( 
      'wcmb-admin-script', 
      \plugin_dir_url( WCMB_PLUGIN )."assets/admin/js/wcmb-admin.js",
      [],
      WCMB_VERSION
    );

    \wp_enqueue_script(
      "wcmb-admin-script",
      \plugin_dir_url( WCMB_PLUGIN )."assets/admin/js/wcmb-admin.js",
      [],
      WCMB_VERSION
    ); 
    // END :: Main Admin Script

    //Main Admin css
    \wp_enqueue_style(
      'wcmb-admin-css',
      \plugin_dir_url( WCMB_PLUGIN )."assets/admin/css/wcmb-admin.css",
      [],
      WCMB_VERSION
    );


  }

}