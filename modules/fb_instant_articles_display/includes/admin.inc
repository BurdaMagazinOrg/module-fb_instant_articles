<?php

/**
 * @file
 * Provides the Facebook Instant Articles administrative interface.
 */

/**
 * Admin settings form.
 */
function fb_instant_articles_display_admin_settings_form() {
  $form['logging'] = array(
    '#type' => 'fieldset',
    '#title' => t('Logging'),
    '#collapsible' => TRUE,
  );
  $form['logging']['fb_instant_articles_display_enable_transformer_logging'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable Instant Article Transformer logging?'),
    '#default_value' => variable_get('fb_instant_articles_display_enable_transformer_logging', FALSE),
  );
  $form = system_settings_form($form);

  return $form;
}
