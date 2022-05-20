<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('ACFE_Updates')):

class ACFE_Updates {
    
    public $license = '';
    public $url     = 'https://www.acf-extended.com';
    public $item    = 'ACF Extended Pro';
    public $updater = false;
    
    /*
     * Construct
     */
    public function __construct(){
        
        if(defined('ACFE_PRO_KEY') && !empty(ACFE_PRO_KEY)){
            
            $this->license = trim(ACFE_PRO_KEY);
            
        }else{
            
            $this->license = acfe_get_settings('license');
            
        }
    
        add_action('admin_menu',    array($this, 'admin_menu'));
        
        add_action('admin_init',    array($this, 'admin_init'), 4);
        add_action('admin_init',    array($this, 'updater_init'), 5);
        
        //add_action('current_screen', array($this, 'update_core'));
        
    }
    
    /*
     * Admin Menu
     */
    function admin_menu(){
    
        // custom-fields_page_acf-settings-updates
        // $page_hook;
    
        // custom-fields_page_acf-settings-updates
        $page = get_plugin_page_hookname('acf-settings-updates', 'edit.php?post_type=acf-field-group');
    
        add_action($page, array($this, 'html'), 20);
        
    }
    
    /*
     * Admin Init
     */
    function admin_init(){
        
        // Var
        global $plugin_page;
        
        // Bail early
        if($plugin_page !== 'acf-settings-updates')
            return;
        
        // Check activate
        if(acf_verify_nonce('acfe_pro_activate_licence')){
        
            $this->activate_licence();
        
        // Check deactivate
        }elseif(acf_verify_nonce('acfe_pro_deactivate_licence')){
        
            $this->deactivate_licence();
        
        }elseif(acf_maybe_get_GET('acfe-force-check')){
    
            $this->refresh_plugins_transient();
    
        }
        
    }
    
    /*
     * Updater
     */
    function updater_init(){
    
        // Var
        global $plugin_page;
        
        $this->updater = new ACFE_Updater($this->url, ACFE_FILE, array(
            'version'   => ACFE_VERSION,
            'license'   => $this->license,
            'item_name' => $this->item,
            'author'    => 'ACF Extended'
        ));
        
        if($plugin_page === 'acf-settings-updates'){
            
            $this->updater->check_update('');
            $version_info = $this->updater->get_cached_version_info();
            
            if(isset($version_info->msg)){
    
                acf_add_admin_notice($version_info->msg, 'warning');
                
            }
        
        }
        
    }
    
    /*
     * Update Core
     */
    function update_core(){
        
        if(!acf_is_screen('update-core') || !acf_maybe_get_GET('force-check'))
            return;
    
        $this->refresh_plugins_transient();
        
    }
    
    /*
     * HTML
     */
    function html(){
        
        // License
        $license = $this->license;
        $active = $license ? true : false;
        $nonce = $active ? 'acfe_pro_deactivate_licence' : 'acfe_pro_activate_licence';
        $button = $active ? __('Deactivate License', 'acf') : __('Activate License', 'acf');
        $readonly = $active ? 1 : 0;
        
        // Update
        $updater = $this->updater->check_update('');
        $name = plugin_basename(ACFE_FILE);
        $current_version = ACFE_VERSION;
        
        $no_update = (bool) isset($updater->no_update[$name]);
        
        if($no_update){
            
            $info = $updater->no_update[$name];
            $remote_version = isset($info->new_version) ? $info->new_version : false;
            
        }else{
    
            $info = $updater->response[$name];
            $remote_version = isset($info->new_version) ? $info->new_version : false;
            
        }
        
        $update_available = (bool) acf_version_compare($remote_version, '>', $current_version);
        
        if(!$remote_version){
    
            $remote_version = 'None';
            $update_available = false;
            
        }
    
        $changelog = false;
        
        if(isset($info->sections->changelog)){
    
            $changelog = acf_get_instance('ACF_Admin_Updates')->get_changelog_changes($info->sections->changelog, $remote_version);
            
        }
        
        $upgrade_notice = false;
        
        ?>
        <div class="acf-box" id="acfe-license-information">
            <div class="title">
                <h3><?php _e('ACF Extended: License Information', 'acf'); ?></h3>
            </div>
            <div class="inner">

                <p><?php printf(__('To unlock updates, please enter your license key below. If you don\'t have a licence key, please see <a href="%s" target="_blank">details & pricing</a>.','acf'),
                        esc_url('https://www.acf-extended.com/pro')); ?></p>
                
                <form action="" method="post">
                    
                    <?php acf_nonce_input($nonce); ?>
                    
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th>
                                <label for="acfe_pro_licence"><?php _e('License Key', 'acf'); ?></label>
                            </th>
                            <td>
                                <?php
                                
                                // render field
                                acf_render_field(array(
                                    'type'      => 'text',
                                    'name'      => 'acfe_pro_licence',
                                    'value'     => str_repeat('*', strlen($license)),
                                    'readonly'  => $readonly,
                                ));
                                
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>
                                <input type="submit" value="<?php echo esc_attr($button); ?>" class="button button-primary">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </form>
        
            </div>
    
        </div>

        <div class="acf-box" id="acfe-update-information">
            <div class="title">
                <h3><?php _e('ACF Extended: Update Information', 'acf'); ?></h3>
            </div>
            <div class="inner">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th>
                            <label><?php _e('Current Version', 'acf'); ?></label>
                        </th>
                        <td>
                            <?php echo esc_html($current_version); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e('Latest Version', 'acf'); ?></label>
                        </th>
                        <td>
                            <?php echo esc_html($remote_version); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e('Update Available', 'acf'); ?></label>
                        </th>
                        <td>
                            <?php if($update_available): ?>

                                <span style="margin-right: 5px;"><?php _e('Yes', 'acf'); ?></span>
                
                                <?php if($active): ?>
                                    <a class="button button-primary" href="<?php echo esc_attr( admin_url('plugins.php?s=Advanced+Custom+Fields:+Extended+PRO') ); ?>">
                                        <?php _e('Update Plugin', 'acf'); ?>
                                    </a>
                                <?php else: ?>
                                    <a class="button" disabled="disabled" href="#"><?php _e('Please enter your license key above to unlock updates', 'acf'); ?></a>
                                <?php endif; ?>
            
                            <?php else: ?>

                                <span style="margin-right: 5px;"><?php _e('No', 'acf'); ?></span>
                                <a class="button" href="<?php echo esc_attr(add_query_arg('acfe-force-check', 1)); ?>"><?php _e('Check Again', 'acf'); ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if($changelog): ?>
                        <tr>
                            <th>
                                <label><?php _e('Changelog', 'acf'); ?></label>
                            </th>
                            <td class="changelog">
                                <?php echo acf_esc_html($changelog); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if($upgrade_notice): ?>
                        <tr>
                            <th>
                                <label><?php _e('Upgrade Notice', 'acf'); ?></label>
                            </th>
                            <td>
                                <?php echo acf_esc_html($upgrade_notice); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>

            </div>

        </div>
        
        <style>
        #acf_pro_licence,
        #acfe_pro_licence{
            width:100%;
        }

