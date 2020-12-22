<?php

namespace Everexpert_Woocommerce_Authors;

defined('ABSPATH') or die('No script kiddies please!');

class Everexpert_Woocommerce_Authors
{

  function __construct()
  {
    add_action('plugin_row_meta', array('\Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors', 'plugin_row_meta'), 10, 2);
    add_action('woocommerce_init', array($this, 'register_authors_taxonomy'), 10, 0);
    add_action('init', array($this, 'add_authors_metafields'));
    add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    $this->author_logo_position();
    add_action('wp', array($this, 'author_desc_position'));
    add_action('woocommerce_after_shop_loop_item_title', array($this, 'show_authors_in_loop'));
    $this->add_shortcodes();
    if (is_plugin_active('js_composer/js_composer.php') || is_plugin_active('visual_composer/js_composer.php')) {
      add_action('vc_before_init', array($this, 'vc_map_shortcodes'));
    }
    add_action('widgets_init', array($this, 'register_widgets'));
    add_filter('woocommerce_structured_data_product', array($this, 'product_microdata'), 10, 2);
    add_action('pre_get_posts', array($this, 'ewa_author_filter'));
    add_action('wp_ajax_dismiss_ewa_notice', array($this, 'dismiss_ewa_notice'));
    add_action('admin_notices', array($this, 'review_notice'));

    add_action('wp', function () {
      if (is_tax('ewa-author'))
        remove_action('woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);
    });
    add_action('woocommerce_product_duplicate', array($this, 'product_duplicate_save'), 10, 2);

    add_filter('woocommerce_get_breadcrumb', array($this, 'breadcrumbs'));

    add_filter('shortcode_atts_products', array($this, 'extend_products_shortcode_atts'), 10, 4);
    add_filter('woocommerce_shortcode_products_query', array($this, 'extend_products_shortcode'), 10, 2);

    add_filter('manage_edit-product_sortable_columns', array($this, 'authors_column_sortable'), 90);
    add_action('posts_clauses', array($this, 'authors_column_sortable_posts'), 10, 2);
    add_filter('post_type_link', array($this, 'author_name_in_url'), 10, 2);
    add_action('pre_get_posts', array($this, 'search_by_author_name'));

    //clean caches
    add_action('edited_terms', array($this, 'clean_caches'), 10, 2);
    add_action('created_term', array($this, 'clean_caches_after_edit_author'), 10, 3);
    add_action('delete_term', array($this, 'clean_caches_after_edit_author'), 10, 3);
  }

  public function clean_caches($term_id, $taxonomy)
  {
    if ($taxonomy != 'ewa-author')
      return;
    delete_transient('ewa_az_listing_cache');
  }

  public function clean_caches_after_edit_author($term_id, $tt_id, $taxonomy)
  {
    if ($taxonomy != 'ewa-author')
      return;
    delete_transient('ewa_az_listing_cache');
  }

  /**
   * Show row meta on the plugin screen.
   *
   * @param mixed $links Plugin Row Meta.
   * @param mixed $file  Plugin Base file.
   *
   * @return array
   */
  public static function plugin_row_meta($links, $file)
  {
    if (EWA_PLUGIN_BASENAME === $file) {
      $row_meta = array(
        'docs' => '<a target="_blank" rel="noopener noferrer" href="' . EWA_DOCUMENTATION_URL . '">' . esc_html__('Documentation', 'everexpert-woocommerce-authors') . '</a>',
      );
      return array_merge($links, $row_meta);
    }
    return (array) $links;
  }

  public function author_name_in_url($permalink, $post)
  {
    if ($post->post_type == 'product' && strpos($permalink, '%ewa-author%') !== false) {
      $term = 'product';
      $authors = wp_get_post_terms($post->ID, 'ewa-author');
      if (!empty($authors) && !is_wp_error($authors))
        $term = current($authors)->slug;
      $permalink = str_replace('%ewa-author%', $term, $permalink);
    }
    return $permalink;
  }

  public function authors_column_sortable_posts($clauses, $wp_query)
  {
    global $wpdb;

    if (isset($wp_query->query['orderby']) && 'taxonomy-ewa-author' == $wp_query->query['orderby']) {

      $clauses['join'] .= "
      LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
      LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
      LEFT OUTER JOIN {$wpdb->terms} USING (term_id)";

      $clauses['where'] .= " AND (taxonomy = 'ewa-author' OR taxonomy IS NULL)";
      $clauses['groupby'] = "object_id";
      $clauses['orderby'] = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC) ";
      $clauses['orderby'] .= ('ASC' == strtoupper($wp_query->get('order'))) ? 'ASC' : 'DESC';
    }

    return $clauses;
  }

