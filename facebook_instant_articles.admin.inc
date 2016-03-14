<?php
/**
 * @file
 * Facebook instant articles admin module file.
 */


/**
 * Admin config form for Facebook instant articles.
 *
 * @param $form
 * @param $form_state
 */
function facebook_instant_articles_config_form($form, &$form_state) {
  $form['fia_pagesid'] = array(
    '#type' => 'textfield',
    '#title' => t('FaceBook application ID'),
    '#description' => t('The facebook application id is used in the drupal site, to identify the site to facebook, as participating in facebook applications.  The primary impact is the addition of a metatag to the drupal application <meta property="fb:pages" content="{application id}"/>.'),
    '#maxlength' => 64,
    '#size' => 64,
    '#default_value' => variable_get('fia_pagesid', ''),
  );
  return system_settings_form($form);
}