        #acf-update-information ul,
        #acfe-update-information ul {
            list-style: square inside;
            line-height: 1.4;
        }

        #acf-update-information ul li,
        #acfe-update-information ul li {
            margin-bottom:10px;
        }
        
        #acfe-update-information .changelog h4{
            display:none;
        }
        </style>
        
        <script>
        (function($){
            
            // Insert After
            $('#acfe-license-information, #acfe-update-information').insertAfter('#acf-update-information');
            
            // Wrap ACF
            $('#acf-license-information').wrapAll('<div style="width:49.5%; float:left; margin-right:1%;" />');
            $('#acf-update-information').insertAfter('#acf-license-information');
            
            // Wrap ACFE
            $('#acfe-license-information').wrapAll('<div style="width:49.5%; float:left;" />');
            $('#acfe-update-information').insertAfter('#acfe-license-information');
            
            $('#acf-license-information h3, #acf-update-information h3').prepend('ACF: ');

        })(jQuery);
        </script>
        
        <?php
        
    }
    
    /*
     * Activate License
     */
    function activate_licence(){
        
        // Input
        $license = trim($_POST['acfe_pro_licence']);
        
        // data to send in our API request
        $api_params = array(
            'edd_action' => 'activate_license',
            'license'    => $license,
            'item_name'  => urlencode($this->item),
            'url'        => home_url()
        );
    
        // Call API
        $response = wp_remote_post($this->url, array(
            'timeout' => 15,
            'sslverify' => false,
            'body' => $api_params
        ));
        
        // Response: Not OK
        if(is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200){
    
            $message = __('An error occurred, please try again.');
            
            if(is_wp_error($response))
                $message = $response->get_error_message();
            
        // Reponse: OK
        }else{
        
            $license_data = json_decode(wp_remote_retrieve_body($response));
        
            if($license_data->success === false){
    
                $message = __('An error occurred, please try again.');
            
                switch($license_data->error){
                
                    case 'expired' :
                    
                        $message = sprintf(
                            __('Your license key expired on %s.'),
                            date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                        );
                        break;
                
                    case 'disabled' :
                    case 'revoked' :
                    
                        $message = __('Your license key has been disabled.');
                        break;
                
                    case 'missing' :
                    
                        $message = __('<b>Licence key not found</b>. Make sure you have copied your licence key exactly as it appears in your receipt.', 'acf');
                        break;
                
                    case 'invalid' :
                    case 'site_inactive' :
                    
                        $message = __('Your license is not active for this URL.');
                        break;
                
                    case 'item_name_mismatch' :
                    
                        $message = sprintf(__('This appears to be an invalid license key for %s.'), $this->item);
                        break;
                
                    case 'no_activations_left':
                    
                        $message = __('Your license key has reached its activation limit.');
                        break;
                        
                }
            
            }
        
        }
        
        // Error
        if(!empty($message)){
    
            acf_add_admin_notice($message, 'warning');
            
        }else{
    
            $this->license = $license;
            acfe_update_settings('license', $license);
    
            $this->refresh_plugins_transient();
            
            acf_add_admin_notice(__('<b>Licence key activated</b>. Updates are now enabled.', 'acf'), 'success');
            
        }
        
    }
    
    /*
     * Deactivate License
     */
    function deactivate_licence(){
        
        // data to send in our API request
        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license'    => $this->license,
            'item_name'  => urlencode($this->item),
            'url'        => home_url()
        );
    
        // Call the custom API.
        $response = wp_remote_post($this->url, array(
            'timeout' => 15,
            'sslverify' => false,
            'body' => $api_params
        ));
    
        // make sure the response came back okay
        if(is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200){
    
            $message = __( 'An error occurred, please try again.' );
        
            if(is_wp_error($response))
                $message = $response->get_error_message();
    
            acf_add_admin_notice($message, 'warning');
            return;
            
        }
    
        $this->license = '';
        acfe_update_settings('license', '');
    
        $this->refresh_plugins_transient();
        
        acf_add_admin_notice(__('<b>Licence key deactivated</b>. Updates are now disabled.', 'acf'), 'info');
    
    }
    
    /*
     * Refresh Transient
     */
    function refresh_plugins_transient(){
        
        delete_site_transient('update_plugins');
        delete_option('acfe_plugin_updates');
        
    }

}

acf_new_instance('ACFE_Updates');

endif;