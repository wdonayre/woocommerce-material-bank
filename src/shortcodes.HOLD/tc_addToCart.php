<?php 

//[tc-product-buttons]
add_shortcode( 'tc-product-buttons', 'tc_add_product_buttons' );
function tc_add_product_buttons(  ) {
    //check if current page is a product single page
    $pid = get_the_ID();

    var_dump($pid);

	return "";
}