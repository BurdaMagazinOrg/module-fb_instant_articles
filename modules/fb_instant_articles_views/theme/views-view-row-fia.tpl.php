<?php
/**
 * @file
 * Default theme implementation to display an item in a views FIA (Facebook Instant Articles) feed.
 *
 * Available variables:
 * - options['title']: RSS item title.
 * - options['subtitle']: RSS item subtitle.
 * - options['kicker']: teaser short text for the article
 * - options['created']: created datetime
 * - options['modified']: modified datetime
 * - options['link']: RSS item link.
 * - options['figure']: a full html markup for a header image (syntax below)
 * - options['authors'] : htmls <address> tags for article authors (syntax below)
 *
 * - content : HTML content for the article
 *
 * - footer : HTML content for the article footer
 *
 * The syntax for the figure allows:
 *   <figure>
 *       <img src="http://mydomain.com/path/to/img.jpg" />
 *       <figcaption>This image is amazing</figcaption>
 *   </figure>
 *
 * The Syntax for the author <address>:
 *      <address>
 *       <a rel="facebook" href="http://facebook.com/brandon.diamond">Brandon Diamond</a>
 *       Brandon is a avid zombie hunter.
 *     </address>
 *     <address>
 *       <a>TR Vishwanath</a>
 *       Vish is a scholar and a gentleman.
 *     </address>
 *
 *
 * @see template_preprocess_views_view_row_fia()
 *
 * @ingroup themeable
 */
?>
<item>
  <title><?php print $options['title']; ?></title>
  <link><?php print $options['link']; ?></link>
  <guid><?php print $options['guid']; ?></guid>
  <pubDate><?php print $options['created']; ?></pubDate>
  <author><?php print $options['author']; ?></author>
  <content:encoded>
    <![CDATA[
    <!-- Article body started -->
      <?php print $row ?>
    ]]>
  </content:encoded>
</item>
