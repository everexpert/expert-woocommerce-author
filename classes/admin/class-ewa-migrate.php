<?php
  namespace Everexpert_Woocommerce_Authors\Admin;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  class EWA_Migrate {

    function __construct(){
      add_action( 'wp_ajax_ewa_admin_migrate_authors', array( $this, 'migrate_from' ) );
    }

    public function migrate_from(){

      if( isset( $_POST['from'] ) ){

        switch( $_POST['from'] ) {
          case 'yith':
            $this->migrate_from_yith();
            break;
          case 'ultimate':
            $this->migrate_from_ultimate();
            break;
	  case 'wooauthors':
            $this->migrate_from_wooauthors();
            break;
        }


      }

      wp_die();
    }

    public function migrate_from_yith(){

      global $wpdb;
      $terms = $wpdb->get_col( 'SELECT term_id FROM '.$wpdb->prefix.'term_taxonomy WHERE taxonomy LIKE "yith_product_author"' );

      foreach( $terms as $term_id ) {

        //change taxonomy
        $wpdb->update(
          $wpdb->prefix . 'term_taxonomy',
          array(
            'taxonomy' => 'ewa-author'
          ),
          array(
            'term_id' => $term_id
          )
        );

        //update term meta
        $wpdb->update(
          $wpdb->prefix . 'termmeta',
          array(
            'meta_key' => 'ewa_author_image'
          ),
          array(
            'meta_key'         => 'thumbnail_id',
            'term_id'          => $term_id
          )
        );

      }

    }

    public function migrate_from_ultimate(){

      global $wpdb;
      $terms = $wpdb->get_col( 'SELECT term_id FROM '.$wpdb->prefix.'term_taxonomy WHERE taxonomy LIKE "product_author"' );

      foreach( $terms as $term_id ) {

        //change taxonomy
        $wpdb->update(
          $wpdb->prefix . 'term_taxonomy',
          array(
            'taxonomy' => 'ewa-author'
          ),
          array(
            'term_id' => $term_id
          )
        );

        /**
        *   Ultimate WooCommerce Authors uses tax-meta-class, tax meta are really options
        *   @link https://github.com/everexpert/expert-woocommerce-author
        */
        $term_meta = get_option('tax_meta_'.$term_id);
        if( isset( $term_meta['mgwb_image_author_thumb']['id'] ) )
          add_term_meta( $term_id, 'ewa_author_image', $term_meta['mgwb_image_author_thumb']['id'] );

      }

    }

    public function migrate_from_wooauthors(){

      global $wpdb;
      $terms = $wpdb->get_col( 'SELECT term_id FROM '.$wpdb->prefix.'term_taxonomy WHERE taxonomy LIKE "product_author"' );

      foreach( $terms as $term_id ) {

        // change taxonomy
        $wpdb->update(
          $wpdb->prefix . 'term_taxonomy',
          array(
            'taxonomy' => 'ewa-author'
          ),
          array(
            'term_id' => $term_id
          )
        );

      	// add the logo id
      	if( $thumb_id = get_woocommerce_term_meta( $term_id, 'thumbnail_id', true ) )
      		add_term_meta( $term_id, 'ewa_author_image', $thumb_id );

      }

    }

  }
