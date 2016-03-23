<?php

/**
 * @file
 * Facebook Instant Articles views module file.
 */

/**
 * The views_plugin_row_fiafields row plugin
 *
 * Renders an RSS item based on fields.
 */
class views_plugin_row_fiafields extends views_plugin_row {
  // Basic properties that let the row style follow relationships.
  var $base_table = 'node';
  var $base_field = 'nid';

  // Stores the nodes loaded with pre_render.
  var $nodes = array();

  function option_definition() {
    $options = parent::option_definition();
    $options['view_mode'] = array('fb_instant_article' => 'fb_instant_article');
    return $options;
  }

  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['view_mode'] = array(
      '#type' => 'select',
      '#title' => t('View mode'),
      '#options' => $this->options_form_summary_options(),
      '#default_value' => $this->options['view_mode'],
    );
  }

  /**
   * Return the main options, which are shown in the summary title.
   */
  function options_form_summary_options() {
    $entity_info = entity_get_info('node');
    $options = array();
    if (!empty($entity_info['view modes'])) {
      foreach ($entity_info['view modes'] as $mode => $settings) {
        $options[$mode] = $settings['label'];
      }
    }
    return $options;
  }

  function summary_title() {
    $options = $this->options_form_summary_options();
    return check_plain($options[$this->options['view_mode']]);
  }


  function pre_render($values) {
    $nids = array();
    foreach ($values as $row) {
      $nids[] = $row->{$this->field_alias};
    }
    if (!empty($nids)) {
      $this->nodes = node_load_multiple($nids);
    }
  }

  /**
   * Render a row object.
   *
   * @return string
   *   The rendered output of a single row, used by the style plugin.
   */
  function render($row) {

    global $base_url;
    global $language;

    $nid = $row->{$this->field_alias};
    if (!is_numeric($nid)) {
      return;
    }

    // Load the specified node:
    $node = $this->nodes[$nid];
    if (empty($node)) {
      return;
    }

    $options['langcode'] = $language->language;
    $uri = entity_uri('node', $node);

    // Build output for node.
    $display_mode = $this->options['view_mode'];
    $node_view = node_view($node, $display_mode);

    $options['title'] = $node->title;
    $options['author'] = format_username($node);
    $options['created'] = date("Y-d-m\TH:i:s\Z", $node->created);
    $options['time_created'] = date("F jS, g:i A", $node->created);
    $options['modified'] = date("Y-d-m\TH:i:s\Z", $node->changed);
    $options['time_modified'] = date("F jS, g:i A", $node->changed);
    $options['link'] = url($uri['path'], $uri['options'] + array('absolute' => TRUE));
    $options['guid'] = $node->nid . ' at ' . $base_url;

    return theme($this->theme_functions(),
      array(
        'view' => $this->view,
        'options' => $options,
        'row' => drupal_render($node_view),
        'field_alias' => isset($this->field_alias) ? $this->field_alias : '',
      ));
  }
}

