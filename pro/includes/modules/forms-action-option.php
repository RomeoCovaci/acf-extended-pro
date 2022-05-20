<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_option')):

class acfe_form_option{
    
    function __construct(){
    
        /*
         * Helpers
         */
        $helpers = acf_get_instance('acfe_dynamic_forms_helpers');
        
        /*
         * Action
         */
        add_filter('acfe/form/actions',                                             array($this, 'add_action'));
        add_filter('acfe/form/load/option',                                         array($this, 'load'), 10, 3);
        add_action('acfe/form/make/option',                                         array($this, 'make'), 10, 3);
        add_action('acfe/form/submit/option',                                       array($this, 'submit'), 10, 5);
        
        /*
         * Admin
         */
        add_filter('acf/prepare_field/name=acfe_form_option_save_meta',             array($helpers, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_option_load_meta',             array($helpers, 'map_fields'));
        
        add_filter('acf/prepare_field/name=acfe_form_option_save_target',           array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_option_load_source',           array($helpers, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_option_save_target',           array($this, 'prepare_choices'), 5);
        add_filter('acf/prepare_field/name=acfe_form_option_load_source',           array($this, 'prepare_choices'), 5);
        
    }
    
    function load($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
        
        // Load values
        $load_values = get_sub_field('acfe_form_option_load_values');
        $load_meta = get_sub_field('acfe_form_option_load_meta');
        
        // Load values
        if(!$load_values)
            return $form;
        
        // Option ID
        $_option_id = get_sub_field('acfe_form_option_load_source');
        $_option_id = acfe_form_map_field_value_load($_option_id, $current_post_id, $form);
        
        // Filters
        $_option_id = apply_filters('acfe/form/load/option_id',                      $_option_id, $form, $action);
        $_option_id = apply_filters('acfe/form/load/option_id/form=' . $form_name,   $_option_id, $form, $action);
        
        if(!empty($action))
            $_option_id = apply_filters('acfe/form/load/option_id/action=' . $action, $_option_id, $form, $action);
        
        // Invalid Option ID
        if(!$_option_id)
            return $form;
        
        // Load others values
        if(!empty($load_meta)){
            
            foreach($load_meta as $field_key){
                
                $field = acf_get_field($field_key);
                
                if(!$field)
                    continue;
                
                if($field['type'] === 'clone' && $field['display'] === 'seamless'){
                    
                    $sub_fields = acf_get_value($_option_id, $field);
                    
                    foreach($sub_fields as $sub_field_key => $value){
    
                        $form['map'][$sub_field_key]['value'] = $value;
                        
                    }
                    
                }else{
    
                    $form['map'][$field_key]['value'] = acf_get_value($_option_id, $field);
                    
                }
                
            }
            
        }
        
        return $form;
        
    }
    
    function make($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
        
        // Prepare
        $prepare = true;
        $prepare = apply_filters('acfe/form/prepare/option',                          $prepare, $form, $current_post_id, $action);
        $prepare = apply_filters('acfe/form/prepare/option/form=' . $form_name,       $prepare, $form, $current_post_id, $action);
    
        if(!empty($action))
            $prepare = apply_filters('acfe/form/prepare/option/action=' . $action,    $prepare, $form, $current_post_id, $action);
        
        if($prepare === false)
            return;
    
        // Option ID
        $_option_id = get_sub_field('acfe_form_option_save_target');
        $_option_id = acfe_form_map_field_value($_option_id, $current_post_id, $form);
        
        // Args
        $_option_id = apply_filters('acfe/form/submit/option_id',                         $_option_id, $form, $action);
        $_option_id = apply_filters('acfe/form/submit/option_id/form=' . $form_name,      $_option_id, $form, $action);
        
        if(!empty($action))
            $_option_id = apply_filters('acfe/form/submit/option_id/action=' . $action,   $_option_id, $form, $action);
        
        // Bail early if false
        if($_option_id === false)
            return;
        
        // Submit
        do_action('acfe/form/submit/option',                        $_option_id, $form, $action);
        do_action('acfe/form/submit/option/form=' . $form_name,     $_option_id, $form, $action);
        
        if(!empty($action))
            do_action('acfe/form/submit/option/action=' . $action,  $_option_id, $form, $action);
        
    }
    
    function submit($_option_id, $form, $action){
        
        // Meta save
        $save_meta = get_sub_field('acfe_form_option_save_meta');
        
        if(!empty($save_meta)){
            
            $meta = acfe_form_filter_meta($save_meta, $_POST['acf']);
            
            if(!empty($meta)){
                
                // Backup original acf post data
                $acf = $_POST['acf'];
                
                // Save meta fields
                acf_save_post($_option_id, $meta);
                
                // Restore original acf post data
                $_POST['acf'] = $acf;
            
            }
            
        }
        
    }
    
    /*
     *  Option: Select2 Choices
     */
    function prepare_choices($field){
        
        $options_pages = acf_get_options_pages();
        
        if($options_pages){
            
            foreach($options_pages as $options_page){
        
                $field['choices'][$options_page['post_id']] = $options_page['page_title'];
                
            }
            
        }
        
        return $field;
        
    }
    
    function add_action($layouts){
        
        $layouts['layout_option'] = array(
            'key' => 'layout_option',
            'name' => 'option',
            'label' => 'Option action',
            'display' => 'row',
            'sub_fields' => array(
        
                /*
                 * Layout: Option Action
                 */
                array(
                    'key' => 'field_acfe_form_option_tab_action',
                    'label' => 'Action',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-no-preference' => true,
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_option_custom_alias',
                    'label' => 'Action name',
                    'name' => 'acfe_form_custom_alias',
                    'type' => 'acfe_slug',
                    'instructions' => '(Optional) Target this action using hooks.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'Option',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
        
                /*
                 * Layout: Option Save
                 */
                array(
                    'key' => 'field_acfe_form_option_tab_save',
                    'label' => 'Save',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_option_save_target',
                    'label' => 'Target',
                    'name' => 'acfe_form_option_save_target',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => '',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_option_save_meta',
                    'label' => 'Save ACF fields',
                    'name' => 'acfe_form_option_save_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should be saved to this option',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'allow_custom' => 0,
                    'default_value' => array(
                    ),
                    'layout' => 'vertical',
                    'toggle' => 1,
                    'return_format' => 'value',
                    'save_custom' => 0,
                ),
        
                /*
                 * Layout: Option Load
                 */
                array(
                    'key' => 'acfe_form_option_tab_load',
                    'label' => 'Load',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_option_load_values',
                    'label' => 'Load Values',
                    'name' => 'acfe_form_option_load_values',
                    'type' => 'true_false',
                    'instructions' => 'Fill inputs with values',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_option_load_source',
                    'label' => 'Source',
                    'name' => 'acfe_form_option_load_source',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_option_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => '',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_option_load_meta',
                    'label' => 'Load ACF fields',
                    'name' => 'acfe_form_option_load_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should have their values loaded',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_option_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'allow_custom' => 0,
                    'default_value' => array(
                    ),
                    'layout' => 'vertical',
                    'toggle' => 1,
                    'return_format' => 'value',
                    'save_custom' => 0,
                ),
        
                /*
                 * Layout: Option Advanced
                 */
                array(
                    'key' => 'field_acfe_form_option_tab_advanced',
                    'label' => 'Code',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_option_advanced_load',
                    'label' => 'Change values source',
                    'name' => 'acfe_form_option_advanced_load',
                    'type' => 'acfe_dynamic_message',
                    'instructions' => 'Alter the Option Post ID where meta values are loaded from',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function($field){
                        
                        $form_name = get_field('acfe_form_name', acfe_get_post_id());
                        if(empty($form_name))
                            $form_name = 'my_form';
                        
                        ?>You may use the following hooks:<br /><br />
<?php acfe_highlight(); ?>
add_filter('acfe/form/load/option_id', 'my_form_option_values_source', 10, 3);
add_filter('acfe/form/load/option_id/form=<?php echo $form_name; ?>', 'my_form_option_values_source', 10, 3);
add_filter('acfe/form/load/option_id/action=my-post-action', 'my_form_option_values_source', 10, 3);<?php acfe_highlight(); ?>
<br />
<?php acfe_highlight(); ?>
/*
 * @int     $option_id  Option Post ID used as source
 * @array   $form       The form settings
 * @string  $action     The action alias name
 */
add_filter('acfe/form/load/option_id/form=<?php echo $form_name; ?>', 'my_form_option_values_source', 10, 3);
function my_form_option_values_source($option_id, $form, $action){
    
    /*
     * Retrieve Form Setting
     */
    if($form['custom_key'] === 'custom_value'){

        // Force to load values from the Option Post ID 'my_options'
        $option_id = 'my_options';
        
    }
    
    return $option_id;
    
}<?php acfe_highlight();
                    
                    },
                ),
                array(
                    'key' => 'field_acfe_form_option_advanced_save_args',
                    'label' => 'Change Options Post ID',
                    'name' => 'acfe_form_option_advanced_save_args',
                    'type' => 'acfe_dynamic_message',
                    'instructions' => 'Alter the Options Post ID before database insert/update',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function($field){
                        
                        $form_name = get_field('acfe_form_name', acfe_get_post_id());
                        if(empty($form_name))
                            $form_name = 'my_form';
                        
                        ?>You may use the following hooks:<br /><br />
<?php acfe_highlight(); ?>
add_filter('acfe/form/submit/option_id', 'my_form_option_id_save', 10, 3);
add_filter('acfe/form/submit/option_id/form=<?php echo $form_name; ?>', 'my_form_option_id_save', 10, 3);
add_filter('acfe/form/submit/option_id/action=my-post-action', 'my_form_option_id_save', 10, 3);<?php acfe_highlight(); ?>
<br />
<?php acfe_highlight(); ?>
/*
 * @int     $option_id  Targeted Option Post ID
 * @array   $form       The form settings
 * @string  $action     The action alias name
 */
add_filter('acfe/form/submit/option_id/form=<?php echo $form_name; ?>', 'my_form_option_id_save', 10, 3);
function my_form_option_id_save($option_id, $form, $action){

    /*
     * Get the form input value named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');

    if($my_field === 'Company'){
    
        // Change Option Post ID
        $option_id = 'my_options';
    
    }

    /*
     * Get previous Post Action output
     */
    $prev_post_action = acfe_form_get_action('post');
    
    if(!empty($prev_post_action)){
    
        if($prev_post_action['post_title'] === 'Company'){
    
            // Change Option Post ID
            $option_id = 'my_options';
            
        }

    }
    
    // Do not save the Options
    // return false;
    
    return $option_id;
    
}<?php acfe_highlight();
                    
                    },
                ),
                array(
                    'key' => 'field_acfe_form_option_advanced_save',
                    'label' => 'Add custom action on option save',
                    'name' => 'acfe_form_option_advanced_save',
                    'type' => 'acfe_dynamic_message',
                    'instructions' => 'This action allows you to hook in before or after the meta data have been saved',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function($field){
                        
                        $form_name = get_field('acfe_form_name', acfe_get_post_id());
                        if(empty($form_name))
                            $form_name = 'my_form';
                        
                        ?>You may use the following hooks:<br /><br />
<?php acfe_highlight(); ?>
add_action('acfe/form/submit/option', 'my_form_option_save', 10, 3);
add_action('acfe/form/submit/option/form=<?php echo $form_name; ?>', 'my_form_option_save', 10, 3);
add_action('acfe/form/submit/option/action=my-post-action', 'my_form_option_save', 10, 3);<?php acfe_highlight(); ?>
<br />
<?php acfe_highlight(); ?>
/*
 * @int     $option_id  Targeted Option Post ID
 * @array   $form       The form settings
 * @string  $action     The action alias name
 *
 * Note: At this point the options & meta fields are already saved in the database
 */
add_action('acfe/form/submit/option/form=<?php echo $form_name; ?>', 'my_form_option_save', 10, 3);
function my_form_option_save($option_id, $form, $action){

    /*
     * Get the form input value named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');

    if($my_field === 'Company'){
    
        // do_something();
        
    }

    /*
     * Get previous Post Action output
     */
    $prev_post_action = acfe_form_get_action('post');
    
    if(!empty($prev_post_action)){
        
        if($prev_post_action['post_title'] === 'Company'){
            
            // do_something();
            
        }
        
    }
    
}<?php acfe_highlight();
                    
                    },
                ),
            ),
            'min' => '',
            'max' => '',
        );
        
        return $layouts;
        
    }
    
}

new acfe_form_option();

endif;