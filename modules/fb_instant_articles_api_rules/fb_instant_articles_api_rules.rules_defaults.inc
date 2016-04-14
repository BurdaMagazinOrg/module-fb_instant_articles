<?php

/**
 * @file
 * Provide default rules integration to manage facebook instant article using API.
 */

/**
 * Implements hook_default_rules_configuration().
 */
function fb_instant_articles_api_rules_default_rules_configuration() {
  // Handling add/update node of supported type in FB Instant Articles library.
  $rule = rules_reaction_rule();
  $rule->label = t('Add/update in FB Instant Articles library when a node of ' .
    'a supported type is saved');
  $rule->active = FALSE;
  $rule->event('node_insert')
    ->event('node_update')
    ->condition('fb_instant_articles_rules_is_article_type', array('data:select' => 'node'))
    ->action('fb_instant_articles_api_rules_action_import', array('data:select' => 'node'));

  $configs['fb_instant_articles_rules_default_saved'] = $rule;

  // Handling node article deletion.
  $rule = rules_reaction_rule();
  $rule->label = t('Remove from FB Instant Articles library when an Article ' .
    'node is deleted');
  $rule->active = FALSE;
  $rule->event('node_delete')
    ->condition('fb_instant_articles_rules_is_article_type', array('data:select' => 'node'))
    ->action('fb_instant_articles_api_rules_action_remove', array('data:select' => 'node'));

  $configs['fb_instant_articles_rules_default_deleted'] = $rule;

  return $configs;
}
