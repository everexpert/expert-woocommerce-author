<?php

/**
 * The template for displaying the dropdown widget
 * @version 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<select class="ewa-dropdown-widget">
  <option selected="true" disabled="disabled">
    <?php echo apply_filters('ewa_dropdown_placeholder', __('Authors', 'everexpert-woocommerce-authors')); ?>
  </option>
  <?php foreach ($authors as $author) : ?>
    <option value="<?php echo esc_url($author->get('link')); ?>" <?php selected($data['selected'], $author->get('id')); ?>>
      <?php echo esc_html($author->get('name')); ?>
    </option>
  <?php endforeach; ?>
</select>