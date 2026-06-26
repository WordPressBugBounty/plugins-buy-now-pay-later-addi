<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Addi_Gateway_Blocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'addi';

    public function initialize() {
        $this->settings = get_option( "woocommerce_{$this->name}_settings", array() );

        // Prefer the already-registered gateway instance from WC to avoid double-instantiation.
        // Guard defensively: WC()->payment_gateways may not be ready in all loading contexts
        // (e.g. WordPress.com initial-check sandbox or race with plugins_loaded priority 10).
        try {
            if ( function_exists( 'WC' ) && WC() && WC()->payment_gateways ) {
                $gateways      = WC()->payment_gateways->payment_gateways();
                $this->gateway = isset( $gateways[ $this->name ] ) ? $gateways[ $this->name ] : null;
            }
        } catch ( Exception $e ) {
            $this->gateway = null;
        }

        // Fallback: instantiate directly if the class is already loaded.
        if ( $this->gateway === null && class_exists( 'WC_Addi_Gateway' ) ) {
            $this->gateway = new WC_Addi_Gateway();
        }
        // If neither path succeeds, $this->gateway stays null and is_active() returns false,
        // preventing any further fatal error.
    }

    public function is_active() {
        return $this->gateway ? $this->gateway->is_available() : false;
    }

    public function get_payment_method_script_handles() {
        wp_register_script(
            'wc-addi-blocks-integration',
            plugin_dir_url(__FILE__) . '../js/checkout-block.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            null,
            true
        );
        if( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations( 'wc-addi-blocks-integration');

        }
        return [ 'wc-addi-blocks-integration' ];
    }

    public function get_payment_method_data() {
        if ( ! $this->gateway ) {
            return array( 'title' => 'Addi', 'description' => '', 'icon' => plugin_dir_url(__FILE__) . '../assets/ADDI_logo.png' );
        }
        $payment_method_data = array(
            'title'       => $this->gateway->title,
            'description' => $this->gateway->payment_fields(true),
            'icon'        => plugin_dir_url(__FILE__) . '../assets/ADDI_logo.png'
        );
        return $payment_method_data;
    }
}
?>