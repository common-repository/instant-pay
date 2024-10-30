<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Instant_Pay
 * @subpackage Instant_Pay/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Instant_Pay
 * @subpackage Instant_Pay/admin
 * @author     Vihar <vihar.thakkar@brainvire.com>
 */
class Instant_Pay_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Instant_Pay_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Instant_Pay_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/instant-pay-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Instant_Pay_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Instant_Pay_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/instant-pay-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Display Admin notice if woocommerce not installed.
	 *
	 * @since    1.0.0
	 */
	function instant_pay_wc_missing_notice() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Instant pay requires WooCommerce to be installed and active. You can download %s here.', 'instant-pay' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
		}
	}

	/**
	 * Initialize instant pay gateway class
	 *
	 * @since    1.0.0
	 */
	public function wc_instant_gateway_init() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		require_once WP_PLUGIN_DIR . '/instant-pay/includes/class-instant-pay-payment.php';
	}

	/**
	 * Register Class
	 *
	 * @since    1.0.0
	 */
	function add_instant_pay_gateway_class( $methods ) {
		$methods[] = 'Instant_Pay_Payment';
		return $methods;
	}

	function instant_pay_check_response() {

		global $woocommerce;
		if ( isset( $_REQUEST['TransactionID'] ) && ! empty( $_REQUEST['TransactionID'] ) && isset( $_REQUEST['Payment'] ) && 'InstantPay' == $_REQUEST['Payment'] ) {
			$transaction_id = sanitize_text_field( wp_unslash( $_REQUEST['TransactionID'] ) );
			$payment = sanitize_text_field( wp_unslash( $_REQUEST['Payment'] ) );
			$instant_pay_class = new Instant_Pay_Payment();
			 $environment = ( 'yes' == $instant_pay_class->environment ) ? 'TRUE' : 'FALSE';
			if ( 'TRUE' == $environment  ) {
				$response = wp_remote_post( 'https://instmrcapi.brainvire.net/api/UserTransaction/CustomerTransactionDetails?TransactionID=' . $transaction_id );
			} else {
				$response = wp_remote_post( 'https://www.instant1.co/api/UserTransaction/CustomerTransactionDetails?TransactionID=' . $transaction_id );
			}

			$response = wp_remote_retrieve_body( $response );
			$response_data = json_decode( $response );
			if ( isset( $response_data->responseData->merchantOrderID ) ) {
				$order_id = $response_data->responseData->merchantOrderID;
			} else {
				$order_id = '';
			}
			if ( isset( $order_id ) && ! empty( $order_id ) ) {
				$order = wc_get_order( $order_id );
				$status = $response_data->responseData->paymentStatus;
				if ( isset( $status ) && 'Success' == $status  ) {
					$order->update_status( 'processing' );
					update_post_meta( $order_id, 'instant_order_id', $response_data->responseData->orderId );
					update_post_meta( $order_id, 'transactionID', $response_data->responseData->transactionID );
					update_post_meta( $order_id, 'transactionDate', $response_data->responseData->transactionDate );
					update_post_meta( $order_id, 'transactionAmount', $response_data->responseData->transactionAmount );
					update_post_meta( $order_id, 'paymentStatus', $response_data->responseData->paymentStatus );
					update_post_meta( $order_id, 'merchantOrderID', $response_data->responseData->merchantOrderID );
					update_post_meta( $order_id, 'merchantName', $response_data->responseData->merchantName );
				} else {
					$order->update_status( 'failed' );
				}
			} else if ( isset( $_REQUEST['TransactionID'] ) && empty( $_REQUEST['TransactionID'] ) && isset( $_REQUEST['OrderId'] ) && ! empty( $_REQUEST['OrderId'] ) && 'InstantPay' == $_REQUEST['Payment'] ) {
				$order_id = sanitize_text_field( wp_unslash( $_REQUEST['OrderId'] ) );
				$order = wc_get_order( $order_id );
				$order->update_status( 'failed' );
			}
		}
	}

}

