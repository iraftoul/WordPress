<?php
/*
Plugin Name: Active Directory Integration for Intranet sites
Plugin URI: https://miniorange.com
Description: Active Directory Integration for Intranet sites plugin provides login to WordPress using credentials stored in your Active Directory / other LDAP Directory.
Author: miniOrange
Version: 3.6.4
Author URI: https://miniorange.com
*/

require_once 'mo_ldap_pages.php';
require_once 'Plugin-tour-UI.php';
require('mo_ldap_support.php');
require('class-mo-ldap-customer-setup.php');
require('class-mo-ldap-utility.php');
require('class-mo-ldap-config.php');
require('class-mo-ldap-role-mapping.php');
require ('mo_ldap_licensing_plans.php');
require('ldap_feedback_form.php');
require_once "PointersManager_ldap.php";
require_once dirname( __FILE__ ) . '/includes/lib/Mo_Pointer_Ldap.php';
require_once dirname( __FILE__ ) . '/includes/lib/export.php';

define( "Tab_ldap_Class_Names", serialize( array(
    "ldap_Login"         => 'mo_options_ldap_acc_details',
    "ldap_config" => 'mo_options_ldap_config_details'
) ) );

error_reporting(E_ERROR);

class Mo_Ldap_Local_Login
{

    function __construct()
    {
        //change version here
        $current_version = '3.6.4';
        add_option('mo_ldap_local_register_user', 1);
        add_option('mo_ldap_local_cust', 0);
        add_action('admin_menu', array($this, 'mo_ldap_local_login_widget_menu'));
        add_action('admin_init', array($this, 'login_widget_save_options'));
        add_action('init', array($this, 'test_attribute_configuration'));
        add_action('admin_enqueue_scripts', array($this, 'mo_ldap_local_settings_style'));
        add_action('admin_enqueue_scripts', array($this, 'mo_ldap_local_settings_script'));
        remove_action('admin_notices', array($this, 'success_message'));
        remove_action('admin_notices', array($this, 'error_message'));
        register_deactivation_hook(__FILE__, array($this, 'mo_ldap_local_deactivate'));
        add_action('show_user_profile', array($this, 'show_user_profile'));
        if (get_option('mo_ldap_local_enable_login') == 1) {
            remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
            remove_filter('authenticate', 'wp_authenticate_email_password', 20, 3);
            add_filter('authenticate', array($this, 'ldap_login'), 7, 3);
        }

        $version_in_db = get_option('mo_ldap_local_current_plugin_version');
        if($version_in_db!=$current_version){
            update_option('mo_ldap_local_current_plugin_version',$current_version);
        }
        register_activation_hook(__FILE__, array($this, 'mo_ldap_activate'));
        add_action('admin_footer', array($this, 'ldap_feedback_request'));
    }

    function ldap_feedback_request()
    {
        display_ldap_feedback_form();
    }

    function show_user_profile($user)
    {

        if ($this->is_administrator_user($user)) {
            $custom_attributes = array();
            $wp_options = wp_load_alloptions();

            ?>
            <h3>Extra profile information</h3>

            <table class="form-table">

                <tr>
                    <td><b><label for="user_dn">User DN</label></b></td>

                    <td>
                        <b><?php echo esc_attr(get_the_author_meta('mo_ldap_user_dn', $user->ID)); ?></b></td>
                    </td>
                </tr>
                <?php
                foreach ($wp_options as $option => $value) {
                    if (strpos($option, "mo_ldap_local_custom_attribute_") === false) {
                        //Do nothing
                    } else {
                        ?>
                        <tr>
                        <td><b><font color="#FF0000"></font><?php echo $value; ?></b></td>
                        <td><input type="text" name="<?php echo $option; ?>"
                                   value="<?php echo get_user_meta($user->ID, $option, true); ?>" style="width:61%;"/>
                        </td>
                        </tr><?php
                    }
                }

                ?>
            </table>

            <?php
        }
    }


    function ldap_login($user, $username, $password)
    {

        if (empty($username) || empty ($password)) {
            //create new error object and add errors to it.
            $error = new WP_Error();

            if (empty($username)) { //No email
                $error->add('empty_username', __('<strong>ERROR</strong>: Email field is empty.'));
            }

            if (empty($password)) { //No password
                $error->add('empty_password', __('<strong>ERROR</strong>: Password field is empty.'));
            }
            return $error;
        }


        $enable_wp_admin_login = get_option('mo_ldap_local_enable_admin_wp_login');
        if ($enable_wp_admin_login == 1) {
            if (username_exists($username)) {
                $user = get_user_by("login", $username);
                if ($user && $this->is_administrator_user($user)) {
                    if (wp_check_password($password, $user->data->user_pass, $user->ID)){
                        return $user;
					}
                }
            }
        }

        $ldap_login_status = get_option("mo_ldap_ldap_login_status");
        if($ldap_login_status==0){
            update_option("mo_ldap_ldap_login_status",-1);
        }

        $mo_ldap_config = new Mo_Ldap_Local_Config();
        $auth_response = $mo_ldap_config->ldap_login($username, $password);

        if ($auth_response->statusMessage == 'LDAP_USER_BIND_SUCCESS') {

            if (username_exists($username) || email_exists($username)) {
                $user = get_user_by("login", $username);
                if (empty($user)) {
                    $user = get_user_by("email", $username);
                }
                if (empty($user)) {
					$this->mo_ldap_report_update($username,'ERROR','<strong>Login Error:</strong> Invalid Username/Password combination');
                    $error = new WP_Error();
                    $error->add('error_fetching_user', __('<strong>ERROR</strong>: Invalid Username/Password combination.'));
                    return $error;
                }

                if(get_option('mo_ldap_local_enable_role_mapping')) {
                    $mo_ldap_role_mapping = new Mo_Ldap_Local_Role_Mapping();
                    $member_of_attr = $mo_ldap_role_mapping->get_member_of_attribute($username, $password);
                    $mo_ldap_role_mapping->mo_ldap_local_update_role_mapping($user->ID, $member_of_attr);
                }

                //Update user password if enabled
                $fallback_login_enabled = get_option('mo_ldap_local_enable_fallback_login');
                if ($fallback_login_enabled)
                    wp_set_password($password, $user->ID);

                //Store distinguishedName in User Meta
                update_user_meta($user->ID, 'mo_ldap_user_dn', $auth_response->userDn, false);

                //Update email, fname and lname attributes for user
                $profile_attributes = $auth_response->profileAttributesList;


                $user_data['ID'] = $user->ID;
                if(!empty($profile_attributes['mail']))
                    $user_data['user_email'] = $profile_attributes['mail'];
                if(!empty($profile_attributes['fname']))
                    $user_data['first_name'] = $profile_attributes['fname'];
                if(!empty($profile_attributes['lname']))
                    $user_data['last_name'] = $profile_attributes['lname'];

                wp_update_user($user_data);

                if (get_option('mo_ldap_local_cust', '1') != '0') {
                    //Store custom attributes in user meta
                    $custom_attributes = $auth_response->attributeList;
                    foreach ($custom_attributes as $attribute => $value) {
                        update_user_meta($user->ID, $attribute, $value);
                    }
                }

                update_option("mo_ldap_ldap_login_status",1);
                return $user;
            } else {

                if (!get_option('mo_ldap_local_register_user')) {
					 $this->mo_ldap_report_update($username,'ERROR','<strong>Login Error:</strong> Your Administrator has not enabled Auto Registration. Please contact your Administrator.');
                    $error = new WP_Error();
                    $error->add('registration_disabled_error', __('<strong>ERROR</strong>: Your Administrator has not enabled Auto Registration. Please contact your Administrator.'));
                    return $error;
                } else {

                    //Update user password as LDAP password if enabled, else generate new password
                    $fallback_login_enabled = get_option('mo_ldap_local_enable_fallback_login');
                    if ($fallback_login_enabled)
                        $user_password = $password;
                    else
                        $user_password = wp_generate_password(10, false);

                    $profile_attributes = $auth_response->profileAttributesList;


                    if(!empty($profile_attributes['mail']))
                        $email = $profile_attributes['mail'];
                    if(!empty($profile_attributes['fname']))
                        $fname = $profile_attributes['fname'];
                    if(!empty($profile_attributes['lname']))
                        $lname = $profile_attributes['lname'];
                    if(!empty($profile_attributes['user_login']))
                        $user_login = $profile_attributes['user_login'];

                    $userdata = array(
                        'user_login' => $user_login,
                        'user_email' => $email,
                        'first_name' => $fname,
                        'last_name' => $lname,
                        'user_pass' => $user_password  // Create user with LDAP password as local password
                    );
                    $user_id = wp_insert_user($userdata);

                    //On success
                    if (!is_wp_error($user_id)) {
                        $user = get_user_by("login", $user_login);

                        //Store distinguishedName in User Meta
                        update_user_meta($user->ID, 'mo_ldap_user_dn', $auth_response->userDn, false);


                        if (get_option('mo_ldap_local_cust', '1') != '0') {
                            //Store custom attributes in user meta
                            $custom_attributes = $auth_response->attributeList;
                            foreach ($custom_attributes as $attribute => $value) {
                                update_user_meta($user->ID, $attribute, $value);
                            }
                        }

                        if(get_option('mo_ldap_local_enable_role_mapping')) {
                            $mo_ldap_role_mapping = new Mo_Ldap_Local_Role_Mapping();
                            $member_of_attr = $mo_ldap_role_mapping->get_member_of_attribute($username, $password);
                            $mo_ldap_role_mapping->mo_ldap_local_update_role_mapping($user->ID, $member_of_attr);
                        }

                        update_option("mo_ldap_ldap_login_status",1);
                        return $user;
                    } else {
                        $error_string = $user_id->get_error_message();
                        $email_exists_error = "Sorry, that email address is already used!";
                        if (email_exists($email) && $error_string == $email_exists_error) {
                            $error = new WP_Error();
                            $this->mo_ldap_report_update($username, $auth_response->statusMessage, '<strong>Login Error:</strong> There was an error registering your account. The email is already registered, please choose another one and try again.');
                            $error->add('registration_error', __('<strong>ERROR</strong>: There was an error registering your account. The email is already registered, please choose another one and try again.'));
                            return $error;
                        }else{
                            $error = new WP_Error();
                            $this->mo_ldap_report_update($username,$auth_response->statusMessage,'<strong>Login Error:</strong> There was an error registering your account. Please try again.');
                            $error->add('registration_error', __('<strong>ERROR</strong>: There was an error registering your account. Please try again.'));
                            return $error;
                        }
                    }
                }
            }

            wp_redirect(site_url());
            exit;

        } else if ($auth_response->statusMessage == 'LDAP_USER_BIND_ERROR' || $auth_response->statusMessage == 'LDAP_USER_NOT_EXIST') {
			$this->mo_ldap_report_update($username,$auth_response->statusMessage,'<strong>Login Error:</strong> Invalid username or password entered.');
            $error = new WP_Error();
            $error->add('LDAP_USER_BIND_ERROR', __('<strong>ERROR</strong>: Invalid username or password entered.'));
            return $error;
        } else if ($auth_response->statusMessage == 'LDAP_ERROR') {
			$this->mo_ldap_report_update($username,$auth_response->statusMessage,'<strong>Login Error:</strong> <a target="_blank" href="http://php.net/manual/en/ldap.installation.php">PHP LDAP extension</a> is not installed or disabled. Please enable it.');
            $error = new WP_Error();
            $error->add('LDAP_ERROR', __('<strong>ERROR</strong>: <a target="_blank" href="http://php.net/manual/en/ldap.installation.php">PHP LDAP extension</a> is not installed or disabled. Please enable it.'));
            return $error;
        } else if ($auth_response->statusMessage == 'OPENSSL_ERROR') {
			$this->mo_ldap_report_update($username,$auth_response->statusMessage,'<strong>Login Error:</strong> <a target="_blank" href="http://php.net/manual/en/openssl.installation.php">PHP OpenSSL extension</a> is not installed or disabled.');
            $error = new WP_Error();
            $error->add('OPENSSL_ERROR', __('<strong>ERROR</strong>: <a target="_blank" href="http://php.net/manual/en/openssl.installation.php">PHP OpenSSL extension</a> is not installed or disabled.'));
            return $error;
        } else if ($auth_response->statusMessage == 'LDAP_PING_ERROR') {
            $this->mo_ldap_report_update($username,$auth_response->statusMessage,'<strong>Login Error: </strong> LDAP server is not responding ');
            /*$fallback_login_enabled = get_option('mo_ldap_local_enable_fallback_login');
            if ($fallback_login_enabled) {
                remove_filter('authenticate', array($this, 'ldap_login'), 20, 3);
                add_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
                $user = wp_authenticate($username, $password);
                return $user;
            }*/
            $error = new WP_Error();
            $error->add('LDAP_PING_ERROR', __('<strong>ERROR</strong>:LDAP server is not reachable. Fallback to local wordpress authentication is not supported.'));
        } else {
            $error = new WP_Error();
			$this->mo_ldap_report_update($username,$auth_response->statusMessage,"<strong>Login Error:</strong> Unknown error occurred during authentication. Please contact your administrator.");
            $error->add('UNKNOWN_ERROR', __('<strong>ERROR</strong>: Unknown error occurred during authentication. Please contact your administrator.'));
            return $error;
        }
    }

