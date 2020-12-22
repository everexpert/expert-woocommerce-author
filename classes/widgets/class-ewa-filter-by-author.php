<?php

namespace Everexpert_Woocommerce_Authors\Widgets;

use WP_Query;

defined('ABSPATH') or die('No script kiddies please!');

class EWA_Filter_By_Author_Widget extends \WP_Widget
{

	function __construct()
	{
		$params = array(
			'description' => __('Recommended for product categories or shop page', 'everexpert-woocommerce-authors'),
			'name'        => __('Filter products by author', 'everexpert-woocommerce-authors')
		);
		parent::__construct('EWA_Filter_By_Author_Widget', '', $params);
	}

	public function form($instance)
	{
		extract($instance);

		$title = (isset($instance['title'])) ? $instance['title'] : esc_html__('Authors', 'everexpert-woocommerce-authors');
		$limit = (isset($instance['limit'])) ? $instance['limit'] : 20;
		$hide_submit_btn         = (isset($hide_submit_btn) && $hide_submit_btn == 'on') ? true : false;
		$only_first_level_authors = (isset($only_first_level_authors) && $only_first_level_authors == 'on') ? true : false;
?>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_html($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('limit')); ?>">
				<?php echo __('Max number of authors', 'everexpert-woocommerce-authors'); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_html($this->get_field_name('limit')); ?>" type="text" value="<?php echo esc_attr($limit); ?>" />
		</p>

		<p>
			<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('hide_submit_btn')); ?>" name="<?php echo esc_attr($this->get_field_name('hide_submit_btn')); ?>" <?php checked($hide_submit_btn); ?>>
			<label for="<?php echo esc_attr($this->get_field_id('hide_submit_btn')); ?>">
				<?php echo __('Hide filter button', 'everexpert-woocommerce-authors'); ?>
			</label>
		</p>

		<p>
			<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('only_first_level_authors')); ?>" name="<?php echo esc_attr($this->get_field_name('only_first_level_authors')); ?>" <?php checked($only_first_level_authors); ?>>
			<label for="<?php echo esc_attr($this->get_field_id('only_first_level_authors')); ?>">
				<?php echo __('Show only first level authors', 'everexpert-woocommerce-authors'); ?>
			</label>
		</p>

