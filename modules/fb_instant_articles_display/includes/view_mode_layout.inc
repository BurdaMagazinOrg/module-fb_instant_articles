<?php

/**
 * @file
 * View mode layout functions for Facebook Instant Articles Display.
 */

/**
 * Includes Facebook Instant Articles Display View Mode elements.
 */
function fb_instant_articles_display_layout_form(&$form, &$form_state) {

  // Get the entity_type, bundle and view mode.
  $entity_type = $form['#entity_type'];
  $bundle_name = $form['#bundle'];
  $view_mode = $form['#view_mode'];
  $form['#export_id'] = $entity_type . '|' . $bundle_name . '|' . $view_mode;

  $layout = fb_instant_articles_display_get_layout_settings($entity_type, $bundle_name);
  $form['#fbia_layout'] = $layout;

  fb_instant_articles_display_field_ui_fields($entity_type, $bundle_name, $view_mode, $form, $form_state);

  // Attach js.
  $form['#attached']['js'][] = drupal_get_path('module', 'fb_instant_articles_display') . '/js/admin.js';

  // Add process function to add the regions.
  $form['#process'][] = 'fb_instant_articles_display_add_regions';

  // Add submit handler. Layout needs be first so
  // we can reset the type key for Field API fields.
  $submit = $form['#submit'];
  $form['#submit'] = array('fb_instant_articles_display_field_ui_layouts_save');
  $form['#submit'] = array_merge($form['#submit'], $submit);
}

/**
 * Includes regions Facebook Instant Articles Display View Mode.
 */
function fb_instant_articles_display_add_regions(&$form, &$form_state) {

  // Get the entity_type, bundle and view mode.
  $entity_type = $form['#entity_type'];
  $bundle = $form['#bundle'];
  $view_mode = $form['#view_mode'];

  // Check layout.
  $layout = isset($form['#fbia_layout']) ? $form['#fbia_layout'] : FALSE;

  $table = &$form['fields'];

  $table['#header'] = array(
    t('Field'),
    t('Weight'),
    t('Parent'),
    t('Region'),
    t('Label'),
    array('data' => t('Format'), 'colspan' => 3),
  );

  $regions = array(
    'header' => t('Header'),
    'body' => t('Body'),
    'footer' => t('Footer'),
  );

  $table['#regions'] = array();
  foreach ($regions as $region_key => $region_title) {
    $region_options[$region_key] = $region_title;
    $table['#regions'][$region_key] = array(
      'title' => $region_title,
      'message' => t('No fields are displayed in this region'),
    );
  }

  // Let other modules alter the regions.
  $context = array(
    'entity_type' => $entity_type,
    'bundle' => $bundle,
    'view_mode' => $view_mode,
  );
  $region_info = array(
    'region_options' => &$region_options,
    'table_regions' => &$table['#regions'],
  );
  drupal_alter('fb_instant_articles_display_layout_region', $context, $region_info);

  $region_options['hidden'] = $view_mode != 'form' ? t('Disabled') : t('Hidden');
  $table['#regions']['hidden'] = array(
    'title' => $view_mode != 'form' ? t('Disabled') : t('Hidden'),
    'message' => t('No fields are hidden.'),
  );

  $region = array(
    '#type' => 'select',
    '#options' => $region_options,
    '#default_value' => 'hidden',
    '#attributes' => array(
      'class' => array('ds-field-region'),
    ),
  );

  // Update existing rows by changing rowHandler and adding regions.
  foreach (element_children($table) as $name) {
    $row = &$table[$name];
    $row['#js_settings'] = array('rowHandler' => 'facebookInstantArticlesDisplay');
    $row['#region_callback'] = 'fb_instant_articles_display_field_ui_row_region';

    // Remove hidden format.
    if (isset($row['format']['type']['#options']['hidden'])) {
      unset($row['format']['type']['#options']['hidden']);
    }

    // Add label class.
    if (isset($row['label'])) {
      if (isset($form_state['formatter_settings']) && isset($form_state['formatter_settings'][$name]['ft'])) {
        if (!empty($form_state['formatter_settings'][$name]['ft']['lb'])) {
          $row['human_name']['#markup'] = check_plain($form_state['formatter_settings'][$name]['ft']['lb']) . ' ' . t('(Original: !orig)', array('!orig' => $row['human_name']['#markup']));
        }
      }
    }

    // Add region.
    $split = ($view_mode != 'form') ? 7 : 6;
    if ($row['#row_type'] == 'group' && $view_mode == 'form') {
      $split = ($view_mode != 'form') ? 8 : 7;
    }

    $second = array_splice($row, $split);
    $row['region'] = $region;
    $row['region']['#default_value'] = (isset($layout->settings['fields'][$name]) && isset($region_options[$layout->settings['fields'][$name]])) ? $layout->settings['fields'][$name] : 'hidden';

    $row = array_merge($row, $second);
  }
  return $form;
}

