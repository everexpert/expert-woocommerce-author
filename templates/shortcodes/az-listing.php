<?php

/**
 * The template for displaying the a-z Listing
 * @version 1.0.1
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<?php if (!empty($grouped_authors)) : ?>

  <div class="ewa-az-listing">

    <div class="ewa-az-listing-header">

      <ul class="ewa-clearfix">

        <?php foreach ($grouped_authors as $letter => $author_group) : ?>
          <li><a href="#ewa-az-listing-<?php echo esc_attr($letter); ?>"><?php echo esc_html($letter); ?></a></li>
        <?php endforeach; ?>

      </ul>

    </div>

    <div class="ewa-az-listing-content">

      <?php foreach ($grouped_authors as $letter => $author_group) : ?>

        <div id="ewa-az-listing-<?php echo esc_attr($letter); ?>" class="ewa-az-listing-row ewa-clearfix">
          <p class="ewa-az-listing-title"><?php echo esc_attr($letter); ?></p>
          <div class="ewa-az-listing-row-in">

            <?php foreach ($author_group as $author) : ?>

              <div class="ewa-az-listing-col">
                <a href="<?php echo get_term_link($author['author_term']->term_id); ?>">
                  <?php echo esc_html($author['author_term']->name); ?>
                </a>
              </div>

            <?php endforeach; ?>

          </div>
        </div>

      <?php endforeach; ?>

    </div>

  </div>

<?php endif; ?>