<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_color_picker')):

class acfe_field_color_picker{
    
    /*
     * Construct
     */
    function __construct(){
        
        // Instance
        $instance = acf_get_field_type('color_picker');
        remove_action('acf/render_field/type=color_picker',             array($instance, 'render_field'), 9);
        
        // Defaults
        $instance->defaults['display'] = 'default';
        $instance->defaults['return_format'] = 'value';
        $instance->defaults['button_label'] = __('Select Color');
        $instance->defaults['color_picker'] = true;
        $instance->defaults['absolute'] = false;
        $instance->defaults['input'] = true;
        $instance->defaults['allow_null'] = true;
        $instance->defaults['alpha'] = false;
        $instance->defaults['theme_colors'] = false;
        $instance->defaults['colors'] = array();
        
        // Hooks
        add_action('acf/input/admin_enqueue_scripts',                   array($this, 'input_admin_enqueue_scripts'));
        add_action('acf/render_field_settings/type=color_picker',       array($this, 'render_field_settings'));
        add_action('acf/update_field/type=color_picker',                array($this, 'update_field'));
        add_action('acf/render_field/type=color_picker',                array($this, 'render_field'), 9);
        add_action('acf/format_value/type=color_picker',                array($this, 'format_value'), 10, 3);
        
    }
    
    /*
     * Enqueue Scripts
     */
    function input_admin_enqueue_scripts(){
    
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        
        // register
        wp_register_script('acfe-color-picker-alpha', acfe_get_url('pro/assets/inc/wp-color-picker-alpha/wp-color-picker-alpha' . $suffix . '.js'), array('wp-color-picker'), '3.0.0');
        
        // enqueue if gutenberg
        if(acfe_is_gutenberg()){
            wp_enqueue_script('acfe-color-picker-alpha');
        }
        
    }
    