/**
 * Returns the region to which a row in the Field UI screen belongs.
 */
function fb_instant_articles_display_field_ui_row_region($row) {
  return isset($row['region']['#value']) ? $row['region']['#value'] : 'hidden';
}

/**
 * Saves layout configuration of Facebook Instant Articles Display View Mode.
 */
function fb_instant_articles_display_field_ui_layouts_save($form, &$form_state) {
  $weight = 0;

  // Get default values.
  $entity_type = $form['#entity_type'];
  $bundle = $form['#bundle'];
  $view_mode = $form['#view_mode'];

  // Save layout and add regions if necessary.
  $record = new stdClass();
  $record->id = $form['#export_id'];
  $record->entity_type = $entity_type;
  $record->bundle = $bundle;
  $record->settings = array();

  $form_state['layout_saved'] = TRUE;

  $fields = fb_instant_articles_display_sort_fields($form_state['values']['fields'], 'weight');

  foreach ($fields as $key => $field) {

    // Make sure we need to save anything for this field.
    if (fb_instant_articles_display_field_valid($key, $field, $form_state, $view_mode)) {
      continue;
    }

    if (!isset($record->settings['regions'][$field['region']])) {
      $record->settings['regions'][$field['region']] = array();
    }
    $record->settings['regions'][$field['region']][$weight++] = $key;
    $record->settings['fields'][$key] = $field['region'];
  }

  // Let other modules alter the layout settings.
  drupal_alter('fb_instant_articles_display_layout_settings', $record, $form_state);

  $layout = $form['#fbia_layout'];
  if (!isset($layout->export_type)) {
    drupal_write_record('fb_instant_articles_display_layout_settings', $record);
  }
  else {
    drupal_write_record('fb_instant_articles_display_layout_settings', $record, array('id'));
  }

  // Clear entity info cache.
  cache_clear_all('entity_info', 'cache', TRUE);
}

/**
 * Gets Facebook Instant Articles Display layouts.
 */
function fb_instant_articles_display_get_layout_info() {
  return array(
    'fb_instant_articles_display' => array(
      'label' => t('Facebook Instant Articles Display'),
      'path' => "",
      'regions' => array(
        'header' => t('Header'),
        'body' => t('Body'),
        'footer' => t('Footer'),
      ),
      'image' => 1,
    ),
  );
}

/**
 * Sorts a multidimensional array by a value in a sub-array.
 *
 * @param array $a
 *   The array to sort.
 * @param string $subkey
 *   The subkey to sort by.
 *
 * @return array
 *   The sorted multidimensional array.
 */
function fb_instant_articles_display_sort_fields(array $a, $subkey) {
  foreach ($a as $k => $v) {
    if (isset($v[$subkey])) {
      $b[$k] = $v[$subkey];
    }
  }
  asort($b);
  foreach ($b as $key => $val) {
    $c[$key] = $a[$key];
  }
  return $c;
}

/**
 * Checks if we need to save anything for this field.
 */
function fb_instant_articles_display_field_valid($key, $field, &$form_state, $view_mode = 'default') {
  $continue = FALSE;

  // Ignore the Field group module and the region to block plugin.
  if ($key == '_add_new_group' || $key == '_add_new_field' || $key == '_add_new_block_region') {
    $continue = TRUE;
  }

  // If the field is in hidden region, do not save. Check if the
  // field has a type key which means it's from Field API and
  // we need to reset that type to 'hidden' so it doesn't get
  // fired by Field API in the frontend.
  if (isset($field['region']) && $field['region'] == 'hidden') {
    if (isset($field['type']) && $view_mode != 'form') {
      $form_state['values']['fields'][$key]['type'] = 'hidden';
    }

    // In case of a form, fields will be set with #access to FALSE.
    if ($view_mode != 'form') {
      $continue = TRUE;
    }
  }

  return $continue;
}

