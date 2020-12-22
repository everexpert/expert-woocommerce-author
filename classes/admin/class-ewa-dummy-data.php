<?php
  namespace Everexpert_Woocommerce_Authors\Admin;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  class EWA_Dummy_Data {

    function __construct(){
      add_action( 'wp_ajax_ewa_admin_dummy_data', array( $this, 'dummy_data' ) );
    }

    private static function get_attachment_id_from_src($image_src){
      global $wpdb;
      $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
      $id = $wpdb->get_var($query);
      return $id;
    }

    private static function retrieve_img_src( $img ) {
      if (preg_match('/<img(\s+?)([^>]*?)src=(\"|\')([^>\\3]*?)\\3([^>]*?)>/is', $img, $m) && isset($m[4]))
        return $m[4];
      return false;
    }

    private static function upload_image( $post_id, $img_url ){

      require_once ABSPATH . "wp-admin" . '/includes/image.php';
      require_once ABSPATH . "wp-admin" . '/includes/file.php';
      require_once ABSPATH . "wp-admin" . '/includes/media.php';

      //solves media_sideload_image bug with spaces in filenames
      $parsed_file = parse_url($img_url);
      $path = $parsed_file['path'];
      $file_name = basename($path);
      $encoded_file_name = rawurlencode($file_name);
      $path = str_replace($file_name, $encoded_file_name, $path);
      $img_url = $parsed_file['scheme'] . "://" . $parsed_file['host'] . $path;
      $image = '';

      preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $img_url, $file_matches);
      if(isset($file_matches[0])){
         $image = media_sideload_image($img_url, $post_id );
      }

      //media_sideload_image returns a html image
      //extract the src value for get the attachment id
      $image_src = self::retrieve_img_src( $image );
      return self::get_attachment_id_from_src( $image_src );

    }

    private function build_description(){
      $desc = 'lorem ipsum dolor <strong>sit</strong> amet consectetur adipiscing elit etiam mollis faucibus aliquet';
      $desc.= 'sed risus turpis dapibus vel <strong>rhoncus</strong> a vestibulum sed lectus in hac habitasse platea dictumst';
      $desc.= 'suspendisse non luctus felis <strong>morbi</strong> id volutpat ligula quisque rutrum arcu at erat lobortis';
      $exploded_desc = explode( ' ', $desc );
      shuffle( $exploded_desc );
      $desc = implode( ' ', $exploded_desc );
      return ucfirst( $desc );
    }

    public function dummy_data(){

      for( $i=1; $i<11; $i++ ) {
        $term_desc = $this->build_description();
        $author_name = 'author'.$i;
        $attachment_id = self::upload_image( false, EWA_PLUGIN_URL . '/assets/img/dummy-data/'.$author_name.'.png' );
        $inserted_author = wp_insert_term( ucfirst( $author_name ), 'ewa-author', array( "description" => $term_desc ) );
        if( !is_wp_error( $inserted_author ) && isset( $inserted_author['term_id'] ) ){
          add_term_meta( $inserted_author['term_id'], 'ewa_author_image', $attachment_id );
        }
      }

      $this->set_authors_randomly();

      wp_die();

    }

    public function set_authors_randomly(){

      $authors = \Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors::get_authors_array();

      $the_query = new \WP_Query( array( 'posts_per_page' => -1 , 'post_type' => 'product' ) );

      while ( $the_query->have_posts() ) {
      	$the_query->the_post();
        wp_set_object_terms( get_the_ID(), array_rand( $authors ), 'ewa-author' );
      }
      wp_reset_postdata();

    }

  }
