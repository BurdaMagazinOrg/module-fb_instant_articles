<?php

/**
 * @file
 * Provide default rules integration to manage facebook instant article using API.
 */

/**
 * Implements hook_default_rules_configuration().
 */
function fb_instant_articles_api_rules_default_rules_configuration() {
  //Handling add/update node article as published in FB Instant Articles library.
  $rule = rules_reaction_rule();
  $rule->label = t('Add/update as published in FB Instant Articles library ' .
                   'when a published Article node is saved');
  $rule->active = FALSE;
  $rule->event('node_presave')
    ->condition(rules_condition('data_is', array('data:select' => 'node:status',
      'value' => TRUE))->negate())
    ->condition('data_is', array('data:select' => 'node:type', 'value' => 'article'))
    ->action('rules_action_fb_instant_articles_update', array('data:select' => 'node'));

  $configs['fb_instant_articles_rules_default_published'] = $rule;

  //Handling add/update node article as unpublished in FB Instant Articles library.
  $rule = rules_reaction_rule();
  $rule->label = t('Add/update as unpublished in FB Instant Articles library ' .
    'when a unpublished Article node is saved');
  $rule->active = FALSE;
  $rule->event('node_presave')
    ->condition(rules_condition('data_is', array('data:select' => 'node:status',
      'value' => TRUE))->negate())
    ->condition('data_is', array('data:select' => 'node:type', 'value' => 'article'))
    ->action('rules_action_fb_instant_articles_update', array('data:select' => 'node'));

  $configs['fb_instant_articles_rules_default_unpublished'] = $rule;

  //Handling node article deletion.
  $rule = rules_reaction_rule();
  $rule->label = t('Remove from FB Instant Articles library when an Article ' .
                   'node is deleted');
  $rule->active = FALSE;
  $rule->event('node_delete')
    ->condition(rules_condition('data_is', array('data:select' => 'node:status',
      'value' => TRUE))->negate())
    ->condition('data_is', array('data:select' => 'node:type', 'value' => 'article'))
    ->action('facebook_instant_articles_delete_action', array('data:select' => 'node'));

  $configs['fb_instant_articles_rules_default_deleted'] = $rule;
  return $configs;
}
