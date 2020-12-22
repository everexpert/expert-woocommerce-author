<?php

namespace Everexpert_Woocommerce_Authors;

defined('ABSPATH') or die('No script kiddies please!');

class EWA_Exporter_Support{

    function __construct(){
      add_filter( 'woocommerce_product_export_column_names', array( $this, 'add_export_column' ) );
      add_filter( 'woocommerce_product_export_product_default_columns',  array( $this, 'add_export_column' ) );
      add_filter( 'woocommerce_product_export_product_column_ewa-author', array( $this, 'add_export_data' ), 10, 2 );
    }

    /**
     * Add the custom column to the exporter and the exporter column menu.
     *
     * @param array $columns
     * @return array $columns
     */
    public function add_export_column( $columns ) {
    	$columns['ewa-author'] = esc_html__('Author', 'everexpert-woocommerce-authors');
    	return $columns;
    }

    /**
     * Provide the data to be exported for one item in the column.
     *
     * @param mixed $value (default: '')
     * @param WC_Product $product
     * @return mixed $value - Should be in a format that can be output into a text file (string, numeric, etc).
     */
    public function add_export_data( $value, $product ) {
      $authors = wp_get_post_terms( $product->get_id(), 'ewa-author' );
      $author_names = array();
      foreach( $authors as $author ) $author_names[] = $author->name;
    	return implode( ',', $author_names );
    }

}
