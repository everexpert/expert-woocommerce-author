<?php

namespace Everexpert_Woocommerce_Authors;

defined('ABSPATH') or die('No script kiddies please!');

class EWA_Product_Tab
{

  function __construct()
  {
    add_filter('woocommerce_product_tabs', array($this, 'product_tab'));
  }

  public function product_tab($tabs)
  {

    global $product;

    if (isset($product)) {
      $authors = wp_get_object_terms($product->get_id(), 'ewa-author');

      if (!empty($authors)) {
        $show_author_tab = get_option('wc_ewa_admin_tab_author_single_product_tab');
        if ($show_author_tab == 'yes' || !$show_author_tab) {
          $tabs['ewa_tab'] = array(
            'title'     => __('Author', 'everexpert-woocommerce-authors'),
            'priority'   => 20,
            'callback'   => array($this, 'product_tab_content')
          );
        }
      }
    }

    return $tabs;
  }

  public function product_tab_content()
  {

    global $product;
    $authors = wp_get_object_terms($product->get_id(), 'ewa-author');

    ob_start();
?>

    <h2><?php echo apply_filters('woocommerce_product_author_heading', esc_html__('Author', 'everexpert-woocommerce-authors')); ?></h2>
    <?php foreach ($authors as $author) : ?>

      <?php
      $image_size = get_option('wc_ewa_admin_tab_author_logo_size', 'thumbnail');
      $author_logo = get_term_meta($author->term_id, 'ewa_author_image', true);
      $author_logo = wp_get_attachment_image($author_logo, apply_filters('ewa_product_tab_author_logo_size', $image_size));
      ?>

      <div id="tab-ewa_tab-content">
        <h3><?php echo esc_html($author->name); ?></h3>
        <?php if (!empty($author->description)) echo '<div>' . do_shortcode($author->description) . '</div>'; ?>
        <?php if (!empty($author_logo)) echo '<span>' . $author_logo . '</span>'; ?>
      </div>

    <?php endforeach; ?>

<?php
    echo ob_get_clean();
  }
}
