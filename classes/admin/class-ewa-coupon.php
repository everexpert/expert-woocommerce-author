<?php
  namespace Everexpert_Woocommerce_Authors\Admin;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  class EWA_Coupon{

    function __construct(){
      add_action( 'woocommerce_coupon_options_usage_restriction', array( $this, 'coupon_restriction' ) );
      add_action( 'woocommerce_coupon_options_save',  array( $this, 'coupon_save' ) );
      add_filter( 'woocommerce_coupon_is_valid', array( $this, 'is_valid_coupon' ), 10, 2 );
      add_filter( 'woocommerce_coupon_is_valid_for_product', array( $this, 'is_valid_for_product_author' ), 10, 4 );
    }

    public function coupon_restriction() {
        global $thepostid, $post;
        $thepostid = empty( $thepostid ) ? $post->get_ID() : $thepostid;

        $selected_authors = get_post_meta( $thepostid, '_ewa_coupon_restriction', true );
        if( $selected_authors == '' ) $selected_authors = array();

        ob_start();
        ?>
        <p class="form-field"><label for="_ewa_coupon_restriction"><?php _e( 'Authors restriction', 'everexpert-woocommerce-authors' ); ?></label>
				<select id="_ewa_coupon_restriction" name="_ewa_coupon_restriction[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Any author', 'everexpert-woocommerce-authors' ); ?>">
					<?php
						$categories   = get_terms( 'ewa-author', 'orderby=name&hide_empty=0' );
						if ( $categories ) {
							foreach ( $categories as $cat ) {
								echo '<option value="' . esc_attr( $cat->term_id ) . '"' . selected( in_array( $cat->term_id, $selected_authors ), true, false ) . '>' . esc_html( $cat->name ) . '</option>';
							}
						}
					?>
				</select> <?php echo wc_help_tip( __( 'Coupon will be valid if there are at least one product of this authors in the cart', 'everexpert-woocommerce-authors' ) ); ?></p>
        <?php
        echo ob_get_clean();

    }

    public function coupon_save( $post_id ){
      $_ewa_coupon_restriction = isset( $_POST['_ewa_coupon_restriction'] ) ? $_POST['_ewa_coupon_restriction'] : '';
      update_post_meta( $post_id, '_ewa_coupon_restriction', $_ewa_coupon_restriction );
    }

    public function is_valid_coupon( $availability, $coupon ){
      $selected_authors = get_post_meta( $coupon->get_ID(), '_ewa_coupon_restriction', true );
      if( !empty( $selected_authors ) ){
        global $woocommerce;
        $products = $woocommerce->cart->get_cart();
        foreach( $products as $product ) {
          $product_authors = wp_get_post_terms( $product['product_id'], 'ewa-author', array( 'fields' => 'ids' ) );
          $valid_authors = array_intersect( $selected_authors, $product_authors );
          if( !empty( $valid_authors ) ) return true;
        }
        return false;
      }
      return true;
    }

    public function is_valid_for_product_author( $valid, $product, $coupon, $values ){
      if ( !$valid ) return false;

      $coupon_id = is_callable( array( $coupon, 'get_id' ) ) ?  $coupon->get_id() : $coupon->id;
      $selected_authors = get_post_meta( $coupon_id, '_ewa_coupon_restriction', true );
      if ( empty( $selected_authors ) ) return $valid;

      if( $product->is_type( 'variation' ) ){
        $product_id = $product->get_parent_id();
      }else{
        $product_id = is_callable( array( $product, 'get_id' ) ) ?  $product->get_id() : $product->id;
      }
      $product_authors = wp_get_post_terms( $product_id, 'ewa-author', array( 'fields' => 'ids' ) );
      $valid_authors = array_intersect( $selected_authors, $product_authors );
      return !empty( $valid_authors );
    }

  }
