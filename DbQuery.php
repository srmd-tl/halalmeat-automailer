<?php
require_once( BASE_PATH . 'Helper.php' );

/**
 *
 */
class DbQuery {
	public function findOrCreate(): bool {
		$currentDate=date('Y-m-d');
		$postTitle="sent_soft_order_to_butcher";
		global $wpdb;
		$results = $wpdb->get_results("SELECT ID FROM wp_posts where post_title = '{$postTitle}' and date(post_date) = '{$currentDate}'");
		if($results)
		{
			return false;
		}
		else
		{
			$my_post = array(
				'post_title'    => 'sent_soft_order_to_butcher',
				'post_content'  => 'sent_soft_order_to_butcher',
				'post_author'   => 1,
				'post_type'=>'soft_order'
			);
			wp_insert_post($my_post);
			return true;
		}
	}
	public function findOrCreateOrderPost(): bool {
		$currentDate=date('Y-m-d');
		$postTitle="sent_soft_order_to_butcher";
		global $wpdb;
		$results = $wpdb->get_results("SELECT ID FROM wp_posts where post_title = '{$postTitle}' and date(post_date) = '{$currentDate}'");
		if($results)
		{
			return false;
		}
		else
		{
			$my_post = array(
				'post_title'    => 'sent_order_to_both',
				'post_content'  => 'sent_order_to_both',
				'post_author'   => 1,
				'post_type'=>'both_order'
			);
			wp_insert_post($my_post);
			return true;
		}
	}
	public function markComplete( array $ordersId ) {
		global $wpdb;
		$table_name = $wpdb->prefix.'posts';
		foreach ( $ordersId as $orderId ) {
			$data_update = array('post_status' => 'wc-completed');
			$data_where = array('ID' => $orderId);
			$wpdb->update($table_name , $data_update, $data_where);
		}
	}

	/**
	 * @param array $orders
	 */
	public function sendToLogistics( array $orders, string $orderType ) {
		$settings = $this->getSetting();
		//for logistics
		$fileHeader = array(
			'Bedrijf (lossen)',
			'Email (lossen)',
			'Mobiel (lossen)',
			'Straat (lossen)',
			'Huisnummer (lossen)',
			'Postcode (lossen)',
			'Plaats (lossen)',
			'huisnummer toev.',
			'Datum',
			'Gewenst (lossen)',
			'Gewenst (lossen, tot)',
			'Aantal',
			'Notities (lossen)'
		);
		//Save logistics file in temp path
		$this->array2csv( $orders, 'logistics.csv', $fileHeader );
		//Send attachment
		Helper::sendMail(
			$settings && key_exists( 'logistics_email', $settings ) ? current( $settings['logistics_email'] ) : 'sarmadking@gmail.com',
			'Logistics', 'logistics', $orderType );
	}

	/**
	 * @return array
	 */
	public
	function getSetting(): array {
		$post = get_page_by_title( 'dummy', OBJECT, 'halalmeat_automailer' );
		if ( $post ) {
			$butcherEmail   = get_post_meta( $post->ID, 'butcher_email' );
			$logisticsEmail = get_post_meta( $post->ID, 'logistics_email' );
			$preOrderTime   = get_post_meta( $post->ID, 'preorder_time' );
			$orderTime      = get_post_meta( $post->ID, 'order_time' );
			$senderEmail    = get_post_meta( $post->ID, 'sender_email' );
			$senderName     = get_post_meta( $post->ID, 'sender_name' );

			$smtpUsername = get_post_meta( $post->ID, 'smtp_username' );
			$smtpPassword = get_post_meta( $post->ID, 'smtp_password' );
			$smtpHost     = get_post_meta( $post->ID, 'smtp_host' );
			$smtpPort     = get_post_meta( $post->ID, 'smtp_port' );
			$smtpSecurity = get_post_meta( $post->ID, 'smtp_security' );
			$mode         = get_post_meta( $post->ID, 'test_mode' );

			return [
				'butcher_email'   => $butcherEmail,
				'logistics_email' => $logisticsEmail,
				'preorder_time'   => $preOrderTime,
				'order_time'      => $orderTime,

				'sender_email' => $senderEmail,
				'sender_name'  => $senderName,

				'smtp_username' => $smtpUsername,
				'smtp_password' => $smtpPassword,
				'smtp_host'     => $smtpHost,
				'smtp_port'     => $smtpPort,
				'smtp_security' => $smtpSecurity,
				'test_mode'     => $mode
			];
		}

		return [];

	}

	/**
	 * @param $data
	 * @param $fileName
	 * @param $fileHeader
	 * @param int $type
	 * @param string $delimiter
	 * @param string $enclosure
	 * @param string $escape_char
	 */
	public function array2csv( $data, $fileName, $fileHeader, int $type = 0, string $delimiter = ',', string $enclosure = '"', string $escape_char = "\\" ) {
		$output = fopen( BASE_PATH . $fileName, "w" );
		fputcsv( $output, $fileHeader );
		foreach ( $data as $item ) {
			fputcsv( $output, $item, $delimiter, $enclosure, $escape_char );
		}

//		rewind( $output );
//		return stream_get_contents( $output );
	}

