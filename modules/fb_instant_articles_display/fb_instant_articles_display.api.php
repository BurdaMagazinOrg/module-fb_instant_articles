<?php

/**
 * @file
 * Hooks provided by Facebook Instant Articles Display module.
 */

/**
 * Allows modules to alter if an entity type and bundle are treated as Facebook
 * Instant Articles.
 *
 * @param $is_type
 *   A boolean for if this is a node type that should be served as a Facebook
 *   Instant Article.
 * @param $entity_type
 *   The entity type name.
 * @param $bundle
 *   The entity bundle name.
 *
 * @see fb_instant_articles_display_is_article_type()
 */
function hook_fb_instant_articles_display_is_article_type_alter(&$is_type, $entity_type, $bundle) {
  // Explicitly set content type to be included in the RSS feed.
  if (!$is_type && $entity_type == 'article' && $bundle == 'node') {
    $is_type = TRUE;
  }
}

/**
 * Allows modules to alter entity types that are treated as Facebook Instant
 * Articles.
 *
 * @param $entity_types
 *   Array of entity types and bundles.
 *
 * @see fb_instant_articles_display_get_article_entity_types()
 */
function hook_fb_instant_articles_display_entity_types_alter(&$entity_types) {
  // Explicitly set content type to be included as a Facebook Instant Article.
  $entity_types['article']['node'] = array(
      'type' => 'article',
      'bundle' => 'node',
  );
}

/**
 * Sets the entity type as an allowable Facebook Instant Article type.
 *
 * Perform actions when an fb_instant_article is about to be set.
 *
 * @param $entity_type
 *   The entity type name.
 * @param $bundle
 *   The entity bundle name.
 *
 * @see fb_instant_articles_display_set_entity_type()
 */
function hook_fb_instant_articles_display_set_type($entity_type, $bundle) {
  if ($entity_type == 'node' && $bundle == 'article') {
    // Perform some action.
  }
}

/**
 * Deletes the entity type as an allowable Facebook Instant Article type.
 *
 * Perform actions when an fb_instant_article is about to be deleted.
 *
 * @param $entity_type
 *   The entity type name.
 * @param $bundle
 *   The entity bundle name.
 *
 * @see fb_instant_articles_display_delete_entity_type()
 */
function hook_fb_instant_articles_display_delete_type($entity_type, $bundle) {
  if ($entity_type == 'node' && $bundle == 'article') {
    // Perform some action.
  }
}

/**
 * Allows other modules to alter Facebook Instant Articles region info.
 *
 * @param $context
 *   An array of consisting of the entity type, bundle and view_mode.
 * @param $region_info
 *   Region table options.
 *
 * @see fb_instant_articles_display_add_regions()
 */
function hook_fb_instant_articles_display_layout_region_alter($context, &$region_info) {
  if ($context['entity_type'] == 'node' && $context['bundle'] == 'article') {
    $region_info = array(
      'region_options' => array(),
      'table_regions' => array(),
    );
  }
}

/**
 * Allows other modules to alter Facebook Instant Articles layout settings.
 *
 * @param $record
 *   The exportable of the view mode layout.
 * @param $form_state
 *   The $form_state of the view mode form/table.
 *
 * @see fb_instant_articles_display_field_ui_layouts_save()
 */
function hook_fb_instant_articles_display_layout_settings_alter(&$record, &$form_state) {
  // Alter $form_state or/and $record.
}

/**
 * Allows modules to alter Facebook Instant Articles display label options.
 *
 * @param array $field_label_options
 *   An array of field label options.
 *
 * @see fb_instant_articles_display_field_ui_fields()
 */
function hook_fb_instant_articles_display_label_options_alter(&$field_label_options) {
}

/**
 * Allows other modules to modify the field settings before they get saved.
 *
 * @param $field_settings
 * @param $form
 * @param $form_state
 *
 * @see fb_instant_articles_display_field_ui_fields_save()
 */
function hook_fb_instant_articles_display_field_settings_alter(&$field_settings, &$form, &$form_state) {
}

/**
 * Allows modules to alter field data prior to being handling formatter display.
 *
 * @param array $field
 * @param array $items
 * @param array $display
 *
 * @see fieldFormatterView()
 */
function hook_fb_instant_articles_field_view_alter(&$field, &$items, &$display) {
}
