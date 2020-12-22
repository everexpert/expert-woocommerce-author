<?php
namespace Everexpert_Woocommerce_Authors\Admin;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Edit_Authors_Page {

  private static $current_user;

  function __construct(){
    add_filter( 'get_terms', array( $this, 'author_list_admin_filter' ), 10, 3 );
    add_filter( 'manage_edit-ewa-author_columns', array( $this, 'author_taxonomy_columns_head' ) );
    add_filter( 'manage_ewa-author_custom_column', array( $this, 'author_taxonomy_columns' ), 10, 3 );
    add_action( 'wp_ajax_ewa_admin_set_featured_author', array( $this, 'set_featured_author' ) );
    add_filter( 'screen_settings', array( $this, 'add_screen_options' ), 10, 2 );
    add_action( 'wp_ajax_ewa_admin_save_screen_settings', array( $this, 'save_screen_options' ) );
    add_action( 'plugins_loaded', function(){ \Everexpert_Woocommerce_Authors\Admin\Edit_Authors_Page::$current_user = wp_get_current_user(); } );
    add_action( 'after-ewa-author-table', array( $this, 'add_authors_count' ) );
  }

  private static function is_edit_authors_page(){
    global $pagenow;
    return ( $pagenow == 'edit-tags.php' && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'ewa-author' ) ? true : false;
  }

  public function add_authors_count( $tax_name ){
    $authors = get_terms(
      $tax_name,
      array( 'hide_empty' => false )
    );
    $authors_featured = get_terms(
      $tax_name,
      array( 'hide_empty' => false, 'meta_query' => array( array( 'key' => 'ewa_featured_author', 'value' => true ) ) )
    );

    echo \Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors::render_template(
      'edit-authors-bottom',
      'admin',
      array( 'featured_count' => count( $authors_featured ), 'text_featured'  => esc_html__('featured', 'everexpert-woocommerce-authors') )
    );

  }

  public function author_list_admin_filter( $authors, $taxonomies, $args ) {

    if( self::is_edit_authors_page() ){

      $featured = get_user_option( 'ewa-first-featured-authors', self::$current_user->ID );
      if( $featured ){
        $featured_authors = array();
        $other_authors    = array();
        foreach( $authors as $author ) {
          if( get_term_meta( $author->term_id, 'ewa_featured_author', true ) ){
            $featured_authors[] = $author;
          }else{
            $other_authors[] = $author;
          }
        }
        return array_merge( $featured_authors, $other_authors );
      }

    }
    return $authors;

  }

  public function author_taxonomy_columns_head( $columns ){
    $new_columns = array();

    if ( isset( $columns['cb'] ) ) {
      $new_columns['cb'] = $columns['cb'];
      unset( $columns['cb'] );
    }

    if( isset( $columns['description'] ) ) unset( $columns['description'] );

    $new_columns['logo'] = __( 'Logo', 'everexpert-woocommerce-authors' );
    $columns['featured'] = '<span class="ewa-featured-col-title">'.__( 'Featured', 'everexpert-woocommerce-authors' ).'</span>';

    return array_merge( $new_columns, $columns );
  }

  public function author_taxonomy_columns($c, $column_name, $term_id){
    switch( $column_name ){
      case 'logo':
        $image = wp_get_attachment_image( get_term_meta( $term_id, 'ewa_author_image', 1 ), array('40','40') );
        return ( $image ) ? $image : wc_placeholder_img( array('40','40') );
        break;
      case 'featured':
        $featured_class = ( $this->is_featured_author( $term_id ) ) ? 'dashicons-star-filled' : 'dashicons-star-empty';
        printf(
          '<span class="dashicons %1$s" title="%2$s" data-author-id="%3$s"></span>',
          $featured_class, esc_html__('Set as featured', 'everexpert-woocommerce-authors'), $term_id
        );
        break;
    }
  }

  private function is_featured_author( $author_id ){
    return ( get_term_meta( $author_id, 'ewa_featured_author', true ) );
  }

  public function set_featured_author(){
    if( isset( $_POST['author'] ) ){
      $direction = 'up';
      $author = intval( $_POST['author'] );
      if( $this->is_featured_author( $author ) ){
        delete_term_meta( $author, 'ewa_featured_author', true );
        $direction = 'down';
      }else{
        update_term_meta( $author, 'ewa_featured_author', true );
      }
      wp_send_json_success( array( 'success' => true, 'direction' => $direction ) );
    }else{
      wp_send_json_error( array( 'success' => false, 'error_msg' => __( 'Error!','everexpert-woocommerce-authors' ) ) );
    }
    wp_die();
  }

  public function add_screen_options( $status, $args ){
    if( self::is_edit_authors_page() ){
      $featured = get_user_option( 'ewa-first-featured-authors', self::$current_user->ID );
      ob_start();
      ?>
      <legend><?php esc_html_e('Authors','everexpert-woocommerce-authors');?></legend>
      <label>
        <input id="ewa-first-featured-authors" type="checkbox" <?php checked($featured,true);?>>
        <?php esc_html_e('Show featured authors first','everexpert-woocommerce-authors');?>
      </label>
      <?php
      return ob_get_clean();
    }
  }

  public function save_screen_options(){
    if( isset( $_POST['new_val'] ) ){
      $new_val = ( $_POST['new_val'] == 'true' ) ? true : false;
      update_user_option( self::$current_user->ID, 'ewa-first-featured-authors', $new_val );
    }
    wp_die();
  }

}
