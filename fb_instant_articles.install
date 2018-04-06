<?php

/**
 * @file
 * Install and update hooks.
 */

use Facebook\InstantArticles\Transformer\Logs\TransformerLog;

/**
 * Change the enable_logging setting to transformer_logging_level.
 */
function fb_instant_articles_update_8201() {

  $config = \Drupal::configFactory()->getEditable('fb_instant_articles.settings');
  $enable_logging = $config->get('enable_logging');
  if ($enable_logging) {
    $config->set('transformer_logging_level', TransformerLog::ERROR);
  }
  else {
    $config->set('transformer_logging_level', TransformerLog::OFF);
  }
  $config->clear('enable_logging');
  $config->save();
}
