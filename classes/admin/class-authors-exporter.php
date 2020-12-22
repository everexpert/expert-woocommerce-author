<?php
  namespace Everexpert_Woocommerce_Authors\Admin;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  class Authors_Exporter {

    function __construct(){
      add_action( 'after-ewa-author-table', array( $this, 'exporter_button' ) );
      add_action( 'wp_ajax_ewa_authors_export', array( $this, 'export_authors' ) );
      add_action( 'wp_ajax_ewa_authors_import', array( $this, 'import_authors' ) );
    }

    public function exporter_button(){
      echo \Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors::render_template(
        'authors-exporter', 'admin', array( 'ok' => 'va' )
      );
    }

    public function export_authors(){
      $this->get_authors();
      wp_die();
    }

    private function get_authors(){

      $authors_data = array();

      $authors = get_terms( 'ewa-author',array( 'hide_empty' => false ) );
      foreach( $authors as $author ){

        $current_author = array(
          'slug'        =>  $author->slug,
          'name'        =>  $author->name,
          'banner_link' =>  get_term_meta( $author->term_id, 'ewa_author_banner_link', true ),
          'desc'        =>  htmlentities( $author->description )
        );

        $image = get_term_meta( $author->term_id, 'ewa_author_image', true );
        $image = wp_get_attachment_image_src( $image, 'full' );
        if( $image ) $current_author['image'] = $image[0];

        $banner = get_term_meta( $author->term_id, 'ewa_author_banner', true );
        $banner = wp_get_attachment_image_src( $banner, 'full' );
        if( $banner ) $current_author['banner'] = $banner[0];

        $authors_data[] = $current_author;

      }

      $export_file = fopen( WP_CONTENT_DIR . '/uploads/ewa-export.json', 'w' );
      fwrite( $export_file, json_encode( $authors_data ) );
      fclose( $export_file );

      $result = array( 'export_file_url' => WP_CONTENT_URL . '/uploads/ewa-export.json' );

      wp_send_json_success( $result );

    }

    public function import_authors(){

      if( isset( $_FILES['file'] ) ){
        $file = $_FILES['file'];

        $file_content = json_decode( file_get_contents( $file['tmp_name'] ), true );

        if( is_array( $file_content ) ){

          foreach( $file_content as $author ){

            $new_author = wp_insert_term( $author['name'], 'ewa-author', array(
              'slug'        => $author['slug'],
              'description' => html_entity_decode( $author['desc'] )
            ));

            if( !is_wp_error( $new_author ) ){

              if( !empty( $author['image'] ) )
                $this->upload_remote_image_and_attach( $author['image'], $new_author['term_id'], 'ewa_author_image' );
              if( !empty( $author['banner'] ) )
                $this->upload_remote_image_and_attach( $author['banner'], $new_author['term_id'], 'ewa_author_banner' );
              if( !empty( $author['banner_link'] ) )
                update_term_meta( $new_author['term_id'], 'ewa_author_banner_link', $author['banner_link'], true );

            }

          }

          wp_send_json_success();

        }else{
          wp_send_json_error();
        }



      }else{
        wp_send_json_error();
      }

      wp_die();
    }

    private function upload_remote_image_and_attach( $image_url, $term_id, $meta_key ){

      $get  = wp_remote_get( $image_url );
      $type = wp_remote_retrieve_header( $get, 'content-type' );

      if( !$type ) return false;

      $mirror = wp_upload_bits( basename( $image_url ), '', wp_remote_retrieve_body( $get ) );

      $attachment = array(
        'post_title'     => basename( $image_url ),
        'post_mime_type' => $type
      );

      $attach_id = wp_insert_attachment( $attachment, $mirror['file'] );
      require_once ABSPATH . 'wp-admin/includes/image.php';
      $attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
      wp_update_attachment_metadata( $attach_id, $attach_data );

      update_term_meta( $term_id, $meta_key, $attach_id, true );

    }

  }