    function mo_ldap_local_login_widget_menu()
    {
        add_menu_page('LDAP/AD Login for Intranet', 'LDAP/AD Login for Intranet', 'activate_plugins', 'mo_ldap_local_login', array($this, 'mo_ldap_local_login_widget_options'), plugin_dir_url(__FILE__) . 'includes/images/miniorange_icon.png');
        add_submenu_page( 'mo_ldap_local_login'	,'LDAP/AD plugin','Licensing Plans','manage_options','mo_ldap_local_login&amp;tab=pricing', array( $this, 'mo_ldap_show_licensing_page'));

    }

    function mo_ldap_local_login_widget_options()
    {
        update_option('mo_ldap_local_host_name', 'https://login.xecurify.com');
        //Setting default configuration
        $default_config = array(
            'server_url' => 'ldap://58.64.132.235:389',
            'service_account_dn' => 'cn=testuser,cn=Users,dc=miniorange,dc=com',
            'admin_password' => 'XXXXXXXX',
            'dn_attribute' => 'distinguishedName',
            'search_base' => 'cn=Users,dc=miniorange,dc=com',
            'search_filter' => '(&(objectClass=*)(cn=?))',
            'test_username' => 'testuser',
            'test_password' => 'password'
        );
        update_option('mo_ldap_local_default_config', $default_config);
        if (!get_option('load_static_UI')) {
            add_option('load_static_UI','true');
        }
        if (get_option('load_static_UI') && get_option('load_static_UI') == 'true') {
            plugin_tour_ui();
        } else {
            mo_ldap_local_settings();
        }
    }

    public static function checkPasswordpattern($password){
        $pattern = '/^[(\w)*(\!\@\#\$\%\^\&\*\.\-\_)*]+$/';

        return !preg_match($pattern,$password);
    }

    function create_customer() {
        $customer    = new Mo_Ldap_Local_Customer();
        $customerKey = $customer->create_customer();

        $response = array();

        if (!empty($customerKey)) {
            $customerKey = json_decode($customerKey,true);

            if (strcasecmp($customerKey['status'], 'ERROR') == 0) {
                $response['status'] = "ERROR";
            } else if (strcasecmp($customerKey['status'],'CUSTOMER_USERNAME_ALREADY_EXISTS') == 0) {
                $response['status'] = 'USER_ALREADY_EXISTS';
            } else if (strcasecmp($customerKey['status'], 'INVALID_EMAIL') == 0) {
                $response['status'] = 'INVALID_EMAIL';
            } else if (strcasecmp($customerKey['status'],'INVALID_EMAIL_QUICK_EMAIL') == 0) {
                $response['status'] = 'INVALID_QUICK_EMAIL';
            } else if (strcasecmp($customerKey['status'], 'SUCCESS') == 0 && strpos($customerKey['message'], 'Customer successfully registered.') !== false) {
                $this->save_success_customer_config($customerKey['id'], $customerKey['apiKey'], $customerKey['token'], 'Thanks for registering with the miniOrange');
                update_option('mo_ldap_local_password', '');
                $response['status'] = "SUCCESS";
                return $response;
            }
        } else {
            $response['status'] = "ERROR";
        }

        return $response;
    }

    function get_current_customer() {
        $customer    = new Mo_Ldap_Local_Customer();
        $content     = $customer->get_customer_key();

        $response = array();

        if (!empty($content)) {
            $customerKey = json_decode($content, true);
            if (empty($customerKey)) {
                $customerKey = $content;
            }

            if (strcasecmp(isset($customerKey['status']), 'ERROR') == 0) {
                $response['status'] = "ERROR";
            } else if (!is_array($customerKey) && strpos($customerKey, 'Invalid username or password. Please try again.') !== false) {
                $response['status'] = "INVALID_EMAIL_PASSWORD";
            } else if (!is_array($customerKey) && strpos($customerKey, 'The customer is not valid') !== false) {
                $response['status'] = "INVALID_CUSTOMER";
            } else if (strcasecmp($customerKey['status'], 'SUCCESS') == 0) {
                $this->save_success_customer_config($customerKey['id'], $customerKey['apiKey'], $customerKey['token'], 'Thanks for registering with the miniOrange');
                update_option('mo_ldap_local_password', '');
                $response['status'] = "SUCCESS";
            }
        } else {
            $response['status'] = "ERROR";
        }
            return $response;
        }

