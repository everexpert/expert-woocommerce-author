<?php

namespace Everexpert_Woocommerce_Authors\Admin;

defined('ABSPATH') or die('No script kiddies please!');

class EWA_Notices {

  protected static $instance;

  function __construct() {
    add_filter('plugin_action_links_' . EWA_PLUGIN_BASENAME, array($this, 'add_action_links'));
//    add_action('admin_notices', array($this, 'add_notices'));
//    add_action('wp_ajax_ewa_dismiss_notice', array($this, 'ajax_dismiss_notice'));
  }

  function ajax_dismiss_notice() {

    if (check_admin_referer('ewa_dismiss_notice', 'nonce') && isset($_REQUEST['notice_id'])) {

      $notice_id = sanitize_key($_REQUEST['notice_id']);

      update_user_meta(get_current_user_id(), $notice_id, true);

      wp_send_json($notice_id);
    }

    wp_die();
  }

  function add_notices() {

    if (!get_transient('ewa-first-rating') && !get_user_meta(get_current_user_id(), 'ewa-user-rating', true)) {
      ?>
      <div id="ewa-admin-rating" class="ewa-notice notice is-dismissible" data-notice_id="ewa-user-rating">
        <div class="notice-container" style="padding-top: 10px; padding-bottom: 10px; display: flex; justify-content: left; align-items: center;">
          <div class="notice-image">
            <img style="border-radius:50%;max-width: 90px;" src="<?php echo plugins_url('/assets/img/icon_ewa.jpg', EWA_PLUGIN_FILE); ?>" alt="<?php echo esc_html(EWA_PLUGIN_NAME); ?>>">
          </div>
          <div class="notice-content" style="margin-left: 15px;">
            <p>
              <?php printf(esc_html__('Hello! Thank you for choosing the %s plugin!', 'everexpert-woocommerce-authors'), EWA_PLUGIN_NAME); ?>
              <br/>
              <?php esc_html_e('Could you please give it a 5-star rating on WordPress? We know its a big favor, but we\'ve worked very much and very hard to release this great product. Your feedback will boost our motivation and help us promote and continue to improve this product.', 'everexpert-woocommerce-authors'); ?>
            </p>
            <a href="<?php echo esc_url(EWA_REVIEW_URL); ?>" class="button-primary" target="_blank">
              <?php esc_html_e('Yes, of course!', 'everexpert-woocommerce-authors'); ?>
            </a>
            <a href="<?php echo esc_url(EWA_SUPPORT_URL); ?>" class="button-secondary" target="_blank">
              <?php esc_html_e('Report a bug', 'everexpert-woocommerce-authors'); ?>
            </a>
          </div>				
        </div>
      </div>
      <script>
        (function ($) {
          $('.ewa-notice').on('click', '.notice-dismiss', function (e) {
            e.preventDefault();
            var notice_id = $(e.delegateTarget).data('notice_id');
            $.ajax({
              type: 'POST',
              url: ajaxurl,
              data: {
                notice_id: notice_id,
                action: 'ewa_dismiss_notice',
                nonce: '<?php echo wp_create_nonce('ewa_dismiss_notice'); ?>'
              },
              success: function (response) {
                console.log(response);
              },
            });
          });
        })(jQuery);
      </script>
      <?php
    }
  }

  public function add_action_links($links) {

    $links[] = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=ewa_admin_tab')) . '">' . esc_html__('Settings', 'everexpert-woocommerce-authors') . '</a>';

    return $links;
  }

}
