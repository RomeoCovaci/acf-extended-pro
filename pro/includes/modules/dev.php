<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_pro_dev')):

class acfe_pro_dev{
    
    /*
     * Construct
     */
    function __construct(){
        
        add_action('acfe/dev/add_meta_boxes', array($this, 'add_meta_boxes'), 10, 2);
        
    }
    
    function add_meta_boxes($post_id, $object_type){
    
        // WP Post Object
        $info = acf_decode_post_id($post_id);
        $title = 'WP Post Object';
    
        if($info['type'] === 'term'){
        
            $title = 'WP Term Object';
        
        }elseif($info['type'] === 'user'){
        
            $title = 'WP User Object';
        
        }elseif($info['type'] === 'option'){
        
            $title = 'ACF Options Page';
        
        }
    
        add_meta_box('acfe-wp-object', $title, array($this, 'render_object'), $object_type, 'normal', 'low', array('post_id' => $post_id, 'type' => $info['type'], 'info' => $info, 'title' => $title));
        
    }
    
    function render_object($post, $metabox){
        
        $title = $metabox['args']['title'];
        $info = $metabox['args']['info'];
        $type = $metabox['args']['type'];
        $acf_id = $metabox['args']['post_id'];
        $wp_id = $info['id'];
        
        $rows = array();
        
        if($info['type'] === 'post'){
            
            $rows = array(
                array(
                    'name' => 'Post ID',
                    'value' => "<pre>{$wp_id}</pre>"
                ),
                array(
                    'name' => 'Post Type',
                    'value' => "<pre>{$post->post_type}</pre>"
                ),
            );
            
            $object = $post;
            
        }elseif($info['type'] === 'term'){
            
            $rows = array(
                array(
                    'name' => 'Term ID',
                    'value' => "<pre>{$wp_id}</pre>"
                ),
                array(
                    'name' => 'Taxonomy',
                    'value' => "<pre>{$post->taxonomy}</pre>"
                ),
            );
            
            $object = $post;
            
        }elseif($info['type'] === 'user'){
            
            $user_roles = implode(', ', $post->roles);
            
            $rows = array(
                array(
                    'name' => 'User ID',
                    'value' => "<pre>{$wp_id}</pre>"
                ),
                array(
                    'name' => 'Role',
                    'value' => "<pre>{$user_roles}</pre>"
                ),
            );
            
            $object = $post;
            
        }elseif($info['type'] === 'option'){
            
            // vars
            global $plugin_page;
            $options_page = acf_get_options_page($plugin_page);
            
            $rows = array(
                array(
                    'name' => 'Post ID',
                    'value' => "<pre>{$acf_id}</pre>"
                ),
                array(
                    'name' => 'Menu slug',
                    'value' => "<pre>{$options_page['menu_slug']}</pre>"
                ),
            );
            
            $object = $options_page;
            
        }
        
        if(empty($rows))
            return;
        
        ?>
        <table class="wp-list-table widefat fixed striped" style="border:0;">
            <thead>
            <tr>
                
                <?php if(current_user_can(acf_get_setting('capability'))){ ?>
                    <td scope="col" class="check-column"></td>
                <?php } ?>
                
                <th scope="col" style="width:30%;"><span style="margin-left:-30px;">Name</span></th>
                <th scope="col" style="width:auto;">Value</th>
            
            </tr>
            </thead>
            <tbody>
            <?php foreach($rows as $row){ ?>
                <tr>
                    <?php if(current_user_can(acf_get_setting('capability'))){ ?>
                        <th scope="row" class="check-column"></th>
                    <?php } ?>
                    <td><strong style="margin-left:-30px;"><?php echo $row['name']; ?></strong></td>
                    <td><?php echo $row['value']; ?></td>
                </tr>
            <?php } ?>
            <tr>
                <?php if(current_user_can(acf_get_setting('capability'))){ ?>
                    <th scope="row" class="check-column"></th>
                <?php } ?>
                <td></td>
                <td><a class="button button-secondary acfe-wp-object-modal" href="javascript:void(0)" data-modal-key="acfe-wp-object" data-modal-title="<?php echo $title; ?>">View Data</a></td>
            </tr>
            </tbody>
        
        </table>
        <div class="acfe-modal" data-modal-key="acfe-wp-object">
            <div style="padding:15px;">
                <pre><?php print_r($object); ?></pre>
            </div>
        </div>
        <?php
        
    }
    
}

new acfe_pro_dev();

endif;