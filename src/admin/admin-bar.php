<?php 

/**
 * Add Admin Menu
 */
function tc_materialbank_admin_menu($admin_bar) {
	$mb_menu_id = 'tc-materialbank-menu';
    $admin_bar->add_menu(
		array(
			'id'    => $mb_menu_id,
			'title' => 'MaterialBank', 
			'href'  => '#'
    	)
	);
	$admin_bar->add_menu(
		array(
			'id'     => 'tc-materialbank-settings',
			'parent' => $mb_menu_id,
			'title'  => 'Settings', 
			'href'   => '/wp-admin/admin.php?page=crb_carbon_fields_container_woomaterialbank_options.php'
    	)
	);
	
	$admin_bar->add_menu(
		array(
			'id'     => 'tc-materialbank-resync',
			'parent' => $mb_menu_id,
			'title'  => 'Sync Inventory', 
			'href'   => '#'
    	)
	);
}
add_action('admin_bar_menu', 'tc_materialbank_admin_menu', 999);