<?php
  namespace Everexpert_Woocommerce_Authors;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  delete_option('wc_ewa_admin_tab_section_title');
  delete_option('wc_ewa_admin_tab_slug');
  delete_option('wc_ewa_admin_tab_author_logo_size');
  delete_option('wc_ewa_admin_tab_author_single_position');
  delete_option('wc_ewa_admin_tab_author_single_product_tab');
  delete_option('wc_ewa_admin_tab_author_desc');
  delete_option('wc_ewa_admin_tab_section_end');
  delete_option('wc_ewa_notice_plugin_review');

  //remove exported authors if exists
  unlink( WP_CONTENT_DIR . '/uploads/ewa-export.json' );

  //update permalinks and clean cache
  flush_rewrite_rules();
  wp_cache_flush();
