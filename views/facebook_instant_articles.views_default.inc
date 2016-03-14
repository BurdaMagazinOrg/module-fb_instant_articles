<?php

/**
 * @file
 * Facebook instant articles default views module file.
 *
 * Provide default views for Facebook instant articles module.
 */

/**
 * Implements hook_views_default_views().
 */
function facebook_instant_articles_views_default_views() {
  $export = array();

  $view = new view();
  $view->name = 'facebook_instant_articles';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'node';
  $view->human_name = 'FaceBook Instant Articles';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['style_plugin'] = 'default';
  $handler->display->display_options['row_plugin'] = 'fields';
  /* Field: Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'node';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['label'] = '';
  $handler->display->display_options['fields']['title']['alter']['word_boundary'] = FALSE;
  $handler->display->display_options['fields']['title']['alter']['ellipsis'] = FALSE;
  $handler->display->display_options['fields']['title']['element_label_colon'] = FALSE;
  /* Sort criterion: Content: Post date */
  $handler->display->display_options['sorts']['created']['id'] = 'created';
  $handler->display->display_options['sorts']['created']['table'] = 'node';
  $handler->display->display_options['sorts']['created']['field'] = 'created';
  $handler->display->display_options['sorts']['created']['order'] = 'DESC';
  /* Filter criterion: Content: Published */
  $handler->display->display_options['filters']['status']['id'] = 'status';
  $handler->display->display_options['filters']['status']['table'] = 'node';
  $handler->display->display_options['filters']['status']['field'] = 'status';
  $handler->display->display_options['filters']['status']['value'] = 1;
  $handler->display->display_options['filters']['status']['group'] = 1;
  $handler->display->display_options['filters']['status']['expose']['operator'] = FALSE;
  /* Filter criterion: Content: Valid Facebook instant articles filter */
  $handler->display->display_options['filters']['facebook_instant_articles']['id'] = 'facebook_instant_articles';
  $handler->display->display_options['filters']['facebook_instant_articles']['table'] = 'node';
  $handler->display->display_options['filters']['facebook_instant_articles']['field'] = 'facebook_instant_articles';

  /* Display: Feed */
  $handler = $view->new_display('feed', 'Feed', 'feed_fia_rss');
  $handler->display->display_options['pager']['type'] = 'some';
  $handler->display->display_options['style_plugin'] = 'rss';
  $handler->display->display_options['style_options']['description'] = 'Facebook Instant Articles feed';
  $handler->display->display_options['row_plugin'] = 'fiafields';
  $handler->display->display_options['row_options']['view_mode'] = 'facebook_instant_articles_rss';
  $handler->display->display_options['path'] = 'fia/nodes.xml';

  $export['facebook_instant_articles'] = $view;

  return $export;
}