  public function authors_column_sortable($columns)
  {
    $columns['taxonomy-ewa-author'] = 'taxonomy-ewa-author';
    return $columns;
  }

  public function extend_products_shortcode_atts($out, $pairs, $atts, $shortcode)
  {
    if (!empty($atts['authors']))
      $out['authors'] = explode(',', $atts['authors']);
    return $out;
  }

  public function extend_products_shortcode($query_args, $atts)
  {

    if (!empty($atts['authors'])) {
      global $wpdb;

      $terms = $atts['authors'];
      $terms_count = count($atts['authors']);
      $terms_adapted = '';

      $terms_i = 0;
      foreach ($terms as $author) {
        $terms_adapted .= '"' . $author . '"';
        $terms_i++;
        if ($terms_i < $terms_count)
          $terms_adapted .= ',';
      }

      $ids = $wpdb->get_col("
      SELECT DISTINCT tr.object_id
      FROM {$wpdb->prefix}term_relationships as tr
      INNER JOIN {$wpdb->prefix}term_taxonomy as tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
      INNER JOIN {$wpdb->prefix}terms as t ON tt.term_id = t.term_id
      WHERE tt.taxonomy LIKE 'ewa_author' AND t.slug IN ($terms_adapted)
      ");

      if (!empty($ids)) {
        if (1 === count($ids)) {
          $query_args['p'] = $ids[0];
        } else {
          $query_args['post__in'] = $ids;
        }
      }
    }

    return $query_args;
  }

  public function review_notice()
  {
    $show_notice = get_option('wc_ewa_notice_plugin_review', true);
    $activate_on = get_option('ewa_activate_on', time());
    $now = time();
    $one_week = 604800;
    $date_diff = $now - $activate_on;

    if ($show_notice && $date_diff > $one_week) {
?>
      <div class="notice notice-info ewa-notice-dismissible is-dismissible" data-notice="wc_ewa_notice_plugin_review">
        <p><?php echo esc_html__('We know that you´re in love with Everexpert WooCommerce Authors, you can help us making it a bit better. Thanks a lot!', 'everexpert-woocommerce-authors'); ?><span class="dashicons dashicons-heart"></span></p>
        <p>
          <a href="https://wordpress.org/support/plugin/everexpert-woocommerce-authors/reviews/?rate=5#new-post" target="_blank"><?php esc_html_e('Leave a review', 'everexpert-woocommerce-authors'); ?></a>
          <a href="https://translate.wordpress.org/projects/wp-plugins/everexpert-woocommerce-authors" target="_blank"><?php esc_html_e('Translate the plugin', 'everexpert-woocommerce-authors'); ?></a>
          <a href="<?php echo esc_url(EWA_GITHUB_URL); ?>" target="_blank"><?php esc_html_e('View on GitHub', 'everexpert-woocommerce-authors'); ?></a>
        </p>
      </div>
<?php
    }
  }

  public function dismiss_ewa_notice()
  {
    $notice_name_whitelist = array('wc_ewa_notice_plugin_review');
    if (isset($_POST['notice_name']) && in_array($_POST['notice_name'], $notice_name_whitelist)) {
      update_option($_POST['notice_name'], 0);
      echo 'ok';
    } else {
      echo 'error';
    }
    wp_die();
  }

  public function ewa_author_filter($query)
  {

    if (!empty($_GET['ewa-author-filter'])) {

      $terms_array = explode(',', $_GET['ewa-author-filter']);

      //remove invalid terms (security)
      for ($i = 0; $i < count($terms_array); $i++) {
        if (!term_exists($terms_array[$i], 'ewa-author'))
          unset($terms_array[$i]);
      }

      $filterable_product = false;
      if (is_product_taxonomy() || is_post_type_archive('product'))
        $filterable_product = true;

      if ($filterable_product && $query->is_main_query()) {

        $query->set('tax_query', array(
          array(
            'taxonomy' => 'ewa-author',
            'field' => 'slug',
            'terms' => $terms_array
          )
        ));
      }
    }
  }

  /*
   *   Adds microdata (authors) to single products
   */

  public function product_microdata($markup, $product)
  {

    $new_markup = array();
    $authors = wp_get_post_terms($product->get_id(), 'ewa-author');
    foreach ($authors as $author) {
      $new_markup['author'][] = $author->name;
    }

    return array_merge($markup, $new_markup);
  }

  public function add_shortcodes()
  {
    add_shortcode('ewa-carousel', array(
      '\Everexpert_Woocommerce_Authors\Shortcodes\EWA_Carousel_Shortcode',
      'carousel_shortcode'
    ));
    add_shortcode('ewa-product-carousel', array(
      '\Everexpert_Woocommerce_Authors\Shortcodes\EWA_Product_Carousel_Shortcode',
      'product_carousel_shortcode'
    ));
    add_shortcode('ewa-all-authors', array(
      '\Everexpert_Woocommerce_Authors\Shortcodes\EWA_All_authors_Shortcode',
      'all_authors_shortcode'
    ));
    add_shortcode('ewa-az-listing', array(
      '\Everexpert_Woocommerce_Authors\Shortcodes\EWA_AZ_Listing_Shortcode',
      'shortcode'
    ));
    add_shortcode('ewa-author', array(
      '\Everexpert_Woocommerce_Authors\Shortcodes\EWA_author_Shortcode',
      'author_shortcode'
    ));
  }

  public function register_widgets()
  {
    register_widget('\Everexpert_Woocommerce_Authors\Widgets\EWA_List_Widget');
    register_widget('\Everexpert_Woocommerce_Authors\Widgets\EWA_Dropdown_Widget');
    register_widget('\Everexpert_Woocommerce_Authors\Widgets\EWA_Filter_By_author_Widget');
  }

  public function show_authors_in_loop()
  {

    $authors_in_loop = get_option('wc_ewa_admin_tab_authors_in_loop');
    $image_size_selected = get_option('wc_ewa_admin_tab_author_logo_size', 'thumbnail');

    if ($authors_in_loop == 'author_link' || $authors_in_loop == 'author_image') {

      global $product;
      $product_id = $product->get_id();
      $product_authors = wp_get_post_terms($product_id, 'ewa-author');
      if (!empty($product_authors)) {
        echo '<div class="ewa-authors-in-loop">';
        foreach ($product_authors as $author) {

          echo '<span>';
          $author_link = get_term_link($author->term_id, 'ewa-author');
          $attachment_id = get_term_meta($author->term_id, 'ewa_author_image', 1);

          $attachment_html = wp_get_attachment_image($attachment_id, $image_size_selected);
          if (!empty($attachment_html) && $authors_in_loop == 'author_image') {
            echo '<a href="' . $author_link . '">' . $attachment_html . '</a>';
          } else {
            echo '<a href="' . $author_link . '">' . $author->name . '</a>';
          }
          echo '</span>';
        }
        echo '</div>';
      }
    }
  }

  /**
   * woocommerce_single_product_summary hook.
   *
   * @hooked woocommerce_template_single_title - 5
   * @hooked woocommerce_template_single_rating - 10
   * @hooked woocommerce_template_single_price - 10
   * @hooked woocommerce_template_single_excerpt - 20
   * @hooked woocommerce_template_single_add_to_cart - 30
   * @hooked woocommerce_template_single_meta - 40
   * @hooked woocommerce_template_single_sharing - 50
   */
  private function author_logo_position()
  {
    $position = 41;
    $position_selected = get_option('wc_ewa_admin_tab_author_single_position');
    if (!$position_selected) {
      update_option('wc_ewa_admin_tab_author_single_position', 'after_meta');
    }

    switch ($position_selected) {
      case 'before_title':
        $position = 4;
        break;
      case 'after_title':
        $position = 6;
        break;
      case 'after_price':
        $position = 11;
        break;
      case 'after_excerpt':
        $position = 21;
        break;
      case 'after_add_to_cart':
        $position = 31;
        break;
      case 'after_meta':
        $position = 41;
        break;
      case 'after_sharing':
        $position = 51;
        break;
    }

    if ($position_selected == 'meta') {
      add_action('woocommerce_product_meta_end', [$this, 'action_woocommerce_single_product_summary']);
    } else {
      add_action('woocommerce_single_product_summary', [$this, 'action_woocommerce_single_product_summary'], $position);
    }
  }

  public function author_desc_position()
  {

    if (is_tax('ewa-author') && !is_paged()) {

      $show_banner = get_option('wc_ewa_admin_tab_author_banner');
      $show_desc = get_option('wc_ewa_admin_tab_author_desc');

      if ((!$show_banner || $show_banner == 'yes') && (!$show_desc || $show_desc == 'yes')) {
        //show banner and description before loop
        add_action('woocommerce_archive_description', array($this, 'print_author_banner_and_desc'), 15);
      } elseif ($show_banner == 'yes_after_loop' && $show_desc == 'yes_after_loop') {
        //show banner and description after loop
        add_action('woocommerce_after_main_content', array($this, 'print_author_banner_and_desc'), 9);
      } else {
        //show banner and description independently

        if (!$show_banner || $show_banner == 'yes') {
          add_action('woocommerce_archive_description', array($this, 'print_author_banner'), 15);
        } elseif ($show_banner == 'yes_after_loop') {
          add_action('woocommerce_after_main_content', array($this, 'print_author_banner'), 9);
        }

        if (!$show_desc || $show_desc == 'yes') {
          add_action('woocommerce_archive_description', array($this, 'print_author_desc'), 15);
        } elseif ($show_desc == 'yes_after_loop') {
          add_action('woocommerce_after_main_content', array($this, 'print_author_desc'), 9);
        }
      }
    }
  }

  /*
   * Maps shortcode (for visual composer plugin)
   *
   * @since 1.0
   * @link https://vc.wpbakery.com/
   * @return mixed
   */

  public function vc_map_shortcodes()
  {
    $available_image_sizes_adapted = array();
    $available_image_sizes = get_intermediate_image_sizes();

    foreach ($available_image_sizes as $image_size) {
      $available_image_sizes_adapted[$image_size] = $image_size;
    }

    vc_map(array(
      "name" => __("EWA Product carousel", "everexpert-woocommerce-authors"),
      "description" => __("Product carousel by author or by category", "everexpert-woocommerce-authors"),
      "base" => "ewa-product-carousel",
      "class" => "",
      "icon" => EWA_PLUGIN_URL . '/assets/img/icon_ewa.jpg',
      "category" => "WooCommerce",
      "params" => array(
        array(
          "type" => "dropdown",
          "heading" => __("Author", "everexpert-woocommerce-authors"),
          "param_name" => "author",
          "admin_label" => true,
          "value" => self::get_authors_array(true)
        ),
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Products", "everexpert-woocommerce-authors"),
          "param_name" => "products",
          "value" => "10",
          "description" => __("Number of products to load", "everexpert-woocommerce-authors")
        ),
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Products to show", "everexpert-woocommerce-authors"),
          "param_name" => "products_to_show",
          "value" => "5",
          "description" => __("Number of products to show", "everexpert-woocommerce-authors")
        ),
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Products to scroll", "everexpert-woocommerce-authors"),
          "param_name" => "products_to_scroll",
          "value" => "1",
          "description" => __("Number of products to scroll", "everexpert-woocommerce-authors")
        ),
        array(
          "type" => "checkbox",
          "holder" => "div",
          "heading" => __("Autoplay", "everexpert-woocommerce-authors"),
          "param_name" => "autoplay",
          "description" => __("Autoplay carousel", "everexpert-woocommerce-authors")
        ),
        array(
          "type" => "checkbox",
          "holder" => "div",
          "heading" => __("Arrows", "everexpert-woocommerce-authors"),
          "param_name" => "arrows",
          "description" => __("Display prev and next arrows", "everexpert-woocommerce-authors")
        )
      )
    ));

    vc_map(array(
      "name" => __("EWA Authors carousel", "everexpert-woocommerce-authors"),
      "description" => __("Authors carousel", "everexpert-woocommerce-authors"),
      "base" => "ewa-carousel",
      "class" => "",
      "icon" => EWA_PLUGIN_URL . '/assets/img/icon_ewa.jpg',
      "category" => "WooCommerce",
      "params" => array(
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Items", "everexpert-woocommerce-authors"),
          "param_name" => "items",
          "value" => "10",
          "description" => __("Number of items to load (or 'featured')", "everexpert-woocommerce-authors")
        ),
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Items to show", "everexpert-woocommerce-authors"),
          "param_name" => "items_to_show",
          "value" => "5",
          "description" => __("Number of items to show", "everexpert-woocommerce-authors")
        ),
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Items to scroll", "everexpert-woocommerce-authors"),
          "param_name" => "items_to_scroll",
          "value" => "1",
          "description" => __("Number of items to scroll", "everexpert-woocommerce-authors")
        ),
        array(
          "type" => "checkbox",
          "holder" => "div",
          "heading" => __("Autoplay", "everexpert-woocommerce-authors"),
          "param_name" => "autoplay",
          "description" => __("Autoplay carousel", "everexpert-woocommerce-authors")
        ),
        array(
          "type" => "checkbox",
          "holder" => "div",
          "heading" => __("Arrows", "everexpert-woocommerce-authors"),
          "param_name" => "arrows",
          "description" => __("Display prev and next arrows", "everexpert-woocommerce-authors")
        ),
        array(
          "type" => "dropdown",
          "heading" => __("Author logo size", "everexpert-woocommerce-authors"),
          "param_name" => "image_size",
          "admin_label" => true,
          "value" => $available_image_sizes_adapted
        )
      )
    ));

    vc_map(array(
      "name" => __("EWA All authors", "everexpert-woocommerce-authors"),
      "description" => __("Show all authors", "everexpert-woocommerce-authors"),
      "base" => "ewa-all-authors",
      "class" => "",
      "icon" => EWA_PLUGIN_URL . '/assets/img/icon_ewa.jpg',
      "category" => "WooCommerce",
      "params" => array(
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Authors per page", "everexpert-woocommerce-authors"),
          "param_name" => "per_page",
          "value" => "10",
          "description" => __("Show x authors per page", "everexpert-woocommerce-authors")
        ),
        array(
          "type" => "dropdown",
          "heading" => __("Author logo size", "everexpert-woocommerce-authors"),
          "param_name" => "image_size",
          "admin_label" => true,
          "value" => $available_image_sizes_adapted
        ),
        array(
          "type" => "dropdown",
          "heading" => __("Order by", "everexpert-woocommerce-authors"),
          "param_name" => "order_by",
          "admin_label" => true,
          "value" => array(
            'name' => 'name',
            'slug' => 'slug',
            'term_id' => 'term_id',
            'id' => 'id',
            'description' => 'description',
            'rand' => 'rand'
          )
        ),
        array(
          "type" => "dropdown",
          "heading" => __("Order", "everexpert-woocommerce-authors"),
          "param_name" => "order",
          "admin_label" => true,
          "value" => array(
            'ASC' => 'ASC',
            'DSC' => 'DSC'
          )
        ),
        array(
          "type" => "dropdown",
          "heading" => __("Title position", "everexpert-woocommerce-authors"),
          "param_name" => "title_position",
          "admin_label" => true,
          "value" => array(
            __("Before image", "everexpert-woocommerce-authors") => 'before',
            __("After image", "everexpert-woocommerce-authors") => 'after',
            __("Hide", "everexpert-woocommerce-authors") => 'none'
          )
        ),
        array(
          "type" => "checkbox",
          "holder" => "div",
          "heading" => __("Hide empty", "everexpert-woocommerce-authors"),
          "param_name" => "hide_empty",
          "description" => __("Hide authors that have not been assigned to any product", "everexpert-woocommerce-authors")
        )
      )
    ));

    vc_map(array(
      "name" => __("EWA AZ Listing", "everexpert-woocommerce-authors"),
      "description" => __("AZ Listing for authors", "everexpert-woocommerce-authors"),
      "base" => "ewa-az-listing",
      "class" => "",
      "icon" => EWA_PLUGIN_URL . '/assets/img/icon_ewa.jpg',
      "category" => "WooCommerce",
      "params" => array(
        array(
          "type" => "dropdown",
          "heading" => __("Only parent authors", "everexpert-woocommerce-authors"),
          "param_name" => "only_parents",
          "admin_label" => true,
          "value" => array(esc_html__('No') => 'no', esc_html__('Yes') => 'yes'),
        )
      )
    ));

    vc_map(array(
      "name" => __("EWA author", "everexpert-woocommerce-authors"),
      "description" => __("Show author for a specific product", "everexpert-woocommerce-authors"),
      "base" => "ewa-author",
      "class" => "",
      "icon" => EWA_PLUGIN_URL . '/assets/img/icon_ewa.jpg',
      "category" => "WooCommerce",
      "params" => array(
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Product id", "everexpert-woocommerce-authors"),
          "param_name" => "product_id",
          "value" => null,
          "description" => __("Product id (post id)", "everexpert-woocommerce-authors")
        ),
        array(
          "type" => "dropdown",
          "heading" => __("Author logo size", "everexpert-woocommerce-authors"),
          "param_name" => "image_size",
          "admin_label" => true,
          "value" => $available_image_sizes_adapted
        )
      )
    ));
  }

  public function action_woocommerce_single_product_summary()
  {
    $authors = wp_get_post_terms(get_the_ID(), 'ewa-author');

    if (!is_wp_error($authors)) {

      if (sizeof($authors) > 0) {

        $show_as = get_option('wc_ewa_admin_tab_authors_in_single');

        if ($show_as != 'no') {

          do_action('ewa_before_single_product_authors', $authors);

          echo '<div class="ewa-single-product-authors ewa-clearfix">';

          if ($show_as == 'author_link') {
            $before_authors_links = '<span class="ewa-text-before-authors-links">';
            $before_authors_links .= apply_filters('ewa_text_before_authors_links', esc_html__('Authors', 'everexpert-woocommerce-authors'));
            $before_authors_links .= ':</span>';
            echo apply_filters('ewa_html_before_authors_links', $before_authors_links);
          }

          foreach ($authors as $author) {
            $author_link = get_term_link($author->term_id, 'ewa-author');
            $attachment_id = get_term_meta($author->term_id, 'ewa_author_image', 1);

            $image_size = 'thumbnail';
            $image_size_selected = get_option('wc_ewa_admin_tab_author_logo_size', 'thumbnail');
            if ($image_size_selected != false) {
              $image_size = $image_size_selected;
            }

            $attachment_html = wp_get_attachment_image($attachment_id, $image_size);

            if (!empty($attachment_html) && $show_as == 'author_image' || !empty($attachment_html) && !$show_as) {
              echo '<a href="' . $author_link . '" title="' . $author->name . '">' . $attachment_html . '</a>';
            } else {
              echo '<a href="' . $author_link . '" title="' . esc_html__('View author', 'everexpert-woocommerce-authors') . '">' . $author->name . '</a>';
            }
          }
          echo '</div>';

          do_action('ewa_after_single_product_authors', $authors);
        }
      }
    }
  }

  public function enqueue_scripts()
  {

    wp_register_script(
      'ewa-lib-slick',
      EWA_PLUGIN_URL . '/assets/lib/slick/slick.min.js',
      array('jquery'),
      '1.8.0',
      false
    );

    wp_register_style(
      'ewa-lib-slick',
      EWA_PLUGIN_URL . '/assets/lib/slick/slick.css',
      array(),
      '1.8.0',
      'all'
    );

    wp_enqueue_style(
      'ewa-styles-frontend',
      EWA_PLUGIN_URL . '/assets/css/styles-frontend.min.css',
      array(),
      EWA_PLUGIN_VERSION,
      'all'
    );

    wp_register_script(
      'ewa-functions-frontend',
      EWA_PLUGIN_URL . '/assets/js/functions-frontend.min.js',
      array('jquery'),
      EWA_PLUGIN_VERSION,
      true
    );

    wp_localize_script('ewa-functions-frontend', 'ewa_ajax_object', array(
      'carousel_prev' => apply_filters('ewa_carousel_prev', '&lt;'),
      'carousel_next' => apply_filters('ewa_carousel_next', '&gt;')
    ));

    wp_enqueue_script('ewa-functions-frontend');
  }

  public function admin_enqueue_scripts($hook)
  {
    $screen = get_current_screen();
    if ($hook == 'edit-tags.php' && $screen->taxonomy == 'ewa-author' || $hook == 'term.php' && $screen->taxonomy == 'ewa-author') {
      wp_enqueue_media();
    }

    wp_enqueue_style('ewa-styles-admin', EWA_PLUGIN_URL . '/assets/css/styles-admin.min.css', array(), EWA_PLUGIN_VERSION);

    wp_register_script('ewa-functions-admin', EWA_PLUGIN_URL . '/assets/js/functions-admin.min.js', array('jquery'), EWA_PLUGIN_VERSION, true);
    wp_localize_script('ewa-functions-admin', 'ewa_ajax_object_admin', array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'site_url' => site_url(),
      'authors_url' => admin_url('edit-tags.php?taxonomy=ewa-author&post_type=product'),
      'translations' => array(
        'migrate_notice' => esc_html__('¿Start migration?', 'everexpert-woocommerce-authors'),
        'migrating' => esc_html__('We are migrating the product authors. ¡Don´t close this window until the process is finished!', 'everexpert-woocommerce-authors'),
        'dummy_data_notice' => esc_html__('¿Start loading dummy data?', 'everexpert-woocommerce-authors'),
        'dummy_data' => esc_html__('We are importing the dummy data. ¡Don´t close this window until the process is finished!', 'everexpert-woocommerce-authors')
      )
    ));
    wp_enqueue_script('ewa-functions-admin');
  }

  public function register_authors_taxonomy()
  {
    $labels = array(
      'name' => esc_html__('Authors', 'everexpert-woocommerce-authors'),
      'singular_name' => esc_html__('Author', 'everexpert-woocommerce-authors'),
      'menu_name' => esc_html__('Authors', 'everexpert-woocommerce-authors'),
      'all_items' => esc_html__('All Authors', 'everexpert-woocommerce-authors'),
      'edit_item' => esc_html__('Edit Author', 'everexpert-woocommerce-authors'),
      'view_item' => esc_html__('View Author', 'everexpert-woocommerce-authors'),
      'update_item' => esc_html__('Update Author', 'everexpert-woocommerce-authors'),
      'add_new_item' => esc_html__('Add New Author', 'everexpert-woocommerce-authors'),
      'new_item_name' => esc_html__('New Author Name', 'everexpert-woocommerce-authors'),
      'parent_item' => esc_html__('Parent Author', 'everexpert-woocommerce-authors'),
      'parent_item_colon' => esc_html__('Parent Author:', 'everexpert-woocommerce-authors'),
      'search_items' => esc_html__('Search Authors', 'everexpert-woocommerce-authors'),
      'popular_items' => esc_html__('Popular Authors', 'everexpert-woocommerce-authors'),
      'separate_items_with_commas' => esc_html__('Separate authors with commas', 'everexpert-woocommerce-authors'),
      'add_or_remove_items' => esc_html__('Add or remove authors', 'everexpert-woocommerce-authors'),
      'choose_from_most_used' => esc_html__('Choose from the most used authors', 'everexpert-woocommerce-authors'),
      'not_found' => esc_html__('No authors found', 'everexpert-woocommerce-authors')
    );

    $new_slug = get_option('wc_ewa_admin_tab_slug');
    $old_slug = get_option('old_wc_ewa_admin_tab_slug');

    $new_slug = ($new_slug != false) ? $new_slug : 'author';
    $old_slug = ($old_slug != false) ? $old_slug : 'null';

    $args = array(
      'hierarchical' => true,
      'labels' => $labels,
      'show_ui' => true,
      'query_var' => true,
      'public' => true,
      'show_admin_column' => true,
      'rewrite' => array(
        'slug' => apply_filters('ewa_taxonomy_rewrite', $new_slug),
        'hierarchical' => true,
        'with_front' => apply_filters('ewa_taxonomy_with_front', true),
        'ep_mask' => EP_PERMALINK
      )
    );

    register_taxonomy('ewa-author', array('product'), $args);

    if ($new_slug != false && $old_slug != false && $new_slug != $old_slug) {
      flush_rewrite_rules();
      update_option('old_wc_ewa_admin_tab_slug', $new_slug);
    }
  }

  public function add_authors_metafields()
  {
    register_meta('term', 'ewa_author_image', array($this, 'add_authors_metafields_sanitize'));
  }

  public function add_authors_metafields_sanitize($author_img)
  {
    return $author_img;
  }

  public static function get_authors($hide_empty = false, $order_by = 'name', $order = 'ASC', $only_featured = false, $ewa_term = false, $only_parents = false)
  {
    $result = array();

    $authors_args = array('hide_empty' => $hide_empty, 'orderby' => $order_by, 'order' => $order);
    if ($only_featured)
      $authors_args['meta_query'] = array(array('key' => 'ewa_featured_author', 'value' => true));
    if ($only_parents)
      $authors_args['parent'] = 0;

    $authors = get_terms('ewa-author', $authors_args);

    foreach ($authors as $key => $author) {

      if ($ewa_term) {
        $authors[$key] = new EWA_Term($author);
      } else {
        $author_image_id = get_term_meta($author->term_id, 'ewa_author_image', true);
        $author_banner_id = get_term_meta($author->term_id, 'ewa_author_banner', true);
        $author->author_image = wp_get_attachment_image_src($author_image_id);
        $author->author_banner = wp_get_attachment_image_src($author_banner_id);
      }
    }

    if (is_array($authors) && count($authors) > 0)
      $result = $authors;

    return $result;
  }

  public static function get_authors_array($is_select = false)
  {
    $result = array();

    //if is for select input adds default value
    if ($is_select)
      $result[0] = esc_html__('All', 'everexpert-woocommerce-authors');

    $authors = get_terms('ewa-author', array(
      'hide_empty' => false
    ));

    foreach ($authors as $author) {
      $result[$author->term_id] = $author->slug;
    }

    return $result;
  }

  public function print_author_banner()
  {
    $queried_object = get_queried_object();
    $author_banner = get_term_meta($queried_object->term_id, 'ewa_author_banner', true);
    $author_banner_link = get_term_meta($queried_object->term_id, 'ewa_author_banner_link', true);
    $show_banner = get_option('wc_ewa_admin_tab_author_banner');
    $show_banner = get_option('wc_ewa_admin_tab_author_banner');
    $show_banner_class = (!$show_banner || $show_banner == 'yes') ? 'ewa-before-loop' : 'ewa-after-loop';

    if ($author_banner != '') {
      echo '<div class="ewa-author-banner ewa-clearfix ' . $show_banner_class . '">';
      if ($author_banner_link != '') {
        echo '<a href="' . site_url($author_banner_link) . '">' . wp_get_attachment_image($author_banner, 'full', false) . '</a>';
      } else {
        echo wp_get_attachment_image($author_banner, 'full', false);
      }
      echo '</div>';
    }
  }

  public function print_author_desc()
  {
    $queried_object = get_queried_object();
    $show_desc = get_option('wc_ewa_admin_tab_author_desc');
    $show_desc = get_option('wc_ewa_admin_tab_author_desc');
    $show_desc_class = (!$show_desc || $show_desc == 'yes') ? 'ewa-before-loop' : 'ewa-after-loop';

    if ($queried_object->description != '' && $show_desc !== 'no') {
      echo '<div class="ewa-author-description ' . $show_desc_class . '">';
      echo do_shortcode(wpautop($queried_object->description));
      echo '</div>';
    }
  }

  public function print_author_banner_and_desc()
  {
    $queried_object = get_queried_object();

    $show_desc = get_option('wc_ewa_admin_tab_author_desc');
    $show_desc_class = (!$show_desc || $show_desc == 'yes') ? 'ewa-before-loop' : 'ewa-after-loop';

    $author_banner = get_term_meta($queried_object->term_id, 'ewa_author_banner', true);
    $author_banner_link = get_term_meta($queried_object->term_id, 'ewa_author_banner_link', true);

    if ($author_banner != '' || $queried_object->description != '' && $show_desc !== 'no') {
      echo '<div class="ewa-author-banner-cont ' . $show_desc_class . '">';
      $this->print_author_banner();
      $this->print_author_desc();
      echo '</div>';
    }
  }

  public static function render_template($name, $folder = '', $data, $private = true)
  {
    //default template
    if ($folder)
      $folder = $folder . '/';
    $template_file = dirname(__DIR__) . '/templates/' . $folder . $name . '.php';

    //theme overrides
    if (!$private) {
      $theme_template_path = get_stylesheet_directory() . '/everexpert-woocommerce-authors/';
      if (file_exists($theme_template_path . $folder . $name . '.php'))
        $template_file = $theme_template_path . $folder . $name . '.php';
    }

    extract($data);

    ob_start();
    include $template_file;
    return ob_get_clean();
  }

  public function product_duplicate_save($duplicate, $product)
  {
    $product_authors = wp_get_object_terms($product->get_id(), 'ewa-author', array('fields' => 'ids'));
    wp_set_object_terms($duplicate->get_id(), $product_authors, 'ewa-author');
  }

  public function breadcrumbs($crumbs)
  {

    if (is_tax('ewa-author')) {

      $authors_page_id = get_option('wc_ewa_admin_tab_authors_page_id');

      if (!empty($authors_page_id) && $authors_page_id != '-') {

        $cur_author = get_queried_object();
        $author_ancestors = get_ancestors($cur_author->term_id, 'ewa-author', 'taxonomy');

        $author_page_pos = count($crumbs) - (count($author_ancestors) + 2);
        if (is_paged())
          $author_page_pos -= 1;

        if (isset($crumbs[$author_page_pos][1]))
          $crumbs[$author_page_pos][1] = get_page_link($authors_page_id);
      }
    }

    return $crumbs;
  }

  /**
   *  Redirect if the search matchs with a authors name
   *  Better search experience
   */
  public function search_by_author_name($query)
  {

    if (wp_doing_ajax())
      return;

    if (!is_admin() && $query->is_main_query() && $query->is_search()) {

      $authors = get_terms(array('taxonomy' => 'ewa-author', 'fields' => 'id=>name'));

      if ($match = array_search(strtolower(trim($query->get('s'))), array_map('strtolower', $authors))) {

        wp_redirect(get_term_link($match));
        exit;
      }
    }
  }
}
