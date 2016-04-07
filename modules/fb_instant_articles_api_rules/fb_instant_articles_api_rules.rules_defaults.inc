<?php

/**
 * @file
 * Provide default rules integration to manage facebook instant article using API.
 */

/**
 * Implements hook_default_rules_configuration().
 */
function fb_instant_articles_api_rules_default_rules_configuration() {
  // Handling add/update node article as published in FB Instant Articles library.
  $rule = rules_reaction_rule();
  $rule->label = t('Add/update as published in FB Instant Articles library ' .
    'when a published Article node is saved');
  $rule->active = FALSE;
  $rule->event('node_presave')
    ->condition('node_is_published')
    ->condition('fb_instant_articles_rules_is_article_type', array('data:select' => 'node'))
    ->action('fb_instant_articles_api_rules_action_import', array('data:select' => 'node'));

  $configs['fb_instant_articles_rules_default_published'] = $rule;

  // Handling add/update node article as unpublished in FB Instant Articles library.
  $rule = rules_reaction_rule();
  $rule->label = t('Add/update as unpublished in FB Instant Articles library ' .
    'when a unpublished Article node is saved');
  $rule->active = FALSE;
  $rule->event('node_presave')
    ->condition(rules_condition('node_is_published')->negate())
    ->condition('fb_instant_articles_rules_is_article_type', array('data:select' => 'node'))
    ->action('fb_instant_articles_api_rules_action_import', array('data:select' => 'node'));

  $configs['fb_instant_articles_rules_default_unpublished'] = $rule;

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
