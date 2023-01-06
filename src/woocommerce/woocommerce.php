<?php

//TODO implementing count all submitted and count submitted for the current product only
//TODO make sql query that counts all pending under a product


use \WooMaterialBank\Modules\MaterialBank;

use \Carbon_Fields\Container;
use \Carbon_Fields\Field;


/**
 * CUSTOM ORDER STATUS
 */

function register_in_mb_fulfillment_order_status() {
    register_post_status( 'wc-in-fulfillment', array(
        'label'                     => 'In Fulfillment',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Shipment Arrival <span class="count">(%s)</span>', 'Shipment Arrival <span class="count">(%s)</span>' )
    ) );
}
add_action( 'init', 'register_in_mb_fulfillment_order_status' );

// function add_mb_in_fulfillment_to_order_status( $order_statuses ) {
//     $new_order_statuses = array();
//     foreach ( $order_statuses as $key => $status ) {
//         $new_order_statuses[ $key ] = $status;
//         if ( 'wc-processing' === $key ) {
//             $new_order_statuses['wc-in-fulfillment'] = 'In Fulfillment';
//         }
//     }
//     return $new_order_statuses;
// }
// add_filter( 'wc_order_statuses', 'add_mb_in_fulfillment_to_order_status' );  

add_filter( 'wc_order_statuses', 'in_fulfillment_cb');
function in_fulfillment_cb($order_statuses){
	$order_statuses['wc-in-fulfillment'] = "In Fulfillment";
	return $order_statuses;
}


// /**
//  * CUSTOM FIELDS
//  */
// add_action('woocommerce_after_order_notes', 'my_custom_checkout_field');

// function my_custom_checkout_field( $checkout ) {
	
// 	echo '<div id="my_custom_checkout_field"><h3>'.__('My Field').'</h3>';
				
// 	woocommerce_form_field( 'my_field_name', array( 
// 		'type' 			=> 'text', 
// 		'class' 		=> array('my-field-class orm-row-wide'), 
// 		'label' 		=> __('Fill in this field'),
// 		'required'		=> true,
// 		'placeholder' 	=> __('Enter a number'),
// 		), $checkout->get_value( 'my_field_name' ));

// 	echo '</div>';
// }





add_action( 'woocommerce_before_order_itemmeta', function($item_id, $item, $type ){

    tclogger('ITEM DATA >> '.json_encode($item->get_data()));
    $sku = null;
    $product = $item->get_product();
    if ( is_object( $product ) ) {
		$sku           = $product->get_sku();
	}

    if($item->get_type() == "line_item"){
        $lineItemMeta = get_post_meta(($item->get_data())['order_id'], '_line_item_action__'.($item->get_data())['product_id'], true);
    }
    $isDisabledLineAction = get_option('_crb_disable_line_items_action');

    if($isDisabledLineAction == false || empty($isDisabledLineAction) || $isDisabledLineAction =='false' ){
        $isDisabledLineAction = false;
    } 
    else {
        $isDisabledLineAction = true;
    }
    if($sku){
        $mbInventory = MaterialBank::instance()->getInventory($sku);
        tclogger('SKU : '.$sku);
        tclogger('MB INVENTORY : '.json_encode($mbInventory));
    }

    if(!$isDisabledLineAction && $item->get_type() == "line_item" /*&& !empty($mbInventory)*/){
        // var_dump($item->get_data_store());
        echo '<div style="max-width:500px;"><select data-action="select-line-item" style="font-size:12px;">
            <option>-- Select line item action --</option>
            <option '.(($lineItemMeta=="submit-to-materialbank")?"selected":"").' value="submit-to-materialbank">Submit to MaterialBank for fulfillment</option>
            <option '.(($lineItemMeta=="custom-order")?"selected":"").' value="custom-order">Custom Order</option>
        </select><button style="display:none;" data-item=\''.json_encode($item->get_data()).'\' data-action="line-item-action" class="button save_order button-primary">Save</button></div><small>Available Stock: '.$product->get_stock_quantity().'</small>';
    }
}, 10, 3 );


/**
 * AJAX
 */
add_action( 'wp_ajax_line_item_action', 'func_line_item_action' );

