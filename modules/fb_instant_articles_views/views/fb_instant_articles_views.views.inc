<?php

/**
 * @file
 * Facebook instant articles views module file.
 *
 * This is the views related module file for fb_instant_articles_views.
 */

/**
 * Implements hook_views_data_alter().
 */
function fb_instant_articles_views_views_data_alter(&$data) {
  $data['node']['fb_instant_articles_views'] = array(
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
function fb_instant_articles_views_views_plugins() {
  $path = drupal_get_path('module', 'fb_instant_articles_views');
  return array(
    'module' => 'fb_instant_articles_views',
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
        'theme file' => 'fb_instant_articles_views.theme.inc',
        'theme path' => "$path/theme",
      ),
    ),
  );
}
