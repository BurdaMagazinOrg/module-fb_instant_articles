<?php
/**
 * @file
 * Facebook instant articles theme file.
 */

/**
 * Implements hook_theme().
 */
function facebook_instant_articles_theme($existing, $type, $theme, $path) {
  return array(
    'views_view_row_fia' => array(
      'variables' => array(
        'row' => array(),
        'view' => array(),
        'title' => NULL,
        'author' => NULL,
        'created' => NULL,
        'modified' => NULL,
        'link' => NULL,
        'guid' => NULL
      ),
      'path'=> $path . '/theme',
      'template'=>'views-view-row-fia',
    ),
  );
}
