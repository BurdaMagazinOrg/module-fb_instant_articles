<?php

/**
 * @file
 * Settings for Facebook Instant Articles Base module.
 */

/**
 * Form constructor for Facebook Instant Articles Base settings form.
 */
function fb_instant_articles_settings() {
  // Initialize this module's settings form
  $form = array();

  // Add the page id configuration.
  $args = array(
    '@claim_url' => 'https://developers.facebook.com/docs/instant-articles/claim-url',
  );
  $form['fb_instant_articles_page_id'] = array(
    '#title' => t('Facebook Page ID'),
    '#type' => 'textfield',
    '#default_value' => variable_get('fb_instant_articles_page_id'),
    '#description' => t('In order to designate the domain that will host your
      articles you must add your Facebook page ID to a metatag in the HEAD tag
      of your HTML page. Entering your Facebook Page ID here will add the
      metatag automatically. See <a href="@claim_url">Claiming your URL
      </a>.', $args),
  );

  // Add the style configuration.
  $form['fb_instant_articles_style'] = array(
    '#type' => 'textfield',
    '#title' => t('Article Style'),
    '#default_value' => variable_get('fb_instant_articles_style', 'default'),
    '#size' => 30,
    '#element_validate' => array('fb_instant_articles_validate_style'),
    '#description' => t('Assign your Instant Articles a custom style. To begin, customize a template using the <a href="@style_url" target="_blank">Style Editor</a>. Next, input the name of the style below. <strong>Note</strong>: if this field is left blank, the module will enable the “Default” style. Learn more about Instant Articles style options in the <a href="@design_url" target="_blank">Design Guide</a>.', array('@style_url' => '', '@design_url' => 'https://developers.facebook.com/docs/instant-articles/guides/design')),
  );

  // Add the Ads sub-section.
  $form = fb_instant_articles_module_config_ads($form);

  // Add the Analytics sub-section.
  $form = fb_instant_articles_module_config_analytics($form);

  // Add the Debug Configuration.
  $form['fb_instant_articles_enable_logging'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable FB Instant Articles SDK logging?'),
    '#default_value' => variable_get('fb_instant_articles_enable_logging'),
    '#description' => t('Sends Facebook Instant Articles SDK logging messages to Drupal watchdog.'),
  );

  $form = system_settings_form($form);
  return $form;
}

/**
 * Generates Ads sub-section of Module Configuration section
 */
function fb_instant_articles_module_config_ads($form) {
  $form['ads'] = array(
    '#type' => 'fieldset',
    '#title' => t('Ads'),
    '#collapsible' => TRUE,
    '#description' => t('Choose your preferred method for displaying ads in your Instant Articles and input the code in the boxes below. Learn more about your options for <a href="@ads_url">advertising in Instant Articles</a>', array('@ads_url' => 'https://developers.facebook.com/docs/instant-articles/ads-analytics')),
  );

  $form['ads']['fb_instant_articles_ad_type'] = array(
    '#type' => 'select',
    '#title' => t('Ad Type'),
    '#default_value' => variable_get('fb_instant_articles_ad_type', FB_INSTANT_ARTICLES_AD_TYPE_NONE),
    '#options' => fb_instant_articles_get_ad_types(),
    '#description' => t('<strong>Note:</strong> this module will automatically place the ads within your articles.'),
    '#attributes' => array('class' => array('ad-type')),
  );

  $form['ads']['fb_instant_articles_ads_iframe_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Ad Source URL'),
    '#default_value' => variable_get('fb_instant_articles_ads_iframe_url'),
    '#description' => t('<strong>Note:</strong> Instant Articles only supports Direct Sold ads. No programmatic ad networks, other than Facebook\'s Audience Network, are permitted.'),
    '#size' => 80,
    '#element_validate' => array('fb_instant_articles_validate_ad_source_url'),
    '#states' => array(
      'visible' => array(
        '[name=fb_instant_articles_ad_type]' => array('value' => FB_INSTANT_ARTICLES_AD_TYPE_SOURCE_URL),
      ),
    ),
  );

  $form['ads']['fb_instant_articles_ads_an_placement_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Audience Network Placement ID'),
    '#default_value' => variable_get('fb_instant_articles_ads_an_placement_id', ''),
    '#description' => t('Find your <a href="@placement_id_url" target="_blank">Placement ID</a> on your app\'s <a href="@audience_network_url" target="_blank">Audience Network Portal</a>.', array('@placement_id_url' => '', '@audience_netowrk_url' => '')),
    '#size' => 30,
    '#element_validate' => array('fb_instant_articles_validate_an_placement_id'),
    '#states' => array(
      'visible' => array(
        '[name=fb_instant_articles_ad_type]' => array('value' => FB_INSTANT_ARTICLES_AD_TYPE_FBAN),
      ),
    ),
  );

  $form['ads']['fb_instant_articles_ads_dimensions'] = array(
    '#type' => 'select',
    '#title' => t('Ad Dimensions'),
    '#options' => array(
      '300x250' => t('Large (300 x 250)'),
    ),
    '#default_value' => variable_get('fb_instant_articles_ads_dimensions'),
  );

  $form['ads']['fb_instant_articles_ads_embed_code'] = array(
    '#type' => 'textarea',
    '#title' => t('Ad Embed Code'),
    '#default_value' => variable_get('fb_instant_articles_ads_embed_code'),
    '#description' => t('Add code to be used for displayed ads in your Instant Articles.'),
    '#size' => 30,
    '#element_validate' => array('fb_instant_articles_validate_ad_embed_code'),
    '#states' => array(
      'visible' => array(
        '[name=fb_instant_articles_ad_type]' => array('value' => FB_INSTANT_ARTICLES_AD_TYPE_EMBED_CODE),
      ),
    ),
  );

  return $form;
}

