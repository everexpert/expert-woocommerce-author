<?php
namespace Everexpert_Woocommerce_Authors\Shortcodes;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class EWA_Carousel_Shortcode{

  private static $atts;

  public static function carousel_shortcode( $atts ) {

    self::$atts = shortcode_atts( array(
        'items'             => "10",
        'items_to_show'     => "5",
        'items_to_scroll'   => "1",
        'image_size'        => "thumbnail",
        'autoplay'          => "false",
        'arrows'            => "false",
        'hide_empty'        => false
    ), $atts, 'ewa-carousel' );

    //enqueue deps
    if( !wp_style_is('ewa-lib-slick') ) wp_enqueue_style('ewa-lib-slick');
    if( !wp_script_is('ewa-lib-slick') ) wp_enqueue_script('ewa-lib-slick');

    return \Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors::render_template(
      'carousel',
      'shortcodes',
      array( 'slick_settings' => self::slick_settings(), 'authors' => self::authors_data() ),
      false
    );

  }

  private static function slick_settings(){

    $slick_settings = array(
      'slidesToShow'   => (int)self::$atts['items_to_show'],
      'slidesToScroll' => (int)self::$atts['items_to_scroll'],
      'autoplay'       => ( self::$atts['autoplay'] === 'true' ) ? true: false,
      'arrows'         => ( self::$atts['arrows'] === 'true' ) ? true: false
    );
    return htmlspecialchars( json_encode( $slick_settings ), ENT_QUOTES, 'UTF-8' );

  }

  private static function authors_data(){

    $authors = array();
    $foreach_i = 0;
    if( self::$atts['items'] == 'featured' ){
      $authors_array = \Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors::get_authors( self::$atts['items'], 'name', 'ASC', true );
    }else{
      $authors_array = \Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors::get_authors( self::$atts['items'] );
    }
    foreach( $authors_array as $author ){
        if( self::$atts['items'] != 'featured' && $foreach_i >= (int)self::$atts['items'] ) break;

        $author_id = $author->term_id;
        $author_link = get_term_link($author_id);
        $attachment_id = get_term_meta( $author_id, 'ewa_author_image', 1 );
        $attachment_html = $author->name;
        if($attachment_id!='') $attachment_html = wp_get_attachment_image( $attachment_id, self::$atts['image_size'] );

        $authors[] = array( 'link' => $author_link, 'attachment_html' => $attachment_html, 'name' => $author->name );

        $foreach_i++;
    }

    return $authors;

  }

}
