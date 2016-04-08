<?php

/**
 * @file
 * Default theme implementation to display an item in a Facebook Instant
 * Articles feed.
 *
 * Available variables:
 * - $title: RSS item title.
 * - $link: canonical URL for this item.
 * - $guid: globally unique id for this item.
 * - $content: the fully rendered instant article markup.
 * - $author: the author of the article.
 * - $created: date created timestamp in ISO 8601 format.
 * - $modified: date modified timestamp in ISO 8601 format.
 *
 * @see template_preprocess_views_view_row_fia()
 *
 * @ingroup themeable
 */
?>
<item>
  <title><?php print $title; ?></title>
  <link><?php print $link; ?></link>
  <guid><?php print $guid; ?></guid>
  <pubDate><?php print $created; ?></pubDate>
  <author><?php print $author; ?></author>
  <content:encoded>
    <![CDATA[
    <!-- Article body started -->
      <?php print $content; ?>
    ]]>
  </content:encoded>
</item>