/**
 * Generates Analytics sub-section of Module Configuration section
 */
function fb_instant_articles_module_config_analytics($form) {
  $form['analytics'] = array(
    '#type' => 'fieldset',
    '#title' => t('Analytics'),
    '#collapsible' => TRUE,
    '#description' => t('Enable 3rd-party analytics to be used with Instant Articles. You can embed code to insert your own trackers and analytics. Learn more about <a href="@analytics_url">analytics in Instant Articles</a>.', array('@analytics_url' => 'https://developers.facebook.com/docs/instant-articles/ads-analytics#analytics')),
  );

  $form['analytics']['fb_instant_articles_analytics_embed_code'] = array(
    '#type' => 'textarea',
    '#title' => t('Analytics Embed Code'),
    '#default_value' => variable_get('fb_instant_articles_analytics_embed_code'),
    '#description' => t('Add code for any analytics services you wish to use. <strong>Note:</strong> you do not need to include any &lt;op-tracker&gt; tags. The module will automatically include them in the article markup.'),
    '#size' => 30,
  );

  return $form;
}

/**
 * Validation handler for Style
 */
function fb_instant_articles_validate_style($element, &$form_state, $form) {
  if (empty($element['#value'])) {
    form_error($element, t('You must specify a style for your Instant Articles.'));
  }
}

/**
 * Validation handler for Audience Network Placement ID
 */
function fb_instant_articles_validate_an_placement_id($element, &$form_state, $form) {
  // Only validate if Audience Network is selected as ad type
  if ($form_state['values']['fb_instant_articles_ad_type'] != FB_INSTANT_ARTICLES_AD_TYPE_FBAN) {
    return;
  }

  if (empty($element['#value'])) {
    form_error($element, t('You must specify a valid Placement ID when using the Audience Network ad type.'));
  }

  if (!(is_numeric($element['#value']))) {
    form_error($element, t('You must specify a valid Placement ID when using the Audience Network ad type.'));
  }
}

/**
 * Validation handler for Ads Source URL
 */
function fb_instant_articles_validate_ad_source_url($element, &$form_state, $form) {
  // Only validate if Source URL is selected as ad type
  if ($form_state['values']['fb_instant_articles_ad_type'] != FB_INSTANT_ARTICLES_AD_TYPE_SOURCE_URL) {
    return;
  }

  if (empty($element['#value'])) {
    form_error($element, t('You must specify a valid source URL for your Ads when using the Source URL ad type.'));
  }

  if (filter_var($element['#value'], FILTER_VALIDATE_URL) === FALSE) {
    form_error($element, t('You must specify a valid source URL for your Ads when using the Source URL ad type.'));
  }
}

/**
 * Validation handler for Ads Embed Code
 */
function fb_instant_articles_validate_ad_embed_code($element, &$form_state, $form) {
  // Only validate if Embed Code is selected as ad type
  if ($form_state['values']['fb_instant_articles_ad_type'] != FB_INSTANT_ARTICLES_AD_TYPE_EMBED_CODE) {
    return;
  }

  if (empty($element['#value'])) {
    form_error($element, t('You must specify Embed Code for your Ads when using the Embed Code ad type.'));
  }
}
