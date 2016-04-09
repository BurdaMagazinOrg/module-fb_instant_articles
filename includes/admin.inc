<?php

/**
 * @file
 * Settings for Facebook Instant Articles Base module.
 */

/**
 * Form constructor for Facebook Instant Articles Base settings form.
 */
function fb_instant_articles_settings() {
  $args = array(
    '@claim_url' => 'https://developers.facebook.com/docs/instant-articles/claim-url',
  );

  $form['fb_instant_articles_page_id'] = array(
    '#title' => t('Facebook Page ID'),
    '#type' => 'textfield',
    '#default_value' => variable_get('fb_instant_articles_page_id'),
    '#description' => t('In order to designate the domain that will host your 
      articles you must add your Facebook page ID to a metatag in the HEAD tag 
      of your HTML page. See <a href="@claim_url">Claiming your URL</a>.', $args),
  );

  $form['fb_instant_articles_enable_logging'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable FB Instant Articles SDK logging?'),
    '#default_value' => variable_get('fb_instant_articles_enable_logging'),
    '#description' => t('Sends Facebook Instant Articles SDK logging messages to Drupal watchdog.'),
  );

  return system_settings_form($form);
}
