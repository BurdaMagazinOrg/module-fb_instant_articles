<?php

/**
 * @file
 * Settings for Facebook Instant Articles API module.
 */

/**
 * Form constructor for Facebook Instant Articles API settings form.
 */
function fb_instant_articles_api_settings() {
  // Initialize this module's settings form
  $form = array();

  // Generate the Module Activation section of the settings form
  $form = fb_instant_articles_api_module_activation($form);

  // Add the Publishing Settings sub-section
  $form = fb_instant_articles_display_module_config_publishing($form);

  return system_settings_form($form);
}

/**
 * Generates Module Activation section of this module's settings form
 */
function fb_instant_articles_api_module_activation($form) {

  // If the person is coming back to edit FB app settings, drop them back
  // into the correct state
  if (array_key_exists('edit', $_GET)) {
    $edit_state = $_GET['edit'];
  } else {
    $edit_state = '';
  }

  // Grab the current module settings from the database to determine where
  // the person is in the configuration state
  $fb_app_id = variable_get('fb_instant_articles_api_app_id', '');
  $fb_app_secret = variable_get('fb_instant_articles_api_app_secret', '');
  $fb_access_token = variable_get('fb_instant_articles_api_access_token', '');
  $fb_page_id = variable_get('fb_instant_articles_page_id', '');

  // If the App ID or App Secret haven't been configured for the module yet,
  // drop the person into the initial state
  if (($fb_app_id == '') || ($fb_app_secret == '') || ($edit_state == 'fb_app_settings')) {
    $form = fb_instant_articles_api_module_activation_fb_app_settings($form);
  } elseif ($fb_access_token == '') {
    // If we don't have the access token yet, have the person connect their
    // Facebook account next
    $form = fb_instant_articles_api_module_activation_connect_fb_account($form);
  } elseif (($fb_page_id == '') || ($edit_state =='fb_page')) {
    // If we have access token but not selected page ID, have the person
    // select the page next
    $form = fb_instant_articles_api_module_activation_select_fb_page($form);
  } else {
    // Everything's been configured, so let's provide the summary view
    $form = fb_instant_articles_api_module_activation_summary($form);
  }
  return $form;
}

/**
 * Generates Facebook App settings state of Module Activation section
 */
function fb_instant_articles_api_module_activation_fb_app_settings($form) {

  $form['fb_instant_articles_api']['module_activation'] = array(
    '#type' => 'fieldset',
    '#title' => t('Module Activation'),
    '#description' => '<div>' .t('You need a Facebook App to publish Instant Articles using this module. If you already have one, input the App ID and App Secret below, which you can find by clicking on your app <a href="https://developers.facebook.com/apps">here</a>. If you don\'t, <a href="https://developers.facebook.com/apps">create one here </a> before continuing.') . '</div>',
    '#collapsible' => TRUE,
    '#attributes' => array('style' => array('width:800px')),
  );
  $form['fb_instant_articles_api']['module_activation']['fb_instant_articles_api_app_id'] = array(
    '#type' => 'textfield',
    '#title' => t('App ID'),
    '#default_value' => variable_get('fb_instant_articles_api_app_id', ''),
    '#size' => 30,
    '#element_validate' => array('fb_instant_articles_api_validate_fb_app_id')
  );
  $form['fb_instant_articles_api']['module_activation']['fb_instant_articles_api_app_secret'] = array(
    '#type' => 'textfield',
    '#title' => t('App Secret'),
    '#default_value' => variable_get('fb_instant_articles_api_app_secret', ''),
    '#size' => 30,
  );
  $form['fb_instant_articles_api']['module_activation']['next'] = array(
    '#type' => 'submit',
    '#value' => 'Next',
    '#submit' => array('fb_instant_articles_api_fb_app_details_submit'),
  );

  return $form;
}

/**
 * Generates state of Facebook account connection for Module Activation section
 */
