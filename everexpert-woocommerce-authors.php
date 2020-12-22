<?php

/**
 *  Plugin Name: Expert Authors for WooCommerce
 *  Plugin URI: https://everexpert.com
 *  Description: Expert WooCommerce Authors allows you to show product authors in your WooCommerce based store.
 *  Version: 1.0.0
 *  Author: Naeem Hasan
 *  Author URI: https://everexpert.com
 *  Text Domain: expert-woocommerce-authors
 *  Domain Path: /lang
 *  License: GPLv3
 *      Expert WooCommerce Authors version 1.8.5, Copyright (C) 2020 EverExpert
 *      Expert WooCommerce Authors is free software: you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation, either version 3 of the License, or
 *      (at your option) any later version.
 *
 *      Expert WooCommerce Authors is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      You should have received a copy of the GNU General Public License
 *      along with Expert WooCommerce Authors.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  WC requires at least: 3.1.0
 *  WC tested up to: 4.6.3
 */

namespace Everexpert_Woocommerce_Authors;

defined('ABSPATH') or die('No script kiddies please!');

//plugin constants
define('EWA_PLUGIN_FILE', __FILE__);
define('EWA_PLUGIN_URL', plugins_url('', __FILE__));
define('EWA_PLUGIN_DIR', __DIR__ . DIRECTORY_SEPARATOR);
define('EWA_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('EWA_PLUGIN_VERSION', '1.8.5');
define('EWA_PLUGIN_NAME', 'Everexpert WooCommerce Authors');
define('EWA_PREFIX', 'ewa');
define('EWA_REVIEW_URL', 'https://everexpert.com');
define('EWA_DEMO_URL', 'https://everexpert.com');
define('EWA_PURCHASE_URL', EWA_DEMO_URL);
define('EWA_SUPPORT_URL', 'https://everexpert.com');
define('EWA_DOCUMENTATION_URL', 'https://everexpert.com');
define('EWA_GITHUB_URL', 'https://github.com/everexpert/expert-woocommerce-author');
define('EWA_GROUP_URL', 'https://everexpert.com');

register_activation_hook(__FILE__, function() {
  update_option('ewa_activate_on', time());
});

//clean authors slug on plugin deactivation
register_deactivation_hook(__FILE__, function() {
  update_option('old_wc_ewa_admin_tab_slug', 'null');
});

//loads textdomain for the translations
add_action('plugins_loaded', function() {
  load_plugin_textdomain('everexpert-woocommerce-authors', false, EWA_PLUGIN_DIR . '/lang');
});

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if (is_plugin_active('woocommerce/woocommerce.php')) {

  require 'classes/class-ewa-term.php';
  require 'classes/widgets/class-ewa-dropdown.php';
  require 'classes/widgets/class-ewa-list.php';
  require 'classes/widgets/class-ewa-filter-by-author.php';
  require 'classes/shortcodes/class-ewa-product-carousel.php';
  require 'classes/shortcodes/class-ewa-carousel.php';
  require 'classes/shortcodes/class-ewa-all-authors.php';
  require 'classes/shortcodes/class-ewa-az-listing.php';
  require 'classes/shortcodes/class-ewa-author.php';
  require 'classes/class-everexpert-woocommerce-authors.php';
  require 'classes/class-ewa-api-support.php';
  new EWA_API_Support();
  require 'classes/admin/class-ewa-coupon.php';
  new Admin\EWA_Coupon();

  if (is_admin()) {
    require 'classes/admin/class-ewa-suggestions.php';
    new Admin\EWA_Suggestions();
    require 'classes/admin/class-ewa-notices.php';
    new Admin\EWA_Notices();
    require 'classes/admin/class-ewa-system-status.php';
    new Admin\EWA_System_Status();
    require 'classes/admin/class-ewa-admin-tab.php';
    require 'classes/admin/class-ewa-migrate.php';
    new Admin\EWA_Migrate();
    require 'classes/admin/class-ewa-dummy-data.php';
    new Admin\EWA_Dummy_Data();
    require 'classes/admin/class-edit-authors-page.php';
    new Admin\Edit_Authors_Page();
    require 'classes/admin/class-authors-custom-fields.php';
    new Admin\Authors_Custom_Fields();
    require 'classes/admin/class-authors-exporter.php';
    new Admin\Authors_Exporter();
    require 'classes/admin/class-ewa-importer-support.php';
    new EWA_Importer_Support();
    require 'classes/admin/class-ewa-exporter-support.php';
    new EWA_Exporter_Support();
  } else {
    include_once 'classes/class-ewa-product-tab.php';
    new EWA_Product_Tab();
  }

  new \Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors();
} elseif (is_admin()) {

  add_action('admin_notices', function() {
    $message = esc_html__('Everexpert WooCommerce Authors needs WooCommerce to run. Please, install and active WooCommerce plugin.', 'everexpert-woocommerce-authors');
    printf('<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', $message);
  });
}
