<?php
/**
 * The template for displaying the edit-tags.php exporter/importer
 * @version 1.0.0
 */

 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>

<div class="ewa-authors-exporter ewa-clearfix">
  <button class="button ewa-authors-export"><?php esc_html_e('Export authors', 'everexpert-woocommerce-authors');?></button>
  <button class="button ewa-authors-import"><?php esc_html_e('Import authors', 'everexpert-woocommerce-authors');?></button>
  <input type="file" class="ewa-authors-import-file" accept="application/json">
  <p><?php _e( 'This tool allows you to export and import the authors between different sites using EWA.', 'everexpert-woocommerce-authors' );?></p>
</div>
