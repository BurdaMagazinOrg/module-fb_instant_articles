<?php

/**
 * @file
 * Settings for Facebook Instant Articles Base module.
 */

define('FB_INSTANT_ARTICLES_AD_TYPE_NONE', 'None');
define('FB_INSTANT_ARTICLES_AD_TYPE_FBAN', 'Facebook Audience Network');
define('FB_INSTANT_ARTICLES_AD_TYPE_SOURCE_URL', 'Source URL');
define('FB_INSTANT_ARTICLES_AD_TYPE_EMBED_CODE', 'Embed Code');

/**
 * Form constructor for Facebook Instant Articles Base settings form.
 */
function fb_instant_articles_settings() {

  // Initialize this module's settings form
  $form = array();

  // Generate the Instant Articles Configuration section of the settings form
  $form = fb_instant_articles_ia_config($form);

  // Generate the Module Configuration section of the settings form
  $form = fb_instant_articles_module_config($form);

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

  $form = system_settings_form($form);
  return $form;
}

/**
 * Generates Instant Articles Configuration section of this module's settings form
 */
function fb_instant_articles_ia_config($form) {

  $form['fb_instant_articles']['ia_configuration'] = array(
    '#type' => 'fieldset',
    '#title' => t('Instant Articles Configuration'),
    '#collapsible' => TRUE,
    '#attributes' => array('style' => array('width:800px')),
  );
  $form['fb_instant_articles']['ia_configuration']['content'] = array(
    '#markup' => '
      <div>
        <p>Once you\'ve activated this Drupal module, set up your Instant Articles and submit them to Facebook for a one-time review. The review is required before you can begin publishing. Follow these steps to get started:</p>
        <ol>
          <li>Sign up for Instant Articles, if you haven\'t already, and enabled the same Facebook Page you selected in the step above.</li>
          <li>Claim the URL you will use to publish articles.</b> Right now, we think the URL for this page is: <b>'. $_SERVER['HTTP_HOST'].'</b>. <a href="#" target="_blank">Claim your URL here.</a></li>
          <li>Install the Pages Manager App to preview your articles and styles on <a href="http://itunes.apple.com/app/facebook-pages-manager/id514643583?ls=1&mt=8&ign-mscache=1" target="_blank">iOS</a> or <a href="https://play.google.com/store/apps/details?id=com.facebook.pages.app" target="_blank">Android</a>.</li>
          <li>Create a style template for your articles using the <a href="" target="_blank">Style Editor</a>. Be sure to provide the name of the template you want to use in the Module Configuration settings below.</li>
          <li>[Optional] Enable Audience Network, if you choose. Learn more about <a href="https://fbinstantarticles.files.wordpress.com/2016/03/audience-network_wp_instant-articles-2-2-web_self-serve.pdf" target="_blank">Audience Network</a> for Instant Articles and <a href="" target="_blank">sign up here</a>. </li>
          <li>[Optional] Set up your ads and analytics, including Audience Network, in the Configuration area, below.</li>
          <li><a href="" target="_blank">Submit your articles</a> for review.</li>
        </ol>
        <p>Other Resources:</p>
        <ol>
          <li>Read the <a href="https://developers.facebook.com/docs/instant-articles" target="_blank">documentation</a> to answer additional questions you might have about Instant Articles.</li>
          <li>Check out the <a href="https://developers.facebook.com/ia/blog/" target="_blank">Instant Articles blog</a> and <a href="" target="_blank">sign up</a> to receive notifications of important updates. </li>
          <li>To give other members of your team access to the <a href="" target="_blank">Instant Articles tools</a>, assign them <a href="" target="_blank">page roles here</a>.</li>
        </ol>
      </div>
      ',
  );

  return $form;
}

/**
 * Generates Module Configuration section of this module's settings form
 */
