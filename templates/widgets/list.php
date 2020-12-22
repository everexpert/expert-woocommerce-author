<?php

/**
 * The template for displaying the list widget
 * @version 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<ul class="ewa-row">

  <?php foreach ($data['authors'] as $author) : ?>

    <li>
      <a href="<?php echo esc_html($author->get('link')); ?>" title="<?php echo esc_html($data['title_prefix'] . ' ' . $author->get('name')); ?>">
        <?php echo esc_html($author->get('name')); ?>
      </a>
    </li>

  <?php endforeach; ?>

</ul>