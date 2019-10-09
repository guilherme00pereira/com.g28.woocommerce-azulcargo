<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Shipping_Calculator {

    public function __construct( $package = array(), WC_AzulCargo $azulcargo ){
        $this->pkg = $package;
        $this->postcode = $this->normalize_postcode($package['destination']['postcode']);
        $this->azul = $azulcargo;
        $this->weight = 0;

    }

    private function calculate_cubage(){
        $width =(int) $this->azul->minimum_width;
        $height =(int) $this->azul->minimum_height;
        $length =(int) $this->azul->minimum_length;
        return ceil(($height * $width * $length) / 6000);
    }

    private function calculate_weight(){
        $total_weight = 0;
        $cart_itens = $this->pkg['contents'];
        foreach($cart_itens as $cart_item){
            // TODO: alterar peso fixo por peso do item ou por parametro de configuração
            $total_weight += $cart_item['quantity'] * 0.5;
        }
        return ceil($total_weight);
    }

    private function compare_weight_versus_cubage(){
        $cubage = $this->calculate_cubage();
        $weight = $this->calculate_weight();
        return ($cubage > $weight ? $cubage : $weight);
    }

    private function normalize_postcode($code){
        // format postcode to remove not numbers characters
        return preg_replace('/[^0-9]/', '', $code);
    }

    private function add_additional_fees($value){
        //add collection rate
        $value += $this->azul->collection_rate;
        
        // add ad-valorem
        if ( 'C' === $db_values->type){
            $value += floatval($this->pkg['cart_subtotal']) * ($this->azul->ad_valorem_capital/100);
        } else {
            $value += floatval($this->pkg['cart_subtotal']) * ($this->azul->ad_valorem_country/100);
        }

        // add capatazia
        $value += $this->weight * $this->azul->capatazia;
        
        // add emission tax
        $value += 1;

        // add package box cost
        $value += $this->azul->package_cost;

        //TODO: aplicar desconto sobre o valor final (ver na azul contrato)

        return $value;
    }

    private function get_airpot_code_and_type_from_database(){
        global $wpdb;
        $sql_cep_range = $wpdb->prepare(
            "SELECT c.type, c.airport_code from wpsu_azul_cep_ranges c
            where '%s' BETWEEN c.initial_cep and c.final_cep",
            $this->postcode
        );
        $result = $wpdb->get_results($sql_cep_range);
        return $result;
    }

    private function get_tax_value_from_database($airport_code, $type){
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT r.value, r.type from wpsu_azul_rates r
            where r.weight = '%s' and r.airport_iata in (%s, 'RED') ",
            $this->weight, $airport_code
        );
        $result = $wpdb->get_results($sql);

        // TODO: refatorar e extrair para um metodo o if-else abaixo
        if ($type === 'C'){
            foreach($result as $r){
                if ($r->type === 'C') return $r->value;
            }
        } else if ($type === 'I'){
            $v = 0;
            foreach($result as $r){
                if ($r->type != 'R') $v .= $r->value;
            }
            return $v;
        } else {
            $v = 0;
            foreach($result as $r){
                if ($r->type != 'I') $v .= $r->value;
            }
            return $v;
        }
    }

    public function get_final_shipping_cost(){

        // check if it will use cubage or total item's weight to calculate the freight's value
        $this->weight = $this->compare_weight_versus_cubage();

        $query_result = $this->get_airpot_code_and_type_from_database()[0];
        if(empty($query_result)){
            return 0;
        }
        // get base value and the rate type (Capital City or Country)
        $db_value = $this->get_tax_value_from_database($query_result->airport_code, $query_result->type);
        // TODO: trarar melhor resultado em caso de retornar um array vazio do banco
        // atualmente retorna 0 e desativa a opção no carrinho
        if(empty($db_value)){
            return 0;
        }
        // calculate final value with the additional fees
        $value_with_fees = $this->add_additional_fees($db_value);
        // return value of the freight
        return $value_with_fees;
        
    }

}
