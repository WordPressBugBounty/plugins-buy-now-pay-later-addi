<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Addi_Gateway_Blocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'addi';

    public function initialize() {
        $this->settings = get_option( "woocommerce_{$this->name}_settings", array() );
        $this->gateway = new WC_Addi_Gateway();
    }

    public function is_active() {
        return $this->gateway->is_available();
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
        $payment_method_data = array(
            'title'       => $this->gateway->title, 
            'description' => $this->gateway->payment_fields(true), 
            'icon'         => plugin_dir_url(__FILE__) . '../assets/ADDI_logo.png'
        );
        return $payment_method_data;	
    }
}
?>