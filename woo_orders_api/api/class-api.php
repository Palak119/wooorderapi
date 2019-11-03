<?php

class Class_api extends WooOrders_API {
	
	function __construct($plugin_name, $version) {
		parent::__construct($plugin_name, $version);
	}

	public function createOrder($request) {
		global $wpdb, $woocommerce;
		$user_id = $this->user_id;
		wc()->frontend_includes();
		WC()->session = new WC_Session_Handler();
		WC()->session->init();
		WC()->customer = new WC_Customer( $user_id, true );
		WC()->cart = new WC_Cart();

		if($user_id > 0){

			$active_methods   = array();
			$products    = $request->get_param('pid');
			$cust_address     = $request->get_param('address');
			$cust_cca2        = $request->get_param('cca2');
			$cust_city        = $request->get_param('city');
			$cust_countryName = $request->get_param('countryName');
			$cust_fname       = $request->get_param('fname');
			$cust_lname       = $request->get_param('lname');
			$cust_phone       = $request->get_param('phone');
			$cust_state       = $request->get_param('state');
			$cust_zipcode     = $request->get_param('zipcode');
			$payment_method     = $request->get_param('payment_method');
			$selectedShippingMethod = $request->get_param('selectedShippingMethod');
			
			$product_array = json_decode($products, true);
			$product_array = array_filter($product_array, 'strlen');

			$address = $request->get_param('address');

			if(isset($product_array)){
				$user = get_user_by('id', $user_id);
				$user = $user->data;
		    $address = array(
		        'first_name' => $cust_fname,
		        'last_name'  => $cust_lname,
		        'company'    => '',
		        'email'      => $user->user_email,
		        'phone'      => $cust_phone,
		        'address_1'  => $cust_address,
		        'address_2'  => $cust_cca2, 
		        'city'       => $cust_city,
		        'state'      => $cust_state,
		        'postcode'   => $cust_zipcode,
		        'country'    => $cust_countryName
		    );

				foreach ($product_array as $productID => $productQunty) {
					if($productID && $productQunty){
						$woocommerce->cart->add_to_cart($productID, $productQunty);
					}
				}

				$valu_shipp = array(
					'countries'    	=> $cust_countryName,
					'amount'		=> number_format((float)$woocommerce->cart->cart_contents_total+$woocommerce->cart->tax_total, 2, '.', '')
				);
				
				$woocommerce->cart->empty_cart();
				WC()->shipping->calculate_shipping(get_shipping_packages($valu_shipp));
				$shipping_methods = WC()->shipping->packages;

				if($selectedShippingMethod && $selectedShippingMethod != ''){
					foreach ($shipping_methods[0]['rates'] as $id => $shipping_method) {
					if($shipping_method->method_id === $selectedShippingMethod){
							$active_methods = $shipping_method;
						}
				  }
				}

				$order = wc_create_order(array('customer_id' => $user->ID));
				$order_id = $order->id;
			    foreach ($product_array as $productID => $productQunty) {
					$order->add_product(wc_get_product($productID), $productQunty);
				}
				$order->set_address( $address, 'billing' );
			    $order->set_address( $address, 'shipping' );
				$payment_gateways = WC()->payment_gateways->payment_gateways();

				if($payment_method != '' && $payment_gateways[$payment_method]){
					$order->set_payment_method($payment_gateways[$payment_method]);
				}else{
					$order->set_payment_method($payment_gateways['cod']);
				}
				
				if($selectedPaymentMethod == "cod"){
					$order->update_status('processing');
				}else{
					$order->update_status('pending');
				}
				
				$order->calculate_totals();
				$order->save();
				
				$orderDetails = array(
					'id' => $order->id,
					'shipping_total' => $order->data->shipping_total,
					'subtotal' => number_format((float)$order->get_subtotal(), 2, '.', ''),
					'total' => number_format((float)$order->get_total(), 2, '.', ''),
				);

				$return = array(
					'status'	=> $this->getStatusCode('HTTP_OK'),
					'orderDetails' => $orderDetails
				);
				
			} else {
				$return = array(
					'status' 		=> $this->getStatusCode('HTTP_NOT_FOUND'),
					'messasge' 		=> 'No Valid Request Found',
				);
			}
		} else {
			$return = array(
				'status' 	=> $this->getStatusCode('HTTP_NOT_FOUND'),
			);
		}
		return $return;
	}

