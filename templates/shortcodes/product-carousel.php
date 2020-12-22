<?php

/**
 * The template for displaying the product carousels
 * @version 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<?php if (!empty($products)) : ?>

  <div class="ewa-product-carousel" data-slick="<?php echo $slick_settings; ?>">

    <?php foreach ($products as $product) : ?>
      <div class="ewa-slick-slide">
        <a href="<?php echo esc_url($product['permalink']); ?>">
          <?php echo wp_kses_post($product['thumbnail']); ?>
          <h3><?php echo esc_html($product['title']); ?></h3>
          <?php echo do_shortcode('[add_to_cart id="' . esc_attr($product['id']) . '" style=""]'); ?>
        </a>
      </div>
    <?php endforeach; ?>

    <div class="ewa-carousel-loader"><?php esc_html_e('Loading', 'everexpert-woocommerce-authors'); ?>...</div>

  </div>

<?php else : ?>

  <div><?php esc_html_e('Nothing found'); ?></div>

<?php endif; ?>