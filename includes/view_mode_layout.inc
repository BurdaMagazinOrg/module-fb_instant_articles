<?php
/**
 * @file
 * Functions to handle layout of the FBIA View Mode.
 */

/**
 * Includes FBIA View Mode elements.
 */
function facebook_instant_articles_layout_form(&$form, &$form_state) {

  // Get the entity_type, bundle and view mode.
  $entity_type = $form['#entity_type'];
  $bundle_name = $form['#bundle'];
  $view_mode = $form['#view_mode'];
  $form['#export_id'] = $entity_type . '|' . $bundle_name . '|' . $view_mode;

  $layout_options = array();

  $layout = facebook_instant_articles_get_node_layout_settings($bundle_name);
  $form['#fbia_layout'] = $layout;

  facebook_instant_articles_field_ui_fields($entity_type, $bundle_name, $view_mode, $form, $form_state);

    // Attach js.
  $form['#attached']['js'][] = drupal_get_path('module', 'facebook_instant_articles') . '/js/admin.js';

  // Add process function to add the regions.
  $form['#process'][] = 'facebook_instant_articles_add_regions';

  // Add submit handler. Layout needs be first so
  // we can reset the type key for Field API fields.
  $submit = $form['#submit'];
  $form['#submit'] = array('facebook_instant_articles_field_ui_layouts_save');
  $form['#submit'] = array_merge($form['#submit'], $submit);
}

/**
 * Includes regions FBIA View Mode.
 */