	/**
	 * @param array $orders
	 */
	public function sendToButcher( array $orders, string $orderType ) {
		$settings = $this->getSetting();
		//for butcher
		$fileHeader = array(
			'artikelnummer',
			'aantal',
		);
		//Save logistics file in temp path
		$this->array2csv( $orders, 'order_details.csv', $fileHeader );
		//Send attachment
		Helper::sendMail(
			$settings && key_exists( 'butcher_email', $settings ) ? current( $settings['butcher_email'] ) : 'sarmadking@gmail.com'
			, 'ROCKY', 'butcher', $orderType );
	}

	/**
	 * @param $orders
	 *
	 * @return array
	 */
	public function getOrderProductsForButcher( $orders ): array {
		$data = [];
		foreach ( $orders as $order ) {
			foreach ( $order->get_items() as $item_id => $item ) {

				if ( array_key_exists( $item->get_name(), $data ) ) {
					$data[ $item->get_name() ] = [
						$item->get_name(),
						( (int) $data[ $item->get_name() ][1] ) + $item->get_quantity()
					];
				} else {
					$data[ $item->get_name() ] = [ $item->get_name(), $item->get_quantity() ];
				}
//				$product_id   = $item->get_product_id();
//				$variation_id = $item->get_variation_id();
//				$product      = $item->get_product();
//				$name         = $item->get_name();
//				$quantity     = $item->get_quantity();
//				$subtotal     = $item->get_subtotal();
//				$total        = $item->get_total();
//				$tax          = $item->get_subtotal_tax();
//				$taxclass     = $item->get_tax_class();
//				$taxstat      = $item->get_tax_status();
//				$allmeta      = $item->get_meta_data();
//				$somemeta     = $item->get_meta( '_whatever', true );
//				$type         = $item->get_type();
			}
		}

		return $data;


	}

	/**
	 * @param array $wooOrdersObjArray
	 * @param array $orders
	 * @param array $orderIds
	 * @param string $type
	 */
	public function getAllOrders( array &$wooOrdersObjArray, array &$orders, array &$orderIds, string $type ) {
		$orderPosts = $this->getOrders( $type );
		foreach ( $orderPosts as $orderPost ) {
			$wooOrderObj         = wc_get_order( $orderPost->ID );
			$wooOrdersObjArray[] = $wooOrderObj;
			$orders[]            = $this->getOrderDetail( $wooOrderObj );
			$orderIds[]          = $orderPost->ID;
		}
	}

	/**
	 * @param string $type
	 *
	 * @return int[]|WP_Post[]
	 */
	public function getOrders( string $type ) {
		$settings = $this->getSetting();
		$liveMode = $settings && key_exists( 'test_mode', $settings ) && current( $settings['test_mode'] ) == null;
		$testMode = $settings && key_exists( 'test_mode', $settings ) && current( $settings['test_mode'] ) == 'checked';
		if ( $liveMode && in_array( strtolower( Helper::getCurrentDay() ), [ 'fri', 'tue' ] ) ) {
			$args = array(
				'post_type'      => 'shop_order',
				'posts_per_page' => '10000',
				'post_status'    => 'wc-processing',
				'date_query'     => array(
					'column' => 'post_modified',
					'before' => date( "Y-m-d H:i:s" )
				),
			);
			if ( Helper::getCurrentDay() == 'Tue' ) {
				$args['date_query']['after'] = Helper::afterDate( 4, $type );
			} else if ( Helper::getCurrentDay() == 'Fri' ) {
				$args['date_query']['after'] = Helper::afterDate( 3, $type );
			}

		} else if($testMode){
			echo 'testing things';
			$args = array(
				'post_type'      => 'shop_order',
				'posts_per_page' => '10',
				'post_status'    => 'wc-processing',
			);
		}
		$query = new WP_Query( $args );
		return $query->posts;
	}

