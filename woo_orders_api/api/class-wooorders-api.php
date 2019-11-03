<?php
use \Firebase\JWT\JWT;

class WooOrders_API {
	
	const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;            // RFC2518

    const HTTP_OK = 200;

    // The server successfully created a new resource
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;

    // The server successfully processed the request, though no content is returned
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;          // RFC4918
    const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    const HTTP_IM_USED = 226;               // RFC3229

    // Redirection
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;

    // The resource has not been modified since the last request
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238

    // The request cannot be fulfilled due to multiple errors
    const HTTP_BAD_REQUEST = 400;

    // The user is unauthorized to access the requested resource
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;

    // The requested resource is unavailable at this present time
    const HTTP_FORBIDDEN = 403;

    // The requested resource could not be found
    const HTTP_NOT_FOUND = 404;

    // The request method is not supported by the following resource
    const HTTP_METHOD_NOT_ALLOWED = 405;

    // The request was not acceptable
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;

    // The request could not be completed due to a conflict with the current state
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const HTTP_LOCKED = 423;                                                      // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585

    // The server encountered an unexpected error
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    // The server does not recognise the request method
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;
	
    public $plugin_name;
    public $version;
    public $jwt_error = null;
	public $routes = array();
	public $secretkey = '';
	public $header = array();
	public $is_api_call = FALSE;
	public $user_id = 0;
	public $is_auth = TRUE;
	
    public function __construct($plugin_name, $version) {
        $routes = array();
        require_once 'routes.php';
        require_once 'functions.php';
    	
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    	$this->routes = $routes;
    	
    	$is_rest_call = defined('REST_REQUEST') ? JWT_AUTH_SECRET_KEY : false;
    	if(preg_match('/wp-json/',$_SERVER['REQUEST_URI'])){
    		$this->is_api_call = TRUE;
    	}
    	
    	if($this->is_api_call){
    		$this->get_headers();
    		$secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
    		/** First thing, check the secret key if not exist return a error*/
            
            if (!$secret_key) {
                return new WP_Error(
                    'jwt_auth_bad_config',
                    __('APP not configurated properly.', 'wp-api-jwt-auth'),
                    array(
                        'status' => 403,
                    )
                );
            }
    		$this->secretkey = $secret_key;
    		$this->check_token(false);
            // $check_token = $this->check_token(false);
    	}
    }
	
	public function get_headers($value='') {
		if(isset($_SERVER['HTTP_OS']) && $_SERVER['HTTP_OS'] != ''){
			$header = array(
				'OS' 			=> $_SERVER['HTTP_OS'],
				'LANG' 			=> $_SERVER['HTTP_LANG'],
				'DEVICE_NAME' 	=> $_SERVER['HTTP_DEVICENAME'],
				'OS_VERSION' 	=> $_SERVER['HTTP_OSV'],
				'APP_VERSION' 	=> $_SERVER['HTTP_APPV'],
			);
			if(isset($_SERVER['HTTP_DEVICETOKEN']) && $_SERVER['HTTP_DEVICETOKEN'] != ''){
				$header['DEVICE_TOKEN'] = $_SERVER['HTTP_DEVICETOKEN'];
			}
			if(isset($_SERVER['HTTP_ACCESSTOKEN']) && $_SERVER['HTTP_ACCESSTOKEN'] != ''){
				$header['ACCESS_TOKEN'] = $_SERVER['HTTP_ACCESSTOKEN'];
			}
			$this->header = $header;	
		}
	}
	
    public function add_api_routes() {
		$routes = $this->routes;
		
		foreach ($routes as $namespace => $route) {
            
			require_once 'class-'.$namespace.'.php';
			$classname = 'Class_'.$namespace;
			$obj = new $classname($this->plugin_name,$this->version);
			foreach ($route as $endpoint) {

                if($endpoint == 'paystack_response'){
                    register_rest_route($namespace, $endpoint, array(
                        'methods' => 'GET',
                        'callback' => array($obj, $endpoint),
                    ));
                }else{
                    register_rest_route($namespace, $endpoint, array(
                        'methods' => 'POST',
                        'callback' => array($obj, $endpoint),
                    ));
                }
				
			}
		}
       
    }
	
    public function add_cors_support() {
        $enable_cors = defined('JWT_AUTH_CORS_ENABLE') ? JWT_AUTH_CORS_ENABLE : false;
        if ($enable_cors) {
            $headers = apply_filters('jwt_auth_cors_allow_headers', 'Access-Control-Allow-Headers, Content-Type, Authorization');
            header(sprintf('Access-Control-Allow-Headers: %s', $headers));
        }
    }

    public function generate_token($user_id) {
    		$secret_key = $this->secretkey;
        $issuedAt = time();
        $notBefore = apply_filters('jwt_auth_not_before', $issuedAt, $issuedAt);
        $expire = apply_filters('jwt_auth_expire', $issuedAt + (DAY_IN_SECONDS * 7), $issuedAt);
        $token = array(
            'iss' => get_bloginfo('url'),
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expire,
            'data' => array(
                'user' => array(
                    'id' => $user_id,
                ),
            ),
        );
        $token = JWT::encode($token, $secret_key);
		return $token;
    }

