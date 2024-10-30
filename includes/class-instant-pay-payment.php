<?php
/**
 * The instant pay payment-specific functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Instant pay
 * @subpackage Instant/payment
 */

/**
 * The Instant pay payment-specific functionality of the plugin.
 *
 * Initialize payemt class for custom payment gateway
 *
 * @package    Instant pay
 * @subpackage Instant pay/admin
 * @author     # <#>
 */
class Instant_Pay_Payment extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {

		$this->id                   = 'instant_pay_gateway_id';
		$this->icon                 = '';
		$this->has_fields           = false;
		$this->method_title         = __( 'Instant Pay Payment', 'instant-pay' );
		$this->method_description   = __( 'Instant Pay Payment Gateway Plug-in for WooCommerce', 'instant-pay' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Turn these settings into variables we can use
		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}

		// Save settings
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Hide instant-pay payment if app key or payment redirect url is missing
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'hide_instant_pay_payment' ) );
	}


	/**
	 * Initialize Gateway Settings Form Fields
	 */
	public function init_form_fields() {

		$this->form_fields = array(

			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'instant-pay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this payment gateway', 'instant-pay' ),
				'default' => 'yes',
			),

			'title' => array(
				'title'       => __( 'Title', 'instant-pay' ),
				'type'        => 'text',
				'description' => __( 'Payment title of checkout process.', 'instant-pay' ),
				'default'     => __( 'Instant Pay Payment', 'instant-pay' ),
				'desc_tip'    => true,
			),

			'description' => array(
				'title'       => __( 'Description', 'instant-pay' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'instant-pay' ),
				'default'     => __( 'Please pay using your details', 'instant-pay' ),
				'desc_tip'    => true,
			),
			'environment' => array(
				'title'    => __( 'Instant Pay Test Mode', 'instant-pay' ),
				'label'    => __( 'Enable Test Mode', 'instant-pay' ),
				'type'    => 'checkbox',
				'description' => __( 'This is the test mode of gateway.', 'instant-pay' ),
				'default'  => 'no',
			),

			'appkey' => array(
				'title'       => __( 'App Key', 'instant-pay' ),
				'type'        => 'text',
				'description' => __( 'This is the APP key provided by instant pay payment when you signed up for an account.', 'instant-pay' ),
				'default'     => '',
				'desc_tip'    => true,
			),

			'test_url' => array(
				'title'    => __( 'Test URL', 'instant-pay' ),
				'type'    => 'url',
				'desc_tip'  => __( 'This is the URL to connect testing environment of instant pay payment.', 'instant-pay' ),
			),

			'live_url' => array(
				'title'    => __( 'Live URL', 'instant-pay' ),
				'type'    => 'url',
				'desc_tip'  => __( 'This is the URL to connect live environment of instant pay payment.', 'instant-pay' ),
			),
		);
	}

	/**
	 * Process the payment and return the result
	 *
	 * @param int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );
		$appkey = $this->appkey;
		$environment = ('yes' == $this->environment ) ? 'TRUE' : 'FALSE';

		if ('' != $this->appkey && ( '' != $this->live_url || '' != $this->test_url  ) ) {
			$environment_url = ( 'FALSE' == $environment ) ? $this->live_url : $this->test_url;

			$thank_you_page = $this->get_return_url( $order ) . '&payment=instant';

			$querystring = 'amt=' . $order->order_total . '&orderId=' . $order_id . '&appKey=' . $appkey . '&redirect_url=' . $thank_you_page;

			// Mark as pending (we're awaiting the payment)
			$order->update_status( 'pending', __( 'Awaiting Instant payment', 'instant-pay' ) );

			// Reduce stock levels
			$order->reduce_order_stock();

			// Remove cart
			WC()->cart->empty_cart();

			// Redirect to instant pay portal
			return array(
				'result'   => 'success',
				'redirect' => $environment_url . '?' . $querystring,
			);
		} else {
			throw new Exception( __( 'There is issue for connecting payment gateway.Please add APP key. Sorry for the inconvenience.', 'instant-pay' ) );
		}
	}

	/**
	 * Hide instant-pay payment if app key or payment redirect url is missing
	 *
	 * @since    1.0.0
	 */
	public function hide_instant_pay_payment( $available_payment_gateways ) {
		if ('' == $this->appkey && ( '' == $this->live_url || '' == $this->test_url ) ) {
			unset( $available_payment_gateways[ $this->id ] );
		}
		return $available_payment_gateways;
	}
}
