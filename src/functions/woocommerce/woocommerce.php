<?php

use \WooMaterialBank\MaterialBank;

use \Carbon_Fields\Container;
use \Carbon_Fields\Field;

function sv_wc_add_order_meta_box_action( $actions ) {
	global $theorder;

	// bail if the order has been paid for or this action has been run
	if ( get_post_meta( $theorder->id, '_wc_order_marked_material_bank_submitted', true ) ) {
		return $actions;
	}

	// add "mark printed" custom action
	$actions['wc_custom_order_action'] = __( 'Send to MaterialBank for fulfillment', 'wcmb' );
	return $actions;
}
// add_action( 'woocommerce_order_actions', 'sv_wc_add_order_meta_box_action' );

/**
 * Add an order note when custom action is clicked
 * Add a flag on the order to show it's been run
 *
 * @param \WC_Order $order
 */
function sv_wc_process_order_meta_box_action( $order, $a2, $a3 ) {

    //TRIGGER MATERIAL BANK SUBMISSION
    error_log('wdonayre...');
    var_dump($a2);
    $res = MaterialBank::instance()->sendOrder($order);
    if($res){
        // add the order note
        $message = sprintf( __( 'Submitted to Material Bank for fulfillment. - %s', 'wcmb' ), wp_get_current_user()->display_name );
        ////$order->add_order_note( $message );

        // $order->update_status('wc-arrival-shipment'); 
        ////$order->set_status('wc-in-fulfillment'); 
        ////$order->save();

        // add the flag so this action won't be shown again
        // update_post_meta( $order->id, '_wc_order_marked_material_bank_submitted', 'yes' );
    } else {
        $message = sprintf( __( 'Submission to Material Bank failed. - %s', 'wcmb' ), wp_get_current_user()->display_name );
        $order->add_order_note( $message );

        $order->set_status('wc-failed'); 
        $order->save();

        ////wc_add_notice( __('Oups! The order key is invalid...', 'woocommerce'), 'error');
    }
}
add_action( 'woocommerce_order_action_wc_custom_order_action', 'sv_wc_process_order_meta_box_action',10,2);


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
    //update_post_meta( $order->id, '_wc_order_marked_material_bank_submitted', 'yes' );
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

add_action('woocommerce_before_order_notes', 'custom_checkout_field');

function custom_checkout_field($checkout) {

    echo '<div id="custom_checkout_field"><h3>' . __('Project Details') . '</h3>';

    woocommerce_form_field('custom_field_name', array(
        'type' => 'text',
        'class' => array('my-field-class form-row-wide') ,
        'label' => __('Company') ,
        'placeholder' => __('') ,
        'required' => true
    ) ,
    $checkout->get_value('custom_company'));

    woocommerce_form_field('custom_field_project_name', array(
        'type' => 'text',
        'class' => array('my-field-class form-row-wide') ,
        'label' => __('Project Name') ,
        'placeholder' => __('') ,
        'required' => true
    ) ,
    $checkout->get_value('custom_project_name'));

    woocommerce_form_field('custom_field_project_type', array(
        'type' => 'select',
        'class' => array('my-field-class form-row-wide select2') ,
        'label' => __('Project Type') ,
        'placeholder' => __('') ,
        'required' => true,
        'options' => [
            'blank' => __( 'Select a project type', 'wcmb' ),
            'Aviation' => __('Aviation', 'wcmb'),
            'Condominium/Cooperative ' => __('Condominium/Cooperative ', 'wcmb'),
            'Government' => __('Government', 'wcmb'),
            'Health care' => __('Health care', 'wcmb'),
            'Hospitality: Hotel' => __('Hospitality: Hotel', 'wcmb'),
            'Hospitality: Restaurant' => __('Hospitality: Restaurant', 'wcmb'),
            'Industrial' => __('Industrial', 'wcmb'),
            'Institution/Education' => __('Institution/Education', 'wcmb'),
            'Marine' => __('AviMarineation', 'wcmb'),
            'Medical/Dental' => __('Medical/Dental', 'wcmb'),
            'Mixed-Use & Multi-Unit Residential' => __('Mixed-Use & Multi-Unit Residential', 'wcmb'),
            'Power & Energy' => __('Power & Energy', 'wcmb')
        ]
    ) ,
    $checkout->get_value('custom_project_type'));

    woocommerce_form_field('custom_field_project_desc', array(
        'type' => 'textarea',
        'class' => array('my-field-class form-row-wide') ,
        'label' => __('Project Description') ,
        'placeholder' => __('') ,
        'required' => true
    ) ,
    $checkout->get_value('custom_project_desc'));
    

    woocommerce_form_field('custom_field_project_phase', array(
        'type' => 'select',
        'class' => array('my-field-class form-row-wide select2') ,
        'label' => __('Project Phase') ,
        'placeholder' => __('') ,
        'required' => true,
        'options' => [
            'blank' => __( 'Select a project phase', 'wcmb' ),
            'Concept Design' => __('Concept Design', 'wcmb'),
            'Schematic Design' => __('Schematic Design', 'wcmb'),
            'Design Development' => __('Design Development', 'wcmb'),
            'Specification' => __('Specification', 'wcmb'),
            'Construction Admin' => __('Construction Admin', 'wcmb'),
            'Reselection/ Substitution' => __('Reselection/ Substitution', 'wcmb'),
        ]
    ) ,
    $checkout->get_value('custom_project_phase'));
    echo '</div>';

}


