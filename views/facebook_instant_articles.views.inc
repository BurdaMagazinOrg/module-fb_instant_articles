<?php

/**
 * @file
 * Facebook instant articles views module file.
 *
 * This is the views related module file for facebook_instant_articles.
 */

/**
 * Implements hook_views_data_alter().
 */
function facebook_instant_articles_views_data_alter(&$data) {
  $data['node']['facebook_instant_articles'] = array(
    'title' => t('Valid Facebook instant articles filter'),
    'filter' => array(
      'title' => t('Valid Facebook instant articles filter'),
      'help' => t('Filter for items that should be listed in the facebook instant articles pages.'),
      'field' => 'type',
      'handler' => 'views_handler_filter_valid_fia'
    ),
  );
}

/**
 * Implements hook_views_plugins().
 */
function facebook_instant_articles_views_plugins() {
  $path = drupal_get_path('module', 'facebook_instant_articles');
  return array(
    'module' => 'facebook_instant_articles',
    'row' => array(
      'fiafields' => array(
        'title' => t('FIA Fields'),
        'help' => t('Display fields as FIA (facebook instant articles) items.'),
        'handler' => 'views_plugin_row_fiafields',
        'base' => array('node'), // only works with 'node' as base.
        'uses options' => TRUE,
        'type' => 'feed',
        'path' => "$path/theme",
        'theme' => "views_view_row_fia",
        'theme file' => 'facebook_instant_articles.theme.inc',
        'theme path' => "$path/theme",
      ),
    ),
  );
}
