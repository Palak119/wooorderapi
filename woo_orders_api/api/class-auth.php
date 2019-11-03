<?php

if ( ! function_exists( 'wp_handle_upload' ) )
	        require_once( ABSPATH . 'wp-admin/includes/file.php' );
	
class Class_auth extends WooOrders_API {
	
	function __construct($plugin_name, $version) {
		parent::__construct($plugin_name, $version);

	}
	
	public function login($request) {
		$secret_key = $this->secretkey;
		$username = $request->get_param('username');
    $password = $request->get_param('password');
    $userName = $request->get_param('username');

    if(!is_email($userName)){
        	
			$get_user_by 	= get_user_by( 'login', $userName );
			$user_id 		= $get_user_by->ID;
			$user_name 		= $get_user_by->display_name;
			$username 		= $get_user_by->user_email;
			$user = wp_authenticate($username, $password);

		}else if(is_email($username)){

			$user = wp_authenticate($username, $password);
		}

    if (is_wp_error($user)) {
      
      $error_code = $user->get_error_code();

			return array(
				'status' 	=> $this->getStatusCode('HTTP_OK'),
				'code' => $error_code,
				'message'  => 'Invalid login details. Please try again',
				'error-message'	=> 'Please try again'
			);

      return new WP_Error(
          '[jwt_auth] '.$error_code,
          $user->get_error_message($error_code),
          array(
              'status' => 403,
          )
      );
    }
		
		$token = $this->generate_token($user->data->ID);

		if($token) {
			$user_pro_pic = get_user_meta($user->data->ID,'user_pro_pic');
			
			if($user_pro_pic[0] == ''){
				$get_avatar = get_avatar( $user->data->ID );
				preg_match("/src='(.*?)'/i", $get_avatar, $matches);
	    		$avatar = $matches[1];
			}else{
				$avatar = get_site_url().'/wp-content/uploads/user/avatar/'.$user_pro_pic[0];
			}

			$data = array(
        'token' 			=> $token,
        'id' 			=> $user->data->ID,
        'email' 			=> $user->data->user_email,
        'name' 			=> $user->data->display_name,
        'avatar'			=> $avatar,
    	);

			$userProfile = getUserProfilemeta($user->data->ID);
			
			return array(
				'status' 	=> $this->getStatusCode('HTTP_OK'),
				'data'		=> $data, 
				'userProfile' => $userProfile,
				'message'	=> 'Login Successful',
				'error-message'	=> ''
			);
		} else {
			return array(
				'status' 	=> $this->getStatusCode('HTTP_OK'),
				'message'	=> 'Login Failed. Please try again.',
				'error-message'	=> 'Please try again'
			);
		}
	}


	public function register($request) {
		
		$user_login		= $request->get_param('email');	
		$user_email		= $request->get_param('email');
		$user_name  	= $request->get_param('name');
		
		$user_pass		= $request->get_param('password');
		$pass_confirm 	= $request->get_param('password_confirm');

 		$flag = true;
		
		if(username_exists($user_login)) {
			$flag = FALSE;
			$msg = 'Username already registered.';
		}
		if($user_name == '' || $user_name == NULL){
			$flag = FALSE;
			$msg = 'Please enter your name.';
		}
		if(!validate_username($user_login)) {
			$flag = FALSE;
			$msg = 'Invalid Username';
		}
		if($user_login == '') {
			$flag = FALSE;
			$msg = 'Empty Username';
		}
		if(!is_email($user_email)) {
			$flag = FALSE;
			$msg = 'Invalid Email Address';
		}
		if(email_exists($user_email)) {
			$flag = FALSE;
			$msg = 'Email Address Already Registered';
		}
		if($user_pass == '') {
			$flag = FALSE;
			$msg = 'Please enter password';
		}
		if($user_pass != $pass_confirm) {
			$flag = FALSE;
			$msg = 'Passwords do not match';
		}
 		
		if($flag && $flag == TRUE) {
			$new_user_id = wp_insert_user(array(
					'user_login'		=> $user_login,
					'user_pass'	 		=> $user_pass,
					'user_email'		=> $user_email,
					'first_name'		=> $user_name,
					'user_registered'	=> date('Y-m-d H:i:s'),
					'role'				=> 'API_user'
				)
			);
		
			//$res = $this->save_user_device_info($new_user_id);

			if($new_user_id) {
				
				wp_new_user_notification($new_user_id);
				wp_set_current_user($new_user_id, $user_login);	
				$token = $this->generate_token($new_user_id);
				
				$data = array(
          'token' => $token,
          'id' 	=> (string)$new_user_id,
          'email' => $user_email,
          'name' 	=> $user_name,
        );
				
				$userProfile = getUserProfilemeta($new_user_id);
				
				return array(
					'status' 		=> $this->getStatusCode('HTTP_OK'),
					'data'			=> $data,
					'userProfile' 	=> $userProfile,
					'message'   	=> 'Register successfully'
				);
			} else {
				return array(
					'status' 	=> $this->getStatusCode('HTTP_BAD_REQUEST'),
					'message' 	=> 'Error occured. Please try again'
				);
			}
		} else {
			return array(
				'status' 	=> $this->getStatusCode('HTTP_NOT_ACCEPTABLE'),
				'message' 	=> $msg
			);
		}
	}
}