add_filter( 'woocommerce_checkout_fields' , 'materialbank_checkout_fields' ); 

function materialbank_checkout_fields( $fields ) { 
    unset($fields['billing']['billing_company']); 
return $fields; 

}



add_action( 'woocommerce_before_order_itemmeta', function($item_id, $item, $type ){
    //e.g. _line_item_action__29
    // error_log(json_encode($item->get_data()));
    // error_log("TYPE >>".$type);
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

    if(!$isDisabledLineAction && $item->get_type() == "line_item"){
        // var_dump($item->get_data_store());
        echo '<div style="max-width:500px;"><select data-action="select-line-item" style="font-size:12px;">
            <option>-- Select line item action --</option>
            <option '.(($lineItemMeta=="submit-to-materialbank")?"selected":"").' value="submit-to-materialbank">Submit to MaterialBank for fulfillment</option>
            <option '.(($lineItemMeta=="custom-order")?"selected":"").' value="custom-order">Custom Order</option>
        </select><button style="display:none;" data-item=\''.json_encode($item->get_data()).'\' data-action="line-item-action" class="button save_order button-primary">Save</button></div>';
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

    $order = wc_get_order( $order_id );
    if($action_value === "submit-to-materialbank"){
        $order->add_order_note( "<b>Line item: ". $payload['name'].'</b> qty:'.$payload['quantity'] .' has been submitted to Material Bank for fulfillment.');
    }
    else if($action_value === "custom-order"){
        $order->add_order_note( "<b>Line item ". $payload['name'].'</b> qty:'.$payload['quantity'] .' has been updated to "Custom Order"');
    }
    $order->save();

    // add the flag so this action won't be shown again
    update_post_meta( $order->id, '_wc_order_marked_material_bank_submitted', 'yes' );

    $data = maybe_serialize($action_value);
    
    if($action_value == "submit-to-materialbank"){
        $currentPossibleData = maybe_serialize(['pid'=>$pid, 'value'=>'custom-order']);   
    }
    else {
        $currentPossibleData = maybe_serialize(['pid'=>$pid, 'value'=>'submit-to-materialbank']);   
    }

    // error_log('CURRENT DATA >>'.$currentPossibleData);
    // error_log('NEW DATA >>'.$data);

    $result = update_post_meta($order_id, '_line_item_action__'.$pid, $action_value);
    // error_log('RESULT ENTRY >>'.$result);
    
    //TODO
    // get order id and product details

    return true;

    $payload = [
        "ORDER" => 
        [
            "ORDERHEADER" => [
                "CONSIGNEE" => "0106",
                "ORDERID" => "EXAMPLE2",
                "ORDERTYPE" => "FUL",
                "REFERENCEORDER" => "SOEXAMPLE2",
                "ORIGIN" => "",
                "COMPANYNAME" => "ege by Talk Carpet",
                "COMPANYTYPE" => "CS",
                "STATUS" => "NEW",
                "ORDERDATE" => "2022-09-19",
                "CARRIER" => null,
                "ACCOUNT" => null,
                "SHIPMETHOD" => "01",
                "NOTES" => null
            ],
            "CONTACTINFORMATION" => [
                "SHIPTO" => null,
                "RECEIPTCOMPANYNAME" => null,
                "CONTACTNAME" => "Jon Doe",
                "CUSTOMERNAME" => "Jon Doe",
                "CONTACTPHONE" => "(786) 123456789",
                "CONTACTEMAIL" => null,
                "ADDRESS1" => "123 street abc",
                "ADDRESS2" => null,
                "ADDRESS3" => null,
                "CITY" => "Miami",
                "STATE" => "FL",
                "ZIPCODE" => "12345",
                "CCode" => "US"
            ],
            "ORDERLINES" => [
                "LINE" => [
                    "ORDERLINE"=> "1",
                    "SKU"=> "RF52952680",
                    "SKUDESCRIPTION"=> "Christian Lacroix Calades",
                    "QTYORIGINAL"=> "1",
                    "UOM"=> "EACH"
                ]
            ]
        ]
    ];

    $res = MaterialBank::instance()->sendOrder($payload);
    if($res){
        // add the order note
        $message = sprintf( __( 'Submitted to Material Bank for fulfillment. - %s', 'wcmb' ), wp_get_current_user()->display_name );
        ////$order->add_order_note( $message );

        // $order->update_status('wc-arrival-shipment'); 
        ////$order->set_status('wc-in-fulfillment'); 
        ////$order->save();

        // add the flag so this action won't be shown again
        
        // update_post_meta( $order->id, '_wc_order_marked_material_bank_submitted', 'yes' );
    } else {
        $message = sprintf( __( 'Submission to Material Bank failed. - %s', 'wcmb' ), wp_get_current_user()->display_name );
        $order->add_order_note( $message );

        $order->set_status('wc-failed'); 
        $order->save();

        ////wc_add_notice( __('Oups! The order key is invalid...', 'woocommerce'), 'error');
    }

	wp_die(); // this is required to terminate immediately and return a proper response
}


/**
 * Custom Field for Single Product Template
 */

add_action( 'carbon_fields_register_fields',function(){
    Container::make( 'post_meta', 'TalkCarpet Product Options' )
    ->where( 'post_type', '=', 'product' )
    ->add_fields( array(
        Field::make( 'image', 'crb_photo' ),
    ));
});