function fb_instant_articles_api_module_activation_connect_fb_account($form) {

  $fb_app_id = variable_get('fb_instant_articles_api_app_id');
  $fb_app_secret = variable_get('fb_instant_articles_api_app_secret');

  $fb = new Facebook\Facebook([
    'app_id' => $fb_app_id,
    'app_secret' => $fb_app_secret,
    'default_graph_version' => 'v2.5',
  ]);

  $permissions = ['pages_show_list', 'pages_manage_instant_articles'];
  $helper = $fb->getRedirectLoginHelper();

  $redirect_uri = url('admin/config/services/fb-instant-articles/api/login', array('absolute' => TRUE));
  $login_url = $helper->getLoginUrl($redirect_uri, $permissions);

  $form['fb_instant_articles_api']['module_activation'] = array(
    '#type' => 'fieldset',
    '#title' => t('Module Activation'),
    '#collapsible' => TRUE,
    '#attributes' => array('style' => array('width:800px')),
  );
  $form['fb_instant_articles_api']['module_activation']['fb_app_settings'] = array(
    '#markup' => '
      <div>
        <p>Your Facebook App ID is <b>'. $fb_app_id .'</b>. <a href="?edit=fb_app_settings">Click here</a> to update.</p>
        <p>Login to Facebook and then select the Facebook Page where you will publish Instant Articles.</p>
      </div>
    ',
  );

  $form['fb_instant_articles_api']['module_activation']['login_button'] = array(
    '#markup' => '
      <div>
        <a href="'. $login_url .'"><img src="https://fbcdn-dragon-a.akamaihd.net/hphotos-ak-xtf1/t39.2178-6/11405239_920140564714397_256329502_n.png"></img></a>
      </div>
    ',
  );

  return $form;
}

/**
 * Generates Facebook Page selection state of Module Activation section
 */
function fb_instant_articles_api_module_activation_select_fb_page($form) {

  $fb_app_id = variable_get('fb_instant_articles_api_app_id', '');
  $fb_app_secret = variable_get('fb_instant_articles_api_app_secret', '');

  $fb = new Facebook\Facebook([
       'app_id' => $fb_app_id,
       'app_secret' => $fb_app_secret,
       'default_graph_version' => 'v2.5',
   ]);

  $access_token_string = variable_get('fb_instant_articles_api_access_token', '');
  $expires = time() + 60 * 60 * 2;
  $access_token = new Facebook\Authentication\AccessToken($access_token_string, $expires);

  $sdk_helper = new Facebook\InstantArticles\Client\Helper($fb);
  $pages = $sdk_helper->getPagesAndTokens($access_token);

  $form['fb_instant_articles_api']['module_activation'] = array(
    '#type' => 'fieldset',
    '#title' => t('Module Activation'),
    '#collapsible' => TRUE,
    '#attributes' => array('style' => array('width:800px')),
  );
  $form['fb_instant_articles_api']['module_activation']['fb_app_settings'] = array(
    '#markup' => '
      <div>
        <p>Your Facebook App ID is <b>'. $fb_app_id .'</b>. <a href="?edit=fb_app_settings">Click here</a> to update.</p>
        <p>Select the Facebook Page where you will publish Instant Articles.</p>
      </div>
    ',
  );

  $page_options = array();
  foreach ($pages as $page) {
    array_push($page_options, $page['name']);
  }

  $form['fb_instant_articles_api']['module_activation']['fb_instant_articles_page_id'] = array(
    '#type' => 'select',
    '#options' => $page_options,
  );

  $form['fb_instant_articles_api']['module_activation']['next'] = array(
    '#type' => 'submit',
    '#value' => 'Next',
    '#submit' => array('fb_instant_articles_api_fb_page_submit'),
  );

  return $form;
}

/**
 * Generates summary state of Module Activation section
 */
function fb_instant_articles_api_module_activation_summary($form) {

  $fb_app_id = variable_get('fb_instant_articles_api_app_id', '');
  $fb_page_name = variable_get('fb_instant_articles_page_name', '');

  $form['fb_instant_articles_api']['module_activation'] = array(
    '#type' => 'fieldset',
    '#title' => t('Module Activation'),
    '#collapsible' => TRUE,
    '#attributes' => array('style' => array('width:800px')),
  );
  $form['fb_instant_articles_api']['module_activation']['fb_app_settings'] = array(
    '#markup' => '
      <div>
        <p>Your Facebook App ID is <b>'. $fb_app_id .'</b>. <a href="?edit=fb_app_settings">Click here</a> to update.</p>
        <p>Your Facebook Page is <b>'. $fb_page_name .'</b>. <a href="?edit=fb_page">Click here</a> to update.</p>
      </div>
    ',
  );

  return $form;
}

