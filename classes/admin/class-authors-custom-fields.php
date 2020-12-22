<?php

namespace Everexpert_Woocommerce_Authors\Admin;

defined('ABSPATH') or die('No script kiddies please!');

class Authors_Custom_Fields
{

  function __construct()
  {
    add_action('ewa-author_add_form_fields', array($this, 'add_authors_metafields_form'));
    add_action('ewa-author_edit_form_fields', array($this, 'add_authors_metafields_form_edit'));
    add_action('edit_ewa-author', array($this, 'add_authors_metafields_save'));
    add_action('create_ewa-author', array($this, 'add_authors_metafields_save'));
  }

  public function add_authors_metafields_form()
  {
    ob_start();
?>

    <div class="form-field ewa_author_cont">
      <label for="ewa_author_desc"><?php _e('Description'); ?></label>
      <textarea id="ewa_author_description_field" name="ewa_author_description_field" rows="5" cols="40"></textarea>
      <p id="author-description-help-text"><?php _e('Author description for the archive pages. You can include some html markup and shortcodes.', 'everexpert-woocommerce-authors'); ?></p>
    </div>

    <div class="form-field ewa_author_cont">
      <label for="ewa_author_image"><?php _e('Author logo', 'everexpert-woocommerce-authors'); ?></label>
      <input type="text" name="ewa_author_image" id="ewa_author_image" value="">
      <a href="#" id="ewa_author_image_select" class="button"><?php esc_html_e('Select image', 'everexpert-woocommerce-authors'); ?></a>
    </div>

    <div class="form-field ewa_author_cont">
      <label for="ewa_author_banner"><?php _e('Author banner', 'everexpert-woocommerce-authors'); ?></label>
      <input type="text" name="ewa_author_banner" id="ewa_author_banner" value="">
      <a href="#" id="ewa_author_banner_select" class="button"><?php esc_html_e('Select image', 'everexpert-woocommerce-authors'); ?></a>
      <p><?php _e('This image will be shown on author page', 'everexpert-woocommerce-authors'); ?></p>
    </div>

    <div class="form-field ewa_author_cont">
      <label for="ewa_author_banner_link"><?php _e('Author banner link', 'everexpert-woocommerce-authors'); ?></label>
      <input type="text" name="ewa_author_banner_link" id="ewa_author_banner_link" value="">
      <p><?php _e('This link should be relative to site url. Example: product/product-name', 'everexpert-woocommerce-authors'); ?></p>
    </div>

    <?php wp_nonce_field(basename(__FILE__), 'ewa_nonce'); ?>

  <?php
    echo ob_get_clean();
  }

