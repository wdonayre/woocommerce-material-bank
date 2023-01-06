<?php 

/**
 * Show SKU on cart
 */
add_filter( 'woocommerce_cart_item_name', 'tc_sku_cart_page', 99, 3 );
function tc_sku_cart_page( $item_name, $cart_item, $cart_item_key  ) {
    // The WC_Product object
    $product = $cart_item['data'];
    // Get the  SKU
    $sku = $product->get_sku();

    // When sku doesn't exist
    if(empty($sku)) return $item_name;

    // Add the sku
    $item_name .= '<br><small class="product-sku">' . __( "SKU: ", "wcmb") . $sku . '</small>';

    return $item_name;
}


/**
 * Add Cart validation to only add 1 product/variant per cart
 */
add_filter( 'woocommerce_add_to_cart_validation', 'woocommerce_add_to_cart_validationcustom', 10, 5 );
function woocommerce_add_to_cart_validationcustom( $passed, $product_id, $quantity, $variation_id=null, $variations=null ) {

	global $woocommerce;

    $search_products = $variation_id?[$variation_id]:[$product_id];

	// Set a minimum item quantity
	$maximum = 1; 
	$exist = false;
    if ( !WC()->cart->is_empty() ) {
        foreach(WC()->cart->get_cart() as $cart_item ) {
            // Handling also variable products and their products variations
            $cart_item_ids = $variation_id?[$cart_item['variation_id']]:[$cart_item['product_id']];
            
            // Handle a simple product Id (int or string) or an array of product Ids 
            if( ( array_intersect($search_products, $cart_item_ids) )    ) 
            //|| ( !is_array($search_products) && in_array($search_products, $cart_item_ids)) )
                //$count++; // incrementing items count
                $exist = true;
        }
    }
    
	// Check if the quantity is less then our minimum
	if ( /*$quantity > $maximum*/ $exist){
     wc_add_notice( sprintf( __( "You can only add 1 type of product per order.", "your-theme-language" ) ) ,'error' );  
	  return false;
	} else {
		return true;
	}
}  