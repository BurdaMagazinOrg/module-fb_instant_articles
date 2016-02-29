<?php
/**
 * @file
 * Provides the Facebook Instant Articles administrative interface.
 */

/**
 * Admin settings form.
 */
function facebook_instant_articles_admin_settings_form() {
  $form['general'] = array(
    '#type' => 'fieldset',
    '#title' => t('General Settings'),
    '#collapsible' => TRUE,
  );
  $form['general']['facebook_instant_articles_page_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Article Page Id'),
    '#default_value' => variable_get('facebook_instant_articles_page_id', ''),
    '#description' => t('Includes the meta tag to claim your domain.'),
    '#size' => 30,
  );
  $form['general']['facebook_instant_articles_channel_title'] = array(
    '#type' => 'textfield',
    '#title' => t('Channel Title'),
    '#default_value' => variable_get('facebook_instant_articles_channel_title', variable_get('site_name', t('Default site name'))),
    '#size' => 60,
  );
  $form['general']['facebook_instant_articles_channel_description'] = array(
    '#type' => 'textfield',
    '#title' => t('Channel Description'),
    '#default_value' => variable_get('facebook_instant_articles_channel_description', variable_get('site_slogan', t('Default site slogan.'))),
    '#size' => 140,
  );
  $form['general']['facebook_instant_articles_channel_language'] = array(
    '#type' => 'textfield',
    '#title' => t('Channel Language'),
    '#default_value' => variable_get('facebook_instant_articles_channel_language', 'en-gb'),
    '#size' => 10,
  );
  $form['general']['facebook_instant_articles_style'] = array(
    '#type' => 'textfield',
    '#title' => t('Article Style'),
    '#default_value' => variable_get('facebook_instant_articles_style', 'default'),
    '#size' => 50,
  );
  $form = system_settings_form($form);

  return $form;
}
