<?php
namespace Everexpert_Woocommerce_Authors\Shortcodes;
use WP_Query;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class EWA_AZ_Listing_Shortcode {

  public static function shortcode( $atts ) {

    $grouped_authors = get_transient('ewa_az_listing_cache');

    if ( ! $grouped_authors ) {

      $atts = shortcode_atts( array(
        'only_parents' => false,
      ), $atts, 'ewa-az-listing' );

      $only_parents = filter_var( $atts['only_parents'], FILTER_VALIDATE_BOOLEAN );

      $authors         = \Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors::get_authors( true, 'name', 'ASC', false, false, $only_parents );
      $grouped_authors = array();

      foreach ( $authors as $author ) {

        if ( self::has_products( $author->term_id ) ) {

          $letter = mb_substr( htmlspecialchars_decode( $author->name ), 0, 1 );
          $letter = strtolower( $letter );
          $grouped_authors[$letter][] = [ 'author_term' => $author ];

        }

      }

      set_transient( 'ewa_az_listing_cache', $grouped_authors, 43200 );//12 hours

    }

    return \Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors::render_template(
      'az-listing',
      'shortcodes',
      array( 'grouped_authors' => $grouped_authors ),
      false
    );

  }

  private static function has_products( $author_id ){

    $args = array(
      'posts_per_page' => -1,
      'post_type'      => 'product',
      'tax_query'      => array(
        array(
          'taxonomy' => 'ewa-author',
          'field'    => 'term_id',
          'terms'    => array( $author_id )
        )
      ),
      'fields' => 'ids'
    );

    if( get_option('woocommerce_hide_out_of_stock_items') === 'yes' ){
      $args['meta_query'] = array(
        array(
          'key'     => '_stock_status',
          'value'   => 'outofstock',
          'compare' => 'NOT IN'
        )
      );
    }

    $wp_query = new WP_Query($args);
    wp_reset_postdata();
    return $wp_query->posts;

  }

}
