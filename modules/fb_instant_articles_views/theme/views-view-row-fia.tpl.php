<?php

/**
 * @file
 * Default theme implementation to display an item in a Facebook Instant
 * Articles feed.
 *
 * Available variables:
 * - $title: RSS item title.
 * - $link: canonical URL for this item.
 * - $content: the fully rendered instant article markup.
 * - $item_elements: additional optional <item> child tags.
 *
 * @see template_preprocess_views_view_row_fia()
 *
 * @ingroup themeable
 */
?>
<item>
  <title><?php print $title; ?></title>
  <link><?php print $link; ?></link>
  <content:encoded>
    <![CDATA[
    <!-- Article body started -->
      <?php print $content; ?>
    ]]>
  </content:encoded>
  <?php print $item_elements; ?>
</item>
