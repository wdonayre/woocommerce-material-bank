<?php
/**
 * Plugin Name:     Woocommerce Material Bank
 * Plugin URI:      https://wordpress.org/plugins/woocommerce-material-bank
 * Description:     Woocommerce Integration with Material Bank
 * Version:         0.0.1
 * Author:          William Donayre Jr - Glabs Tech
 * Author URI:      https://glabs.tech/
 * Text Domain:     wcmb
 * Tested up to:    5.4.1
 *
 * @package         WooMaterialBank
 * @author          wdonayre
 * @copyright       wdonayre
 */
 
namespace WooMaterialBank;

use WooMaterialBank\scGroups;

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


define( 'WCMB_VERSION', '1.0.3' );

define( 'WCMB_REQUIRED_WP_VERSION', '4.2' );

define( 'WCMB_PLUGIN', __FILE__ );

define( 'WCMB_PLUGIN_BASENAME', plugin_basename( WCMB_PLUGIN ) );

define( 'WCMB_PLUGIN_NAME', trim( dirname( WCMB_PLUGIN_BASENAME ), '/' ) );

define( 'WCMB_PLUGIN_DIR', untrailingslashit( dirname( WCMB_PLUGIN ) ) );

define( 'WCMB_PLUGIN_MODULES_DIR', WCMB_PLUGIN_DIR . '/src/modules' );
define( 'WCMB_PLUGIN_FUNCTIONS_DIR', WCMB_PLUGIN_DIR . '/src/functions' );
define ('WCMB_PLUGIN_LOG_PATH', WCMB_PLUGIN_DIR.'/logs');

define( 'WCMB_API_HOST_SANDBOX', ' https://apistage.materialbank.com');
define( 'WCMB_API_HOST', 'https://api.materialbank.com');
//SEND ORDER ENDPOINT =>  https://apistage.materialbank.com/order/sendOrder

define( 'WCMB_SANDBOX_API_KEY', 'eb8gtxvFmw43tzMROH3RL6Wd0zkVmoYy981sa2j2' );
define( 'WCMB_PRODUCTION_API_KEY', '' );

define( 'WCMB_GITHUB_USER', 'wdonayre');

if( !class_exists('WooMaterialBank') ) {
    require_once \plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

// require_once 'src/shortcodes/shortcodes.php';
\WooMaterialBank\Plugin::load(); 
