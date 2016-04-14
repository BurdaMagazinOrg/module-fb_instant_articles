<?php

/**
 * @file
 * Provide rules integration to manage facebook instant article using API.
 */

/**
 * Implements hook_rules_action_info() on behalf of the Facebook Instant Articles API module.
 */
function fb_instant_articles_api_rules_rules_action_info() {
  $defaults = array(
    'parameter' => array(
      'node' => array(
        'type' => 'node',
        'label' => t('Content'),
        'description' => t('The content which needs to be managed as facebook
          instant article.'),
      ),
    ),
    'group' => t('Node'),
  );
  $actions['fb_instant_articles_api_rules_action_import'] = $defaults + array(
    'label' => t('Add/Update facebook instant article'),
    'base' => 'fb_instant_articles_api_rules_action_import',
  );
  $actions['fb_instant_articles_api_rules_action_remove'] = $defaults + array(
    'label' => t('Remove facebook instant article'),
    'base' => 'fb_instant_articles_api_rules_action_remove',
  );
  return $actions;
}

/**
 * Action: Add facebook instant article.
 *
 * @param object $node
 */
function fb_instant_articles_api_rules_action_import($node = NULL) {
  // Loading this again as we are missing some field values and resetting cache
  // to get the updated node object.
  $node = node_load($node->nid, NULL, TRUE);
  fb_instant_articles_api_rules_import_article($node);
}


/**
 * Action: Delete facebook instant article.
 *
 * @param object $node
 */
function fb_instant_articles_api_rules_action_remove($node = NULL) {
  fb_instant_articles_api_rules_remove_article($node);
}

/**
 * Implements hook_rules_condition_info().
 */
function fb_instant_articles_api_rules_rules_condition_info() {
  return array(
    'fb_instant_articles_rules_is_article_type' => array(
      'group' => t('Facebook Instant Articles Rules'),
      'label' => t('Is an Instant Article type'),
      'parameter' => array(
        'node' => array(
          'type' => 'node',
          'label' => t('Node to verify'),
        )
      ),
    ),
  );
}

/**
 * Callback for Rules condition.
 */
function fb_instant_articles_rules_is_article_type($node) {
  return fb_instant_articles_display_is_article_type('node', $node->type);
}
