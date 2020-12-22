<?php

namespace Everexpert_Woocommerce_Authors;

use WP_Error, WP_REST_Server;

defined('ABSPATH') or die('No script kiddies please!');

class EWA_API_Support
{

  private $namespaces = array("wc/v1", "wc/v2", "wc/v3");
  private $base = 'authors';

  function __construct()
  {
    add_action('rest_api_init', array($this, 'register_endpoints'));
    add_action('rest_api_init', array($this, 'register_fields'));
  }

  /**
   * Registers the endpoint for all possible $namespaces
   */
  public function register_endpoints()
  {
    foreach ($this->namespaces as $namespace) {
      register_rest_route($namespace, '/' . $this->base, array(
        array(
          'methods'  => WP_REST_Server::READABLE,
          'callback' => function () {
            return rest_ensure_response(
              Everexpert_Woocommerce_Authors::get_authors()
            );
          },
          'permission_callback' => '__return_true'
        ),
        array(
          'methods'  => WP_REST_Server::CREATABLE,
          'callback'  => array($this, 'create_author'),
          'permission_callback' => function () {
            return current_user_can('manage_options');
          }

        ),
        array(
          'methods'   => WP_REST_Server::DELETABLE,
          'callback'  => array($this, 'delete_author'),
          'permission_callback' => function () {
            return current_user_can('manage_options');
          }
        )
      ));
    }
  }

  public function delete_author($request)
  {
    foreach ($request['authors'] as $author) {
      $delete_result = wp_delete_term($author, 'ewa-author');
      if (is_wp_error($delete_result)) return $delete_result;
    }
    return true;
  }

  public function create_author($request)
  {
    $new_author = wp_insert_term($request['name'], 'ewa-author', array('slug' => $request['slug'], 'description' => $request['description']));
    if (!is_wp_error($new_author)) {
      return array('id' => $new_author['term_id'], 'name' => $request['name'], 'slug' => $request['slug'], 'description' => $request['description']);
    } else {
      return $new_author;
    }
  }

  /**
   * Entry point for all rest field settings
   */
  public function register_fields()
  {
    register_rest_field('product', 'authors', array(
      'get_callback'    => array($this, "get_callback"),
      'update_callback' => array($this, "update_callback"),
      'schema'          => $this->get_schema(),
    ));
  }

  /**
   * Returns the schema of the "authors" field on the /product route
   * To attach a author to a product just append a "authors" key containing an array of author id's
   * An empty array wil detach all authors.
   * @return array
   */
  public function get_schema()
  {
    return array(
      'description' => __('Product authors', 'everexpert-woocommerce-authors'),
      'type' => 'array',
      'items' => array(
        "type" => "integer"
      ),
      'context' => array("view", "edit")
    );
  }

  /**
   * Returns all attached authors to a GET request to /products(/id)
   * @param $product
   * @return array|\WP_Error
   */
  public function get_callback($product)
  {
    $authors = wp_get_post_terms($product['id'], 'ewa-author');

    $result_authors_array = array();
    foreach ($authors as $author) {
      $result_authors_array[] = array(
        'id'   => $author->term_id,
        'name' => $author->name,
        'slug' => $author->slug
      );
    }

    return $result_authors_array;
  }

  /**
   * Entry point for an update call
   * @param $authors
   * @param $product
   */
  public function update_callback($authors, $product)
  {
    $this->remove_authors($product);
    $this->add_authors($authors, $product);
  }


  /**
   * Detaches all authors from a product
   * @param \WC_Product $product
   */
  private function remove_authors($product)
  {
    $authors = wp_get_post_terms($product->get_id(), 'ewa-author');
    if (!empty($authors)) {
      wp_set_post_terms($product->get_id(), array(), 'ewa-author');
    }
  }

  /**
   * Attaches the given authors to a product. Earlier attached authors, not in this array, will be removed
   * @param array $authors
   * @param \WC_Product $product
   */
  private function add_authors($authors, $product)
  {
    wp_set_post_terms($product->get_id(), $authors, "ewa-author");
  }
}