	public function getOrderList($request) {

		global $wpdb, $woocommerce;
		$user_id = $this->user_id;
		$uploadDir = wp_upload_dir();
		$uploadDir = $uploadDir['baseurl'];
		if($user_id > 0){
			
			$sql = "
			SELECT 
				p.ID, p.post_date, p.post_modified,
				MAX(CASE WHEN pm.meta_key = '_customer_user' then pm.meta_value ELSE '' END) as customer_user
			FROM ".$wpdb->prefix."posts as p 
			LEFT JOIN ".$wpdb->prefix."postmeta as pm ON ( pm.post_id = p.ID)
			WHERE ( p.post_type = 'shop_order' ) AND p.post_status != 'trash' AND (pm.meta_key = '_customer_user' AND pm.meta_value = '$user_id') 
			GROUP BY p.ID 
			ORDER BY p.post_date DESC";
			$customer_orders = $wpdb->get_results($sql, ARRAY_A);

			if(count($customer_orders) > 0){
				foreach ($customer_orders as $key => $orderID) {
					$order = wc_get_order( $orderID['ID'] );
					$total 			= $order->get_total();
					$subtotal 		= $order->get_subtotal();
					$shipping_total = $order->get_shipping_total();
					$cart_tax 		= $order->get_cart_tax();
					$status 		= $order->get_status();

					$address = $order->get_address()['address_1'].', ';
					$address .= $order->get_address()['address_2'].', ';
					$address .= $order->get_address()['city'].', ';
					$address .= $order->get_address()['state'];
					
					$customer_orders[$key]['subtotal'] 		= $subtotal;
					$customer_orders[$key]['total'] 		 	= $total;
					$customer_orders[$key]['shipping_total'] = $shipping_total;
					$customer_orders[$key]['tax'] 		 	= $cart_tax;
					$customer_orders[$key]['address'] 		= $address;
					$customer_orders[$key]['post_date'] 		= date('D, d M Y - H:i', strtotime($orderID['post_date']));
					$customer_orders[$key]['status'] 		= $status;

					$sql = "
						SELECT  
							p.*,
							post.post_title,
							MAX(CASE WHEN pm.meta_key = 'alt_title' then pm.meta_value ELSE '' END) as alt_title,
							MAX(CASE WHEN pm.meta_key = '_regular_price' then pm.meta_value ELSE '' END) as regular_price,
							MAX(CASE WHEN pm.meta_key = '_sale_price' then pm.meta_value ELSE '' END) as sale_price,
							MAX(CASE WHEN pm.meta_key = '_price' then pm.meta_value ELSE '' END) as price,
							term.name as category_name,
							term.slug as category_slug,
							term.term_id as category_id
						FROM (
							select
								MAX(CASE WHEN t2.meta_key = '_product_id' then t2.meta_value ELSE NULL END) as ID,
								MAX(CASE WHEN t2.meta_key = '_qty' then t2.meta_value ELSE '' END) as qty,
								MAX(CASE WHEN t2.meta_key = '_line_subtotal' then t2.meta_value ELSE '' END) as subtotal,
								MAX(CASE WHEN t2.meta_key = '_line_tax' then t2.meta_value ELSE '' END) as tax,
								MAX(CASE WHEN t2.meta_key = '_line_total' then t2.meta_value ELSE '' END) as total
							FROM ".$wpdb->prefix."woocommerce_order_items as t1 
							JOIN ".$wpdb->prefix."woocommerce_order_itemmeta as t2 ON t1.order_item_id = t2.order_item_id
							WHERE t1.order_id=".$orderID['ID']."
							GROUP BY t1.order_item_id
						) as p
						LEFT JOIN ".$wpdb->prefix."posts as post ON ( post.ID = p.ID)
						LEFT JOIN ".$wpdb->prefix."postmeta as pm ON ( pm.post_id = p.ID)
						LEFT JOIN ".$wpdb->prefix."term_relationships as tr ON tr.object_id = p.ID
						LEFT JOIN ".$wpdb->prefix."terms as term ON tr.term_taxonomy_id = term.term_id
						WHERE p.ID IS NOT NULL
						GROUP BY p.ID  
						";
										
					$res = $wpdb->get_results($sql, ARRAY_A);
					$customer_orders[$key]['items'] = $res;					
					
				}
			}
			$return = array(	
				'status'			=> $this->getStatusCode('HTTP_OK'),
				'orderList' 		=> $customer_orders,
				'error-message'	=> ''
			);
		}else{
			$return = array(
				'status' 	=> $this->getStatusCode('HTTP_NOT_FOUND'),
				'error-message'	=> 'Please try again'
			);
		}
		return $return;
	}

}