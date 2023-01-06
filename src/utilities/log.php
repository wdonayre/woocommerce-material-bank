<?php 

use \WooMaterialBank\MaterialBank;


//crb_enable_debug carbon_get_theme_option
function tclogger($message){
    $isDevMode = get_option('_crb_enable_debug');
    if($isDevMode === 'yes'){
        $currentDate = date("Y-m-d");
        error_log('TC LOG '.date("Y-m-d H:i:s").': '.$message.PHP_EOL, 3, WCMB_PLUGIN_LOG_PATH.'/'.$currentDate.'-tc.log');
    }
}
