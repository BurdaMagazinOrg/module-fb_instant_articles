<?php
/**
 * @file
 * Hooks provided by the Facebook Instant Articles API.
 */

/**
 * Implements hook_fb_instant_articles_is_article_type_alter().
 *
 * @param $is_type
 *   A boolean for if this is a node type that should be served as a FBIA.
 * @param $entity_type
 *   The entity type name.
 * @param $bundle
 *   The entity bundle name.
 *
 * @see fb_instant_articles_is_article_type()
 */
function hook_fb_instant_articles_is_article_type_alter(&$is_type, $entity_type, $bundle) {
  // Explicitly set content type to be included in the RSS feed.
  if (!$is_type && $entity_type == 'article' && $bundle == 'node') {
    $is_type = TRUE;
  }
}

/**
 * Implements hook_fb_instant_articles_entity_types_alter().
 *
 * @param $entity_types
 *   Array of entity types and bundles.
 *
 * @see fb_instant_articles_get_article_entity_types()
 */
function hook_fb_instant_articles_entity_types_alter(&$entity_types) {
  // Explicitly set content type to be included as a product.
  $entity_types['article']['node'] = array(
      'type' => 'article',
      'bundle' => 'node',
  );
}

/**
 * Implements hook_fb_instant_articles_set_type().
 *
 * Perform actions when an fb_instant_article is about to be set.
 *
 * @param $type
 *   The entity type name.
 * @param $bundle
 *   The entity bundle name.
 *
 * @see fb_instant_articles_set_entity_type()
 */
function hook_fb_instant_articles_set_type($type, $bundle) {
  if ($entity_type == 'article' && $bundle == 'node') {
    // Perform some action.
  }
}

/**
 * Implements hook_fb_instant_articles_delete_type().
 *
 * Perform actions when an fb_instant_article is about to be deleted.
 *
 * @param $type
 *   The entity type name.
 * @param $bundle
 *   The entity bundle name.
 *
 * @see fb_instant_articles_delete_entity_type()
 */
function hook_fb_instant_articles_delete_entity_type($type, $bundle) {
  if ($entity_type == 'article' && $bundle == 'node') {
    // Perform some action.
  }
}

/**
 * Implements hook_fb_instant_articles_layout_region_alter().
 *
 * @param $context
 *   An array of consisting of the entity type, bundle and view_mode.
 * @param $region_info
 *   Region table options.
 *
 * @see fb_instant_articles_layout_region_alter()
 */
function hook_fb_instant_articles_layout_region_alter(&$context, &$region_info) {
  // Let other modules alter the regions.
  $context = array(
    'entity_type' => $entity_type,
    'bundle' => $bundle,
    'view_mode' => $view_mode,
  );

  $region_info = array(
    'region_options' => array(),
    'table_regions' => array(),
  );
}

/**
 * Implements hook_fb_instant_articles_layout_region_alter().
 *
 * @param $context
 *   An array of consisting of the entity type, bundle and view_mode.
 * @param $region_info
 *   Region table options.
 *
 * @see fb_instant_articles_layout_region_alter()
 */
function hook_fb_instant_articles_layout_region_alter(&$context, &$region_info) {
  // Let other modules alter the regions.
  $context = array(
    'entity_type' => $entity_type,
    'bundle' => $bundle,
    'view_mode' => $view_mode,
  );

  $region_info = array(
    'region_options' => array(),
    'table_regions' => array(),
  );
}

/**
 * Implements hook_fb_instant_articles_layout_settings().
 *
 * @param $record
 *   The exportable of the view mode layout.
 * @param $form_state
 *   The $form_state of the view mode form/table.
 *
 * @see fb_instant_articles_field_ui_layouts_save()
 */
function hook_fb_instant_articles_layout_settings_alter(&$record, &$form_state) {
  // Alter $form_state or/and $record.
}
?>