    /*
     * Render Field Settings
     */
    function render_field_settings($field){
    
        $field['colors'] = acf_encode_choices($field['colors']);
    
        acf_render_field_setting($field, array(
            'label'         => __('Display Style','acf'),
            'instructions'  => '',
            'name'          => 'display',
            'type'          => 'radio',
            'layout'        => 'horizontal',
            'choices'       => array(
                'default'   => 'Default',
                'palette'   => 'Palette'
            )
        ));
        acf_render_field_setting($field, array(
            'label'         => __('Return Value','acf'),
            'instructions'  => '',
            'name'          => 'return_format',
            'type'          => 'radio',
            'layout'        => 'horizontal',
            'choices'       => array(
                'value'         => __('Value','acf'),
                'label'         => __('Label','acf'),
                'array'         => __('Both (Array)','acf')
            )
        ));
        
        acf_render_field_setting($field, array(
            'label'         => __('Button Label','acf'),
            'instructions'  => '',
            'name'          => 'button_label',
            'type'          => 'text',
            'default_value' => __('Select Color'),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'display',
                        'operator'  => '==',
                        'value'     => 'default',
                    ),
                )
            )
        ));
    
        acf_render_field_setting($field, array(
            'label'         => __('Color Picker','acf'),
            'instructions'  => '',
            'name'          => 'color_picker',
            'type'          => 'true_false',
            'ui'            => 1
        ));
    
        acf_render_field_setting($field, array(
            'label'         => __('Position Absolute','acf'),
            'instructions'  => '',
            'name'          => 'absolute',
            'type'          => 'true_false',
            'ui'            => 1,
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'color_picker',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            )
        ));
    
        acf_render_field_setting($field, array(
            'label'         => __('Text Input','acf'),
            'instructions'  => '',
            'name'          => 'input',
            'type'          => 'true_false',
            'ui'            => 1,
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'display',
                        'operator'  => '==',
                        'value'     => 'default',
                    ),
                ),
                array(
                    array(
                        'field'     => 'color_picker',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            )
        ));
    
        acf_render_field_setting($field, array(
            'label'         => __('Allow null','acf'),
            'instructions'  => '',
            'name'          => 'allow_null',
            'type'          => 'true_false',
            'ui'            => 1
        ));
        
        acf_render_field_setting($field, array(
            'label'         => __('RGBA','acf'),
            'instructions'  => '',
            'name'          => 'alpha',
            'type'          => 'true_false',
            'ui'            => 1,
        ));
    
        acf_render_field_setting($field, array(
            'label'         => __('Use Theme Colors','acf'),
            'instructions'  => '',
            'name'          => 'theme_colors',
            'type'          => 'true_false',
            'ui'            => 1,
        ));
        
        acf_render_field_setting($field, array(
            'label'         => __('Custom Colors','acf'),
            'instructions'  => __('Enter each choice on a new line.','acf') . '<br /><br />' . __('For more control, you may specify both a value and label like this:','acf'). '<br /><br />' . __('#2271b1 : Primary','acf'),
            'type'          => 'textarea',
            'name'          => 'colors',
        ));
    
    }
    
    /*
     * Update Field
     */
    function update_field($field){
        
        $field['colors'] = acf_decode_choices($field['colors']);
        
        return $field;
        
    }
    
    /*
     * Render Field
     */
    function render_field($field){
        
        // Enqueue Dashicons
        wp_enqueue_style('dashicons');
        
        // Enqueue Color Picker Alpha
        if($field['alpha']){
            wp_enqueue_script('acfe-color-picker-alpha');
        }
        
        // vars
        $text_input = acf_get_sub_array($field, array('id', 'class', 'value'));
        $hidden_input = acf_get_sub_array($field, array('name', 'value'));
    
        // Get Colors
        $field['colors'] = $this->get_colors($field);
    
        // Attributes
        $atts = array(
            'class'             => "acf-color-picker {$field['class']}",
            'data-display'      => $field['display'],
            'data-button_label' => $field['button_label'],
            'data-allow_null'   => $field['allow_null'],
            'data-color_picker' => $field['color_picker'],
            'data-alpha'        => $field['alpha'],
            'data-absolute'     => $field['absolute'],
            'data-input'        => $field['input'],
            'data-colors'       => array_keys($field['colors']),
        );
        
        // html
        ?>
        <div <?php echo acf_esc_attrs($atts); ?>>
            
            <?php acf_hidden_input($hidden_input); ?>
            
            <?php if($field['display'] === 'default'): ?>
    
                <?php acf_text_input($text_input); ?>
            
            <?php else: ?>

                <div class="acf-color-picker-palette">
                    
                    <?php foreach($field['colors'] as $color => $name): ?>
                        
                        <?php
                        $title = $color !== $name ? $name : false;
                        $selected = $color === $field['value'] ? 'selected' : false;
                        $border = $color;
                        
                        // check if gradient
                        if(stripos($border, 'gradient')){
                            
                            preg_match('/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)|#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?/', $border, $matches);
                            
                            // set border as first gradient color
                            if(isset($matches[0])){
                                
                                $border = $matches[0];
                                
                            }
                            
                        }
                        
                        // Palette selected, remove value from color picker
                        if($selected)
                            $text_input['value'] = '';
                        ?>
                    
                        <a href="#" class="color <?php echo $title ? 'acf-js-tooltip' : ''; ?> <?php echo $selected; ?>" data-color="<?php echo $color; ?>" title="<?php echo $title; ?>">
                            <span class="color-alpha" style="background:<?php echo $color; ?>; color:<?php echo $border; ?>;"></span>
                            <span class="dashicons dashicons-saved"></span>
                        </a>
        
                    <?php endforeach; ?>
                    
                    <?php
                    if($field['color_picker']){
                        acf_text_input($text_input);
                    }
                    ?>
                    
                </div>
            
            <?php endif; ?>
            
        </div>
        <?php
        
    }
    
    /*
     * Format Value
     */
    function format_value($value, $post_id, $field){
    
        // bail early if is empty
        if(acf_is_empty($value))
            return $value;
        
        // Get Colors
        $field['colors'] = $this->get_colors($field);
        
        // vars
        $label = acf_maybe_get($field['colors'], $value, $value);
    
        // value
        if($field['return_format'] == 'value'){
        
            // do nothing
        
        // label
        }elseif($field['return_format'] == 'label'){
        
            $value = $label;
        
        // array
        }elseif($field['return_format'] == 'array'){
        
            $value = array(
                'value' => $value,
                'label' => $label
            );
        
        }
        
        // return
        return $value;
        
    }
    
    /*
     * Get Colors
     */
    function get_colors($field){
        
        $colors = array();
        
        if($field['theme_colors']){
            
            // https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#block-color-palettes
            $editor_color_palette = current(acf_get_array(get_theme_support('editor-color-palette')));
            
            if(!empty($editor_color_palette)){
                
                foreach($editor_color_palette as $row){
                    
                    $colors[$row['color']] = $row['name'];
                    
                }
                
            }
            
        }
        
        foreach($field['colors'] as $color => $name){
            
            if(isset($colors[$color]))
                continue;
            
            $colors[$color] = $name;
            
        }
        
        return $colors;
        
    }
    
}

new acfe_field_color_picker();

endif;