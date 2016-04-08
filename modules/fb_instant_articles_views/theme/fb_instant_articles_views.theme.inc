<?php

/**
 * @file
 * Facebook Instant Articles theme hooks.
 */

/**
 * Implements hook_theme().
 */
function fb_instant_articles_views_theme($existing, $type, $theme, $path) {
  return array(
    'views_view_row_fia' => array(
      'variables' => array(
        'view' => NULL,
        'options' => array(),
        'row' => NULL,
      ),
      'path' => $path . '/theme',
      'template' => 'views-view-row-fia',
    ),
  );
}

/**
 * Templete preprocessor for FBIA views row theme hook.
 */
function template_preprocess_views_view_row_fia(&$vars) {
  $view = &$vars['view'];
  $options = &$vars['options'];
  $item = &$vars['row'];

  $vars['title'] = check_plain($item->title);
  $vars['link'] = check_url($item->link);
  $vars['content'] = $item->content;
  $vars['item_elements'] = empty($item->elements) ? '' : format_xml_elements($item->elements);
}
