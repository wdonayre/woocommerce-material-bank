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
    self::instance()->setCustomFields();
  }

  public function setCustomFields(){
    \add_action('carbon_fields_register_fields', function(){
      Container::make( 'post_meta', 'Additional Product CTA' )
      ->where( 'post_type', '=', 'product' )
      ->add_fields( array(
          Field::make( 'checkbox', 'crb_cta_enabled' ),
          Field::make( 'text', 'crb_cta_url' )
          ->set_conditional_logic( array(
              array(
                  'field' => 'crb_cta_enabled',
                  'value' => true,
              )
            )
          ),
          Field::make( 'text', 'crb_cta_label' )
          ->set_conditional_logic( array(
            array(
                'field' => 'crb_cta_enabled',
                'value' => true,
            )
          )
        )
          
      ))
      ->set_context( 'side' );
    });
  }

    public function renderAdminOption($container){}

    public function shortcode($attr){
      global $product;
      $pid = get_the_ID();
      
      $extraBtnURL = carbon_get_post_meta( get_the_ID(), 'crb_cta_url' );
      $extraBtnLabel = carbon_get_post_meta( get_the_ID(), 'crb_cta_label' );

      ob_start();
      
      echo '<div class="tc-cart-buttons">';

      if( $product->get_type() !== 'external' ) {
        echo "<a class='main-cta single_add_to_cart_button button alt' href='".$product->add_to_cart_url()."'>add to cart</a>";
      }
      
      if(carbon_get_post_meta( get_the_ID(), 'crb_cta_enabled')){
        echo "<a target='_blank' class='extra-cta single_add_to_cart_button button alt' href='".$extraBtnURL."'>".$extraBtnLabel."</a>";
      }
      echo '</div>';
      ?>

      <?php
      
  
      return ob_get_clean();

    }

    public function getShortcodeName(){
      return $this->shortcodeName;
    }


}