<?php

namespace Everexpert_Woocommerce_Authors;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class EWA_Term{

  protected $term_obj, $id, $name, $link, $image, $banner, $banner_link, $desc, $slug;

  function __construct( $term_obj ){
    $this->term_obj = $term_obj;
    $this->is_wp_term();
  }

  private function is_wp_term(){
    if( is_a( $this->term_obj, 'WP_Term' ) ){
      $this->build_ewa_term( $this->term_obj );
    }else{
      throw new Exception( esc_html__('Only WP_Term objects are allowed', 'everexpert-woocommerce-authors') );
    }
  }

  protected function build_ewa_term(){
    $this->id          = $this->term_obj->term_id;
    $this->name        = $this->term_obj->name;
    $this->slug        = $this->term_obj->slug;
    $this->desc        = get_term_meta( $this->id, 'ewa_author_banner_description', true );
    $this->link        = get_term_link( $this->term_obj->term_id );
    $this->image       = htmlentities( wp_get_attachment_image( get_term_meta( $this->id, 'ewa_author_image', true ), 'full' ) );
    $this->banner      = htmlentities( wp_get_attachment_image( get_term_meta( $this->id, 'ewa_author_banner', true ), 'full' ) );
    $this->banner_link = get_term_meta( $this->id, 'ewa_author_banner_link', true) ;
  }

  public function get( $key = 'id' ){
    return ( isset( $this->$key ) ) ? $this->$key : false;
  }

}
