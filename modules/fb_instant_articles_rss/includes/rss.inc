<?php
/**
 * @file
 * Functions to generate the RSS feed.
 */

/**
 * Callback for the RSS page menu item.
 */
function fb_instant_articles_rss_page() {
  module_load_include('inc', 'fb_instant_articles_rss', 'includes/FacebookInstantArticlesRSSSimpleXMLElement');

  header('Content-Type: text/xml; charset=utf-8', TRUE);

  $rss = new FacebookInstantArticlesRSSSimpleXMLElement('<rss xmlns:content="http://purl.org/rss/1.0/modules/content/"></rss>');

  $rss->addAttribute('version', '2.0');
  $channel = $rss->addChild('channel');

  // Add Channel information;
  $channel->addChild('title', variable_get('fb_instant_articles_rss_channel_title', variable_get('site_name', 'Default site name')));
  $channel->addChild('description', variable_get('fb_instant_articles_rss_channel_description', variable_get('site_slogan', t('Default site slogan.'))));
  global $base_url;
  $channel->addChild('link', $base_url);
  $channel->addChild('language', variable_get('fb_instant_articles_rss_channel_language', 'en-gb'));

  // Create ISO 8601 formatted date.
  $date_time = new DateTime('NOW');
  $channel->addChild('lastBuildDate', $date_time->format(DateTime::ATOM));

  // Create RSS items.
  $nodes = fb_instant_articles_rss_get_article_nodes();
  foreach ($nodes as $node) {
    $item = $channel->addChild('item');
    $item->addChild('title', $node->title);
    $item->addChild('link', url("node/" . $node->nid, array('absolute' => TRUE)));

    $node_view = node_view($node, 'fb_instant_article');
    $item->addChildCdata('content', drupal_render($node_view));

    // Add published date.
    $ts = $node->created;
    $date = new DateTime("@$ts");
    $item->addChild('pubDate', $date->format(DateTime::ATOM));
  }

  echo $rss->asXML();
}

/**
 * Return an array of nodes that are treated as articles.
 *
 * @return array
 *   Array of nodes.
 */
function fb_instant_articles_rss_get_article_nodes() {
  $nodes = array();
  $entity_types = fb_instant_articles_display_get_article_entity_types();
  if (isset($entity_types['node'])) {
    foreach ($entity_types['node'] as $content_type => $entity_information) {
      $query = db_select('node', 'n');
      $query->fields('n', array('nid'))
        ->condition('type', $content_type, '=')
        ->condition('status', NODE_PUBLISHED);
      $published = db_select('fb_instant_articles_rss_entity_settings', 'fiafs');
      $published->fields('fiafs', array('entity_id'));
      if (!variable_get('fb_instant_articles_rss_all_enabled_default', TRUE)) {
        // If all nodes are disabled by default, select which nodes should be
        // enabled and intersect that set with the current set of results.
        $published->condition('fia_enabled', '1', '=');
        $query->condition('nid', $published, 'IN');
      }
      else {
        // If all nodes are enabled by default, select which nodes should be
        // disabled and intersect that set with the current set of results.
        $published->condition('fia_enabled', '0', '=');
        $query->condition('nid', $published, 'NOT IN');
      }
      $query->range(0, variable_get('fb_instant_articles_rss_number_results_in_feed', 50));
      $result = $query->execute()->fetchAllAssoc('nid');
      if (!empty($result)) {
        $nids = array_keys($result);
        $nodes += entity_load('node', $nids);
      }
    }
  }
  return $nodes;
}