/**
 * Adds the fields to the Field UI form.
 *
 * @param string $entity_type
 *   The name of the entity type.
 * @param string $bundle
 *   The name of the bundle.
 * @param string $view_mode
 *   The name of the view_mode.
 * @param array $form
 *   A collection of form properties.
 * @param array $form_state
 *   A collection of form_state properties.
 */
function fb_instant_articles_display_field_ui_fields($entity_type, $bundle, $view_mode, array &$form, array &$form_state) {

  // Do not add the fields if there is no layout.
  if (!isset($form['#fbia_layout'])) {
    return;
  }

  // Get the fields and put them on the form.
  $fields = fb_instant_articles_display_get_fields($entity_type, FALSE);

  // Get field settings.
  $field_settings = fb_instant_articles_display_get_field_settings($entity_type, $bundle, $view_mode, FALSE);
  $form['#field_settings'] = $field_settings;

  $table = &$form['fields'];
  $form['#fbia_fields'] = array();

  $field_label_options = array(
    'above' => t('Above'),
    'inline' => t('Inline'),
    'hidden' => t('<Hidden>'),
  );
  drupal_alter('fb_instant_articles_display_label_options', $field_label_options);

  foreach ($fields as $key => $field) {

    $form['#fbia_fields'][] = $key;

    // Check on formatter settings.
    if (isset($form_state['formatter_settings'][$key])) {
      $field['formatter_settings'] = $form_state['formatter_settings'][$key];
    }
    elseif (isset($field_settings[$key]['formatter_settings'])) {
      $field['formatter_settings'] = $field_settings[$key]['formatter_settings'];
      $form_state['formatter_settings'][$key] = $field['formatter_settings'];
    }

    if (!isset($field_settings[$key]['ft']) && isset($field_settings[$key]['ft'])) {
      $form_state['formatter_settings'][$key]['ft'] = $field_settings[$key]['ft'];
    }

    $hidden = array('hidden' => t('<Hidden>'));
    $formatters = isset($field['properties']['formatters']) ? $hidden + $field['properties']['formatters'] : $hidden + array('default' => t('Default'));

    $table[$key] = array(
      '#row_type' => 'field',
      '#js_settings' => array('field'),
      '#region_callback' => 'field_ui_display_overview_row_region',
      '#attributes' => array('class' => array('draggable', 'tabledrag-leaf')),
      'human_name' => array(
        '#markup' => check_plain($field['title']),
      ),
      'weight' => array(
        '#type' => 'textfield',
        '#default_value' => isset($field_settings[$key]['weight']) ? $field_settings[$key]['weight'] : 0,
        '#size' => 3,
        '#attributes' => array('class' => array('field-weight')),
      ),
      'parent_wrapper' => array(
        'parent' => array(
          '#type' => 'select',
          '#empty_value' => '',
          '#options' => array(),
          '#attributes' => array('class' => array('field-parent')),
          '#parents' => array('fields', $key, 'parent'),
        ),
        'hidden_name' => array(
          '#type' => 'hidden',
          '#default_value' => $key,
          '#attributes' => array('class' => array('field-name')),
        ),
      ),
      'label' => array(
        '#type' => 'select',
        '#options' => $field_label_options,
        '#default_value' => isset($field_settings[$key]['label']) ? $field_settings[$key]['label'] : 'hidden',
      ),
      'format' => array(
        'type' => array(
          '#type' => 'select',
          '#options' => $formatters,
          '#default_value' => isset($field_settings[$key]['format']) ? $field_settings[$key]['format'] : 'hidden',
          '#attributes' => array('class' => array('field-formatter-type')),
        ),
      ),
      'settings_summary' => array(),
      'settings_edit' => array(),
    );

    $field['name'] = $key;
    $field['entity_type'] = $entity_type;
    $field['bundle'] = $bundle;
    $field['view_mode'] = $view_mode;
  }

  // Add fields submit handler.
  $form['#submit'][] = 'fb_instant_articles_display_field_ui_fields_save';
}

/**
 * Gets all fields.
 *
 * @param string $entity_type
 *   The name of the entity.
 * @param bool $cache
 *   Whether we need to get the fields from cache or not.
 *
 * @return array
 *   Collection of fields.
 */
