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
    'access callback' => 'fb_instant_articles_api_rules_integration_access',
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
 * Facebook Instant Articles API integration access callback.
 */
function fb_instant_articles_api_rules_integration_access($type, $name) {
  return user_access('administer site configuration');
}

/**
 * Action: Add facebook instant article.
 *
 * @param object $node
 */
function fb_instant_articles_api_rules_action_import($node = NULL) {
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
