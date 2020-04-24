<?php
/**
 * Plugin Name: MCB Juice WooCommerce Gateway
 * Plugin URI: https://www.gws-technologies.com
 * Description: A Woocoomerce payment gateway plugin that adds instructions for checking out using MCB Juice in Mauritius. More info on MCB Juice at https://www.mcb.mu/en/juice/
 * Version: 1.0
 * Author: Jacques David Commarmond - GWS Technologies LTD
 * Author URI: https://www.gws-technologies.com
 */
defined( 'ABSPATH' ) or exit;

 // Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

/**
 * Add the gateway to WC Available Gateways
 * 
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + WC_MCB_Juice_WooCommerce gateway
 */
function wc_mcb_juice_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_MCB_Juice_WooCommerce';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_mcb_juice_add_to_gateways' );

/**
 * Adds plugin page links
 * 
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function wc_mcb_juice_gateway_plugin_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=mcb_juice_gateway' ) . '">' . __( 'Configure', 'wc-mcb-juice-gateway' ) . '</a>'
	);
	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_mcb_juice_gateway_plugin_links' );

/**
 * MCB JUICE WooCommerce Gateway
 *
 * Provides an MCB JUICE Payment Gateway;
 * We load it later to ensure WC is loaded first since we're extending it.
 *
 * @class       WC_MCB_Juice_WooCommerce
 * @extends     WC_Payment_Gateway
 * @version     1.0.0
 * @package     WooCommerce/Classes/Payment
 * @author      SkyVerge
 */
add_action( 'plugins_loaded', 'wc_mcb_juice_gateway_init', 11 );

function wc_mcb_juice_gateway_init() {

    class WC_MCB_Juice_WooCommerce extends WC_Payment_Gateway {

        /**
		 * Constructor for the gateway.
		 */
		public function __construct() {
	  
			$this->id                 = 'mcb_juice_gateway';
			$this->icon               = apply_filters('mcb_juice_icon', '');
			$this->has_fields         = false;
			$this->method_title       = __( 'JUICE By MCB', 'wc-mcb-juice-gateway' );
			$this->method_description = __( 'Allows JUICE By MCB payments.', 'wc-mcb-juice-gateway' );
		  
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();
		  
			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->instructions = $this->get_option( 'instructions', $this->description );
		  
			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		  
			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}

        /**
         * Initialize Gateway Settings Form Fields
         */
        public function init_form_fields() {
            
            $this->form_fields = apply_filters( 'wc_mcb_juice_form_fields', array(
                
                'enabled' => array(
                    'title'   => __( 'Enable/Disable', 'wc-mcb-juice-gateway' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable JUICE By MCB Payment', 'wc-mcb-juice-gateway' ),
                    'default' => 'no'
                ),

                'title' => array(
                    'title'       => __( 'Title', 'wc-mcb-juice-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wc-mcb-juice-gateway' ),
                    'default'     => __( 'JUICE By MCB', 'wc-mcb-juice-gateway' ),
                    'desc_tip'    => true,
                ),

                'description' => array(
                    'title'       => __( 'Description', 'wc-mcb-juice-gateway' ),
                    'type'        => 'textarea',
                    'description' => __( 'Payment method description that the customer will see on your checkout.', 'wc-mcb-juice-gateway' ),
                    'default'     => __( '1. Open Juice Application'.PHP_EOL.'2. Select Pay &amp; Transfer'.PHP_EOL.'3. Select Pay a Juice Merchant'.PHP_EOL.'4. Search [SET MERCHANT NAME HERE]'.PHP_EOL.'5. Enter the donated amount', 'wc-mcb-juice-gateway' ),
                    'desc_tip'    => true,
                ),

                'instructions' => array(
                    'title'       => __( 'Instructions', 'wc-mcb-juice-gateway' ),
                    'type'        => 'textarea',
                    'description' => __( 'Instructions that will be added to the thank you page and emails.', 'wc-mcb-juice-gateway' ),
                    'default'     => 'Make your payment via JUICE By MCB using the Pay Juice Agent Option of your JUICE mobile Application, Searching for "[SET MERCHANT NAME HERE]" or scanning the QR Code. Please use your Order ID as the payment note. Your donation won\'t be applied until the funds have cleared in our account.',
                    'desc_tip'    => true,
                ),
            ) );
        }

        /**
		 * Output for the order received page.
		 */
		public function thankyou_page() {
			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );
			}
		}
	
	
		/**
		 * Add content to the WC emails.
		 *
		 * @access public
		 * @param WC_Order $order
		 * @param bool $sent_to_admin
		 * @param bool $plain_text
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		
			if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {
				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			}
		}
	
	
		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
	
			$order = wc_get_order( $order_id );
			
			// Mark as on-hold (we're awaiting the payment)
			$order->update_status( 'on-hold', __( 'Awaiting JUICE By MCB payment', 'wc-mcb-juice-gateway' ) );
			
			// Reduce stock levels
			$order->reduce_order_stock();
			
			// Remove cart
			WC()->cart->empty_cart();
			
			// Return thankyou redirect
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order )
			);
		}

    }
}

add_filter( 'woocommerce_gateway_description', 'gateway_mcb_juice_custom_gateway_custom_fields', 20, 2 );
function gateway_mcb_juice_custom_gateway_custom_fields( $description, $payment_id ){
    //
    if( 'mcb_juice_gateway' === $payment_id ){
        ob_start(); // Start buffering

        echo '<div  class="mcb_juice_gateway-fields" style="padding:10px 0;">';

        woocommerce_form_field( 'transaction_id', array(
            'type'          => 'text',
            'label'         => __("6. Enter MCB Juice Confirmation Number", "wc-mcb-juice-gateway"),
            'class'         => array('form-row-wide'),
            'required'      => false,
        ), '');

        echo '</div>';

        $description .= ob_get_clean(); // Append buffered content
    }
    return $description;
}
// Process the field (validation)
add_action('woocommerce_checkout_process', 'transaction_id_field_validation');
function transaction_id_field_validation() {
if ( $_POST['payment_method'] === 'mcb_juice_gateway' && isset($_POST['transaction_id']) && empty($_POST['transaction_id']) )
    wc_add_notice( __( 'Please enter the JUICE Confirmation Number in the previous step.' ), 'error' );
}

// Save "transaction_id" number to the order as custom meta data
add_action('woocommerce_checkout_create_order', 'save_transaction_id_to_order_meta_data', 10, 4 );
function save_transaction_id_to_order_meta_data( $order, $data ) {
    if( $data['payment_method'] === 'mcb_juice_gateway' && isset( $_POST['transaction_id'] ) ) {
		$order->set_transaction_id( sanitize_text_field( $_POST['transaction_id'] ) );
    }
}