function func_line_item_action() {
	global $wpdb; // this is how you get access to the database

    $payload = $_POST['payload'];
    $pid = $payload['product_id'];
    $order_id = $payload['order_id'];
    $qty = $payload['quantity'];
    $variation_id = $payload['variation_id'];
    $action_value = $_POST['action_value'];
    $order_note = get_the_excerpt($order_id);
    $line_id = $payload['id'];

    $product = wc_get_product( $pid );

    // tclogger(json_encode($payload));

    $order = wc_get_order( $order_id );
    tclogger('ORDER DETAILS >> ');
    tclogger($order);
    $customer = [
        'name' => $order->get_formatted_billing_full_name(),
        'email' => $order->get_billing_email(),
        'phone' => $order->get_billing_phone(),
        'address_formatted' => $order->get_formatted_billing_address(),
        'address_1' => $order->get_billing_address_1(),
        'address_2' => $order->get_billing_address_2(),
        'city' => $order->get_billing_city(),
        'state' => $order->get_billing_state(),
        'postcode' => $order->get_billing_postcode(),
        'country' => $order->get_billing_postcode(),
        'company' => get_post_meta( $order_id, '_custom_company', true )
    ];
    
    $line_item_object = new WC_Order_Item_Product($line_id);
    $pendingCount = get_post_meta($pid, '_material_bank_pending', 1);

    if($action_value === "submit-to-materialbank"){
        //increment pending orders
        if(!empty($pendingCount)){
            $pendingCount = (int)$pendingCount + 1;
        }
        else {
            $pendingCount = 0;
        }
        //add line item meta
        $line_item_object->update_meta_data('_material_bank_fulfillment','processing'); 
        $line_item_object->save_meta_data();

        //add product meta for material fulfillment pending submissions
        add_post_meta($pid, '_material_bank_pending', 'yes', 1);

        $order->add_order_note( "<b>Line item: ". $payload['name'].'</b> qty:'.$payload['quantity'] .' has been submitted to Material Bank for fulfillment.');
        $order->save();
    }
    else if($action_value === "custom-order"){
        //decrement pending orders
        if(!empty($pendingCount) && ((int)$pendingCount)>0){
            $pendingCount = (int)$pendingCount - 1;
        }
        $line_item_object->delete_meta_data('_material_bank_fulfillment');
        $line_item_object->save_meta_data();

        $order->add_order_note( "<b>Line item ". $payload['name'].'</b> qty:'.$payload['quantity'] .' has been updated to "Custom Order"');
        $order->save();
        $result = update_post_meta($order_id, '_line_item_action__'.$pid, $action_value);
        wp_die();
    }
    
    
    $data = maybe_serialize($action_value);
    

    $result = update_post_meta($order_id, '_line_item_action__'.$pid, $action_value);
    // error_log('RESULT ENTRY >>'.$result);
    
    //TODO
    // get order id and product details

    $dataPayload = [
        "ORDER" => 
        [
            "ORDERHEADER" => [
                "CONSIGNEE" => MaterialBank::instance()->get_mbid(),
                "ORDERID" => $order_id,
                "ORDERTYPE" => "FUL",
                "REFERENCEORDER" => $order_id,
                "ORIGIN" => "",
                "COMPANYNAME" => MaterialBank::instance()->get_company_name(),
                "COMPANYTYPE" => MaterialBank::instance()->get_company_type(),
                "STATUS" => "NEW",
                "ORDERDATE" => date("Y-m-d"), 
                "CARRIER" => null,
                "ACCOUNT" => null,
                "SHIPMETHOD" => "01", //TODO: Identify ship methods and set from options admin page instead
                "NOTES" => $order_note
            ],
            "CONTACTINFORMATION" => [
                "SHIPTO" => null,
                "RECEIPTCOMPANYNAME" => $customer['company'],
                "CONTACTNAME" => $customer['name'],
                "CUSTOMERNAME" => $customer['name'],
                "CONTACTPHONE" => $customer['phone'],
                "CONTACTEMAIL" => $customer['email'],
                "ADDRESS1" => $customer['address_1'],
                "ADDRESS2" => $customer['address_2'],
                "ADDRESS3" => null,
                "CITY" => $customer['city'],
                "STATE" => $customer['state'],
                "ZIPCODE" => $customer['postcode'],
                "CCode" => "US"
            ],
            "ORDERLINES" => [
                "LINE" => [
                    "ORDERLINE"=> $line_id,
                    "SKU"=> $product->get_sku(),
                    "SKUDESCRIPTION"=> $product->get_short_description(),
                    "QTYORIGINAL"=> "1",
                    "UOM"=> "EACH"
                ]
            ]
        ]
    ];
    $res = MaterialBank::instance()->sendOrder($dataPayload);
    if($res){
        // add the order note
        $message = sprintf( __( 'Line Item: %s has been submitted to Material Bank for fulfillment. - %s', 'wcmb' ), $line_id, wp_get_current_user()->display_name );
        $order->add_order_note( $message );

        //add the flag so this action won't be shown again
        update_post_meta( $order->id, '_wc_order_has_material_bank_fulfilled', 'yes' );
    } else {
        $message = sprintf( __( 'Line Item: %s submission to Material Bank failed. - %s', 'wcmb' ), $line_id,wp_get_current_user()->display_name );
        $order->add_order_note( $message );

        $order->set_status('wc-failed'); 
        $order->save();

    }

	wp_die(); // this is required to terminate immediately and return a proper response
}




/**
 * Change BILLING DETAILS to SHIPPING DETAILS
 */
function wc_replace_cart_checkout_labels( $translated_text, $text, $domain ) {
    switch ( $translated_text ) {
        case 'Billing details' :
            $translated_text = __( 'Shipping Details', 'wcmb' );
            break;
        case 'Your order' :
            $translated_text = __( 'Your sample order', 'wcmb' );
            break;
    }
    return $translated_text;
}
add_filter( 'gettext', 'wc_replace_cart_checkout_labels', 20, 3 );




/**
 * Override woocommerce template from this template where
 * it automatically use the template added under /src/woocommerce/templates/*.php
 */
add_filter('wc_get_template', function($template, $template_name, $args, $template_path, $default_path){
    $tc_template_override = WCMB_PLUGIN_DIR.'/src/woocommerce/templates/'.$template_name;
    return file_exists( $tc_template_override ) ? $tc_template_override : $template;
}, 10, 5);




/**
 * Change Place order text on checkout page
 */
//add_filter( 'woocommerce_order_button_text', 'woo_custom_order_button_text' ); 

// function woo_custom_order_button_text() {
//     return __( 'Your new button text here', 'woocommerce' ); 
// }


// function output_payment_button() {
//     $order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) );
//     echo '<input type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '" />';
// }

// add_action( 'woocommerce_review_order_before_payment', 'output_payment_button' );

// function remove_woocommerce_order_button_html() {
//     return '';
// }

// add_filter( 'woocommerce_order_button_html', 'remove_woocommerce_order_button_html' );



//Admin
include 'functions/extend_admin_order.php';

//Front End
include 'functions/extend_checkout_fields.php';
include 'functions/extend_cart.php';
include 'functions/extend_email.php';