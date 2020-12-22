<?php

namespace Everexpert_Woocommerce_Authors\Admin;

use WC_Admin_Settings,
  WC_Settings_Page;

defined('ABSPATH') or die('No script kiddies please!');

class Pwb_Admin_Tab
{

  public function __construct()
  {

    $this->id = 'ewa_admin_tab';
    $this->label = __('Authors', 'everexpert-woocommerce-authors');

    add_filter('woocommerce_settings_tabs_array', [$this, 'add_tab'], 200);
    add_action('woocommerce_settings_' . $this->id, [$this, 'output']);
    add_action('woocommerce_sections_' . $this->id, [$this, 'output_sections']);
    add_action('woocommerce_settings_save_' . $this->id, [$this, 'save']);
  }

  public function add_tab($settings_tabs)
  {

    $settings_tabs[$this->id] = $this->label;

    return $settings_tabs;
  }

  public function get_sections()
  {

    $sections = array(
      '' => __('General', 'everexpert-woocommerce-authors'),
      'author-pages' => __('Archives', 'everexpert-woocommerce-authors'),
      'single-product' => __('Products', 'everexpert-woocommerce-authors'),
      'tools' => __('Tools', 'everexpert-woocommerce-authors'),
    );

    return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
  }

  public function output_sections()
  {
    global $current_section;

    $sections = $this->get_sections();

    if (empty($sections) || 1 === sizeof($sections)) {
      return;
    }

    echo '<ul class="subsubsub">';

    $array_keys = array_keys($sections);

    foreach ($sections as $id => $label) {
      echo '<li><a href="' . admin_url('admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title($id)) . '" class="' . ($current_section == $id ? 'current' : '') . '">' . $label . '</a> ' . (end($array_keys) == $id ? '' : '|') . ' </li>';
    }

    echo ' | <li><a target="_blank" href="' . admin_url('edit-tags.php?taxonomy=ewa-author&post_type=product') . '">' . __('Authors', 'everexpert-woocommerce-authors') . '</a></li>';
    echo ' | <li><a target="_blank" href="' . admin_url('admin.php?page=ewa_suggestions') . '">' . __('Suggestions', 'everexpert-woocommerce-authors') . '</a></li>';
    echo ' | <li><a target="_blank" href="' . EWA_DOCUMENTATION_URL . '">' . __('Documentation', 'everexpert-woocommerce-authors') . '</a></li>';

    echo '</ul><br class="clear" />';
  }

  public function get_settings($current_section = '')
  {

    $available_image_sizes_adapted = array();
    $available_image_sizes = get_intermediate_image_sizes();
    foreach ($available_image_sizes as $image_size)
      $available_image_sizes_adapted[$image_size] = $image_size;
    $available_image_sizes_adapted['full'] = 'full';

    $pages_select_adapted = array('-' => '-');
    $pages_select = get_pages();
    foreach ($pages_select as $page)
      $pages_select_adapted[$page->ID] = $page->post_title;

    if ('single-product' == $current_section) {

      $settings = apply_filters('wc_ewa_admin_tab_settings', array(
        'section_title' => array(
          'name' => __('Products', 'everexpert-woocommerce-authors'),
          'type' => 'title',
          'desc' => '',
          'id' => 'wc_ewa_admin_tab_section_title'
        ),
        'author_single_product_tab' => array(
          'name' => __('Products tab', 'everexpert-woocommerce-authors'),
          'type' => 'checkbox',
          'default' => 'yes',
          'desc' => __('Show author tab in single product page', 'everexpert-woocommerce-authors'),
          'id' => 'wc_ewa_admin_tab_author_single_product_tab'
        ),
        'show_author_in_single' => array(
          'name' => __('Show authors in single product', 'everexpert-woocommerce-authors'),
          'type' => 'select',
          'class' => 'ewa-admin-tab-field',
          'desc' => __('Show author logo (or name) in single product', 'everexpert-woocommerce-authors'),
          'default' => 'author_image',
          'id' => 'wc_ewa_admin_tab_authors_in_single',
          'options' => array(
            'no' => __('No', 'everexpert-woocommerce-authors'),
            'author_link' => __('Show author link', 'everexpert-woocommerce-authors'),
            'author_image' => __('Show author image (if is set)', 'everexpert-woocommerce-authors')
          )
        ),
        'author_single_position' => array(
          'name' => __('Author position', 'everexpert-woocommerce-authors'),
          'type' => 'select',
          'class' => 'ewa-admin-tab-field',
          'desc' => __('For single product', 'everexpert-woocommerce-authors'),
          'id' => 'wc_ewa_admin_tab_author_single_position',
          'options' => array(
            'before_title' => __('Before title', 'everexpert-woocommerce-authors'),
            'after_title' => __('After title', 'everexpert-woocommerce-authors'),
            'after_price' => __('After price', 'everexpert-woocommerce-authors'),
            'after_excerpt' => __('After excerpt', 'everexpert-woocommerce-authors'),
            'after_add_to_cart' => __('After add to cart', 'everexpert-woocommerce-authors'),
            'meta' => __('In meta', 'everexpert-woocommerce-authors'),
            'after_meta' => __('After meta', 'everexpert-woocommerce-authors'),
            'after_sharing' => __('After sharing', 'everexpert-woocommerce-authors')
          )
        ),
        'section_end' => array(
          'type' => 'sectionend',
          'id' => 'wc_ewa_admin_tab_section_end'
        )
      ));
    } elseif ('author-pages' == $current_section) {

      $settings = apply_filters('wc_ewa_admin_tab_author_pages_settings', array(
        'section_title' => array(
          'name' => __('Archives', 'everexpert-woocommerce-authors'),
          'type' => 'title',
          'desc' => '',
          'id' => 'wc_ewa_admin_tab_section_title'
        ),
        'author_description' => array(
          'name' => __('Show author description', 'everexpert-woocommerce-authors'),
          'type' => 'select',
          'class' => 'ewa-admin-tab-field',
          'default' => 'yes',
          'desc' => __('Show author description (if is set) on author archive page', 'everexpert-woocommerce-authors'),
          'id' => 'wc_ewa_admin_tab_author_desc',
          'options' => array(
            'yes' => __('Yes, before product loop', 'everexpert-woocommerce-authors'),
            'yes_after_loop' => __('Yes, after product loop', 'everexpert-woocommerce-authors'),
            'no' => __('No, hide description', 'everexpert-woocommerce-authors')
          )
        ),
        'author_banner' => array(
          'name' => __('Show author banner', 'everexpert-woocommerce-authors'),
          'type' => 'select',
          'class' => 'ewa-admin-tab-field',
          'default' => 'yes',
          'desc' => __('Show author banner (if is set) on author archive page', 'everexpert-woocommerce-authors'),
          'id' => 'wc_ewa_admin_tab_author_banner',
          'options' => array(
            'yes' => __('Yes, before product loop', 'everexpert-woocommerce-authors'),
            'yes_after_loop' => __('Yes, after product loop', 'everexpert-woocommerce-authors'),
            'no' => __('No, hide banner', 'everexpert-woocommerce-authors')
          )
        ),
        'show_author_on_loop' => array(
          'name' => __('Show authors in loop', 'everexpert-woocommerce-authors'),
          'type' => 'select',
          'class' => 'ewa-admin-tab-field',
          'desc' => __('Show author logo (or name) in product loop', 'everexpert-woocommerce-authors'),
          'id' => 'wc_ewa_admin_tab_authors_in_loop',
          'options' => array(
            'no' => __('No', 'everexpert-woocommerce-authors'),
            'author_link' => __('Show author link', 'everexpert-woocommerce-authors'),
            'author_image' => __('Show author image (if is set)', 'everexpert-woocommerce-authors')
          )
        ),
        'section_end' => array(
          'type' => 'sectionend',
          'id' => 'wc_ewa_admin_tab_section_end'
        )
      ));
    } elseif ('tools' == $current_section) {

      $settings = apply_filters('wc_ewa_admin_tab_tools_settings', array(
        'section_title' => array(
          'name' => __('Tools', 'everexpert-woocommerce-authors'),
          'type' => 'title',
          'desc' => '',
          'id' => 'wc_ewa_admin_tab_section_tools_title'
        ),
        'author_import' => array(
          'name' => __('Import authors', 'everexpert-woocommerce-authors'),
          'type' => 'select',
          'class' => 'ewa-admin-tab-field',
          'desc' => sprintf(
            __('Import authors from other author plugin. <a href="%s" target="_blank">Click here for more details</a>', 'everexpert-woocommerce-authors'),
            str_replace('/?', '/authors/?', EWA_DOCUMENTATION_URL)
          ),
          'id' => 'wc_ewa_admin_tab_tools_migrate',
          'options' => array(
            '-' => __('-', 'everexpert-woocommerce-authors'),
            'yith' => __('YITH WooCommerce Authors Add-On', 'everexpert-woocommerce-authors'),
            'ultimate' => __('Ultimate WooCommerce Authors', 'everexpert-woocommerce-authors'),
            'wooauthors' => __('Offical WooCommerce Authors', 'everexpert-woocommerce-authors')
          )
        ),
        'author_dummy_data' => array(
          'name' => __('Dummy data', 'everexpert-woocommerce-authors'),
          'type' => 'select',
          'class' => 'ewa-admin-tab-field',
          'desc' => __('Import generic authors and assign it to products randomly', 'everexpert-woocommerce-authors'),
          'id' => 'wc_ewa_admin_tab_tools_dummy_data',
          'options' => array(
            '-' => __('-', 'everexpert-woocommerce-authors'),
            'start_import' => __('Start import', 'everexpert-woocommerce-authors')
          )
        ),
        'authors_system_status' => array(
          'name' => __('System status', 'everexpert-woocommerce-authors'),
          'type' => 'textarea',
          'desc' => __('Show system status', 'everexpert-woocommerce-authors'),
          'id' => 'wc_ewa_admin_tab_tools_system_status'
        ),
        'section_end' => array(
          'type' => 'sectionend',
          'id' => 'wc_ewa_admin_tab_section_tools_end'
        )
      ));
    } else {

      $authors_url = get_option('wc_ewa_admin_tab_slug', __('authors', 'everexpert-woocommerce-authors')) . '/' . __('author-name', 'everexpert-woocommerce-authors') . '/';

      $settings = apply_filters('wc_ewa_admin_tab_product_settings', array(
        'section_title' => array(
          'name' => __('General', 'everexpert-woocommerce-authors'),
          'type' => 'title',
          'desc' => '',
          'id' => 'wc_ewa_admin_tab_section_title'
        ),
        'slug' => array(
          'name' => __('Slug', 'everexpert-woocommerce-authors'),
          'type' => 'text',
          'class' => 'ewa-admin-tab-field',
          'desc' => __('Authors taxonomy slug', 'everexpert-woocommerce-authors'),
          'desc_tip' => sprintf(
            __('Your authors URLs will look like "%s"', 'everexpert-woocommerce-authors'),
            'https://site.com/' . $authors_url
          ),
          'id' => 'wc_ewa_admin_tab_slug',
          'placeholder' => get_taxonomy('ewa-author')->rewrite['slug']
        ),
        'author_logo_size' => array(
          'name' => __('Author logo size', 'everexpert-woocommerce-authors'),
          'type' => 'select',
          'class' => 'ewa-admin-tab-field',
          'desc' => __('Select the size for the author logo image around the site', 'everexpert-woocommerce-authors'),
          'desc_tip' => __('The default image sizes can be configured under "Settings > Media". You can also define your own image sizes', 'everexpert-woocommerce-authors'),
          'id' => 'wc_ewa_admin_tab_author_logo_size',
          'options' => $available_image_sizes_adapted
        ),
        'authors_page_id' => array(
          'name' => __('Authors page', 'everexpert-woocommerce-authors'),
          'type' => 'select',
          'class' => 'ewa-admin-tab-field ewa-admin-selectwoo',
          'desc' => __('For linking breadcrumbs', 'everexpert-woocommerce-authors'),
          'desc_tip' => __('Select your "Authors" page (if you have one), it will be linked in the breadcrumbs.', 'everexpert-woocommerce-authors'),
          'id' => 'wc_ewa_admin_tab_authors_page_id',
          'options' => $pages_select_adapted
        ),
        'section_end' => array(
          'type' => 'sectionend',
          'id' => 'wc_ewa_admin_tab_section_end'
        )
      ));
    }

    return apply_filters('woocommerce_get_settings_' . $this->id, $settings, $current_section);
  }

  public function output()
  {

    global $current_section;

    $settings = $this->get_settings($current_section);
    WC_Admin_Settings::output_fields($settings);
  }

  public function save()
  {

    update_option('old_wc_ewa_admin_tab_slug', get_taxonomy('ewa-author')->rewrite['slug']);
    if (isset($_POST['wc_ewa_admin_tab_slug'])) {
      $_POST['wc_ewa_admin_tab_slug'] = sanitize_title($_POST['wc_ewa_admin_tab_slug']);
    }

    global $current_section;

    $settings = $this->get_settings($current_section);
    WC_Admin_Settings::save_fields($settings);
  }
}

return new Pwb_Admin_Tab();
