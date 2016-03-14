<?php
/**
 * @file
 * Default theme implementation to display an item in a views FIA (facebook instant articles) feed.
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
  <description><?php print $options['kicker']; ?></description>
  <content:encoded>
    <![CDATA[
    <!doctype html>
    <html lang="<?php print $options['langcode']; ?>"
          prefix="op: http://media.facebook.com/op#">
    <head>
      <meta charset="utf-8">
      <link rel="canonical" href="<?php print $options['link']; ?>">
      <meta property="op:markup_version" content="v1.0">
    </head>
    <body>
    <article>
      <header>
        <!-- The title and subtitle shown in your Instant Article -->
        <h1><?php print $options['title']; ?></h1>
        <?php if (!empty($options['subtitle'])): ?>
          <h2><?php print $options['subtitle']; ?></h2>
        <?php endif; ?>
        <!-- The date and time when your article was originally published <?php print $options['created'] ?> -->
        <time class="op-published" datetime="<?php print $options['created'] ?>"><?php print $options['time_created']; ?></time>
        <!-- The date and time when your article was last updated <?php print $options['modified']; ?> -->
        <time class="op-modified" datetime="<?php print $options['modified']; ?>"><?php print $options['time_modified']; ?> </time>
        <?php if (!empty($kicker)): ?>
          <!-- A kicker for your article -->
          <h3 class="op-kicker"><?php print $kicker ?></h3>
        <?php endif; ?>
        <?php if (!empty($options['figure'])): ?>
          <!-- The cover image shown inside your article -->
          <?php print $options['figure']; ?>
        <?php endif; ?>
        <?php if (!empty($options['author'])): ?>
          <!-- The author of your article -->
          <address><?php print $options['author']; ?></address>
        <?php endif; ?>
      </header>
      <!-- Article body started -->
      <?php print $row ?>
      <!-- Article body finished -->
      <?php if (!empty($footer)): ?>
        <footer>
          <?php print $footer ?>
        </footer>
      <?php endif; ?>
    </article>
    </body>
    </html>
    ]]>
  </content:encoded>
</item>