function facebook_instant_articles_add_regions(&$form, &$form_state) {

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
    'footer' => t('Footer')
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
    'view_mode' => $view_mode
  );
  $region_info = array(
    'region_options' => &$region_options,
    'table_regions' => &$table['#regions'],
  );
  drupal_alter('facebook_instant_articles_layout_region', $context, $region_info);

  $region_options['hidden'] = $view_mode != 'form' ? t('Disabled') : t('Hidden');
  $table['#regions']['hidden'] = array(
    'title' => $view_mode != 'form' ? t('Disabled') : t('Hidden'),
    'message' => t('No fields are hidden.')
  );

  $region = array(
    '#type' => 'select',
    '#options' => $region_options,
    '#default_value' => 'hidden',
    '#attributes' => array(
      'class' => array('ds-field-region'),
    )
  );

  $limit_items = array(
    '#type' => 'textfield',
    '#size' => 2,
    '#default_value' => '',
    '#weight' => 10,
    '#default_value' => '#',
    '#prefix' => '<div class="limit-float">',
    '#suffix' => '</div><div class="clearfix"></div>',
    '#attributes' => array(
      'alt' => t('Enter a number to limit the number of items or \'delta\' to print a specific delta (usually configured in views or found in entity->ds_delta). Leave empty to display them all. Note that depending on the formatter settings, this option might not always work.'),
      'title' => t('Enter a number to limit the number of items or \'delta\' to print a specific delta (usually configured in views or found in entity->ds_delta). Leave empty to display them all. Note that depending on the formatter settings, this option might not always work.'),
    ),
  );

   // Update existing rows by changing rowHandler and adding regions.
  foreach (element_children($table) as $name) {
    $row = &$table[$name];
    $row['#js_settings'] = array('rowHandler' => 'facebookInstantArticles');
    $row['#region_callback'] = 'facebook_instant_articles_field_ui_row_region';

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

    // Limit items.
      $field_info = field_info_field($name);
      if (isset($field_info['cardinality']) && $field_info['cardinality'] != 1 && $view_mode != 'form') {
        $row['format']['type']['#prefix'] = '<div class="limit-float">';
        $row['format']['type']['#suffix'] = '</div>';
        $row['format']['limit'] = $limit_items;
        $row['format']['limit']['#default_value'] = (isset($layout->settings['limit']) && isset($layout->settings['limit'][$name])) ? $layout->settings['limit'][$name] : '#';
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
 *
 * @param $row
 *   The current row that is being rendered in the Field UI screen.
 */
function facebook_instant_articles_field_ui_row_region($row) {
  return isset($row['region']['#value']) ? $row['region']['#value'] : 'hidden';
}

/**
 * Save layout configuration of FBIA View Mode.
 */
function facebook_instant_articles_field_ui_layouts_save($form, &$form_state) {
  $weight = 0;

  // Get default values.
  $entity_type = $form['#entity_type'];
  $bundle = $form['#bundle'];
  $view_mode = $form['#view_mode'];

  // Determine layout variables.
  $layout = 'facebook_instant_article';

  // Save layout and add regions if necessary.
  $record = new stdClass;
  $record->id = $form['#export_id'];
  $record->entity_type = $entity_type;
  $record->bundle = $bundle;
  $record->settings = array();

  $form_state['layout_saved'] = TRUE;

  $fields = facebook_instant_articles_sort_fields($form_state['values']['fields'], 'weight');

  foreach ($fields as $key => $field) {

    // Make sure we need to save anything for this field.
    if (facebook_instant_articles_field_valid($key, $field, $form_state, $view_mode)) {
      continue;
    }

    if (!isset($record->settings['regions'][$field['region']])) {
      $record->settings['regions'][$field['region']] = array();
    }
    $record->settings['regions'][$field['region']][$weight++] = $key;
    $record->settings['fields'][$key] = $field['region'];

    // Save limit.
    $limit = isset($field['format']['limit']) ? trim($field['format']['limit']) : '';
    if (is_numeric($limit) || $limit === 'delta') {
      $record->settings['limit'][$key] = $limit;
    }
  }

  // Let other modules alter the layout settings.
  drupal_alter('facebook_instant_articles_layout_settings', $record, $form_state);

  $layout = $form['#fbia_layout'];
  if (!isset($layout->export_type)) {
    drupal_write_record('facebook_instant_articles_layout_settings', $record);
  }
  else {
    drupal_write_record('facebook_instant_articles_layout_settings', $record, array('id'));
  }

  // Clear entity info cache.
  cache_clear_all('entity_info', 'cache', TRUE);
}

/**
 * Get Display Suite layouts.
 */
function facebook_instant_articles_get_layout_info() {
  return array('facebook_instant_article' => array(
            'label' => t('Facebook Instant Article'),
            'path' => "",
            'regions' => array(
              'header' => t('Header'),
              'body' => t('Body'),
              'footer' => t('Footer')
            ),
            'image' => 1
          )
        );
}

/**
 * Utility function to sort a multidimensional array by a value in a sub-array.
 *
 * @param $a
 *   The array to sort.
 * @param $subkey
 *   The subkey to sort by.
 */
function facebook_instant_articles_sort_fields($a, $subkey) {
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
 * Utility function to check if we need to save anything for this field.
 */
function facebook_instant_articles_field_valid($key, $field, &$form_state, $view_mode = 'default') {
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
 * Add the fields to the Field UI form.
 *
 * @param $entity_type
 *   The name of the entity type.
 * @param $bundle
 *   The name of the bundle
 * @param $view_mode
 *   The name of the view_mode
 * @param $form
 *   A collection of form properties.
 * @param $form_state
 *   A collection of form_state properties.
 */
function facebook_instant_articles_field_ui_fields($entity_type, $bundle, $view_mode, &$form, &$form_state) {

  // Do not add the fields if there is no layout.
  if (!isset($form['#fbia_layout'])) {
    return;
  }

  // Get the fields and put them on the form.
  $fields = facebook_instant_articles_get_fields($entity_type, FALSE);

  // Ultimate alter on Field UI fields, only used for edge cases.
  $context = array(
    'entity_type' => $entity_type,
    'bundle' => $bundle,
    'view_mode' => $view_mode,
  );

  // Get field settings.
  $field_settings = facebook_instant_articles_get_field_settings($entity_type, $bundle, $view_mode, FALSE);
  $form['#field_settings'] = $field_settings;

  $table = &$form['fields'];
  $form['#fbia_fields'] = array();

  $field_label_options = array(
    'above' => t('Above'),
    'inline' => t('Inline'),
    'hidden' => t('<Hidden>'),
  );
  drupal_alter('facebook_instant_articles_label_options', $field_label_options);

  // Regions for fields.
  $field_regions = array();
  if (isset($form['#fbia_layout']->settings['fields'])) {
    $field_regions = $form['#fbia_layout']->settings['fields'];
  }

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

    $value = isset($form_state['formatter_settings']) ? $form_state['formatter_settings'] : array();

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

    // Don't show summary or cogwheel in hidden region.
    if (facebook_instant_articles_field_ui_check_hidden_region($key, $form_state, $field_regions)) {
      continue;
    }

    $field['name'] = $key;
    $field['entity_type'] = $entity_type;
    $field['bundle'] = $bundle;
    $field['view_mode'] = $view_mode;
  }

  // Add fields submit handler.
  $form['#submit'][] = 'facebook_instant_articles_field_ui_fields_save';
}

 /**
 * Get all fields.
 *
 * @param $entity_type
 *   The name of the entity.
 * @param $cache
 *   Whether we need to get the fields from cache or not.
 * @return
 *   Collection of fields.
 */
function facebook_instant_articles_get_fields($entity_type, $cache = TRUE) {
  $fields = array('node' => array('title' => array(
    'title' => t('Title'),
    'field_type' => DS_FIELD_TYPE_FUNCTION,
    'function' => 'ds_render_field',
    'properties' => array(
      'entity_render_key' => 'title',
      'settings' => array(
        'link' => array('type' => 'select', 'options' => array('no', 'yes')),
        'wrapper' => array('type' => 'textfield', 'description' => t('Eg: h1, h2, p')),
        'class' => array('type' => 'textfield', 'description' => t('Put a class on the wrapper. Eg: block-title')),
      ),
      'default' => array('wrapper' => 'h2', 'link' => 0, 'class' => ''),
    )
  )));
  return $fields[$entity_type];
}

/**
 * Get the field settings.
 *
 * @param $entity_type
 *   The name of the entity.
 * @param $bundle
 *   The name of bundle (ie, page or story for node types, profile for users)
 * @param $view_mode
 *   The name of view mode.
 */
function facebook_instant_articles_get_field_settings($entity_type, $bundle, $view_mode, $default = TRUE) {
  static $field_settings = NULL;

  if (!isset($field_settings)) {
    if ($cache = cache_get('facebook_instant_articles_field_settings')) {
      $field_settings = $cache->data;
    }
    else {
      ctools_include('export');
      $ds_field_settings = ctools_export_crud_load_all('facebook_instant_articles_field_settings');
      foreach ($ds_field_settings as $layout => $layout_settings) {
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
      cache_set('facebook_instant_articles_field_settings', $field_settings, 'cache');
    }
  }

  return (isset($field_settings[$entity_type][$bundle][$view_mode])) ? $field_settings[$entity_type][$bundle][$view_mode] : (isset($field_settings[$entity_type][$bundle]['default']) && $default ? $field_settings[$entity_type][$bundle]['default'] : array());
}

/**
 * Helper function to check if we are in a hidden region or not.
 */
function facebook_instant_articles_field_ui_check_hidden_region($key, $form_state, $field_regions) {
  $continue = FALSE;
  if (isset($form_state['input']['fields'][$key]['region'])) {
    if ($form_state['input']['fields'][$key]['region'] == 'hidden') {
      $continue = TRUE;
    }
  }
  elseif (!isset($field_regions[$key]) || $field_regions[$key] == 'hidden') {
    $continue = TRUE;
  }

  return $continue;
}

/**
 * Save the field settings from the 'Manage display' screen.
 */
function facebook_instant_articles_field_ui_fields_save($form, &$form_state) {
  // Setup some variables.
  $entity_type = $form['#entity_type'];
  $bundle = $form['#bundle'];
  $view_mode = $form['#view_mode'];

  // Delete previous field configuration configuration.
  db_delete('facebook_instant_articles_field_settings')
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
  foreach ($fields as $key => $field) {

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
  drupal_alter('facebook_instant_articles_field_settings', $field_settings, $form, $form_state);

  // Save the record.
  if (!empty($field_settings)) {
    $record = new stdClass;
    $record->id = $form['#export_id'];
    $record->entity_type = $entity_type;
    $record->bundle = $bundle;
    $record->view_mode = $view_mode;
    $record->settings = $field_settings;
    drupal_write_record('facebook_instant_articles_field_settings', $record);
  }

  // Clear the ds_fields cache.
  cache_clear_all('facebook_instant_articles_fields:', 'cache', TRUE);
  cache_clear_all('facebook_instant_articles_field_settings', 'cache');
}