function fb_instant_articles_module_config($form){

  $form['fb_instant_articles']['module_config'] = array(
    '#type' => 'fieldset',
    '#title' => t('Module Configuration'),
    '#collapsible' => TRUE,
    '#attributes' => array('style' => array('width:800px')),
  );

  // Add the Style sub-section
  $form = fb_instant_articles_module_config_style($form);

  // Add the Ads sub-section
  $form = fb_instant_articles_module_config_ads($form);

  // Add the Analytics sub-section
  $form = fb_instant_articles_module_config_analytics($form);

  // Add the Debug Configuration sub-section
  $form = fb_instant_articles_module_config_debug($form);

  return $form;
}

/**
 * Generates Style sub-section of Module Configuration section
 */
function fb_instant_articles_module_config_style($form) {

  $form['fb_instant_articles']['module_config']['style'] = array(
    '#type' => 'container',
    '#attributes' => array('class' => array('style')),
  );
  $form['fb_instant_articles']['module_config']['style']['style_header'] = array(
    '#markup' => '
      <div>
        <h4>' .t('Styles') . '</h4>
        <p>Assign your Instant Articles a custom style. To begin, customize a template using the <a href="" target="_blank">Style Editor</a>. Next, input the name of the style below.</p>
        <p><b>Note</b>: If this field is left blank, the module will enable the “Default” style. Learn more about Instant Articles style options in the <a href="(https://developers.facebook.com/docs/instant-articles/guides/design" target="_blank">Design Guide</a>.</p>
      </div>
      ',
  );
  $form['fb_instant_articles']['module_config']['style']['fb_instant_articles_style'] = array(
    '#type' => 'textfield',
    '#title' => t('Article Style'),
    '#default_value' => variable_get('fb_instant_articles_style', 'default'),
    '#size' => 30,
    '#element_validate' => array('fb_instant_articles_validate_style'),
  );
  $form['fb_instant_articles']['module_config']['style']['style_footer'] = array(
    '#markup' => '
      <div style="height:20px">
        <hr>
      </div>
    ',
  );

  return $form;
}

/**
 * Generates Ads sub-section of Module Configuration section
 */
function fb_instant_articles_module_config_ads($form) {

  $form['fb_instant_articles']['module_config']['ads'] = array(
    '#type' => 'container',
    '#attributes' => array('class' => array('ads')),
  );

  $form['fb_instant_articles']['module_config']['ads']['ad_header'] = array(
    '#markup' => '
      <div>
        <h4>' .t('Ads') . '</h4>
        <p>Choose your preferred method for displaying ads in your Instant Articles and input the code in the boxes below. Learn more about your options for <a href="https://developers.facebook.com/docs/instant-articles/ads-analytics">advertising in Instant Articles</a>.</p>
      </div>
      ',
  );
  $form['fb_instant_articles']['module_config']['ads']['fb_instant_articles_ad_type'] = array(
    '#type' => 'select',
    '#title' => t('Ad Type'),
    '#default_value' => variable_get('fb_instant_articles_ad_type', FB_INSTANT_ARTICLES_AD_TYPE_NONE),
    '#options' => array(
      FB_INSTANT_ARTICLES_AD_TYPE_NONE => FB_INSTANT_ARTICLES_AD_TYPE_NONE,
      FB_INSTANT_ARTICLES_AD_TYPE_FBAN => FB_INSTANT_ARTICLES_AD_TYPE_FBAN,
      FB_INSTANT_ARTICLES_AD_TYPE_SOURCE_URL => FB_INSTANT_ARTICLES_AD_TYPE_SOURCE_URL,
      FB_INSTANT_ARTICLES_AD_TYPE_EMBED_CODE => FB_INSTANT_ARTICLES_AD_TYPE_EMBED_CODE,
    ),
    '#description' => '<div>' .t('This module will automatically place the ads within your articles.')  . '</div>',
    '#attributes' => array('class' => array('ad-type')),
  );
  $form['fb_instant_articles']['module_config']['ads']['fb_instant_articles_ads_iframe_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Source URL'),
    '#default_value' => variable_get('fb_instant_articles_ads_iframe_url', ''),
    '#description' => '<div>' .t('<b>Note:</b> Instant Articles only supports Direct Sold ads. No programmatic ad networks, other than Facebook\'s Audience Network, are permitted.') .'</div>',
    '#size' => 80,
    '#element_validate' => array('fb_instant_articles_validate_ad_source_url'),
    '#attributes' => array('class' => array('ad-iframe-url')),
  );
  $form['fb_instant_articles']['module_config']['ads']['fb_instant_articles_ads_an_placement_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Audience Network Placement ID'),
    '#default_value' => variable_get('fb_instant_articles_ads_an_placement_id', ''),
    '#description' => '<div>' .t('Find your <a href="" target="_blank">Placement ID</a> on your app\'s <a href="" target="_blank">Audience Network Portal</a>.')  . '</div>',
    '#size' => 30,
    '#element_validate' => array('fb_instant_articles_validate_an_placement_id'),
    '#attributes' => array('class' => array('ad-an-placement-id')),
  );
  $form['fb_instant_articles']['module_config']['ads']['fb_instant_articles_ads_dimension'] = array(
    '#type' => 'select',
    '#title' => t('Ad Dimensions'),
    '#options' => array(
      0 => t('Large (300 x 250)'),
    ),
    '#default_value' => variable_get('fb_instant_articles_ads_dimension', ''),
    '#attributes' => array('class' => array('ad-dimension')),
  );
  $form['fb_instant_articles']['module_config']['ads']['fb_instant_articles_ads_embed_code'] = array(
    '#type' => 'textarea',
    '#title' => t('Embed Code'),
    '#default_value' => variable_get('fb_instant_articles_ads_embed_code', ''),
    '#description' => '<div>' .t('Add code to be used for displayed ads in your Instant Articles.')  . '</div>',
    '#size' => 30,
    '#element_validate' => array('fb_instant_articles_validate_ad_embed_code'),
  );

  $form['fb_instant_articles']['module_config']['ads']['ads_footer'] = array(
    '#markup' => '
      <div class="ads-footer" style="height:20px">
        <hr>
      </div>
    ',
  );
  return $form;
}

