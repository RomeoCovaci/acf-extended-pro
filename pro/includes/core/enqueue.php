<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_pro_enqueue')):

class acfe_pro_enqueue{
    
    var $suffix = '';
    var $version = '';
    
    function __construct(){
        
        // Vars
        $this->suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        $this->version = ACFE_VERSION;
        
        // Hooks
        add_action('acf/input/admin_enqueue_scripts',   array($this, 'acf_enqueue'));
        add_action('admin_enqueue_scripts',             array($this, 'admin_enqueue_scripts'));
    }
    
    /**
     * ACF (Front + Back) Enqueue
     */
    function acf_enqueue(){
        
        // Input
        wp_enqueue_style('acf-extended-pro-input', acfe_get_url('pro/assets/css/acfe-pro-input' . $this->suffix . '.css'), false, $this->version);
        wp_enqueue_script('acf-extended-pro-input', acfe_get_url('pro/assets/js/acfe-pro-input' . $this->suffix . '.js'), array('jquery'), $this->version);
        
        // Field Group
        if(acf_is_screen('acf-field-group')){
    
            wp_enqueue_style('acf-extended-pro-field-group', acfe_get_url('pro/assets/css/acfe-pro-field-group' . $this->suffix . '.css'), false, $this->version);
            wp_enqueue_script('acf-extended-pro-field-group', acfe_get_url('pro/assets/js/acfe-pro-field-group' . $this->suffix . '.js'), array('jquery'), $this->version);
            
        }
        
    }
    
    /*
     * ACF Enqueue on "New Media" Screen
     * Used for Post Object & Relationship inline Add/Edit feature
     */
    function admin_enqueue_scripts(){
        
        if(!acf_is_screen('media'))
            return;
        
        acf_enqueue_scripts();
        
    }
    
}

new acfe_pro_enqueue();

endif;