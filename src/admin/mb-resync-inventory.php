<?php 

/**
 * AJAX Card API action
 */
add_action( 'wp_ajax_mb_inventory_sync', 'func_mb_inventory_sync' );

function func_mb_inventory_sync() {
	$consignee_id = carbon_get_theme_option( 'crb_material_mbid' );
	$xmlString    = carbon_get_theme_option( 'crb_materialbank_inventory' );
    $xmlObject    = simplexml_load_string( $xmlString );
	

	$for_export_all              = array();
	$for_export_non_existent     = array();
	$for_export_non_matching_qty = array();

	foreach ( $xmlObject as $inventory ) {
		global $wpdb;

		$ret          = array();
		
		$sqlStatement = "SELECT product_id FROM {$wpdb->prefix}wc_product_meta_lookup WHERE sku='{$inventory->SKU}' LIMIT 1";

		if ( property_exists($inventory, 'SKU' ) && property_exists($inventory, 'CONSIGNEE' ) && $inventory->CONSIGNEE == $consignee_id ) {

			$product_id = $wpdb->get_var( $wpdb->prepare( $sqlStatement) );

			if ( $product_id ) {

				$qty = intval( $inventory->QTY );

				$old_product = wc_get_product($product_id);

				$old_qty = $old_product->get_stock_quantity();

				if ( strval( $inventory->STATUS ) === 'AVAILABLE' ) {
					update_post_meta( $product_id, 'mb_sample_enabled', true);
					update_post_meta( $product_id, '_manage_stock', 'yes');
					wc_update_product_stock( $product_id, $qty );
				} else {
					// if not available, just set qty to 0.
					wc_update_product_stock( $product_id, 0 );
					update_post_meta( $product_id, 'mb_sample_enabled', false);
				}
				
				$for_export_product = array();

				$wc_product = new WC_Product( $product_id );

				$for_export_product['name'] = $wc_product->get_title();
				$for_export_product['sku'] = $wc_product->get_sku();
				$for_export_product['link'] = $wc_product->get_permalink();
				$for_export_product['stock_quantity'] = $wc_product->get_stock_quantity();
				$for_export_product['link'] = $wc_product->get_permalink();
				$for_export_product['regular_price'] = $wc_product->get_regular_price();
				$for_export_product['previous_qty'] = $old_qty;
				$for_export_product['status'] = strval($inventory->STATUS);

				array_push( $for_export_all, $for_export_product );

			}
			else {
				//echo "cant find: ".$sqlStatement; TODO
				array_push($for_export_non_existent, strval($inventory->SKU) );
			}
		}
		
	} //end foreach

	delete_option( 'mb_inventory_last_synced_data' );
	update_option( 'mb_inventory_last_synced_data', json_encode( $for_export_all ) );

	$d = strtotime("now");

	delete_option( 'mb_inventory_last_synced_date' );
	update_option( 'mb_inventory_last_synced_date', date("Y-m-d h:i:sa", $d) );

	delete_option( 'mb_inventory_non_existent_data' );
	update_option( 'mb_inventory_non_existent_data', $for_export_non_existent);

	// save to transient
	
	wp_send_json( array(
		'updated_products' => $for_export_all,
		'non_existent'     => $for_export_non_existent,
		'success'          => true
	) );

	wp_die();

}

add_action( 'wp_ajax_mb_inventory_existing_export', 'func_mb_inventory_existing_export' );

function func_mb_inventory_existing_export() {
	$xmlString = carbon_get_theme_option( 'crb_materialbank_inventory' );
    $xmlObject = simplexml_load_string( $xmlString );
	
	foreach ( $xmlObject as $inventory ) {
		global $wpdb;

		$ret          = array();
		
		$sqlStatement = "SELECT product_id FROM {$wpdb->prefix}wc_product_meta_lookup WHERE sku='{$inventory->SKU}' LIMIT 1";
		
		$for_export_all    = array();

		//echo $sqlStatement;
		if ( property_exists($inventory, 'SKU' ) ) {
			$product_id = $wpdb->get_var( $wpdb->prepare( $sqlStatement) );

			$for_export_product = array();

			if ( $product_id ) {
				//echo ( $product_id );
				//echo "\n";
				
				$wc_product = new WC_Product( $product_id );

				$for_export_product['name'] = $wc_product->get_title();
				$for_export_product['sku'] = $wc_product->get_sku();
				$for_export_product['link'] = $wc_product->get_permalink();
				$for_export_product['stock_quantity'] = $wc_product->get_stock_quantity();
				$for_export_product['link'] = $wc_product->get_permalink();
				$for_export_product['regular_price'] = $wc_product->get_regular_price();

			}
			else {
				//echo "cant find: ".$sqlStatement; TODO
			}
		}
		
	}

	
	wp_send_json( $ret );
	wp_die();

}


