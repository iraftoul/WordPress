<?php
/** miniOrange enables user to log in using LDAP credentials.
    Copyright (C) 2015  miniOrange

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
* @package 		miniOrange LDAP
* @license		https://xecurify.com/usecases/miniOrange_User_Agreement.pdf
*/
/**
This library is miniOrange Authentication Service. 
Contains Request Calls to Customer service.

**/
class Mo_Ldap_Local_Customer{
	
	public $email;
	public $phone;
	public $customerKey;
	public $transactionId;

	/*
	** Initial values are hardcoded to support the miniOrange framework to generate OTP for email.
	** We need the default value for creating the OTP the first time,
	** As we don't have the Default keys available before registering the user to our server.
	** This default values are only required for sending an One Time Passcode at the user provided email address.
	*/
	private $defaultCustomerKey = "16555";

	private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
	
	function create_customer(){
		if(!Mo_Ldap_Local_Util::is_curl_installed()) {
			return json_encode(array("statusCode"=>'CURL_ERROR','statusMessage'=>'<a target="_blank" href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}
		$url = get_option('mo_ldap_local_host_name') . '/moas/rest/customer/add';
		
		$this->email = esc_attr(get_option('mo_ldap_local_admin_email'));
		$password = esc_attr(get_option('mo_ldap_local_password'));

		$fields = array(
			'areaOfInterest' => 'WP LDAP for Intranet',
			'email' => $this->email,
			'password' => $password
		);
		$field_string = json_encode($fields);

        $headers = array("Content-Type"=>"application/json","charset"=>"UTF - 8","Authorization"=>"Basic");
        $args = array(
            'method' =>'POST',
            'body' => $field_string,
            'timeout' => '10000',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $headers,

        );

        $response = wp_remote_post( $url, $args );
        if ( is_wp_error( $response ) ) {
            return json_encode(array("status"=>'ERROR'));
        }
        return $response['body'];
	}
	
	function get_customer_key() {
		if(!Mo_Ldap_Local_Util::is_curl_installed()) {
			return json_encode(array("apiKey"=>'CURL_ERROR','token'=>'<a target="_blank" href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}
		
		$url = esc_url(get_option('mo_ldap_local_host_name') . "/moas/rest/customer/key");
		$email = esc_attr(get_option('mo_ldap_local_admin_email'));
		$password = esc_attr(get_option('mo_ldap_local_password'));
		
		$fields = array(
			'email' => $email,
			'password' => $password
		);
		$field_string = json_encode($fields);

        $headers = array("Content-Type"=>"application/json","charset"=>"UTF - 8","Authorization"=>"Basic");
        $args = array(
            'method' =>'POST',
            'body' => $field_string,
            'timeout' => '10000',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $headers,

        );

        $response = wp_remote_post( $url, $args );
        if ( is_wp_error( $response ) ) {
            return json_encode(array("status"=>'ERROR'));
        }
        return $response['body'];
	}
	
	function submit_contact_us( $q_email, $q_phone, $query ) {
		if(!Mo_Ldap_Local_Util::is_curl_installed()) {
			return json_encode(array("status"=>'CURL_ERROR','statusMessage'=>'Warning: <a target="_blank" href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled. Please install/enable this extension to contact with miniOrange.<br/>Meanwhile, you can send us email on <a href=mailto:info@xecurify.com><b>info@xecurify.com</b></a>.'));
		}
		
		$url = esc_url(get_option('mo_ldap_local_host_name') . "/moas/rest/customer/contact-us");
		
		$fname = esc_attr(get_option('mo_ldap_local_admin_fname'));
		$lname = esc_attr(get_option('mo_ldap_local_admin_lname'));
		$companyName = esc_attr(get_option('mo_ldap_local_admin_company'));

		$fields = array(
			'firstName'			=> $fname,
			'lastName'	 		=> $lname,
			'company' 			=> $companyName,
			'email' 			=> $q_email,
			'ccEmail'           => 'ldapsupport@xecurify.com',
			'phone'				=> $q_phone,
			'query'				=> $query
		);
		$field_string = json_encode( $fields );

        $headers = array("Content-Type"=>"application/json","charset"=>"UTF - 8","Authorization"=>"Basic");
        $args = array(
            'method' =>'POST',
            'body' => $field_string,
            'timeout' => '10000',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $headers,

        );

        $response = wp_remote_post( $url, $args );

        if ( is_wp_error( $response ) ) {
            return json_encode(array("status"=>'ERROR'));
        }
        return $response['body'];
	}

    function send_email_alert($email,$phone,$message){
        $url = 'https://login.xecurify.com/moas/api/notify/send';
	    if (get_option( 'mo_ldap_local_host_name' )) {
           $url = esc_url(get_option('mo_ldap_local_host_name') . '/moas/api/notify/send');
        }

        $customerKey = $this->defaultCustomerKey;
        $apiKey =  $this->defaultApiKey;


        $currentTimeInMillis = self::get_timestamp();
        $stringToHash 		= $customerKey .  $currentTimeInMillis . $apiKey;
        $hashValue 			= hash("sha512", $stringToHash);
        $fromEmail 			= $email;
        $subject            = "WordPress LDAP/AD Plugin Feedback - ". $email;

        $query        = '[WordPress LDAP/AD Plugin:] '. $message;

        global $user;
        $user         = wp_get_current_user();
        $content='<div >First Name :'.$user->user_firstname.'<br><br>Last  Name :'.$user->user_lastname.'   <br><br>Company :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>Phone Number :'.$phone.'<br><br>Email :<a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br>Query :'.$query.'</div>';



        $fields = array(
            'customerKey'	=> $customerKey,
            'sendEmail' 	=> true,
            'email' 		=> array(
                'customerKey' 	=> $customerKey,
                'fromEmail' 	=> $email,
                'bccEmail' 		=> 'ldapsupport@xecurify.com',                
                'fromName' 		=> 'miniOrange',
                'toEmail' 		=> 'ldapsupport@xecurify.com',
                'toName' 		=> 'ldapsupport@xecurify.com',                
                'subject' 		=> $subject,
                'content' 		=> $content
            ),
        );
        $field_string = json_encode($fields);
        $headers = array("Content-Type"=>"application/json","Customer-Key"=>$customerKey,"Timestamp"=>$currentTimeInMillis,"Authorization"=>$hashValue);
        $args = array(
            'method' =>'POST',
            'body' => $field_string,
            'timeout' => '10000',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $headers,

        );

        $response = wp_remote_post( $url, $args );
        if ( is_wp_error( $response ) ) {
            return json_encode(array("status"=>'ERROR'));
        }
        return $response['body'];
    }

    function get_timestamp() {
	    $url = "https://login.xecurify.com/moas/rest/mobile/get-timestamp";
	    if (get_option( 'mo_ldap_local_host_name' )) {
            $url = esc_url(get_option('mo_ldap_local_host_name') . "/moas/rest/mobile/get-timestamp");
        }

        $response = wp_remote_post( $url);
        if ( is_wp_error( $response ) ) {
            $currentTimeInMillis = round( microtime( true ) * 1000 );
            $currentTimeInMillis = number_format( $currentTimeInMillis, 0, '', '' );
            return $currentTimeInMillis;
        } else {
            return $response['body'];
        }
    }

	function check_customer() {
		if(!Mo_Ldap_Local_Util::is_curl_installed()) {
			return json_encode(array("status"=>'CURL_ERROR','statusMessage'=>'<a target="_blank" href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}
		
		$url 	= esc_url(get_option('mo_ldap_local_host_name') . "/moas/rest/customer/check-if-exists");
		$email 	= get_option("mo_ldap_local_admin_email");

		$fields = array(
			'email' 	=> $email,
		);
		$field_string = json_encode( $fields );
        $headers = array("Content-Type"=>"application/json","charset"=>"UTF - 8","Authorization"=>"Basic");
        $args = array(
            'method' =>'POST',
            'body' => $field_string,
            'timeout' => '10000',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $headers,

        );

        $response = wp_remote_post( $url, $args );
        if ( is_wp_error( $response ) ) {
            return json_encode(array("status"=>'ERROR'));
        }
        return $response['body'];
	}

	function mo_ldap_local_forgot_password($email){
	
		$url = esc_url(get_option('mo_ldap_local_host_name') . '/moas/rest/customer/password-reset');
	
		/* The customer Key provided to you */
		$customerKey = get_option('mo_ldap_local_admin_customer_key');
	
		/* The customer API Key provided to you */
		$apiKey = get_option('mo_ldap_local_admin_api_key');
	
		/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
		$currentTimeInMillis = round(microtime(true) * 1000);
	
		/* Creating the Hash using SHA-512 algorithm */
		$stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
		$hashValue = hash("sha512", $stringToHash);

		//*check for otp over sms/email
		$fields = array(
			'email' => $email
		);
	
		$field_string = json_encode($fields);
        $headers = array("Content-Type"=>"application/json","Customer-Key"=>$customerKey,"Timestamp"=>number_format($currentTimeInMillis, 0, '', ''),"Authorization"=>$hashValue);
        $args = array(
            'method' =>'POST',
            'body' => $field_string,
            'timeout' => '10000',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $headers,

        );

        $response = wp_remote_post( $url, $args );
        if ( is_wp_error( $response ) ) {
            return json_encode(array("status"=>'ERROR'));
        }
        return $response['body'];
	}
}?>