<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_flexible_content_layout_locations')):

class acfe_field_flexible_content_layout_locations{
    
    function __construct(){
    
        // Hooks
        add_filter('acfe/flexible/defaults_field',              array($this, 'defaults_field'), 4);
        add_filter('acfe/flexible/defaults_layout',             array($this, 'defaults_layout'), 4);
        
        add_action('acfe/flexible/render_field_settings',       array($this, 'render_field_settings'), 4);
        add_action('acfe/flexible/render_layout_settings',      array($this, 'render_layout_settings'), 22, 3);
    
        add_filter('acf/update_field/type=flexible_content',    array($this, 'update_field'));
        add_filter('acf/prepare_field/type=flexible_content',   array($this, 'prepare_field'));
        add_action('wp_ajax_acfe/layout/render_location_rule',  array($this, 'ajax_render_location_rule'));
        
    }
    
    function update_field($field){
    
        if(!acf_maybe_get($field, 'acfe_flexible_layouts_locations'))
            return $field;
        
        if(empty($field['layouts']))
            return $field;
    
        foreach($field['layouts'] as &$layout){
            
            // validate
            if(!acf_maybe_get($layout, 'acfe_layout_locations')) continue;
    
            // Remove empty values and convert to associated array.
            $layout['acfe_layout_locations'] = array_filter($layout['acfe_layout_locations']);
            $layout['acfe_layout_locations'] = array_values($layout['acfe_layout_locations']);
            $layout['acfe_layout_locations'] = array_map('array_filter', $layout['acfe_layout_locations']);
            $layout['acfe_layout_locations'] = array_map('array_values', $layout['acfe_layout_locations']);
    
        }
        
        return $field;
        
    }
    
    function prepare_field($field){
        
        if(!acf_maybe_get($field, 'acfe_flexible_layouts_locations'))
            return $field;
    
        if(empty($field['layouts']))
            return $field;
    
        // get screen
        $screen  = acf_get_form_data('screen');
        $post_id = acf_get_form_data('post_id');
    
        // second pass
        if(!$screen){
        
            // nav menu id
            $nav_menu_id = acf_get_data('nav_menu_id');
            
            if($nav_menu_id){
                $screen  = 'nav_menu';
                $post_id = 'term_' . $nav_menu_id;
            }
            
        }
    
        /*
         * @string  $post_id  12   | term_46 | user_22 | my-option | comment_89 | widget_56 | menu_74 | menu_item_96 | block_my-block | blog_55 | site_36 | attachment_24
         * @string  $id       12   | 46      | 22      | my-option | 89         | widget_56 | 74      | 96           | block_my-block | 55      | 36      | 24
         * @string  $type     post | term    | user    | option    | comment    | option    | term    | post         | block          | blog    | blog    | post
         */
        extract(acf_decode_post_id($post_id));
        
        $args = array();
        
        if($screen === 'user'){
    
            $args = array(
                'user_id'   => $id,
                'user_form' => 'edit',
            );
            
        }elseif($screen === 'attachment'){
    
            $args = array(
                'attachment_id' => $id,
                'attachment'    => $id,
            );
            
        }elseif($screen === 'taxonomy'){
    
            $taxonomy = acf_maybe_get_GET('taxonomy');
    
            if(!empty($id)){
                
                $term     = get_term($id);
                $taxonomy = $term->taxonomy;
                
            }
    
            $args = array(
                'taxonomy' => $taxonomy,
            );
            
        }elseif($screen === 'page' || $screen === 'post'){
    
            $post_type = get_post_type($post_id);
    
            $args = array(
                'post_id'   => $post_id,
                'post_type' => $post_type,
            );
            
        }elseif($screen === 'options'){
    
            global $plugin_page;
            
            $args = array(
                'options_page' => $plugin_page,
            );
            
        }elseif($screen === 'nav_menu'){
    
            $args = array(
                'screen'  => $screen,
                'post_id' => $post_id,
            );
            
        }
        
        // validate args
        if(!$args) return $field;
        
        foreach($field['layouts'] as $i => $layout){
            
            // get visibility
            if($this->get_visibility($layout, $args)) continue;
            
            // unset
            unset($field['layouts'][$i]);
            
        }
        
        // return
        return $field;
        
    }
    
    function get_visibility($layout, $args){
    
        if(!$layout['acfe_layout_locations'])
            return false;
    
        // validate screen
        $screen = acf_get_location_screen($args);
    
        // Loop through location groups.
        foreach($layout['acfe_layout_locations'] as $group){
        
            // ignore group if no rules.
            if(empty($group)){
                continue;
            }
        
            // Loop over rules and determine if all rules match.
            $match_group = true;
        
            foreach($group as $rule){
                if(!acf_match_location_rule( $rule, $screen, $layout)){
                    $match_group = false;
                    break;
                }
            }
        
            // If this group matches, show the field group.
            if($match_group){
                return true;
            }
        
        }
        
        return false;
        
    }
    
    function ajax_render_location_rule() {
        
        // validate
        if(!acf_verify_ajax()) die();
        
        // validate rule
        $rule = acf_validate_location_rule($_POST['rule']);
        
        // prefix
        $prefix = $_POST['prefix'];
    
        // render rule
        $this->render_location_rule($rule, $prefix);
        
        // die
        die();
        
    }
    