    function login_widget_save_options()
    {

        if (isset($_POST['option'])) {
            if ($_POST['option'] == "mo_ldap_local_register_customer") {
                //register the customer
                //validate and sanitize
                $email = '';
                $phone = '';
                $password = '';
                $confirmPassword = '';
                $fname = '';
                $lname = '';
                $companyName = '';
                if (Mo_Ldap_Local_Util::check_empty_or_null($_POST['email']) || Mo_Ldap_Local_Util::check_empty_or_null($_POST['password'])) {
                    update_option('mo_ldap_local_message', 'All the fields are required. Please enter valid entries.');
                    $this->show_error_message();
                    return;
                } else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                    update_option('mo_ldap_local_message', 'Please enter a valid email address.');
                    $this->show_error_message();
                    return;
                } else if ($this->checkPasswordpattern(strip_tags($_POST['password']))) {
                    update_option('mo_ldap_local_message', 'Minimum 6 characters should be present. Maximum 15 characters should be present. Only following symbols (!@#.$%^&*-_) should be present.');
                    $this->show_error_message();
                    return;
                } else {
                    $email = sanitize_email($_POST['email']);
                    $password = sanitize_text_field($_POST['password']);
                    $confirmPassword = sanitize_text_field($_POST['confirmPassword']);
                }

                update_option('mo_ldap_local_admin_email', $email);

                if (strcmp($password, $confirmPassword) == 0) {
                    update_option('mo_ldap_local_password', $password);
                    $customer = new Mo_Ldap_Local_Customer();
                    $content = $customer->check_customer();
                    if (!empty($content)) {
                        $content = json_decode($content, true);
                        if (strcasecmp($content['status'], 'CUSTOMER_NOT_FOUND') == 0) {
                            $content = $this->create_customer();
                            if (is_array($content) && array_key_exists('status', $content) && $content['status'] == 'INVALID_EMAIL') {
                                update_option('mo_ldap_local_message', 'There was an error creating an account for you. You may have entered an invalid Email-Id. Please try again with a valid email.');
                                delete_option('mo_ldap_local_admin_email');
                                $this->show_error_message();
                                return;
                            } else if (is_array($content) && array_key_exists('status', $content) && $content['status'] == 'INVALID_QUICK_EMAIL') {
                                update_option('mo_ldap_local_message', 'There was an error creating an account for you. You may have entered an invalid Email-Id. (We discourage the use of disposable emails) Please try again with a valid email.');
                                delete_option('mo_ldap_local_admin_email');
                                $this->show_error_message();
                                return;
                            } else if (is_array($content) && array_key_exists('status', $content) && $content['status'] == 'USER_ALREADY_EXISTS') {
                                update_option('mo_ldap_local_message', 'There was an error creating an account for you. The account already exists with the provided email.');
                                delete_option('mo_ldap_local_admin_email');
                                $this->show_error_message();
                                return;
                            } else if (is_array($content) && array_key_exists('status', $content) && $content['status'] == 'ERROR') {
                                $message = 'There was an error in registering your account. You can also visit our <a target="_blank" href="https://www.miniorange.com/businessfreetrial">miniOrange Sign Up page</a> to register.';
                                update_option('mo_ldap_local_message', $message);
                                delete_option('mo_ldap_local_admin_email');
                                $this->show_error_message();
                                return;
                            } else if (is_array($content) && array_key_exists('status', $content) && $content['status'] == 'SUCCESS') {
                                $pricing_url = add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] );
                                $message = 'Your account has been created successfully. <a href="' . $pricing_url . '">Click here to see our Premium Plans</a> ';
                                update_option('mo_ldap_local_message', $message);
                                $this->show_success_message();
                                return;
                            }
                        } else if (strcasecmp($content['status'], 'ERROR') == 0) {
                            $message = 'There was an error in registering your account. Please check you have active internet connection. You can also visit our <a target="_blank" href="https://www.miniorange.com/businessfreetrial">miniOrange Sign Up page</a> to register.';
                            update_option('mo_ldap_local_message', $message);
                            delete_option('mo_ldap_local_admin_email');
                            $this->show_error_message();
                        } else if (strcasecmp($content['status'], 'TRANSACTION_LIMIT_EXCEEDED') == 0) {
                            $message = 'There was an error in registering your account. You can also visit our <a target="_blank" href="https://www.miniorange.com/businessfreetrial">miniOrange Sign Up page</a> to register.';
                            update_option('mo_ldap_local_message', $message);
                            delete_option('mo_ldap_local_admin_email');
                            $this->show_error_message();
                        } else {
                            $response = $this->get_current_customer();
                            if (is_array($response) && array_key_exists('status', $response) && $response['status'] == 'SUCCESS') {
                                $pricing_url = add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] );
                                $message = 'Your account has been retrieved successfully. <a href="' . $pricing_url . '">Click here to see our Premium Plans</a> ';
                                update_option('mo_ldap_local_message', $message);
                                $this->show_success_message();
                                return;
                            } else if (is_array($response) && array_key_exists('status', $response) && $response['status'] == 'INVALID_EMAIL_PASSWORD') {
                                $message = 'It seems like you already have an account with miniOrange. Please check username or password and try again.';
                                update_option('mo_ldap_local_message', $message);
                                $this->show_error_message();
                                return;
                            } else if (is_array($response) && array_key_exists('status', $response) && strcasecmp($response['status'], 'ERROR') == 0) {
                                $message = 'There was an error in registering your account. You can also visit our <a target="_blank" href="https://www.miniorange.com/businessfreetrial">miniOrange Sign Up page</a> to register.';
                                update_option('mo_ldap_local_message', $message);
                                delete_option('mo_ldap_local_admin_email');
                                $this->show_error_message();
                                return;
                            }
                        }
                    }
                } else {
                    update_option('mo_ldap_local_message', 'Password and Confirm password do not match.');
                    delete_option('mo_ldap_local_verify_customer');
                    $this->show_error_message();
                    return;
                }

            } else if ($_POST['option'] == "mo_ldap_local_verify_customer") {    //login the admin to miniOrange

                //validation and sanitization
                $email = '';
                $password = '';
                if (Mo_Ldap_Local_Util::check_empty_or_null($_POST['email']) || Mo_Ldap_Local_Util::check_empty_or_null($_POST['password'])) {
                    update_option('mo_ldap_local_message', 'All the fields are required. Please enter valid entries.');
                    $this->show_error_message();
                    return;
                } else {
                    $email = sanitize_email($_POST['email']);
                    $password = sanitize_text_field($_POST['password']);
                }

                update_option('mo_ldap_local_admin_email', $email);
                update_option('mo_ldap_local_password', $password);

                $content = $this->get_current_customer();
                if (is_array($content) && array_key_exists('status', $content) && $content['status'] == 'SUCCESS') {
                    $pricing_url = add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] );
                    $message = 'Your account has been retrieved successfully. <a href="' . $pricing_url . '">Click here to see our Premium Plans</a> ';
                    update_option('mo_ldap_local_message', $message);
                    $this->show_success_message();
                    return;
                } else if (is_array($content) && array_key_exists('status', $content) && $content['status'] == 'INVALID_EMAIL_PASSWORD') {
                    $message = 'Invalid username or password. Please check username or password and try again.';
                    update_option('mo_ldap_local_message', $message);
                    $this->show_error_message();
                    return;
                } else if (is_array($content) && array_key_exists('status', $content) && $content['status'] == 'INVALID_CUSTOMER') {
                    $message = 'The email you have entered is not registered with miniOrange. Please register and try again.';
                    update_option('mo_ldap_local_message', $message);
                    delete_option('mo_ldap_local_admin_email');
                    $this->show_error_message();
                    return;
                } else if (is_array($content) && array_key_exists('status', $content) && $content['status'] == 'ERROR') {
                    $message = 'There was an error occurred while verifying your account. Please check you have active internet connection and try again.';
                    update_option('mo_ldap_local_message', $message);
                    delete_option('mo_ldap_local_admin_email');
                    $this->show_error_message();
                    return;
                }
            }else if($_POST['option'] == "clear_ldap_pointers"){
                $uid = get_current_user_id();
                $Ldap_array_dissmised_pointers = explode( ',', (string) get_user_meta( $uid, 'dismissed_wp_pointers', TRUE ) );
                if ( isset( $_GET['tab'] ) ) {
                    $active_tab = $_GET['tab'];
                }else {
                    $active_tab = 'default';
                }
                if (isset($_POST['restart_plugin_tour']) && $_POST['restart_plugin_tour']=='true') {
                    update_option('overall_plugin_tour','true');
                    update_option('load_static_UI','true');
                    $Ldap_array_dissmised_pointers = array_diff($Ldap_array_dissmised_pointers,mo_options_ldap_enum_pointers::$LDAP_PLUGIN_TOUR);
                    unset($Ldap_array_dissmised_pointers[array_search('custom_admin_pointers4_8_52_miniorange-support-ldap',$Ldap_array_dissmised_pointers)]);
                }
                else if (isset($_POST['restart_tour']) && $_POST['restart_tour']=='true') {
                    if (get_option('load_static_UI') && get_option('load_static_UI') == 'true')
                    {
                         update_option('load_static_UI','false');
                    }
                    if($active_tab == 'default') {
                        $remaining_dismissed_pointers = array_diff(mo_options_ldap_enum_pointers::$SERVICE_PROVIDER_LDAP, $Ldap_array_dissmised_pointers);
                        foreach ($remaining_dismissed_pointers as $dismissed_pointer) {
                            array_push($Ldap_array_dissmised_pointers,$dismissed_pointer);
                        }
                    }

                    else if ($active_tab == 'rolemapping') {
                        $remaining_dismissed_pointers = array_diff(mo_options_ldap_enum_pointers::$ROLE_MAPPING_LDAP, $Ldap_array_dissmised_pointers);
                        foreach ($remaining_dismissed_pointers as $dismissed_pointer) {
                            array_push($Ldap_array_dissmised_pointers,$dismissed_pointer);
                        }
                    }
                    else if ($active_tab == 'attributemapping') {
                        $remaining_dismissed_pointers = array_diff(mo_options_ldap_enum_pointers::$ATTRIBUTE_MAPPING_LDAP, $Ldap_array_dissmised_pointers);
                        foreach ($remaining_dismissed_pointers as $dismissed_pointer) {
                            array_push($Ldap_array_dissmised_pointers,$dismissed_pointer);
                        }
                    }
                    else if ($active_tab == 'config_settings') {
                        $remaining_dismissed_pointers = array_diff(mo_options_ldap_enum_pointers::$EXPORT_IMPORT_CONFIG_LDAP, $Ldap_array_dissmised_pointers);
                        foreach ($remaining_dismissed_pointers as $dismissed_pointer) {
                            array_push($Ldap_array_dissmised_pointers,$dismissed_pointer);
                        }
                    }
                }
                else {
                    if ($active_tab == 'default') {
                        update_option('restart_ldap_tour','true');
                        $Ldap_array_dissmised_pointers = array_diff($Ldap_array_dissmised_pointers, mo_options_ldap_enum_pointers::$SERVICE_PROVIDER_LDAP);
                    }
                    else if ($active_tab == 'rolemapping') {
                        $Ldap_array_dissmised_pointers = array_diff($Ldap_array_dissmised_pointers, mo_options_ldap_enum_pointers::$ROLE_MAPPING_LDAP);
                    }
                    else if ($active_tab == 'attributemapping') {
                        $Ldap_array_dissmised_pointers = array_diff($Ldap_array_dissmised_pointers, mo_options_ldap_enum_pointers::$ATTRIBUTE_MAPPING_LDAP);
                    }
                    else if ($active_tab == 'config_settings') {
                        update_option('config_settings_tour','true');
                        $Ldap_array_dissmised_pointers = array_diff($Ldap_array_dissmised_pointers, mo_options_ldap_enum_pointers::$EXPORT_IMPORT_CONFIG_LDAP);
                    }
                }
                update_user_meta($uid,'dismissed_wp_pointers',implode(",",$Ldap_array_dissmised_pointers));
                return;
            } 
            else if ($_POST['option'] == "mo_ldap_local_enable") {        //enable ldap login
                update_option('mo_ldap_local_enable_login', isset($_POST['enable_ldap_login']) ? $_POST['enable_ldap_login'] : 0);
                update_option('mo_ldap_local_enable_admin_wp_login', isset($_POST['enable_ldap_login']) ? 1 : 0);
                if (get_option('mo_ldap_local_enable_login')) {
                    update_option('mo_ldap_local_message', 'Login through your LDAP has been enabled.');
                    $this->show_success_message();
                } else {
                    update_option('mo_ldap_local_message', 'Login through your LDAP has been disabled.');
                    $this->show_error_message();
                }
            } else if($_POST['option'] == 'user_report_logs'){
                update_option( 'mo_ldap_local_user_report_log', isset($_POST['mo_ldap_local_user_report_log']) ? $_POST['mo_ldap_local_user_report_log'] : 0);
                $user_logs_table_exists = get_option('user_logs_table_exists');
                $user_reporting = get_option('mo_ldap_local_user_report_log');
                if($user_reporting == 1 && $user_logs_table_exists != 1) {
                    $this->prefix_update_table();
                }
            }else if($_POST['option'] == 'keep_user_report_logs_on_unistall'){

                update_option( 'mo_ldap_local_keep_user_report_log_on_uninstall', isset($_POST['mo_ldap_local_keep_user_report_log']) ? $_POST['mo_ldap_local_keep_user_report_log'] : 0);
               

            } else if ($_POST['option'] == "mo_ldap_local_register_user") {        //enable auto registration of users
                update_option('mo_ldap_local_register_user', isset($_POST['mo_ldap_local_register_user']) ? $_POST['mo_ldap_local_register_user'] : 0);
                if (get_option('mo_ldap_local_register_user')) {
                    update_option('mo_ldap_local_message', 'Auto Registering users has been enabled.');
                    $this->show_success_message();
                } else {
                    update_option('mo_ldap_local_message', 'Auto Registering users has been disabled.');
                    $this->show_error_message();
                }
            } else if ($_POST['option'] == "mo_ldap_local_save_config") {        //save ldap configuration
                //validation and sanitization
                $server_name = '';
                $dn = '';
                $admin_ldap_password = '';
                if (Mo_Ldap_Local_Util::check_empty_or_null($_POST['ldap_server']) || Mo_Ldap_Local_Util::check_empty_or_null($_POST['dn']) || Mo_Ldap_Local_Util::check_empty_or_null($_POST['admin_password']) || Mo_Ldap_Local_Util::check_empty_or_null($_POST['mo_ldap_protocol']) || Mo_Ldap_Local_Util::check_empty_or_null($_POST['mo_ldap_server_port_no'])) {
                    update_option('mo_ldap_local_message', 'All the fields are required. Please enter valid entries.');
                    $this->show_error_message();
                    return;
                } else {
                    $ldap_protocol = $_POST['mo_ldap_protocol'];
                    $port_number = sanitize_text_field($_POST['mo_ldap_server_port_no']);
                    $server_address = sanitize_text_field($_POST['ldap_server']);
                    $server_name = $ldap_protocol."://".$server_address.":".$port_number;
                    $dn = sanitize_text_field($_POST['dn']);
                    $admin_ldap_password = sanitize_text_field($_POST['admin_password']);
                     
                }

                if (!Mo_Ldap_Local_Util::is_extension_installed('openssl')) {
                    update_option('mo_ldap_local_message', 'PHP openssl extension is not installed or disabled. Please enable it first.');
                    $this->show_error_message();
                } else {
                    //Encrypting all fields and storing them
                    $directory_server_value = $_POST['mo_ldap_directory_server_value'];
                    if (strcasecmp($directory_server_value,'other')==0){
                        $directory_server_custom_value = isset($_POST['mo_ldap_directory_server_custom_value']) && !empty($_POST['mo_ldap_directory_server_custom_value']) ? $_POST['mo_ldap_directory_server_custom_value'] : 'other';
                        update_option('mo_ldap_directory_server_custom_value', $directory_server_custom_value);
                    }
                    update_option('mo_ldap_directory_server_value',$directory_server_value);

                    if(strcasecmp($directory_server_value,"msad")==0){
                        $directory_server = "Microsoft Active Directory";
                    }elseif (strcasecmp($directory_server_value,"openldap")==0){
                        $directory_server = "OpenLDAP";
                    }elseif (strcasecmp($directory_server_value,"freeipa")==0){
                        $directory_server = "FreeIPA";
                    }elseif (strcasecmp($directory_server_value,"jumpcloud")==0){
                        $directory_server = "JumpCloud";
                    }elseif (strcasecmp($directory_server_value,"other")==0){
                        $directory_server = get_option("mo_ldap_directory_server_custom_value");
                    }else{
                        $directory_server = "Not Configured";
                    }

                    update_option('mo_ldap_local_directory_server',$directory_server);
                    update_option('mo_ldap_local_ldap_protocol',$ldap_protocol);
                    update_option('mo_ldap_local_ldap_server_address',Mo_Ldap_Local_Util::encrypt($server_address));
                    if ($ldap_protocol == "ldap") {
                    update_option('mo_ldap_local_ldap_port_number',$port_number);
                    } else if($ldap_protocol == "ldaps") {
                        update_option('mo_ldap_local_ldaps_port_number',$port_number);
                    }

                    update_option('mo_ldap_local_server_url', Mo_Ldap_Local_Util::encrypt($server_name));
                    update_option('mo_ldap_local_server_dn', Mo_Ldap_Local_Util::encrypt($dn));
                    update_option('mo_ldap_local_server_password', Mo_Ldap_Local_Util::encrypt($admin_ldap_password));

                    delete_option('mo_ldap_local_message');
					update_option('refresh',0);
                    $mo_ldap_config = new Mo_Ldap_Local_Config();

                    $message = 'Your configuration has been saved.';
                    $status = 'success';

                    //Test connection with the LDAP configuration provided. This makes a call to check if connection is established successfully.
                    $content = $mo_ldap_config->test_connection();
                    $response = json_decode($content, true);
                    $config_status = get_option('mo_ldap_config_status');
                    if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'BIND_SUCCESS') == 0) {
                        add_option('mo_ldap_local_save_config_status','VALID','','no');
                        update_option('mo_ldap_local_message', $response['statusMessage']);
                        $this->show_success_message();
                        if(strcasecmp($config_status,"none")==0){
                            update_option('mo_ldap_config_status',"step1_connect");
                        }
                    } else if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'BIND_ERROR') == 0) {
                        $this->mo_ldap_report_update('LDAP CONNECTION TEST','ERROR','<strong>Test Connection Error: </strong>'. $response['statusMessage']);
                        update_option('mo_ldap_local_message', $response['statusMessage']);
                        $this->show_error_message();
                        if(strcasecmp($config_status,"none")==0){
                            update_option('mo_ldap_config_status',"error_step_1");
                        }
                    } else if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'PING_ERROR') == 0) {
                        $this->mo_ldap_report_update('LDAP CONNECTION TEST','ERROR','<strong>Test Connection Error: </strong>Cannot connect to LDAP Server. Make sure you have entered correct LDAP server hostname or IP address.');
                        update_option('mo_ldap_local_message', $response['statusMessage']);
                        $this->show_error_message();
                        if(strcasecmp($config_status,"none")==0){
                            update_option('mo_ldap_config_status',"error_step_1");
                        }
                    } else if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'LDAP_ERROR') == 0) {
                        $this->mo_ldap_report_update('LDAP CONNECTION TEST','ERROR','<strong>Test Connection Error: </strong>'. $response['statusMessage']);
                        update_option('mo_ldap_local_message', $response['statusMessage']);
                        $this->show_error_message();
                        if(strcasecmp($config_status,"none")==0){
                            update_option('mo_ldap_config_status',"error_step_1");
                        }
                    } else if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'OPENSSL_ERROR') == 0) {
                        $this->mo_ldap_report_update('LDAP CONNECTION TEST','ERROR','<strong>Test Connection Error: </strong>'. $response['statusMessage']);
                        update_option('mo_ldap_local_message', $response['statusMessage']);
                        $this->show_error_message();
                        if(strcasecmp($config_status,"none")==0){
                            update_option('mo_ldap_config_status',"error_step_1");
                        }
                    } else if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'ERROR') == 0){
                        update_option('mo_ldap_local_message', $response['statusMessage']);
                        $this->mo_ldap_report_update('LDAP CONNECTION TEST','Error','<strong>Test Connection Error: </strong>'. $response['statusMessage']);
                        $this->show_error_message();
                        if(strcasecmp($config_status,"none")==0){
                            update_option('mo_ldap_config_status',"error_step_1");
                        }
                    }


                }
            } else if ($_POST['option'] == "mo_ldap_local_save_user_mapping") {        //save user mapping configuration
                
                delete_option('mo_ldap_local_user_mapping_status');
                //validation and sanitization
                $dn_attribute = '';
                $search_base = '';
                $search_filter = '';
                if (Mo_Ldap_Local_Util::check_empty_or_null($_POST['search_base'])) {
                    update_option('mo_ldap_local_message', 'All the fields are required. Please enter valid entries.');
                     add_option('mo_ldap_local_user_mapping_status','INVALID','','no');
                    $this->show_error_message();
                    return;
                } else {
                    $search_base = sanitize_text_field($_POST['search_base']);
                    if (get_option('mo_ldap_local_cust', '1') == '0') {
                        if (strpos($search_base, ';')) {
                            $pricing_url = add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] );
                            $message = 'You have entered multiple search bases. Multiple Search Bases are supported in the <b>Premium version</b> of the plugin. <a href="' . $pricing_url . '">Click here to upgrade</a>.';
                            update_option('mo_ldap_local_message', $message);
                            $this->show_error_message();
                            return;
                        }
                    }
                }

                if (!Mo_Ldap_Local_Util::is_extension_installed('openssl')) {
                    update_option('mo_ldap_local_message', 'PHP OpenSSL extension is not installed or disabled. Please enable it first.');
                     add_option('mo_ldap_local_user_mapping_status','INVALID','','no');
                    $this->show_error_message();
                } else {
                    //Encrypting all fields and storing them
                    $ldap_username_attribute = $_POST['ldap_username_attribute'];
                    if (!Mo_Ldap_Local_Util::check_empty_or_null($ldap_username_attribute)) {
                        update_option('mo_ldap_local_username_attribute',$ldap_username_attribute);
                        if (($ldap_username_attribute == 'custom_ldap_attribute')) {
                            $custom_ldap_username_attribute = sanitize_text_field($_POST['custom_ldap_username_attribute']);
                            if(Mo_Ldap_Local_Util::check_empty_or_null($custom_ldap_username_attribute)){
                                $directory_server_value = get_option('mo_ldap_directory_server_value');
                                if($directory_server_value == 'openldap' || $directory_server_value == 'freeipa' ){
                                    $ldap_username_attribute = "uid";
                                }
                                else{
                                    $ldap_username_attribute = "samaccountname";
                                }
                            }else{
                            $multiple_username_attributes = explode(';',$custom_ldap_username_attribute);
                            if (count($multiple_username_attributes) > 1) {
                                $pricing_url = add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] );
                                $message = 'You have entered multiple attributes for "Username Attribute" field. Logging in with multiple attributes are supported in the <b>Premium version</b> of the plugin. <a href="' . $pricing_url . '">Click here to upgrade</a> ';
                                update_option('mo_ldap_local_message', $message);
                                $this->show_error_message();
                                return;
                            } else {
                                $ldap_username_attribute = $custom_ldap_username_attribute;
                            }
                            }
                        }
                        $generated_search_filter = '(&(objectClass=*)(' . $ldap_username_attribute . '=?))';
                        update_option('Filter_search', $ldap_username_attribute);
                        update_option('mo_ldap_local_search_filter', Mo_Ldap_Local_Util::encrypt($generated_search_filter));
                    }

                    update_option('mo_ldap_local_search_base', Mo_Ldap_Local_Util::encrypt($search_base));
                    delete_option('mo_ldap_local_message');
                    $message = 'LDAP User Mapping Configuration has been saved. Please proceed for Test Authentication to verify LDAP user authentication.';
                    add_option('mo_ldap_local_message', $message, '', 'no');
                    add_option('mo_ldap_local_user_mapping_status','VALID','','no');
                    $this->show_success_message();
					update_option('import_flag', 1);
                    $config_status = get_option('mo_ldap_config_status');
					if(strcasecmp($config_status,"step3_test_authentication")!=0) {
                        update_option('mo_ldap_config_status', "step2_user_mapping");
                    }
                }
            }else if($_POST['option'] == "mo_ldap_save_attribute_config"){
                $email_attribute=sanitize_text_field($_POST['mo_ldap_email_attribute']);
                update_option("mo_ldap_local_email_attribute",$email_attribute);
                update_option( 'mo_ldap_local_message', 'Successfully saved LDAP Attribute Configuration');
                    $this->show_success_message();
            }else if ($_POST['option'] == "mo_ldap_local_test_auth") {        //test authentication with current settings
                $server_name = get_option('mo_ldap_local_server_url');
                $dn = get_option('mo_ldap_local_server_dn');
                $admin_ldap_password = get_option('mo_ldap_local_server_password');
                $search_base = get_option('mo_ldap_local_search_base');
                $search_filter = get_option('mo_ldap_local_search_filter');

                delete_option('mo_ldap_local_message');

                //validation and sanitization
                $test_username = '';
                $test_password = '';
                //Check if username and password are empty
                if (Mo_Ldap_Local_Util::check_empty_or_null($_POST['test_username']) || Mo_Ldap_Local_Util::check_empty_or_null($_POST['test_password'])) {
					$this->mo_ldap_report_update('Test Authentication ','ERROR','<strong>ERROR</strong>: All the fields are required. Please enter valid entries.');
                    add_option('mo_ldap_local_message', 'All the fields are required. Please enter valid entries.', '', 'no');
                    $this->show_error_message();
                    return;
                } //Check if configuration is saved
                else if (Mo_Ldap_Local_Util::check_empty_or_null($server_name) || Mo_Ldap_Local_Util::check_empty_or_null($dn) || Mo_Ldap_Local_Util::check_empty_or_null($admin_ldap_password) || Mo_Ldap_Local_Util::check_empty_or_null($search_base) || Mo_Ldap_Local_Util::check_empty_or_null($search_filter)) {
					$this->mo_ldap_report_update('Test authentication','ERROR','<strong>Test Authentication Error</strong>: Please save LDAP Configuration to test authentication.');
                    add_option('mo_ldap_local_message', 'Please save LDAP Configuration to test authentication.', '', 'no');
                    $this->show_error_message();
                    return;
                } else {
                    $test_username = sanitize_text_field($_POST['test_username']);
                    $test_password = sanitize_text_field($_POST['test_password']);
                }
                //Call to authenticate test
                $mo_ldap_config = new Mo_Ldap_Local_Config();
                $content = $mo_ldap_config->test_authentication($test_username, $test_password);
                $response = json_decode($content, true);

                if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'LDAP_USER_BIND_SUCCESS') == 0) {
                    $message = 'You have successfully configured your LDAP settings.<br>
								You can set login via directory credentials by checking the Enable LDAP Login in the <b>Sign-In Settings Tab</b> and then <a href="'.wp_logout_url( get_permalink() ).'">Logout</a> from wordpress and login again with your LDAP credentials.<br>';
                    update_option('mo_ldap_local_message', $message);
                    $this->show_success_message();
                    update_option('mo_ldap_config_status',"step3_test_authentication");
                } else if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'LDAP_USER_BIND_ERROR') == 0) {
					$this->mo_ldap_report_update( $_POST['test_username'],'ERROR','<strong>Test Authentication Error: </strong>'. $response['statusMessage']);
                    update_option('mo_ldap_local_message', $response['statusMessage']);
                    $this->show_error_message();
                } else if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'LDAP_USER_SEARCH_ERROR') == 0) {
                    $this->mo_ldap_report_update( $_POST['test_username'],'ERROR','<strong>Test Authentication Error: </strong>'.$response['statusMessage']);
                    update_option('mo_ldap_local_message', $response['statusMessage']);
                    $this->show_error_message();
                } else if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'LDAP_USER_NOT_EXIST') == 0) {
                    $this->mo_ldap_report_update( $_POST['test_username'],'ERROR','<strong>Test Authentication Error: </strong>'.$response['statusMessage']);
                    update_option('mo_ldap_local_message', $response['statusMessage']);
                    $this->show_error_message();
                } else if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'ERROR') == 0) {
					$this->mo_ldap_report_update($_POST['test_username'],'ERROR', '<strong>Test Authentication Error: </strong>'. $response['statusMessage']);
                    update_option('mo_ldap_local_message', $response['statusMessage']);
                    $this->show_error_message();
                } else if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'LDAP_ERROR') == 0) {
					$this->mo_ldap_report_update($_POST['test_username'],'ERROR','<strong>Test Authentication Error: </strong>'. $response['statusMessage']);
                    update_option('mo_ldap_local_message', $response['statusMessage']);
                    $this->show_error_message();
                } else if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'OPENSSL_ERROR') == 0) {
					$this->mo_ldap_report_update($_POST['test_username'],'ERROR','<strong>Test Authentication Error: </strong>'. $response['statusMessage']);
                    update_option('mo_ldap_local_message', $response['statusMessage']);
                    $this->show_error_message();
                } else if (isset($response['statusCode']) && strcasecmp($response['statusCode'], 'LDAP_LOCAL_SERVER_NOT_CONFIGURED') == 0) {
                    $this->mo_ldap_report_update($_POST['test_username'],'ERROR','<strong>Test Authentication Error: </strong>'. $response['statusMessage']);
					update_option('mo_ldap_local_message', $response['statusMessage']);
                    $this->show_error_message();
                } else {
                    $this->mo_ldap_report_update($_POST['test_username'],'ERROR','<strong>Test Authentication Error: </strong> There was an error processing your request. Please verify the Search Base(s) and Username attribute. Your user should be present in the Search base defined.');
					update_option('mo_ldap_local_message', 'There was an error processing your request. Please verify the Search Base(s) and Username attribute. Your user should be present in the Search base defined.');
                    $this->show_error_message();
                }
            }else if($_POST['option']=='mo_ldap_pass'){
                update_option( 'mo_ldap_export', isset($_POST['enable_ldap_login']) ? 1 : 0);

                if(get_option('mo_ldap_export')){
                    update_option( 'mo_ldap_local_message', 'Service account password will be exported in encrypted fashion');
                    $this->show_success_message();
                }
                else{
                    update_option( 'mo_ldap_local_message', 'Service account password will not be exported.');
                    $this->show_error_message();
                }
            } 
			else if($_POST['option']=='mo_ldap_export'){


                $ldap_server_url = get_option('mo_ldap_local_server_url');
                if(!empty($ldap_server_url)) {

                    
                    $this->miniorange_ldap_export();
                }
                else{
                    update_option( 'mo_ldap_local_message', 'LDAP Configuration not set. Please configure LDAP Connection settings.');
                    $this->show_success_message();
                }


            } else if($_POST['option']=='enable_config'){
                update_option( 'en_save_config', isset($_POST['enable_save_config']) ? 1 : 0);
				 if(get_option('en_save_config')){
                    update_option( 'mo_ldap_local_message', 'Plugin configuration will be persisted upon uninstall.');
                    $this->show_success_message();
                }
                else{
                    update_option( 'mo_ldap_local_message', 'Plugin configuration will not be persisted upon uninstall');
                    $this->show_error_message();
                }
            }  else if ($_POST['option'] == 'reset_password') {
                $admin_email = get_option('mo_ldap_local_admin_email');
                $customer = new Mo_Ldap_Local_Customer();
                $forgot_password_response = $customer->mo_ldap_local_forgot_password($admin_email);
                if (!empty($forgot_password_response)) {
                    $forgot_password_response = json_decode($forgot_password_response,'true');
                    if ($forgot_password_response->status == 'SUCCESS') {
                        $message = 'You password has been reset successfully and sent to your registered email. Please check your mailbox.';
                        update_option('mo_ldap_local_message', $message);
                        $this->show_success_message();
                    }
                } else {
                    update_option('mo_ldap_local_message', 'Error in request');
                    $this->show_error_message();
                }
            } else if ($_POST['option'] == 'mo_ldap_local_fallback_login') {
                update_option('mo_ldap_local_enable_fallback_login', isset($_POST['mo_ldap_local_enable_fallback_login']) ? $_POST['mo_ldap_local_enable_fallback_login'] : 0);
                update_option('mo_ldap_local_message', 'Fallback login using Wordpress password enabled');
                $this->show_success_message();
            } else if ($_POST['option'] == 'mo_ldap_local_enable_admin_wp_login') {
                update_option('mo_ldap_local_enable_admin_wp_login', isset($_POST['mo_ldap_local_enable_admin_wp_login']) ? $_POST['mo_ldap_local_enable_admin_wp_login'] : 0);
                if (get_option('mo_ldap_local_enable_admin_wp_login')) {
                    update_option('mo_ldap_local_message', 'Allow administrators to login with WordPress Credentials is enabled.');
                    $this->show_success_message();
                } else {
                    update_option('mo_ldap_local_message', 'Allow administrators to login with WordPress Credentials is disabled.');
                    $this->show_error_message();
                }
            } else if ($_POST['option'] == 'mo_ldap_local_cancel') {
                delete_option('mo_ldap_local_admin_email');
                delete_option('mo_ldap_local_registration_status');
                delete_option('mo_ldap_local_verify_customer');
                delete_option('mo_ldap_local_email_count');
                delete_option('mo_ldap_local_sms_count');
            } else if ($_POST['option'] == "mo_ldap_goto_login") {
                delete_option('mo_ldap_local_new_registration');
                update_option('mo_ldap_local_verify_customer', 'true');
            } else if ($_POST['option'] == 'change_miniorange_account') {
                delete_option('mo_ldap_local_admin_customer_key');
                delete_option('mo_ldap_local_admin_api_key');
                delete_option('mo_ldap_local_password', '');
                delete_option('mo_ldap_local_message');
                delete_option('mo_ldap_local_cust', '0');
                delete_option('mo_ldap_local_verify_customer');
                delete_option('mo_ldap_local_new_registration');
                delete_option('mo_ldap_local_registration_status');
            } else if($_POST['option'] == 'mo_ldap_login_send_query'){
                $email = sanitize_text_field($_POST['inner_form_email_id']);
                $phone = sanitize_text_field($_POST['inner_form_phone_id']);
                $query = sanitize_text_field($_POST['inner_form_query_id']);

                $choice =$_POST['export_configuration_choice'];
                if($choice=='yes'){
                    $configuration=$this->auto_email_ldap_export();
                    $configuration = implode(" <br>",$configuration);
                    $query = $query." ,<br><br>Plugin Configuration:<br> " . $configuration;
                }
                elseif($choice=='no'){
                    $configuration = "Configuration was not uploaded by user";
                    $query = $query." ,<br><br>Plugin Configuration:<br> " . $configuration;
                }
                $query = '[WP LDAP for Intranet (Free Plugin)]: ' . $query;
                $this->mo_ldap_send_query($email, $phone, $query);
            } else if ($_POST['option'] == 'mo_ldap_login_send_feature_request_query') {
                $email = sanitize_text_field($_POST['query_email']);
                $phone = sanitize_text_field($_POST['query_phone']);
                $query = sanitize_text_field($_POST['query']);


                $query = '[WP LDAP for Intranet (Free Plugin)]: ' . $query;
                $this->mo_ldap_send_query($email, $phone, $query);
            }else if (isset($_POST['option']) && $_POST['option'] == 'mo_ldap_plugin_tour_start') {
                update_option('mo_tour_skipped','true');
                update_option('overall_plugin_tour','true');
            }else if (isset($_POST['option']) && $_POST['option'] == 'mo_ldap_skip_ldap_tour') {
                update_option('mo_tour_skipped','true');
                update_option('load_static_UI','false');
            }
            if(isset($_POST['option']) and $_POST['option'] =="mo_ldap_trial_request"){
                if(isset($_POST['mo_ldap_demo_email']))
                    $email = htmlspecialchars($_POST['mo_ldap_demo_email']);

                if(empty($email))
                    $email = get_option('mo_ldap_local_admin_email');

                if(isset($_POST['mo_ldap_demo_plan']))
                    $demo_plan = htmlspecialchars($_POST['mo_ldap_demo_plan']);

                if(isset($_POST['mo_ldap_demo_description']))
                    $demo_requirements = htmlspecialchars($_POST['mo_ldap_demo_description']);

                $license_plans = array(
                    'basic-plan'                   => 'Basic LDAP Authentication Plan',
                    'kerbores-ntlm'                => 'Basic LDAP Authentication Plan + Kerberos/NTLM SSO',
                    'enterprise-plan'              => 'Enterprise/All-Inclusive Plan',
                    'multisite-basic-plan'         => 'Multisite Basic LDAP Authentication Plan',
                    'multisite-kerbores-ntlm'      => 'Multisite Basic LDAP Authentication Plan + Kerberos/NTLM SSO',
                    'enterprise-enterprise-plan'   => 'Multisite Enterprise/All-Inclusive Plan',
                );
                if(isset($license_plans[$demo_plan]))
                    $demo_plan = $license_plans[$demo_plan];
                $addons = array(
                    'directory-sync'          => 'Sync Users LDAP Directory',
                    'buddypress-integration'  => 'Sync BuddyPress Extended Profiles',
                    'password-sync'           => 'Password Sync with LDAP Server',
                    'profile-picture-map'     => 'Profile Picture Sync for WordPress and BuddyPress',
                    'ultimate-member-login'   => 'Ultimate Member Login Integration',
                    'page-post-restriction'   => 'Page/Post Restriction',
                    'search-staff'            => 'Search Staff from LDAP Directory',
                    'profile-sync'            => 'Third Party Plugin User Profile Integration',
                    'gravity-forms'           => 'Gravity Forms Integration',
                    'buddypress-group'        => 'Sync BuddyPress Groups',
                    'memberpress-integration' => 'MemberPress Plugin Integration',
                    'emember-integration'     => 'eMember Plugin Integration'
                );

                $addons_selected = array();
                foreach($addons as $key => $value){
                    if(isset($_POST[$key]) && $_POST[$key] == "true")
                        $addons_selected[$key] = $value;
                }
                $directory_access = '';
                $query ='';
                if(!empty($demo_plan))
                    $query .= "<br><br>[Interested in plan] : " . $demo_plan;

                if(!empty($addons_selected)){
                    $query .= "<br><br>[Interested in add-ons] : ";
                    foreach($addons_selected as $key => $value){
                        $query .= $value;
                        if(next($addons_selected))
                            $query .= ", ";
                    }
                }

                if(!empty($demo_requirements))
                    $query .= "<br><br>[Requirements] : " . $demo_requirements;

                if(isset($_POST['get_directory_access']))
                    $directory_access = htmlspecialchars($_POST['get_directory_access']);

                if($directory_access == "Yes"){
                    $directory_access = "Yes";
                }else{
                    $directory_access = "No";
                }
                $query.='<br><br>[Is your LDAP server publicly accessible?] : '.$directory_access.'';

                $query   = ' [Demo: WordPress LDAP/AD Plugin]: ' . $query;
                $this->mo_ldap_send_query($email, $phone, $query);
            }
            if (isset($_POST['option']) and $_POST['option'] == 'mo_ldap_skip_feedback') {
                deactivate_plugins(__FILE__);
                update_option('mo_ldap_local_message', 'Plugin deactivated successfully');
                $this->show_success_message();
            }
            if (isset($_POST['option']) and $_POST['option'] == 'mo_ldap_feedback') {
                $user = wp_get_current_user();
                $message = 'Plugin Deactivated:';
                $deactivate_reason_message = array_key_exists( 'query_feedback', $_POST ) ? htmlspecialchars($_POST['query_feedback']) : false;

                $reply_required = '';
                if(isset($_POST['get_reply']))
                    $reply_required = htmlspecialchars($_POST['get_reply']);
                if(empty($reply_required)){
                    $reply_required = "NO";
                    $message.='<b style="color:red";> &nbsp;[Follow up Needed : '.$reply_required.']</b>';
                }else{
                    $reply_required = "YES";
                    $message.='<b style="color:green";> &nbsp;[Follow up Needed : '.$reply_required.']</b>';
                }

                if (!empty($deactivate_reason_message)) {
                    $message.= '<br>Feedback : '.$deactivate_reason_message.'<br/>';
                }
                if (isset($_POST['rate'])) {
                    $rate_value = htmlspecialchars($_POST['rate']);
                    $message.= '<br>[Rating : '.$rate_value.']<br>';
                }
                $message.= '<br>1. Plugin Activation Time : '.get_option('mo_ldap_activation_time');

                $directory_server = get_option("mo_ldap_local_directory_server");
                $message.= '<br><br>2. Directory Server : '.$directory_server;

                $message.= '<br><br>3. LDAP Configuration Tab Status';

                $config_status = get_option('mo_ldap_config_status');
                $step1_connect_status = "Not Done";
                $step2_user_mapping_status = "Not Done";
                $step3_test_authentication_status = "Not Done";

                if(strcasecmp($config_status,"step1_connect")==0){
                    $step1_connect_status = "Done";
                }elseif(strcasecmp($config_status,"step2_user_mapping")==0){
                    $step1_connect_status = "Done";
                    $step2_user_mapping_status = "Done";
                }elseif(strcasecmp($config_status,"step3_test_authentication")==0){
                    $step1_connect_status = "Done";
                    $step2_user_mapping_status = "Done";
                    $step3_test_authentication_status = "Done";
                }elseif(strcasecmp($config_status,"error_step_1")==0){
                    $step1_connect_status = "Tried and Failed";
                }


                $message.= '<br>a) LDAP Connection Information : '.$step1_connect_status;
                $message.= '<br>b) User Mapping                : '.$step2_user_mapping_status;
                $message.= '<br>c) Test Authentication         : '.$step3_test_authentication_status;

                $ldap_login_status = get_option("mo_ldap_ldap_login_status");
                if($ldap_login_status==0){
                    $message.= '<br><br>4. LDAP Login Status (WordPress Login Page) : Never Tried';
                }elseif ($ldap_login_status==-1){
                    $message.= '<br><br>4. LDAP Login Status (WordPress Login Page) : Tried and Failed';
                } else{
                    $message.= '<br><br>4. LDAP Login Status (WordPress Login Page) : Successfully Logged In';
                }

                $license_status = get_option('mo_ldap_license_flag');
                if($license_status==0){
                    $message.= '<br><br>5. License Page Visited : No';
                }else{
                    $message.= '<br><br>5. License Page Visited : Yes';
                }

                $current_version = get_option('mo_ldap_local_current_plugin_version');
                $message.= '<br><br>6. Current Version Installed : '.$current_version.'<br>';

                $email = $_POST['query_mail'];

                if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $email = get_option('mo_ldap_local_admin_email');
                    if(empty($email))
                        $email = $user->user_email;
                }
                $phone = get_option( 'mo_ldap_local_admin_phone' );
                $feedback_reasons = new Mo_Ldap_Local_Customer();
                if(!is_null($feedback_reasons)){
                    if(!Mo_Ldap_Local_Util::is_curl_installed()){
                        deactivate_plugins( __FILE__ );
                        wp_redirect('plugins.php');
                    } else {
                        $submited = json_decode( $feedback_reasons->send_email_alert( $email, $phone, $message ), true );
                        if ( json_last_error() == JSON_ERROR_NONE ) {
                            if ( is_array( $submited ) && array_key_exists( 'status', $submited ) && $submited['status'] == 'ERROR' ) {
                                update_option( 'mo_ldap_local_message', $submited['message'] );
                                $this->show_error_message();

                            }
                            else {
                                if ( $submited == false ) {

                                    update_option( 'mo_ldap_local_message', 'Error while submitting the query.' );
                                    $this->show_error_message();
                                }
                            }
                        }

                        deactivate_plugins( __FILE__ );
                        update_option( 'mo_ldap_local_message', 'Thank you for the feedback.' );
                        $this->show_success_message();
                        wp_redirect('plugins.php');
                    }
                }
            }
        }
    }

    function mo_ldap_send_query($email, $phone, $query)
    {
        $current_version = get_option('mo_ldap_local_current_plugin_version');
        $query = $query."<br><br>[Current Version Installed] : ".$current_version;

        if (Mo_Ldap_Local_Util::check_empty_or_null($email) || Mo_Ldap_Local_Util::check_empty_or_null($query)) {
            update_option('mo_ldap_local_message', 'Please submit your query along with email.');
            $this->show_error_message();
            return;
        } else {
            $contact_us = new Mo_Ldap_Local_Customer();
            $submited = json_decode($contact_us->submit_contact_us($email, $phone, $query), true);

            if (isset($submited['status']) && strcasecmp($submited['status'], 'CURL_ERROR') == 0) {
                update_option('mo_ldap_local_message', $submited['statusMessage']);
                $this->show_error_message();
            } else if (isset($submited['status']) && strcasecmp($submited['status'], 'ERROR') == 0) {
                update_option('mo_ldap_local_message', 'There was an error in sending query. Please send us an email on <a href=mailto:info@xecurify.com><b>info@xecurify.com</b></a>.');
                $this->show_error_message();
            } else {
                update_option('mo_ldap_local_message', 'Your query successfully sent.<br>In case we dont get back to you, there might be email delivery failures. You can send us email on <a href=mailto:info@xecurify.com><b>info@xecurify.com</b></a> in that case.');
                $this->show_success_message();
            }
        }
    }

	function miniorange_ldap_export()
    {
        
        if (array_key_exists("option", $_POST) && $_POST['option'] == 'mo_ldap_export') {

            $tab_class_name = unserialize(Tab_ldap_Class_Names);
           
            $configuration_array = array();
            foreach ($tab_class_name as $key => $value) {
                $configuration_array[$key] = $this->mo_get_configuration_array($value);
            }
            
            header("Content-Disposition: attachment; filename=miniorange-ldap-config.json");
            echo(json_encode($configuration_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            exit;

        }
    }
    function mo_get_configuration_array( $class_name ) {
        $class_object = call_user_func( $class_name . '::getConstants' );
        $mapping_count=get_option('mo_ldap_local_role_mapping_count');
        $mo_array = array();
        $mo_map_key= array();
        $mo_map_value=array();
        foreach ( $class_object as $key => $value ) {

            if($value=="mo_ldap_local_server_url"or $value=="mo_ldap_local_server_password" or $value=="mo_ldap_local_server_dn" or $value=="mo_ldap_local_search_base" or $value=="mo_ldap_local_search_filter" or $value=="mo_ldap_local_Filter_Search")
                $flag = 1;
            else
                $flag = 0;
            if($value=="mo_ldap_local_mapping_key_")
            {
                for($i = 1 ; $i <= $mapping_count ; $i++){
                    $mo_map_key[ $i ] = get_option($value.$i);
                }
                $mo_option_exists = $mo_map_key;
            }
            elseif($value=="mo_ldap_local_mapping_value_")
            {
                for($i = 1 ; $i <= $mapping_count ; $i++){
                  $mo_map_value[ $i ] = get_option($value.$i);
                }
                $mo_option_exists = $mo_map_value;

            }
            else
                $mo_option_exists= get_option($value);

            if($mo_option_exists){
                if(@unserialize($mo_option_exists)!==false){
                    $mo_option_exists = unserialize($mo_option_exists);
                }
                if($flag==1 )
                    if($value=="mo_ldap_local_server_password" and get_option('mo_ldap_export')== '0')
                        continue;
                    else if($value=="mo_ldap_local_server_password" and get_option('mo_ldap_export')=='1' )
                        $mo_array[ $key ] = $mo_option_exists;
                    else
                        $mo_array[$key] = Mo_Ldap_Local_Util::decrypt($mo_option_exists);
                else
                    $mo_array[ $key ] = $mo_option_exists;

            }


        }
        return $mo_array;
    }
   
    function auto_email_ldap_export()
		{
		    $directory_name = get_option('mo_ldap_local_directory_server');
			$server_name = get_option('mo_ldap_local_server_url') ? Mo_Ldap_Local_Util::decrypt(get_option('mo_ldap_local_server_url')) : '';
			$dn = get_option('mo_ldap_local_server_dn') ? Mo_Ldap_Local_Util::decrypt(get_option('mo_ldap_local_server_dn')) : '';
			$search_base = get_option( 'mo_ldap_local_search_base') ? Mo_Ldap_Local_Util::decrypt(get_option( 'mo_ldap_local_search_base')) : '';
			$search_filter = get_option( 'mo_ldap_local_search_filter') ? Mo_Ldap_Local_Util::decrypt(get_option( 'mo_ldap_local_search_filter')) : '';
			$configuration = array("LDAP Directory Name"=>"LDAP Directory Name:  ".$directory_name,"LDAP Server"=>"LDAP Server:  ".$server_name,"Service Account DN"=>"Service Account DN:  ".$dn,"Search Base"=>"Search Base:  ".$search_base,"LDAP Search Filter"=>"LDAP Search Filter:  ".$search_filter);
			return $configuration;
		}

    function test_attribute_configuration()
    {
        if (is_user_logged_in()) {
            if (current_user_can('administrator') && isset($_REQUEST['option'])) {
                if ($_REQUEST['option'] != null and $_REQUEST['option'] == 'testattrconfig') {
                    $username = $_REQUEST['user'];
                    $mo_ldap_config = new Mo_Ldap_Local_Config();
                    $mo_ldap_config->test_attribute_configuration($username);
                } else if ($_REQUEST['option'] != null and $_REQUEST['option'] == 'testrolemappingconfig') {
                    $username = $_REQUEST['user'];
                    $mo_ldap_role_mapping = new Mo_Ldap_Local_Role_Mapping();
                    $mo_ldap_role_mapping->test_configuration($username);
                } else if ($_REQUEST['option'] == 'fetchgroups') {
                    $group_search_base = $_REQUEST['searchbase'];
                    $mo_ldap_config = new Mo_Ldap_Local_Config();
                    $mo_ldap_config->fetch_groups_info($group_search_base);
                } else if($_REQUEST['option'] == 'searchbaselist'){
                    $mo_ldap_config = new Mo_Ldap_Local_Config();
                    $mo_ldap_config->show_search_bases_list();
                }
            }
        }
    }

    /*
     * Save all required fields on customer registration/retrieval complete.
     */
    function save_success_customer_config($id, $apiKey, $token, $message)
    {
        update_option('mo_ldap_local_admin_customer_key', $id);
        update_option('mo_ldap_local_admin_api_key', $apiKey);
        update_option('mo_ldap_local_password', '');
        update_option('mo_ldap_local_message', $message);
        update_option('mo_ldap_local_cust', '0');
        delete_option('mo_ldap_local_verify_customer');
        delete_option('mo_ldap_local_new_registration');
        delete_option('mo_ldap_local_registration_status');
        $this->show_success_message();
    }

    function mo_ldap_local_settings_style($page)
    {
		if($page != 'toplevel_page_mo_ldap_local_login'){
            return;
        }
        wp_enqueue_style( 'mo_ldap_admin_ldap_plugin_style', plugins_url( 'includes/css/mo_ldap_plugins_page.css', __FILE__ ) );
        wp_enqueue_style( 'mo_ldap_admin_settings_jquery_style', plugins_url( 'includes/css/jquery.ui.css', __FILE__ ) );
        wp_enqueue_style('mo_ldap_admin_settings_style', plugins_url('includes/css/style_settings.min.css', __FILE__));
        wp_enqueue_style('mo_ldap_admin_settings_phone_style', plugins_url('includes/css/phone.css', __FILE__));
        wp_enqueue_style( 'mo_ldap_admin_font_awsome', plugins_url('includes/css/font-awesome.min.css', __FILE__));

        //Feature Request Tab Styles
        wp_enqueue_style('Grid_Layout', plugins_url('includes/css/grid.min.css', __FILE__));
        wp_enqueue_style('Fr_Style', plugins_url('includes/css/feature_request_style.min.css', __FILE__));

		$ldap_file = plugin_dir_path( __FILE__ ) . 'pointers_ldap.php';

        // Arguments: pointers php file, version (dots will be replaced), prefix
        $ldap_manager = new PointersManager_Ldap( $ldap_file, '4.8.52', 'custom_admin_pointers' );
        $ldap_manager->parse();
        $ldap_pointers = $ldap_manager->filter( $page );
       $plugin_tour_over=get_option('mo_tour_skipped');
        if ($plugin_tour_over=="true" ) { // nothing to do if no pointers pass the filter
            if(empty( $ldap_pointers)) {
                update_option("mo_ldap_local_empty_pointers", "true");
                return;
            }
        }

		 wp_enqueue_style( 'wp-pointer' );
        $js_url = plugins_url( 'includes/js/pointers.js', __FILE__ );
        wp_enqueue_script( 'custom_admin_pointers', $js_url, array('wp-pointer'), NULL, TRUE );
        // data to pass to javascript
        $data = array(
            'next_label' => __( 'Next' ),
            'skip_label' => __('Skip'),
            'close_label'=> __( 'Close'),
            'pointers' => $ldap_pointers
        );
        wp_localize_script('custom_admin_pointers', 'MyAdminPointers', $data);
    }

    function mo_ldap_local_settings_script()
    {
        wp_enqueue_script('mo_ldap_admin_settings_phone_script', plugins_url('includes/js/phone.js', __FILE__));
        wp_register_script('mo_ldap_admin_settings_script', plugins_url('includes/js/settings_page.js', __FILE__), array('jquery'));
        wp_enqueue_script('mo_ldap_admin_settings_script');
    }

    function error_message()
    {
        $class = "error";
        $message = get_option('mo_ldap_local_message');
        echo "<div id='error' class='" . $class . "'> <p>" . $message . "</p></div>";
    }

    function success_message()
    {
        $class = "updated";
        $message = get_option('mo_ldap_local_message');
        echo "<div id='success' class='" . $class . "'> <p>" . $message . "</p></div>";
    }

    function show_success_message()
    {
        remove_action('admin_notices', array($this, 'error_message'));
        add_action('admin_notices', array($this, 'success_message'));
    }

    function show_error_message()
    {
        remove_action('admin_notices', array($this, 'success_message'));
        add_action('admin_notices', array($this, 'error_message'));
    }

	function prefix_update_table() {
        // Assuming we have our current database version in a global variable
        global $prefix_my_db_version;
        // If database version is not the sam
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE if not exists`{$wpdb->base_prefix}user_report` (
			  id int NOT NULL AUTO_INCREMENT,
			  user_name varchar(50) NOT NULL,
			  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			  Ldap_status varchar(250) NOT NULL,
			  Ldap_error varchar(250) ,
			  PRIMARY KEY  (id)
			) $charset_collate;";


        if ( ! function_exists('dbDelta') ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }

        dbDelta( $sql );

        update_option( 'user_logs_table_exists', 1 );

    }

    function mo_ldap_activate()
    {
        $mo_ldap_token_key = get_option('mo_ldap_local_customer_token');
        if (empty($mo_ldap_token_key)) {
            update_option('mo_ldap_local_customer_token', Mo_Ldap_Local_Util::generateRandomString(15));
        }

        $email_attr = get_option("mo_ldap_local_email_attribute");
        if(empty($email_attr)) {
            update_option("mo_ldap_local_email_attribute", "mail");
        }
        add_option("mo_ldap_ldap_login_status",0);
        add_option('mo_ldap_license_flag',0);
        $date = new DateTime("now", new DateTimeZone('Asia/Kolkata'));
        $sys_timestamp = $date->format('j-F-Y h:i:s a').' IST';
        add_option('mo_ldap_activation_time',$sys_timestamp);
        
		$config_step1_present = get_option('mo_ldap_local_save_config_status');
        $config_step2_present = get_option('mo_ldap_local_user_mapping_status');
        if(!$config_step1_present){
            update_option('mo_ldap_config_status',"none");
        }elseif (strcasecmp($config_step2_present,"VALID")==0){
            update_option('mo_ldap_config_status',"step2_user_mapping");
        }elseif (strcasecmp($config_step1_present,"VALID")==0){
            update_option('mo_ldap_config_status',"step1_connect");
        }
        ob_clean();
    }
	 function mo_ldap_report_update($username,$status,$ldapError)
    {
        if(get_option('mo_ldap_local_user_report_log')== 1){
            global $wpdb;
            $table_name = $wpdb->prefix . 'user_report';
        $result = $wpdb->get_row("SELECT id FROM $table_name WHERE user_name ='" . $username . "'");

            $wpdb->insert(
                $table_name,
                array(
                    'user_name' => $username,
                    'time' => current_time('mysql'),
                    'Ldap_status' => $status,
                    'Ldap_error' => $ldapError
                )
            );
        }
    }


    function mo_ldap_local_deactivate()
    {
        //delete all stored key-value pairs
        delete_option('mo_ldap_local_message');
        delete_option('mo_ldap_local_enable_login');

        delete_option('mo_ldap_license_flag');
        delete_option('mo_ldap_activation_time');
        delete_option('mo_ldap_config_status');
        delete_option("mo_ldap_ldap_login_status");

        delete_option('mo_ldap_local_enable_role_mapping');
        delete_option('overall_plugin_tour');
        delete_option('load_static_UI');
        delete_user_meta(get_current_user_id(),'dismissed_wp_pointers');
        delete_option("mo_ldap_local_empty_pointers");
        delete_option('mo_tour_skipped');
        delete_option('restart_ldap_tour');
        delete_option('config_settings_tour');
        delete_option('load_support_tab');
    }

    function is_administrator_user($user)
    {
        $userRole = ($user->roles);
        if (!is_null($userRole) && in_array('administrator', $userRole))
            return true;
        else
            return false;
    }

}

new Mo_Ldap_Local_Login;
?>