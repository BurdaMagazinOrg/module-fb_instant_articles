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
  $form['general']['fb_instant_articles_channel_title'] = array(
    '#type' => 'textfield',
    '#title' => t('Channel Title'),
    '#default_value' => variable_get('fb_instant_articles_channel_title', variable_get('site_name', t('Default site name'))),
    '#size' => 60,
  );
  $form['general']['fb_instant_articles_channel_description'] = array(
    '#type' => 'textfield',
    '#title' => t('Channel Description'),
    '#default_value' => variable_get('fb_instant_articles_channel_description', variable_get('site_slogan', t('Default site slogan.'))),
    '#size' => 140,
  );
  $form['general']['fb_instant_articles_channel_language'] = array(
    '#type' => 'textfield',
    '#title' => t('Channel Language'),
    '#default_value' => variable_get('fb_instant_articles_channel_language', 'en-gb'),
    '#size' => 10,
  );
  $form['general']['fb_instant_articles_style'] = array(
    '#type' => 'textfield',
    '#title' => t('Article Style'),
    '#default_value' => variable_get('fb_instant_articles_style', 'default'),
    '#size' => 50,
  );
  $form['general']['fb_instant_articles_number_results_in_feed'] = array(
    '#type' => 'textfield',
    '#title' => t('Number of results to display in the feed.'),
    '#default_value' => variable_get('fb_instant_articles_number_results_in_feed', 50),
    '#size' => 10,
  );
  $form['general']['fb_instant_articles_all_enabled_default'] = array(
    '#type' => 'checkbox',
    '#title' => t('All nodes enabled by default?'),
    '#default_value' => variable_get('fb_instant_articles_all_enabled_default', TRUE),
  );
  $form = system_settings_form($form);

  return $form;
}