function fb_instant_articles_display_get_fields($entity_type, $cache = TRUE) {
  $fields = array(
    'node' => array(
      'title' => array(
        'title' => t('Title'),
        'field_type' => 2,
        'properties' => array(
          'entity_render_key' => 'title',
          'settings' => array(
            'link' => array(
              'type' => 'select',
              'options' => array('no', 'yes'),
            ),
            'wrapper' => array(
              'type' => 'textfield',
              'description' => t('Eg: h1, h2, p'),
            ),
            'class' => array(
              'type' => 'textfield',
              'description' => t('Put a class on the wrapper. Eg: block-title'),
            ),
          ),
          'default' => array('wrapper' => 'h2', 'link' => 0, 'class' => ''),
        ),
      ),
    ),
  );
  return $fields[$entity_type];
}

/**
 * Gets the field settings.
 *
 * @param string $entity_type
 *   The name of the entity.
 * @param string $bundle
 *   The name of bundle (ie, page or story for node types, profile for users).
 * @param string $view_mode
 *   The name of view mode.
 *
 * @return array
 *   Stored field settings.
 */
function fb_instant_articles_display_get_field_settings($entity_type, $bundle, $view_mode, $default = TRUE) {
  static $field_settings = NULL;

  if (!isset($field_settings)) {
    if ($cache = cache_get('fb_instant_articles_display_field_settings')) {
      $field_settings = $cache->data;
    }
    else {
      ctools_include('export');
      $fbia_field_settings = ctools_export_crud_load_all('fb_instant_articles_display_field_settings');
      foreach ($fbia_field_settings as $layout_settings) {
        // Do not store configuration when the field settings is disabled.
        if (!empty($layout_settings->disabled)) {
          continue;
        }
        // Do not store configuration if settings key is not set.
        if (!isset($layout_settings->settings)) {
          continue;
        }
        foreach ($layout_settings->settings as $field => $settings) {
          $field_settings[$layout_settings->entity_type][$layout_settings->bundle][$layout_settings->view_mode][$field] = $settings;
        }
      }
      cache_set('fb_instant_articles_display_field_settings', $field_settings, 'cache');
    }
  }

  return (isset($field_settings[$entity_type][$bundle][$view_mode])) ? $field_settings[$entity_type][$bundle][$view_mode] : (isset($field_settings[$entity_type][$bundle]['default']) && $default ? $field_settings[$entity_type][$bundle]['default'] : array());
}

/**
 * Saves the field settings from the 'Manage display' screen.
 */
function fb_instant_articles_display_field_ui_fields_save($form, &$form_state) {
  // Setup some variables.
  $entity_type = $form['#entity_type'];
  $bundle = $form['#bundle'];
  $view_mode = $form['#view_mode'];

  // Delete previous field configuration configuration.
  db_delete('fb_instant_articles_display_field_settings')
    ->condition('entity_type', $entity_type)
    ->condition('bundle', $bundle)
    ->condition('view_mode', $view_mode)
    ->execute();

  if (empty($form_state['layout_saved'])) {
    return;
  }

  $field_settings = array();

  // Save settings for each field.
  $fields = $form['#fbia_fields'];
  foreach ($fields as $field) {

    // Field settings.
    $field_values = $form_state['values']['fields'][$field];

    // In case the region is hidden, do not save.
    if (isset($field_values['region']) && $field_values['region'] == 'hidden') {
      continue;
    }

    // Build settings.
    $settings = array();
    $settings['weight'] = $field_values['weight'];
    $settings['label'] = $field_values['label'];
    $settings['format'] = $field_values['format']['type'];

    // Any formatter settings.
    if (isset($form_state['formatter_settings'][$field])) {
      $settings['formatter_settings'] = $form_state['formatter_settings'][$field];
    }

    $field_settings[$field] = $settings;
  }

  // Allow other modules to modify the field settings before they get saved.
  drupal_alter('fb_instant_articles_display_field_settings', $field_settings, $form, $form_state);

  // Save the record.
  if (!empty($field_settings)) {
    $record = new stdClass();
    $record->id = $form['#export_id'];
    $record->entity_type = $entity_type;
    $record->bundle = $bundle;
    $record->view_mode = $view_mode;
    $record->settings = $field_settings;
    drupal_write_record('fb_instant_articles_display_field_settings', $record);
  }

  // Clear the field cache.
  cache_clear_all('fb_instant_articles_display_fields:', 'cache', TRUE);
  cache_clear_all('fb_instant_articles_display_field_settings', 'cache');
}