/**
 * Submit handler for Facebook App details
 */
function fb_instant_articles_api_fb_app_details_submit($form, &$form_state) {

  // Store the FB app details in the database
  variable_set('fb_instant_articles_api_app_id', $form_state['values']['fb_instant_articles_api_app_id']);
  variable_set('fb_instant_articles_api_app_secret', $form_state['values']['fb_instant_articles_api_app_secret']);

  // Clear out the token and FB page if there were ones in the database since they're invalid now
  variable_set('fb_instant_articles_api_access_token', '');
  variable_set('fb_instant_articles_page_id', '');
  variable_set('fb_instant_articles_page_name', '');

  $form_state['redirect'] = 'admin/config/services/fb-instant-articles/api';

}

/**
 * Submit handler for Facebook Page selection
 */
function fb_instant_articles_api_fb_page_submit($form, &$form_state) {

  $fb_app_id = variable_get('fb_instant_articles_api_app_id', '');
  $fb_app_secret = variable_get('fb_instant_articles_api_app_secret', '');

  $fb = new Facebook\Facebook([
    'app_id' => $fb_app_id,
    'app_secret' => $fb_app_secret,
    'default_graph_version' => 'v2.5',
  ]);

  $access_token_string = variable_get('fb_instant_articles_api_access_token', '');
  $expires = time() + 60 * 60 * 2;
  $access_token = new Facebook\Authentication\AccessToken($access_token_string, $expires);

  $sdk_helper = new Facebook\InstantArticles\Client\Helper($fb);
  $pages = $sdk_helper->getPagesAndTokens($access_token);

  $fb_page_id = $pages[$form_state['values']['fb_instant_articles_page_id']]['id'];
  variable_set('fb_instant_articles_page_id', $fb_page_id);
  $fb_page_name = $pages[$form_state['values']['fb_instant_articles_page_id']]['name'];
  variable_set('fb_instant_articles_page_name', $fb_page_name);
  $fb_page_access_token = $pages[$form_state['values']['fb_instant_articles_page_id']]['access_token'];
  variable_set('fb_instant_articles_api_access_token', $fb_page_access_token);

  drupal_set_message('Success! This Instant Articles module has been activated.', 'status');
  $form_state['redirect'] = 'admin/config/services/fb-instant-articles/api';

}

/**
 * Validation handler for Facebook App ID
 */
function fb_instant_articles_api_validate_fb_app_id($element, &$form_state, $form) {

  if (empty($element['#value'])) {
    form_error($element, t('You must enter the App ID before proceeding.'));
  }

  if (!(is_numeric($element['#value']))) {
    form_error($element, t('The App ID that you entered is invalid.'));
  }

}

/**
 * Generates Publishing Settings sub-section of Module Configuration section
 */
function fb_instant_articles_display_module_config_publishing($form) {

  $form['fb_instant_articles_api']['module_config']['publishing'] = array(
    '#type' => 'container',
    '#attributes' => array('class' => array('publishing')),
  );

  $form['fb_instant_articles_api']['module_config']['publishing']['publishing_header'] = array(
    '#markup' => '
      <div>
        <h4>' .t('Publishing Settings') . '</h4>
      </div>
      ',
  );
  $form['fb_instant_articles_api']['module_config']['publishing']['fb_instant_articles_api_development_mode'] = array(
    '#type' => 'checkbox',
    '#title' => t('Development Mode'),
    '#default_value' => variable_get('fb_instant_articles_api_development_mode', FALSE),
    '#description' => '<div>' .t('When publishing in development, none of your articles will be made live, and they will be saved as drafts you can edit in the Instant Articles library on your Facebook Page. Whether in development mode or not, articles will not be published live until you have submitted a sample batch to Facebook and passed a one-time review.')  . '</div>',
  );
  $form['fb_instant_articles_api']['module_config']['publishing']['publishing_footer'] = array(
    '#markup' => '
      <div style="height:20px">
        <hr>
      </div>
    ',
  );

  return $form;
}
