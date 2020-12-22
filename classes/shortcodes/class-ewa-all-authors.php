<?php

namespace Everexpert_Woocommerce_Authors\Shortcodes;

defined('ABSPATH') or die('No script kiddies please!');

class EWA_All_Authors_Shortcode
{

  public static function all_authors_shortcode($atts)
  {

    $atts = shortcode_atts(array(
      'per_page'       => "10",
      'image_size'     => "thumbnail",
      'hide_empty'     => false,
      'order_by'       => 'name',
      'order'          => 'ASC',
      'title_position' => 'before'
    ), $atts, 'ewa-all-authors');

    $hide_empty = ($atts['hide_empty'] != 'true') ? false : true;

    ob_start();

    $authors = array();
    if ($atts['order_by'] == 'rand') {
      $authors = \Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors::get_authors($hide_empty);
      shuffle($authors);
    } else {
      $authors = \Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors::get_authors($hide_empty, $atts['order_by'], $atts['order']);
    }

    //remove residual empty authors
    foreach ($authors as $key => $author) {

      $count = self::count_visible_products($author->term_id);

      if (!$count && $hide_empty) {
        unset($authors[$key]);
      } else {
        $authors[$key]->count_ewa = $count;
      }
    }

?>
    <div class="ewa-all-authors">
      <?php static::pagination($authors, $atts['per_page'], $atts['image_size'], $atts['title_position']); ?>
    </div>
    <?php

    return ob_get_clean();
  }

  /**
   *  WP_Term->count property don´t care about hidden products
   *  Counts the products in a specific author
   */
  public static function count_visible_products($author_id)
  {

    $args = array(
      'posts_per_page' => -1,
      'post_type'      => 'product',
      'tax_query'      => array(
        array(
          'taxonomy'  => 'ewa-author',
          'field'     => 'term_id',
          'terms'     => $author_id
        ),
        array(
          'taxonomy' => 'product_visibility',
          'field'    => 'name',
          'terms'    => 'exclude-from-catalog',
          'operator' => 'NOT IN',
        )
      )
    );
    $wc_query = new \WP_Query($args);

    return $wc_query->found_posts;
  }

  public static function pagination($display_array, $show_per_page, $image_size, $title_position)
  {
    $page = 1;

    if (isset($_GET['ewa-page']) && filter_var($_GET['ewa-page'], FILTER_VALIDATE_INT) == true) {
      $page = $_GET['ewa-page'];
    }

    $page = $page < 1 ? 1 : $page;

    // start position in the $display_array
    // +1 is to account for total values.
    $start = ($page - 1) * ($show_per_page);
    $offset = $show_per_page;

    $outArray = array_slice($display_array, $start, $offset);

    //pagination links
    $total_elements = count($display_array);
    $pages = ((int)$total_elements / (int)$show_per_page);
    $pages = ceil($pages);
    if ($pages >= 1 && $page <= $pages) {

    ?>
      <div class="ewa-authors-cols-outer">
        <?php
        foreach ($outArray as $author) {

          $author_id   = $author->term_id;
          $author_name = $author->name;
          $author_link = get_term_link($author_id);

          $attachment_id = get_term_meta($author_id, 'ewa_author_image', 1);
          $attachment_html = $author_name;
          if ($attachment_id != '') {
            $attachment_html = wp_get_attachment_image($attachment_id, $image_size);
          }

        ?>
          <div class="ewa-authors-col3">

            <?php if ($title_position != 'none' && $title_position != 'after') : ?>
              <p>
                <a href="<?php echo esc_url($author_link); ?>">
                  <?php echo esc_html($author_name); ?>
                </a>
                <small>(<?php echo esc_html($author->count_ewa); ?>)</small>
              </p>
            <?php endif; ?>

            <div>
              <a href="<?php echo esc_url($author_link); ?>" title="<?php echo esc_html($author_name); ?>">
                <?php echo wp_kses_post($attachment_html); ?>
              </a>
            </div>

            <?php if ($title_position != 'none' && $title_position == 'after') : ?>
              <p>
                <a href="<?php echo esc_html($author_link); ?>">
                  <?php echo wp_kses_post($author_name); ?>
                </a>
                <small>(<?php echo esc_html($author->count_ewa); ?>)</small>
              </p>
            <?php endif; ?>

          </div>
        <?php
        }
        ?>
      </div>
<?php
      $next = $page + 1;
      $prev = $page - 1;

      echo '<div class="ewa-pagination-wrapper">';
      if ($prev > 1) {
        echo '<a href="' . get_the_permalink() . '" class="ewa-pagination prev" title="' . esc_html__('First page', 'everexpert-woocommerce-authors') . '">&laquo;</a>';
      }
      if ($prev > 0) {
        echo '<a href="' . get_the_permalink() . '?ewa-page=' . $prev . '" class="ewa-pagination last" title="' . esc_html__('Previous page', 'everexpert-woocommerce-authors') . '">&lsaquo;</a>';
      }

      if ($next <= $pages) {
        echo '<a href="' . get_the_permalink() . '?ewa-page=' . $next . '" class="ewa-pagination first" title="' . esc_html__('Next page', 'everexpert-woocommerce-authors') . '">&rsaquo;</a>';
      }
      if ($next < $pages) {
        echo '<a href="' . get_the_permalink() . '?ewa-page=' . $pages . '" class="ewa-pagination next" title="' . esc_html__('Last page', 'everexpert-woocommerce-authors') . '">&raquo;</a>';
      }
      echo '</div>';
    } else {
      echo esc_html__('No results', 'everexpert-woocommerce-authors');
    }
  }
}