    function defaults_field($field){
        
        $field['acfe_flexible_layouts_locations'] = false;
        
        return $field;
        
    }
    
    function defaults_layout($layout){
    
        $layout['acfe_layout_locations'] = array();
        
        return $layout;
        
    }
    
    function render_field_settings($field){
    
        acf_render_field_setting($field, array(
            'label'         => __('Layouts Locations Rules'),
            'name'          => 'acfe_flexible_layouts_locations',
            'key'           => 'acfe_flexible_layouts_locations',
            'instructions'  => __('Define custom locations rules for each layouts'),
            'type'              => 'true_false',
            'message'           => '',
            'default_value'     => false,
            'ui'                => true,
            'ui_on_text'        => '',
            'ui_off_text'       => '',
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            )
        ));
        
    }
    
    function render_layout_settings($flexible, $layout, $prefix){
        
        if(!acf_maybe_get($flexible, 'acfe_flexible_layouts_locations'))
            return;
        
        // default location
        if(empty($layout['acfe_layout_locations'])){
            
            // get field group
            $field_group = acfe_get_field_group_from_field($flexible);
            
            // apply field group locations as default location
            $layout['acfe_layout_locations'] = $field_group['location'];
            
        }
        
        // Close <li>
        echo '</li>';
    
        ?>
        <div class="acf-field">
            <div class="acf-input">
                <div class="acfe-layout-locations rule-groups">
                
                    <?php $this->render_location_group($layout, $prefix); ?>

                </div>
            </div>
        </div>
        <?php
        
    }
    
    function render_location_group($layout, $l_prefix){
        
        foreach($layout['acfe_layout_locations'] as $i => $group):
            
            // bail early if no group
            if(empty($group)) return;
            
            $group_id = "group_{$i}";
        
            ?>
            <div class="rule-group" data-id="<?php echo $group_id; ?>">

                <h4><?php echo ($group_id == 'group_0') ? __("Location Rules",'acf') : __("or",'acf'); ?></h4>

                <table class="acf-table -clear">
                    <tbody>
                    
                    <?php
                    foreach($group as $i => $rule){
    
                        // validate rule
                        $rule = acf_validate_location_rule($rule);
    
                        // append id and group
                        $rule['id'] = "rule_{$i}";
                        $rule['group'] = $group_id;
    
                        // prefix
                        $prefix = $l_prefix.'[acfe_layout_locations]['.$rule['group'].']['.$rule['id'].']';
                        
                        // render rule
                        $this->render_location_rule($rule, $prefix);
                        
                    }
                    ?>
                    
                    </tbody>
                </table>

            </div>
        <?php
    
        endforeach; ?>

        <a href="#" class="button add-location-group"><?php _e("Add rule group",'acf'); ?></a>
        <?php
        
    }
    
    function render_location_rule($rule, $prefix){
        ?>
        <tr data-id="<?php echo $rule['id']; ?>">
            <td class="param">
                <?php
                
                // vars
                $choices = acf_get_location_rule_types();
                
                // array
                if(is_array($choices)){
                    
                    // remove global conditions
                    foreach($choices as $optgroup => &$optchoices){
                        
                        foreach($optchoices as $key => $optchoice){
                            
                            if(strpos($key, 'field_') !== 0) continue;
                            
                            unset($optchoices[$key]);
                            
                        }
                        
                        unset($choices['Global Fields']);
                        
                    }
                    
                    acf_render_field(array(
                        'type'      => 'select',
                        'name'      => 'param',
                        'prefix'    => $prefix,
                        'value'     => $rule['param'],
                        'choices'   => $choices,
                        'class'     => 'refresh-location-rule'
                    ));
                    
                }
                
                ?>
            </td>
            <td class="operator">
                <?php
                
                // vars
                $choices = acf_get_location_rule_operators($rule);
                
                // array
                if(is_array($choices)){
                    
                    acf_render_field(array(
                        'type'      => 'select',
                        'name'      => 'operator',
                        'prefix'    => $prefix,
                        'value'     => $rule['operator'],
                        'choices'   => $choices
                    ));
                    
                // custom
                }else{
                    
                    echo $choices;
                    
                }
                
                ?>
            </td>
            <td class="value">
                <?php
                
                // vars
                $choices = acf_get_location_rule_values($rule);
                
                // array
                if(is_array($choices)){
                    
                    acf_render_field(array(
                        'type'      => 'select',
                        'name'      => 'value',
                        'prefix'    => $prefix,
                        'value'     => $rule['value'],
                        'choices'   => $choices
                    ));
                    
                // custom
                }else{
                    
                    echo $choices;
                    
                }
                
                ?>
            </td>
            <td class="add">
                <a href="#" class="button add-location-rule"><?php _e("and",'acf'); ?></a>
            </td>
            <td class="remove">
                <a href="#" class="acf-icon -minus remove-location-rule"></a>
            </td>
        </tr>
        <?php
    }
    
}

acf_new_instance('acfe_field_flexible_content_layout_locations');

endif;