	/**
	 * @param $order
	 *
	 * @return array
	 */
	public function getOrderDetail( $order ) {
		return array(
//                    'order_id' => $order->get_id(),
//                    'order_number' => $order->get_order_number(),
//                    'shipping_total' => $order->get_total_shipping(),
//                    'shipping_tax_total' => wc_format_decimal($order->get_shipping_tax(), 2),
//                    'fee_total' =>   wc_format_decimal($order->get_total(), 2),
//                    'fee_tax_total' => wc_format_decimal($order->get_total_tax(), 2),
//                    'tax_total' => wc_format_decimal($order->get_total_tax(), 2),
//                    'cart_discount' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_cart_discount(), 2),
//                    'order_discount' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_order_discount(), 2),
//                    'discount_total' => wc_format_decimal($order->get_total_discount(), 2),
//                    'order_total' => wc_format_decimal($order->get_total(), 2),
//                    'order_currency' => $order->get_currency(),
//                    'payment_method' => $order->get_payment_method(),
//                    'shipping_method' => $order->get_shipping_method(),
//                    'customer_id' => $order->get_user_id(),
//                    'customer_user' => $order->get_user_id(),
//                    'customer_email' => ($a = get_userdata($order->get_user_id() )) ? $a->user_email : '',
//                    'shipping_first_name' => $order->get_shipping_first_name(),
//                    'shipping_last_name' => $order->get_shipping_last_name(),
//                    'shipping_company' => $order->get_shipping_company(),
//                    'shipping_address_1' => $order->get_shipping_address_1(),
//                    'shipping_address_2' => $order->get_shipping_address_2(),
//                    'shipping_postcode' => $order->get_shipping_postcode(),
//                    'shipping_city' => $order->get_shipping_city(),
//                    'shipping_state' => $order->get_shipping_state(),
//                    'shipping_country' => $order->get_shipping_country(),
//                    'customer_note' => $order->get_customer_note(),
//                    'download_permissions' => $order->is_download_permitted() ? $order->is_download_permitted() : 0,
			'billing_fullname'  => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
			'billing_email'     => $order->get_billing_email(),
			'billing_phone'     => $order->get_billing_phone(),
			'billing_address_1' => $order->get_billing_address_1(),
			'billing_address_2' => $order->get_billing_address_2(),
			'billing_postcode'  => $order->get_billing_postcode(),
			'billing_city'      => $order->get_billing_city(),
			'billing_state'     => $order->get_billing_state(),
			'billing_country'   => $order->get_billing_country(),
			'order_date'        => date( 'Y-m-d H:i:s', strtotime( get_post( $order->get_id() )->post_date ) ),
			'status'            => $order->get_status(),
		);
	}

	/**
	 * @param string $butcherEmail
	 * @param string $logisticsEmail
	 */
	public function insertEmail( string $butcherEmail, string $logisticsEmail ) {
		// Gather post data.
		$post      = array(
			'post_title'   => 'dummy',
			'post_content' => 'dummy',
			'post_author'  => 1,
			'post_type'    => 'halalmeat_automailer'
		);
		$fountPost = post_exists( $post['post_title'], '', '', $post['post_type'] );
		// Insert the post into the database.
		$postId = $fountPost ?: wp_insert_post( $post );
		update_post_meta( $postId, 'butcher_email', sanitize_email( $butcherEmail ) );
		update_post_meta( $postId, 'logistics_email', sanitize_email( $logisticsEmail ) );
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @param int $port
	 * @param string $security
	 */
	public function insertSmtpConfigs( string $host, string $username, string $password, int $port, string $security ) {
		// Gather post data.
		$post      = array(
			'post_title'   => 'dummy',
			'post_content' => 'dummy',
			'post_author'  => 1,
			'post_type'    => 'halalmeat_automailer'
		);
		$fountPost = post_exists( $post['post_title'], '', '', $post['post_type'] );
		// Insert the post into the database.
		$postId = $fountPost ?: wp_insert_post( $post );
		update_post_meta( $postId, 'smtp_host', sanitize_text_field( $host ) );
		update_post_meta( $postId, 'smtp_username', sanitize_email( $username ) );
		update_post_meta( $postId, 'smtp_password', $password );
		update_post_meta( $postId, 'smtp_port', $port );
		update_post_meta( $postId, 'smtp_security', sanitize_text_field( $security ) );
	}

	/**
	 * @param string $email
	 * @param string $name
	 */
	public function insertSenderInfo( string $email, string $name ) {
		// Gather post data.
		$post      = array(
			'post_title'   => 'dummy',
			'post_content' => 'dummy',
			'post_author'  => 1,
			'post_type'    => 'halalmeat_automailer'
		);
		$fountPost = post_exists( $post['post_title'], '', '', $post['post_type'] );
		// Insert the post into the database.
		$postId = $fountPost ?: wp_insert_post( $post );
		update_post_meta( $postId, 'sender_email', sanitize_email( $email ) );
		update_post_meta( $postId, 'sender_name', sanitize_text_field( $name ) );
	}

	/**
	 * @param string $preOrderTime
	 * @param string $orderTime
	 */
	public function insertOrderTime( string $preOrderTime, string $orderTime ) {

		// Gather post data.
		$post      = array(
			'post_title'   => 'dummy',
			'post_content' => 'dummy',
			'post_author'  => 1,
			'post_type'    => 'halalmeat_automailer'
		);
		$fountPost = post_exists( $post['post_title'], '', '', $post['post_type'] );
		// Insert the post into the database.
		$postId = $fountPost ?: wp_insert_post( $post );
		update_post_meta( $postId, 'preorder_time', sanitize_text_field( $preOrderTime ) );
		update_post_meta( $postId, 'order_time', sanitize_text_field( $orderTime ) );

	}

	public function insertTestMode( $mode ) {

		// Gather post data.
		$post      = array(
			'post_title'   => 'dummy',
			'post_content' => 'dummy',
			'post_author'  => 1,
			'post_type'    => 'halalmeat_automailer'
		);
		$fountPost = post_exists( $post['post_title'], '', '', $post['post_type'] );
		// Insert the post into the database.
		$postId = $fountPost ?: wp_insert_post( $post );
		update_post_meta( $postId, 'test_mode', $mode == 'not' ? null : sanitize_text_field( $mode ) );
	}

}