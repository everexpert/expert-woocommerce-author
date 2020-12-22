<?php

/**
 * The template for displaying the carousels
 * @version 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<div class="ewa-carousel" data-slick="<?php echo $slick_settings; ?>">

  <?php foreach ($authors as $author) : ?>
    <div class="ewa-slick-slide">
      <a href="<?php echo esc_url($author['link']); ?>" title="<?php echo esc_html($author['name']); ?>">
        <?php echo wp_kses_post($author['attachment_html']); ?>
      </a>
    </div>
  <?php endforeach; ?>

  <div class="ewa-carousel-loader"><?php esc_html_e('Loading', 'everexpert-woocommerce-authors'); ?>...</div>

</div>