<?php

/**
 * @file
 * Settings for Facebook Instant Articles API module.
 */

/**
 * Form constructor for Facebook Instant Articles API settings form.
 */
function fb_instant_articles_api_settings() {
  $args = array(
    '@development_mode' => 'https://developers.facebook.com/docs/instant-articles/api#create-fields',
  );

  // The Facebook app connection info could alternatively be stored via the fb
  // module, but it's not yet ported to D8, and a bit cumbersome just for this.
  $form['fb_instant_articles_api_app_id'] = array(
    '#title' => t('Facebook App ID'),
    '#type' => 'textfield',
    '#default_value' => variable_get('fb_instant_articles_api_app_id'),
  );

  $form['fb_instant_articles_api_app_secret'] = array(
    '#title' => t('Facebook App Secret'),
    '#type' => 'textfield',
    '#default_value' => variable_get('fb_instant_articles_api_app_secret'),
  );

  $form['fb_instant_articles_api_development_mode'] = array(
    '#title' => t('Facebook Instant Articles development mode'),
    '#type' => 'checkbox',
    '#default_value' => variable_get('fb_instant_articles_api_development_mode'),
    '#description' => t('Articles published from a site in development mode can 
      never be taken live. See <a href="@development_mode">development_mode</a> 
      in the API documentation.', $args),
  );

  return system_settings_form($form);
}
