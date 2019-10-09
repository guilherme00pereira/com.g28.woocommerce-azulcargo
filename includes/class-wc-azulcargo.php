<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_AzulCargo Extends WC_Shipping_Method {


    /**
     * Constructor for your shipping class
    *
    * @access public
    * @return void
    */
    public function __construct( $instance_id = 0 ){
        $this->instance_id = absint( $instance_id );
        $this->id = "woocommerce-azulcargo-shipping-method";
        $this->method_title = __("Aéreo (Azul Cargo)");
        $this->method_description = __("Calculate shipping fees from Azul Cargo");
        $this->enabled = "yes";
        $this->title = __("Aéreo (Azul Cargo)");
        $this->supports = array(
            'shipping-zones',
            'instance-settings',
        );

        // Load the form fields.
        $this->init_form_fields();

        // Define user set variables.
        $this->collection_rate    = $this->get_option('collection_rate');
        $this->ad_valorem_capital    = $this->get_option('ad_valorem_capital');
        $this->ad_valorem_country    = $this->get_option('ad_valorem_country');
        $this->capatazia     = $this->get_option( 'capatazia' );
        $this->emission_tax     = $this->get_option( 'emission_tax' );
        $this->package_cost = $this->get_option( 'package_cost' );
        $this->discount     = $this->get_option( 'discount' );
        $this->minimum_height     = $this->get_option( 'minimum_height' );
		$this->minimum_width      = $this->get_option( 'minimum_width' );
		$this->minimum_length     = $this->get_option( 'minimum_length' );

        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }


    function init_form_fields(){
        $this->instance_form_fields = array(
                'enabled'            => array(
                    'title'   => __( 'Enable/Disable', 'woocommerce-azulcargo-shipping-method' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable this shipping method', 'woocommerce-azulcargo-shipping-method' ),
                    'default' => 'yes',
                ),
                'title'              => array(
                    'title'       => __( 'Title', 'woocommerce-azulcargo-shipping-method' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-azulcargo-shipping-method' ),
                    'desc_tip'    => true,
                    'default'     => $this->method_title,
                ),
                'extra_taxes'   => array(
                    'title'       => __( 'Extra Taxes', 'woocommerce-correios' ),
                    'type'        => 'title',
                    'description' => __( 'Minimum measure for your shipping packages.', 'woocommerce-correios' ),
                    'default'     => '',
                ),
                'collection_rate' => array(
                    'title'       => __( 'Colecction Rate', 'woocommerce-azulcargo-shipping-method' ),
                    'type'        => 'price',
                    'default'     => '0',
                ),
                'ad_valorem_capital' => array(
                    'title'       => __( 'Ad Valorem Capital', 'woocommerce-azulcargo-shipping-method' ),
                    'type'        => 'price',
                    'default'     => '0.66',
                ),
                'ad_valorem_country' => array(
                    'title'       => __( 'Ad Valorem Country', 'woocommerce-azulcargo-shipping-method' ),
                    'type'        => 'price',
                    'default'     => '0.85',
                ),
                'capatazia' => array(
                    'title'       => __( 'Capatazia', 'woocommerce-azulcargo-shipping-method' ),
                    'type'        => 'price',
                    'default'     => '0.05',
                ),
                'emission_tax' => array(
                    'title'       => __( 'Emission Tax', 'woocommerce-azulcargo-shipping-method' ),
                    'type'        => 'price',
                    'default'     => '1',
                ),
                'discount' => array(
                    'title'       => __( 'Discount', 'woocommerce-azulcargo-shipping-method' ),
                    'type'        => 'price',
                    'default'     => '0',
                ),
                'package_cost' => array(
                    'title'       => __( 'Package Cost', 'woocommerce-azulcargo-shipping-method' ),
                    'type'        => 'price',
                    'default'     => '0',
                ),
                'package_standard'   => array(
                    'title'       => __( 'Package Standard', 'woocommerce-correios' ),
                    'type'        => 'title',
                    'description' => __( 'Minimum measure for your shipping packages.', 'woocommerce-correios' ),
                    'default'     => '',
                ),
                'minimum_height'     => array(
                    'title'       => __( 'Minimum Height (cm)', 'woocommerce-correios' ),
                    'type'        => 'text',
                    'description' => __( 'Minimum height of your shipping packages. Correios needs at least 2cm.', 'woocommerce-correios' ),
                    'desc_tip'    => true,
                    'default'     => '2',
                ),
                'minimum_width'      => array(
                    'title'       => __( 'Minimum Width (cm)', 'woocommerce-correios' ),
                    'type'        => 'text',
                    'description' => __( 'Minimum width of your shipping packages. Correios needs at least 11cm.', 'woocommerce-correios' ),
                    'desc_tip'    => true,
                    'default'     => '11',
                ),
                'minimum_length'     => array(
                    'title'       => __( 'Minimum Length (cm)', 'woocommerce-correios' ),
                    'type'        => 'text',
                    'description' => __( 'Minimum length of your shipping packages. Correios needs at least 16cm.', 'woocommerce-correios' ),
                    'desc_tip'    => true,
                    'default'     => '16',
                ),
            );
    }

    public function calculate_shipping( $package = array() ) {
        // Check if have postcode and region is valid
		if ( '' === $package['destination']['postcode'] || 'BR' !== $package['destination']['country'] ) {
			return;
        }
        // instantiate class to calculate shipping
        $shipping_calculator = new WC_Shipping_Calculator($package, $this);
        $cost = $shipping_calculator->get_final_shipping_cost();
        if ($cost === 0){
            return;
        }
        $rate = array(
            'id' => $this->id,
            'label' => $this->title,
            'cost' => $cost,
        );
        // Register the rate
        $this->add_rate( $rate );
    }

}
