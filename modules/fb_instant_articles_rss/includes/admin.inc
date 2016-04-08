<?php

/**
 * @file
 * Settings for Facebook Instant Articles RSS module.
 */

/**
 * Form constructor for Facebook Instant Articles RSS settings form.
 */
function fb_instant_articles_rss_settings() {
  $form['fb_instant_articles_rss_channel_title'] = array(
    '#type' => 'textfield',
    '#title' => t('Channel Title'),
    '#default_value' => variable_get('fb_instant_articles_rss_channel_title', variable_get('site_name', t('Default site name'))),
    '#size' => 60,
  );

  $form['fb_instant_articles_rss_channel_description'] = array(
    '#type' => 'textfield',
    '#title' => t('Channel Description'),
    '#default_value' => variable_get('fb_instant_articles_rss_channel_description', variable_get('site_slogan', t('Default site slogan.'))),
    '#size' => 140,
  );

  $form['fb_instant_articles_rss_channel_language'] = array(
    '#type' => 'textfield',
    '#title' => t('Channel Language'),
    '#default_value' => variable_get('fb_instant_articles_rss_channel_language', 'en-gb'),
    '#size' => 10,
  );

  $form['fb_instant_articles_rss_style'] = array(
    '#type' => 'textfield',
    '#title' => t('Article Style'),
    '#default_value' => variable_get('fb_instant_articles_rss_style', 'default'),
    '#size' => 50,
  );

  $form['fb_instant_articles_rss_number_results_in_feed'] = array(
    '#type' => 'textfield',
    '#title' => t('Number of results to display in the feed.'),
    '#default_value' => variable_get('fb_instant_articles_rss_number_results_in_feed', 50),
    '#size' => 10,
  );

  $form['fb_instant_articles_rss_all_enabled_default'] = array(
    '#type' => 'checkbox',
    '#title' => t('All nodes enabled by default?'),
    '#default_value' => variable_get('fb_instant_articles_rss_all_enabled_default', TRUE),
  );

  return system_settings_form($form);
}