    public function determine_current_user($user) {
    	
        $rest_api_slug = rest_get_url_prefix();
        $valid_api_uri = strpos($_SERVER['REQUEST_URI'], $rest_api_slug);
        if(!$valid_api_uri){
            return $user;
        }

        $validate_uri = strpos($_SERVER['REQUEST_URI'], 'token/validate');
        if ($validate_uri > 0) {
            return $user;
        }
		
		if($this->is_auth && $this->is_auth == TRUE){
			
			$token = $this->validate_token(false);
			
	        if (is_wp_error($token)) {
	            if ($token->get_error_code() != 'jwt_auth_no_auth_header') {
	                $this->jwt_error = $token;
	                return $user;
	            } else {
	                return $user;
	            }
	        }
	        return $token->data->user->id;
		}
        return FALSE;
    }


	public function check_token() {

		$auth = isset($_SERVER['HTTP_AUTHORIZATION']) ?  $_SERVER['HTTP_AUTHORIZATION'] : false;

        if (!$auth) {
            $auth = isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ?  $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
        }
		$error = array();
        if (!$auth) {
			$error = array(
				'status' 	=> $this->getStatusCode('HTTP_UNAUTHORIZED'),
				'message' 	=> 'Authorization header not found.'
			);
        }

		$token = $auth;

        list($token) = sscanf($auth, 'Bearer %s');
		
        if (!$token) {
			$error = array(
				'status' 	=> $this->getStatusCode('HTTP_UNAUTHORIZED'),
				'message' 	=> 'Authorization header malformed.'
			);
        }
		
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;

        if (!$secret_key) {
			$error = array(
				'status' 	=> $this->getStatusCode('HTTP_UNAUTHORIZED'),
				'message' 	=> 'Firebase JWT not configurated properly, please contact the admin'
			);
        }

        try {
            $token = JWT::decode($token, $secret_key, array('HS256'));
            if ($token->iss != get_bloginfo('url')) {
				$error = array(
					'status' 	=> $this->getStatusCode('HTTP_UNAUTHORIZED'),
					'message' 	=> 'The token does not match with Server.'
				);
            }
            if (!isset($token->data->user->id)) {
				$error = array(
					'status' 	=> $this->getStatusCode('HTTP_UNAUTHORIZED'),
					'message' 	=> 'User Details not found in token.'
				);
            }
			$this->user_id = $token->data->user->id;

         } catch (Exception $e) {
			$error = array(
				'status' 	=> $this->getStatusCode('HTTP_FORBIDDEN'),
				'message' 	=> $e->getMessage()
			);
         }
		
	}
	
    public function validate_token($output = true) {
        $auth = isset($_SERVER['HTTP_AUTHORIZATION']) ?  $_SERVER['HTTP_AUTHORIZATION'] : false;
        if (!$auth) {
            $auth = isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ?  $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
        }
		$error = array();
        if (!$auth) {
			$error = array(
				'status' 	=> $this->getStatusCode('HTTP_UNAUTHORIZED'),
				'message' 	=> 'Authorization header not found.'
			);
        }
		$token= $auth;
        list($token) = sscanf($auth, 'Bearer %s');
		
        if (!$token) {
			$error = array(
				'status' 	=> $this->getStatusCode('HTTP_UNAUTHORIZED'),
				'message' 	=> 'Authorization header malformed.'
			);
        }
		
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        if (!$secret_key) {
			$error = array(
				'status' 	=> $this->getStatusCode('HTTP_UNAUTHORIZED'),
				'message' 	=> 'Firebase JWT not configurated properly, please contact the admin'
			);
        }
		
        try {
            $token = JWT::decode($token, $secret_key, array('HS256'));
            if ($token->iss != get_bloginfo('url')) {
				$error = array(
					'status' 	=> $this->getStatusCode('HTTP_UNAUTHORIZED'),
					'message' 	=> 'The token does not match with Server.'
				);
            }
            if (!isset($token->data->user->id)) {
				$error = array(
					'status' 	=> $this->getStatusCode('HTTP_UNAUTHORIZED'),
					'message' 	=> 'User Details not found in token.'
				);
            }
			
			$this->user_id = $token->data->user->id;
         } catch (Exception $e) {
			$error = array(
				'status' 	=> $this->getStatusCode('HTTP_FORBIDDEN'),
				'message' 	=> $e->getMessage()
			);
         }
    }

    /**
     * Filter to hook the rest_pre_dispatch, if the is an error in the request
     * send it, if there is no error just continue with the current request.
     *
     * @param $request
     */
    public function rest_pre_dispatch($request)
    {
        if (is_wp_error($this->jwt_error)) {
            return $this->jwt_error;
        }
        return $request;
    }
	
	public function getStatusCode($constant) {
		$reflector = new ReflectionClass(get_class($this));
	    $constants = $reflector->getConstants();
		
		if(isset($constants[$constant])){
			return $constants[$constant];
		}
		return FALSE;
	}
	
}
