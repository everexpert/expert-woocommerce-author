<?php
namespace Everexpert_Woocommerce_Authors\Shortcodes;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class EWA_Author_Shortcode{

  public static function author_shortcode( $atts ) {
    $atts = shortcode_atts( array(
      'product_id' => null,
      'as_link'    => false,
      'image_size' => 'thumbnail',
    ), $atts, 'ewa-author' );

    if( !$atts['product_id'] && is_singular('product') ) $atts['product_id'] = get_the_ID();

    $authors = wp_get_post_terms( $atts['product_id'], 'ewa-author');

    foreach( $authors as $key => $author ){
      $authors[$key]->term_link  = get_term_link ( $author->term_id, 'ewa-author' );
      $authors[$key]->image = wp_get_attachment_image( get_term_meta( $author->term_id, 'ewa_author_image', 1 ), $atts['image_size'] );
    }

    return \Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors::render_template(
      'author',
      'shortcodes',
      array( 'authors' => $authors, 'as_link' => $atts['as_link'] ),
      false
    );

  }

}
