<?php

/**
 * The template for displaying filter by author widget
 * @version 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<div class="ewa-filter-products<?php if ($hide_submit_btn) echo ' ewa-hide-submit-btn'; ?>" data-cat-url="<?php echo esc_url($cate_url); ?>">
  <ul>
    <?php foreach ($authors as $author) : ?>
      <li>
        <label>
          <input type="checkbox" data-author="<?php echo esc_attr($author->term_id); ?>" value="<?php echo esc_html($author->slug); ?>"><?php echo esc_html($author->name); ?>
        </label>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if (!$hide_submit_btn) : ?>
    <button><?php esc_html_e('Apply filter', 'everexpert-woocommerce-authors') ?></button>
  <?php endif; ?>
</div>