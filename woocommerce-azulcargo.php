<?php
/*
Plugin Name: WooCommerce AzulCargo
Plugin URI: https://github.com/guilherme00pereira/azulcargo-shipping-method
Description: Add AzulCargo shipping method to WooCommerce 
Version: 0.2.0
Author: Guilherme Pereira de Souza e Alves
Author URI: http://guilherme-pereira.herokuapp.com
Text Domain: azulcargo-shipping-method
Domain Path: /langs
WC tested up to: 3.4
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//Check if WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {


	function woocommerce_azulcargo_init(){
		if ( ! class_exists( 'WC_AzulCargo' ) ) {
			include_once dirname( __FILE__ ) . '/includes/class-wc-azulcargo.php';
		}
		if ( ! class_exists( 'WC_Shipping_Calculator' ) ) {
			include_once dirname( __FILE__ ) . '/includes/class-wc-shipping-calculator.php';
		}

	}
	add_action( 'woocommerce_shipping_init', 'woocommerce_azulcargo_init' );

	function add_azulcargo_method($methods){
		$methods['woocommerce-azulcargo-shipping-method'] = "WC_AzulCargo";
		return $methods;
	}
	add_filter("woocommerce_shipping_methods", "add_azulcargo_method");

}

?>