<?php
	}

	public function update($new_instance, $old_instance)
	{
		$limit = trim(strip_tags($new_instance['limit']));
		$limit = filter_var($limit, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

		$instance = array();
		$instance['title']      		 = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
		$instance['limit']      		 = ($limit != false) ? $limit : $old_instance['limit'];
		$instance['hide_submit_btn'] = (isset($new_instance['hide_submit_btn'])) ? $new_instance['hide_submit_btn'] : '';
		$instance['only_first_level_authors'] = (isset($new_instance['only_first_level_authors'])) ? $new_instance['only_first_level_authors'] : '';
		return $instance;
	}

	public function widget($args, $instance)
	{
		extract($args);
		extract($instance);

		if (!is_tax('ewa-author') && !is_product()) {

			$hide_submit_btn = (isset($hide_submit_btn) && $hide_submit_btn == 'on') ? true : false;
			$only_first_level_authors = (isset($only_first_level_authors) && $only_first_level_authors == 'on') ? true : false;

			$show_widget = true;
			$current_products = false;
			if (is_product_taxonomy() || is_shop()) {
				$current_products = $this->current_products_query();
				if (empty($current_products)) $show_widget = false;
			}

			if ($show_widget) {

				$title = (isset($instance['title'])) ? $instance['title'] : esc_html__('Authors', 'everexpert-woocommerce-authors');
				$title = apply_filters('widget_title', $title);
				$limit = (isset($instance['limit'])) ? $instance['limit'] : 20;

				echo $args['before_widget'];
				if (!empty($title)) echo $args['before_title'] . $title . $args['after_title'];
				$this->render_widget($current_products, $limit, $hide_submit_btn, $only_first_level_authors);
				echo $args['after_widget'];
			}
		}
	}

	public function render_widget($current_products, $limit, $hide_submit_btn, $only_first_level_authors)
	{

		$result_authors = array();

		if (is_product_taxonomy() || is_shop()) {

			//obtains authors ids
			if (!empty($current_products)) $result_authors = $this->get_products_authors($current_products);

			//excludes the child authors if needed
			if ($only_first_level_authors) {
				$result_authors = $this->exclude_child_authors($result_authors);
			}

			if (is_shop()) {
				$cate_url = get_permalink(wc_get_page_id('shop'));
			} else {
				$cate = get_queried_object();
				$cateID = $cate->term_id;
				$cate_url = get_term_link($cateID);
			}
		} else {
			//no product category
			$cate_url = get_permalink(wc_get_page_id('shop'));
			$result_authors =  get_terms('ewa-author', array('hide_empty' => true, 'fields' => 'ids'));
		}

		if ($limit > 0) $result_authors = array_slice($result_authors, 0, $limit);

		global $wp;
		$current_url = home_url(add_query_arg(array(), $wp->request));

		if (!empty($result_authors)) {

			$result_authors_ordered = array();
			foreach ($result_authors as $author) {
				$author = get_term($author);
				$result_authors_ordered[$author->name] = $author;
			}
			ksort($result_authors_ordered);

			$result_authors_ordered = apply_filters('ewa_widget_author_filter', $result_authors_ordered);

			echo \Everexpert_Woocommerce_Authors\Everexpert_Woocommerce_Authors::render_template(
				'filter-by-author',
				'widgets',
				array('cate_url' => $cate_url, 'authors' => $result_authors_ordered, 'hide_submit_btn' => $hide_submit_btn),
				false
			);
		}
	}

	private function exclude_child_authors($authors)
	{

		//gets parent for all authors
		foreach ($authors as $author_key => $author) {

			$author_o = get_term($author, 'ewa-author');

			if ($author_o->parent) {

				//exclude this child author and include the parent
				unset($authors[$author_key]);
				if (!in_array($author_o->parent, $authors)) $authors[$author_key] = $author_o->parent;
			}
		}

		//reset keys
		$authors = array_values($authors);


		return $authors;
	}

	private function current_products_query()
	{

		$args = array(
			'posts_per_page' => -1,
			'post_type' => 'product',
			'tax_query' => array(
				array(
					'taxonomy' => 'ewa-author',
					'operator' => 'EXISTS'
				)
			),
			'fields' => 'ids',
		);

		$cat = get_queried_object();
		if (is_a($cat, 'WP_Term')) {
			$cat_id 				= $cat->term_id;
			$cat_id_array 	= get_term_children($cat_id, $cat->taxonomy);
			$cat_id_array[] = $cat_id;
			$args['tax_query'][] = array(
				'taxonomy' => $cat->taxonomy,
				'field'    => 'term_id',
				'terms'    => $cat_id_array
			);
		}

		if (get_option('woocommerce_hide_out_of_stock_items') === 'yes') {
			$args['meta_query'] = array(
				array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => 'NOT IN'
				)
			);
		}

		$wp_query = new WP_Query($args);
		wp_reset_postdata();

		return $wp_query->posts;
	}

	private function get_products_authors($product_ids)
	{

		$product_ids = implode(',', array_map('intval', $product_ids));

		global $wpdb;

		$author_ids = $wpdb->get_col("SELECT DISTINCT t.term_id
			FROM {$wpdb->prefix}terms AS t
			INNER JOIN {$wpdb->prefix}term_taxonomy AS tt
			ON t.term_id = tt.term_id
			INNER JOIN {$wpdb->prefix}term_relationships AS tr
			ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE tt.taxonomy = 'ewa-author'
			AND tr.object_id IN ($product_ids)
		");

		return ($author_ids) ? $author_ids : false;
	}
}
