<?php
/**
 * @file
 * Provides the Facebook Instant Articles administrative interface.
 */

/**
 * Admin settings form.
 */
function fb_instant_articles_admin_settings_form() {
  $form['general'] = array(
    '#type' => 'fieldset',
    '#title' => t('General Settings'),
    '#collapsible' => TRUE,
  );
  $form['general']['fb_instant_articles_page_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Article Page Id'),
    '#default_value' => variable_get('fb_instant_articles_page_id', ''),
    '#description' => t('Includes the meta tag to claim your domain.'),
    '#size' => 30,
  );
  $form = system_settings_form($form);

  return $form;
}
