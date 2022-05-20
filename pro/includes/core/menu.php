<?php

if(!defined('ABSPATH'))
    exit;

add_action('admin_menu', 'acfe_pro_admin_settings_submenu_swap', 1000);
function acfe_pro_admin_settings_submenu_swap(){
    
    global $submenu;
    
    if(!acf_maybe_get($submenu, 'edit.php?post_type=acf-field-group'))
        return;
    
    $array = $submenu['edit.php?post_type=acf-field-group'];
    
    foreach($array as $k => $item){
        
        // Forms
        if($item[2] === 'edit.php?post_type=acfe-template'){
            
            acfe_array_move($submenu['edit.php?post_type=acf-field-group'], $k, 7);
            
        }
        
    }
    
}