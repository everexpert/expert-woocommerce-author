<?php

/**
 * The template for displaying the "ewa-author" shortcode
 * @version 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<?php if (!empty($authors)) : ?>

  <div class="ewa-author-shortcode">

    <?php foreach ($authors as $author) : ?>

      <a href="<?php echo esc_url($author->term_link); ?>" title="<?php _e('View author', 'everexpert-woocommerce-authors'); ?>">

        <?php if (!$as_link && !empty($author->image)) : ?>

          <?php echo wp_kses_post($author->image); ?>

        <?php else : ?>

          <?php echo esc_html($author->name); ?>

        <?php endif; ?>

      </a>

    <?php endforeach; ?>

  </div>

<?php endif; ?>