  public function add_authors_metafields_form_edit($term)
  {
    $term_value_image = get_term_meta($term->term_id, 'ewa_author_image', true);
    $term_value_banner = get_term_meta($term->term_id, 'ewa_author_banner', true);
    $term_value_banner_link = get_term_meta($term->term_id, 'ewa_author_banner_link', true);
    ob_start();
  ?>
    <table class="form-table ewa_author_cont">
      <tr class="form-field">
        <th>
          <label for="ewa_author_desc"><?php _e('Description'); ?></label>
        </th>
        <td>
          <?php wp_editor(html_entity_decode($term->description), 'ewa_author_description_field', array('editor_height' => 120)); ?>
          <p id="author-description-help-text"><?php _e('Author description for the archive pages. You can include some html markup and shortcodes.', 'everexpert-woocommerce-authors'); ?></p>
        </td>
      </tr>
      <tr class="form-field">
        <th>
          <label for="ewa_author_image"><?php _e('Author logo', 'everexpert-woocommerce-authors'); ?></label>
        </th>
        <td>
          <input type="text" name="ewa_author_image" id="ewa_author_image" value="<?php echo esc_attr($term_value_image); ?>">
          <a href="#" id="ewa_author_image_select" class="button"><?php esc_html_e('Select image', 'everexpert-woocommerce-authors'); ?></a>

          <?php $current_image = wp_get_attachment_image($term_value_image, array('90', '90'), false); ?>
          <?php if (!empty($current_image)) : ?>
            <div class="ewa_author_image_selected">
              <span>
                <?php echo wp_kses_post($current_image); ?>
                <a href="#" class="ewa_author_image_selected_remove">X</a>
              </span>
            </div>
          <?php endif; ?>

        </td>
      </tr>
      <tr class="form-field">
        <th>
          <label for="ewa_author_banner"><?php _e('Author banner', 'everexpert-woocommerce-authors'); ?></label>
        </th>
        <td>
          <input type="text" name="ewa_author_banner" id="ewa_author_banner" value="<?php echo esc_html($term_value_banner); ?>">
          <a href="#" id="ewa_author_banner_select" class="button"><?php esc_html_e('Select image', 'everexpert-woocommerce-authors'); ?></a>

          <?php $current_image = wp_get_attachment_image($term_value_banner, array('90', '90'), false); ?>
          <?php if (!empty($current_image)) : ?>
            <div class="ewa_author_image_selected">
              <span>
                <?php echo wp_kses_post($current_image); ?>
                <a href="#" class="ewa_author_image_selected_remove">X</a>
              </span>
            </div>
          <?php endif; ?>

        </td>
      </tr>
      <tr class="form-field">
        <th>
          <label for="ewa_author_banner_link"><?php _e('Author banner link', 'everexpert-woocommerce-authors'); ?></label>
        </th>
        <td>
          <input type="text" name="ewa_author_banner_link" id="ewa_author_banner_link" value="<?php echo esc_html($term_value_banner_link); ?>">
          <p class="description"><?php _e('This link should be relative to site url. Example: product/product-name', 'everexpert-woocommerce-authors'); ?></p>
          <div id="ewa_author_banner_link_result"><?php echo wp_get_attachment_image($term_value_banner_link, array('90', '90'), false); ?></div>
        </td>
      </tr>
    </table>

    <?php wp_nonce_field(basename(__FILE__), 'ewa_nonce'); ?>

<?php
    echo ob_get_clean();
  }

  public function add_authors_metafields_save($term_id)
  {

    if (!isset($_POST['ewa_nonce']) || !wp_verify_nonce($_POST['ewa_nonce'], basename(__FILE__)))
      return;

    /* ·············· Author image ·············· */
    $old_img = get_term_meta($term_id, 'ewa_author_image', true);
    $new_img = isset($_POST['ewa_author_image']) ? $_POST['ewa_author_image'] : '';

    if ($old_img && '' === $new_img)
      delete_term_meta($term_id, 'ewa_author_image');

    else if ($old_img !== $new_img)
      update_term_meta($term_id, 'ewa_author_image', $new_img);
    /* ·············· /Author image ·············· */

    /* ·············· Author banner ·············· */
    $old_img = get_term_meta($term_id, 'ewa_author_banner', true);
    $new_img = isset($_POST['ewa_author_banner']) ? $_POST['ewa_author_banner'] : '';

    if ($old_img && '' === $new_img)
      delete_term_meta($term_id, 'ewa_author_banner');

    else if ($old_img !== $new_img)
      update_term_meta($term_id, 'ewa_author_banner', $new_img);
    /* ·············· /Author banner ·············· */

    /* ·············· Author banner link ·············· */
    $old_img = get_term_meta($term_id, 'ewa_author_banner_link', true);
    $new_img = isset($_POST['ewa_author_banner_link']) ? $_POST['ewa_author_banner_link'] : '';

    if ($old_img && '' === $new_img)
      delete_term_meta($term_id, 'ewa_author_banner_link');

    else if ($old_img !== $new_img)
      update_term_meta($term_id, 'ewa_author_banner_link', $new_img);
    /* ·············· /Author banner link ·············· */

    /* ·············· Author desc ·············· */
    if (isset($_POST['ewa_author_description_field'])) {
      $allowed_tags = apply_filters(
        'ewa_description_allowed_tags',
        '<p><span><a><ul><ol><li><h1><h2><h3><h4><h5><h6><pre><strong><em><blockquote><del><ins><img><code><hr>'
      );
      $desc = strip_tags(wp_unslash($_POST['ewa_author_description_field']), $allowed_tags);
      global $wpdb;
      $wpdb->update($wpdb->term_taxonomy, ['description' => $desc], ['term_id' => $term_id]);
    }
    /* ·············· /Author desc ·············· */
  }
}
