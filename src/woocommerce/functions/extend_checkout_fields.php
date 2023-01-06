<?php 


/**
 * Process the checkout
 */
// add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');

// function my_custom_checkout_field_process() {
//     // Check if set, if its not set add an error.
//     if ( ! $_POST['my_field_name'] )
//         wc_add_notice( __( 'Please enter something into this new shiny field.' ), 'error' );
// }




/**
 * Addition checkout fields
 */
add_action('woocommerce_before_order_notes', 'custom_checkout_field');

function custom_checkout_field($checkout) {

    echo '<div id="custom_checkout_field"><h3>' . __('Project Details') . '</h3>';

    woocommerce_form_field('custom_company', array(
        'type' => 'text',
        'class' => array('my-field-class form-row-wide') ,
        'label' => __('Design Firm name') ,
        'placeholder' => __('') ,
        'required' => true
    ) ,
    $checkout->get_value('custom_company'));

    woocommerce_form_field('custom_project_name', array(
        'type' => 'text',
        'class' => array('my-field-class form-row-wide') ,
        'label' => __('Project Name') ,
        'placeholder' => __('') ,
        'required' => true
    ), $checkout->get_value('custom_project_name'));

    woocommerce_form_field('custom_project_type', array(
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
    ), $checkout->get_value('custom_project_type'));

    woocommerce_form_field('custom_project_desc', array(
        'type' => 'textarea',
        'class' => array('my-field-class form-row-wide') ,
        'label' => __('Project Description') ,
        'placeholder' => __('') ,
        'required' => true
    ) ,
    $checkout->get_value('custom_project_desc'));
    

    woocommerce_form_field('custom_project_phase', array(
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
    ), $checkout->get_value('custom_project_phase'));
    echo '</div>';

}

/**
 * Process the checkout's custom fields
 */
add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');

function my_custom_checkout_field_process() {
    // Check if set, if its not set add an error.
    if ( ! $_POST['custom_company'] )
        wc_add_notice( __( 'Please enter an info for "Design Firm name"' ), 'error' );
    if ( ! $_POST['custom_project_name'] )
        wc_add_notice( __( 'Please enter an info for "Project Name"' ), 'error' );
    if ( ! $_POST['custom_project_type'] )
        wc_add_notice( __( 'Please select a value for "Project Type"' ), 'error' );
    if ( ! $_POST['custom_project_desc'] )
        wc_add_notice( __( 'Please enter an info for "Project Description"' ), 'error' );
    if ( ! $_POST['custom_project_phase'] )
        wc_add_notice( __( 'Please select a value for "Project Phase"' ), 'error' );
}

/**
 * Update the order meta with field value
 */
add_action( 'woocommerce_checkout_update_order_meta', 'tc_custom_checkout_field_update_order_meta' );
function tc_custom_checkout_field_update_order_meta( $order_id ) {

    if ( ! empty($_POST['custom_company']) )
        update_post_meta( $order_id, '_custom_company', sanitize_text_field( $_POST['custom_company'] ) );
    if ( ! empty($_POST['custom_project_name']) )
        update_post_meta( $order_id, '_custom_project_name', sanitize_text_field( $_POST['custom_project_name'] ) );
    if ( ! empty($_POST['custom_project_type']) )
        update_post_meta( $order_id, '_custom_project_type', sanitize_text_field( $_POST['custom_project_type'] ) );
    if ( ! empty($_POST['custom_project_desc']) )
        update_post_meta( $order_id, '_custom_project_desc', sanitize_text_field( $_POST['custom_project_desc'] ) );
    if ( ! empty($_POST['custom_project_phase']) )
        update_post_meta( $order_id, '_custom_project_phase', sanitize_text_field( $_POST['custom_project_phase'] ) );
        
}

/**
 * remove billing company field
 */
add_filter( 'woocommerce_checkout_fields' , 'materialbank_checkout_fields' ); 
function materialbank_checkout_fields( $fields ) { 
    unset($fields['billing']['billing_company']); 
    return $fields; 
}


/**
 * Add PLACE ORDER button after order notes field
 */
function output_payment_button() {
    $order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) );
    echo '<input type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '" />';
}
add_action('woocommerce_after_order_notes', 'output_payment_button');


/**
 * Remove the default PLACE ORDER button
 */
function remove_woocommerce_order_button_html() {
    return '';
}
add_filter( 'woocommerce_order_button_html', 'remove_woocommerce_order_button_html' );


