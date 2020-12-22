<?php

namespace Everexpert_Woocommerce_Authors\Admin;

defined('ABSPATH') or die('No script kiddies please!');

class EWA_Suggestions {

  public function __construct() {
    add_action('admin_menu', array($this, 'add_menu'));
    add_action('admin_init', array($this, 'add_redirect'));
    add_action('admin_head', array($this, 'remove_menu'));
    add_filter('network_admin_url', array($this, 'network_admin_url'), 10, 2);
  }

  // Admin
  // -------------------------------------------------------------------------

  public function add_page() {
    include_once( EWA_PLUGIN_DIR . 'classes/class-ewa-suggestions-list.php' );
    ?>
    <div class="wrap about-wrap full-width-layout">

      <h1><?php esc_html_e('Suggestions', 'everexpert-woocommerce-authors'); ?></h1>

      <p class="about-text"><?php printf(esc_html__('Thanks for using our product! We recommend these extensions that will add new features to stand out your business and improve your sales.', 'everexpert-woocommerce-authors'), EWA_PLUGIN_NAME); ?></p>

      <p class="about-text">
        <?php printf('<a href="%s" target="_blank">%s</a>', EWA_PURCHASE_URL, esc_html__('Purchase', 'everexpert-woocommerce-authors')); ?></a> |  
        <?php printf('<a href="%s" target="_blank">%s</a>', EWA_DOCUMENTATION_URL, esc_html__('Documentation', 'everexpert-woocommerce-authors')); ?></a>
      </p>

      <?php printf('<a href="%s" target="_blank"><div style="
               background: #006bff url(%s) no-repeat;
               background-position: top center;
               background-size: 130px 130px;
               color: #fff;
               font-size: 14px;
               text-align: center;
               font-weight: 600;
               margin: 5px 0 0;
               padding-top: 120px;
               height: 40px;
               display: inline-block;
               width: 140px;
               " class="wp-badge">%s</div></a>', 'https://everexpert.com/?utm_source=ewa_admin', plugins_url('/assets/img/everexpert.jpg', EWA_PLUGIN_FILE), esc_html__('EverExpert', 'everexpert-woocommerce-authors')); ?>

    </div>
    <div class="wrap" style="
         position: relative;
         margin: 25px 40px 0 20px;
         max-width: 1200px;">
         <?php
         $wp_list_table = new \Everexpert_Woocommerce_Authors\EWA_Suggestions_List_Table();
         $wp_list_table->prepare_items();
         ?>
      <form id="plugin-filter" method="post" class="importer-item">
        <?php $wp_list_table->display(); ?>
      </form>
    </div>
    <?php
  }

  public function add_menu() {
    add_menu_page(EWA_PLUGIN_NAME, EWA_PLUGIN_NAME, 'manage_woocommerce', EWA_PREFIX, array($this, 'add_page'));
    add_submenu_page(EWA_PREFIX, esc_html__('Suggestions', 'everexpert-woocommerce-authors'), esc_html__('Suggestions', 'everexpert-woocommerce-authors'), 'manage_woocommerce', EWA_PREFIX . '_suggestions', array($this, 'add_page'));
  }

  // fix for activateUrl on install now button
  public function network_admin_url($url, $path) {

    if (wp_doing_ajax() && !is_network_admin()) {
      if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'install-plugin') {
        if (strpos($url, 'plugins.php') !== false) {
          $url = self_admin_url($path);
        }
      }
    }

    return $url;
  }

  public function add_redirect() {

    if (isset($_REQUEST['activate']) && $_REQUEST['activate'] == 'true') {
      if (wp_get_referer() == admin_url('admin.php?page=' . EWA_PREFIX . '_suggestions')) {
        wp_redirect(admin_url('admin.php?page=' . EWA_PREFIX . '_suggestions'));
      }
    }
  }

  public function remove_menu() {
    ?>
    <style>

      li.toplevel_page_<?php echo EWA_PREFIX; ?> {
        display:none;
      }

    </style>
    <?php
  }

}
