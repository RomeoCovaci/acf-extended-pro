<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('ACFE_Pro')):

class ACFE_Pro{
    
    function __construct(){
        
        // ACF Extended
        $acfe = acfe();
        
        // Constants
        $acfe->constants(array(
            'ACFE_PRO', true
        ));
        
        // Reserved Post Types
        $reserved_post_types = array_merge(acfe_get_setting('reserved_post_types', array()), array(
            'acfe-template'
        ));
        
        // Reserved Field Groups
        $reserved_field_groups = array_merge(acfe_get_setting('reserved_field_groups', array()), array(
            'group_acfe_dynamic_block_type_side',
            'group_acfe_dynamic_form_side',
            'group_acfe_dynamic_options_page_side',
            'group_acfe_dynamic_post_type_side',
            'group_acfe_dynamic_taxonomy_side',
            'group_acfe_dynamic_template_side',
        ));
        
        // Settings
        $acfe->settings(array(
            // General
            'reserved_post_types'               => $reserved_post_types,
            'reserved_field_groups'             => $reserved_field_groups,
            
            // Modules
            'modules/classic_editor'            => false,
            'modules/field_group_ui'            => true,
            'modules/force_sync'                => false,
            'modules/global_field_condition'    => true,
            'modules/screen_layouts'            => true,
            'modules/templates'                 => true,
        ));
    
        // Additional
        acfe_include('pro/includes/core/compatibility.php');
        acfe_include('pro/includes/core/helpers.php');
        acfe_include('pro/includes/core/world.php');
        
        // Admin
        acfe_include('pro/includes/admin/settings.php');
        
        // Modules
        acfe_include('pro/includes/modules/block-types.php');
        acfe_include('pro/includes/modules/forms.php');
        acfe_include('pro/includes/modules/options-pages.php');
        acfe_include('pro/includes/modules/post-types.php');
        acfe_include('pro/includes/modules/taxonomies.php');
        
        // Init
        add_action('acf/init',                  array($this, 'includes'), 99);
        
        // Fields
        add_action('acf/include_field_types',   array($this, 'fields'), 99);
        add_action('acfe/include_form_actions', array($this, 'form_actions'));
    
        // Tools
        add_action('acf/include_admin_tools',   array($this, 'tools'));
        
    }
    
    function includes(){
        
        /*
         * Core
         */
        acfe_include('pro/includes/core/enqueue.php');
        acfe_include('pro/includes/core/hooks.php');
        acfe_include('pro/includes/core/menu.php');
        acfe_include('pro/includes/core/updater.php');
        acfe_include('pro/includes/core/updates.php');
    
        /*
         * Fields
         */
        acfe_include('pro/includes/fields/field-checkbox.php');
        acfe_include('pro/includes/fields/field-column.php');
        acfe_include('pro/includes/fields/field-color-picker.php');
        acfe_include('pro/includes/fields/field-date-picker.php');
        acfe_include('pro/includes/fields/field-flexible-content-grid.php');
        acfe_include('pro/includes/fields/field-flexible-content-locations.php');
        acfe_include('pro/includes/fields/field-file.php');
        acfe_include('pro/includes/fields/field-radio.php');
        acfe_include('pro/includes/fields/field-select.php');
        acfe_include('pro/includes/fields/field-tab.php');
        
        /*
         * Fields settings
         */
        acfe_include('pro/includes/fields-settings/instructions.php');
        acfe_include('pro/includes/fields-settings/min-max.php');
        acfe_include('pro/includes/fields-settings/required.php');
        acfe_include('pro/includes/fields-settings/visibility.php');
    
        /*
         * Field Groups
         */
        acfe_include('pro/includes/field-groups/field-group-hide-on-screen.php');
        acfe_include('pro/includes/field-groups/field-group-ui.php');
        
        /*
         * Locations
         */
        acfe_include('pro/includes/locations/location.php');
        acfe_include('pro/includes/locations/menu-item-depth.php');
        acfe_include('pro/includes/locations/menu-item-type.php');
        acfe_include('pro/includes/locations/post-author.php');
        acfe_include('pro/includes/locations/post-author-role.php');
        acfe_include('pro/includes/locations/post-date.php');
        acfe_include('pro/includes/locations/post-date-time.php');
        acfe_include('pro/includes/locations/post-path.php');
        acfe_include('pro/includes/locations/post-screen.php');
        acfe_include('pro/includes/locations/post-slug.php');
        acfe_include('pro/includes/locations/post-time.php');
        acfe_include('pro/includes/locations/post-title.php');
        acfe_include('pro/includes/locations/settings.php');
        acfe_include('pro/includes/locations/taxonomy-term.php');
        acfe_include('pro/includes/locations/taxonomy-term-name.php');
        acfe_include('pro/includes/locations/taxonomy-term-parent.php');
        acfe_include('pro/includes/locations/taxonomy-term-slug.php');
        acfe_include('pro/includes/locations/taxonomy-term-type.php');
        
        /*
         * Modules
         */
        acfe_include('pro/includes/modules/classic-editor.php');
        acfe_include('pro/includes/modules/dev.php');
        acfe_include('pro/includes/modules/force-sync.php');
        acfe_include('pro/includes/modules/global-field-condition.php');
        acfe_include('pro/includes/modules/screen-layouts.php');
        acfe_include('pro/includes/modules/templates.php');
        
    }
    
    /*
     * Form Actions
     */
    function form_actions(){
    
        acfe_include('pro/includes/modules/forms-action-option.php');
    
    }
    
    /*
     * Fields
     */
    function fields(){
        
        acfe_include('pro/includes/fields/field-block-types.php');
        acfe_include('pro/includes/fields/field-countries.php');
        acfe_include('pro/includes/fields/field-currencies.php');
        acfe_include('pro/includes/fields/field-date-range-picker.php');
        acfe_include('pro/includes/fields/field-field-groups.php');
        acfe_include('pro/includes/fields/field-field-types.php');
        acfe_include('pro/includes/fields/field-fields.php');
        acfe_include('pro/includes/fields/field-google-map.php');
        acfe_include('pro/includes/fields/field-image-selector.php');
        acfe_include('pro/includes/fields/field-image-sizes.php');
        acfe_include('pro/includes/fields/field-languages.php');
        acfe_include('pro/includes/fields/field-menus.php');
        acfe_include('pro/includes/fields/field-menu-locations.php');
        acfe_include('pro/includes/fields/field-options-pages.php');
        acfe_include('pro/includes/fields/field-phone-number.php');
        acfe_include('pro/includes/fields/field-post-field.php');
        acfe_include('pro/includes/fields/field-post-formats.php');
        acfe_include('pro/includes/fields/field-relationship.php');
        acfe_include('pro/includes/fields/field-templates.php');
        acfe_include('pro/includes/fields/field-wysiwyg.php');
        
    }
    
    /*
     * Tools
     */
    function tools(){
    
        acfe_include('pro/includes/admin/tools/settings-export.php');
        acfe_include('pro/includes/admin/tools/settings-import.php');
    
        acfe_include('pro/includes/admin/tools/templates-export.php');
        acfe_include('pro/includes/admin/tools/templates-import.php');
        
    }
    
}

new ACFE_Pro();

endif;