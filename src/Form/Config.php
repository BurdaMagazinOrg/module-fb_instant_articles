<?php

/**
 * @file
 * Contains Drupal\fb_instant_articles\Form\Config.
 */

namespace Drupal\fb_instant_articles\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Config.
 *
 * @package Drupal\fb_instant_articles\Form
 */
class Config extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'fb_instant_articles.adminconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fia_admin_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('fb_instant_articles.adminconfig');
    $form['pagesid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('FaceBook application ID'),
      '#description' => $this->t('The facebook application id is used in the drupal site, to identify the site to facebook, as participating in facebook applications.  The primary impact is the addition of a metatag to the drupal application <meta property="fb:pages" content="{application id}"/>.'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('pagesid'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('fb_instant_articles.adminconfig')
      ->set('pagesid', $form_state->getValue('pagesid'))
      ->save();
  }

}