/**
 * Generates Analytics sub-section of Module Configuration section
 */
function fb_instant_articles_module_config_analytics($form){

  $form['fb_instant_articles']['module_config']['analytics'] = array(
    '#type' => 'container',
    '#attributes' => array('class' => array('analytics')),
  );

  $form['fb_instant_articles']['module_config']['analytics']['analytics_header'] = array(
    '#markup' => '
      <div>
        <h4>' .t('Analytics') . '</h4>
        <p>Enable 3rd-party analytics to be used with Instant Articles. You can embed code to insert your own trackers and analytics. Learn more about <a href="https://developers.facebook.com/docs/instant-articles/ads-analytics#analytics">Analytics in Instant Articles</a>.</p>
      </div>
      ',
  );
  $form['fb_instant_articles']['module_config']['analytics']['fb_instant_articles_analytics_embed_code'] = array(
    '#type' => 'textarea',
    '#title' => t('Embed Code'),
    '#default_value' => variable_get('fb_instant_articles_analytics_embed_code', ''),
    '#description' => '<div>' .t('Add code for any analytics services you wish to use. <b>Note:</b> You do not need to include any <op-tracker> tags. The module will automatically include them in the article markup.')  . '</div>',
    '#size' => 30,
  );
  $form['fb_instant_articles']['module_config']['analytics']['analytics_footer'] = array(
    '#markup' => '
      <div style="height:20px">
        <hr>
      </div>
    ',
  );

  return $form;
}

/**
 * Generates Debug Configuration sub-section of Module Configuration section
 */
function fb_instant_articles_module_config_debug($form) {

  $form['fb_instant_articles']['module_config']['debug'] = array(
    '#type' => 'container',
    '#attributes' => array('class' => array('debug')),
  );

  $form['fb_instant_articles']['module_config']['debug']['debug_header'] = array(
    '#markup' => '
      <div>
        <h4>' .t('Debug Configuration') . '</h4>
      </div>
      ',
  );
  $form['fb_instant_articles']['module_config']['debug']['fb_instant_articles_enable_logging'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable FB Instant Articles SDK logging?'),
    '#default_value' => variable_get('fb_instant_articles_enable_logging'),
    '#description' => t('Sends Facebook Instant Articles SDK logging messages to Drupal watchdog.'),
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
