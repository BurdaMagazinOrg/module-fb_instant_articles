<?php

namespace Drupal\fb_instant_articles_api\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Facebook\Authentication\AccessToken;
use Facebook\Facebook;
use Facebook\InstantArticles\Client\Helper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Facebook Instant Articles API form.
 */
class ApiSettingsForm extends ConfigFormBase {

  /**
   * Current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Constructs a \Drupal\fb_instant_articles_api\ApiSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Request $current_request) {
    parent::__construct($config_factory);
    $this->currentRequest = $current_request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'fb_instant_articles_api.settings',
      'fb_instant_articles.base_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'api_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Generate the module activation section of the settings form.
    $form = $this->moduleActivationBuildForm($form, $form_state);

    // Add the publishing settings sub-section.
    $form = $this->moduleConfigPublishingBuildForm($form, $form_state);

    return parent::buildForm($form, $form_state);
  }

  /**
   * Generate the module activation section of the settings form.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   Form array.
   */
  protected function moduleActivationBuildForm(array $form, FormStateInterface $form_state) {

    // If the person is coming back to edit FB app settings, drop them back into
    // the correct state.
    $edit_state = '';
    if ($edit = $this->currentRequest->get('edit')) {
      $edit_state = $edit;
    }

    $base_config = $this->config('fb_instant_articles.base_settings');
    $api_config = $this->config('fb_instant_articles_api.settings');

    // Grab the current module settings from the database to determine where
    // the person is in the configuration state.
    $app_id = $api_config->get('app_id');
    $app_secret = $api_config->get('app_secret');
    $access_token = trim($api_config->get('access_token'));
    $page_id = $base_config->get('page_id');

    // If the App ID or App Secret haven't been configured for the module yet,
    // drop the person into the initial state.
    if (empty($app_id) || empty($app_secret) || $edit_state === 'fb_app_settings') {
      $form = $this->moduleActivationFbAppSettings($form, $app_id, $app_secret);
    }
    // If we don't have the access token yet, have the person connect their
    // Facebook account next.
    elseif (empty($access_token)) {
      $form = $this->moduleActivationConnectFbAccount($form, $app_id, $app_secret);
    }
    // If we have access token but not selected page ID, have the person
    // select the page next.
    elseif (empty($page_id) || $edit_state === 'fb_page') {
      $form = $this->moduleActivationSelectFbPage($form, $app_id, $app_secret, $access_token, $page_id);
    }
    // Everything's been configured, so let's provide the summary view.
    else {
      $form = $this->moduleActivationSummary($form, $app_id, $page_id);
    }
    return $form;
  }

  /**
   * Generates state of FB account connection for Module Activation section.
   *
   * @param array $form
   *   FAPI array.
   * @param string $app_id
   *   Facebook application id.
   * @param string $app_secret
   *   Facebook application secret.
   *
   * @return array
   *   FAPI array.
   */
  protected function moduleActivationFbAppSettings(array $form, $app_id, $app_secret) {
    $form['module_activation'] = [
      '#type' => 'details',
      '#title' => t('Module activation'),
      '#description' => $this->t('You need a Facebook App to publish Instant Articles using this module. If you already have one, input the App ID and App Secret below, which you can find by clicking on your app <a href="https://developers.facebook.com/apps">here</a>. If you don\'t, <a href="https://developers.facebook.com/apps">create one here </a> before continuing.'),
      '#open' => TRUE,
    ];

    $form['module_activation']['app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#default_value' => $app_id,
      '#size' => 30,
      '#element_validate' => [
        [$this, 'validateFbAppId'],
      ],
    ];

    $form['module_activation']['app_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App Secret'),
      '#default_value' => $app_secret,
      '#size' => 30,
    ];

    $form['module_activation']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#submit' => [
        [$this, 'fbAppDetailsSubmit'],
      ],
    ];

    return $form;
  }

  /**
   * Validate the Facebook application id.
   *
   * @param array $form
   *   FAPI array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function validateFbAppId(array &$form, FormStateInterface $form_state) {
    $app_id = $form_state->getValue('app_id');
    if (empty($app_id)) {
      $form_state->setErrorByName('app_id', $this->t('You must enter the App ID before proceeding.'));
    }

    if (!is_numeric($app_id)) {
      $form_state->setErrorByName('app_id', $this->t('The App ID that you entered is invalid.'));
    }
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   FAPI array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function fbAppDetailsSubmit(array &$form, FormStateInterface $form_state) {
    $this->config('fb_instant_articles_api.settings')
      // Save the FB app details.
      ->set('app_id', $form_state->getValue('app_id'))
      ->set('app_secret', $form_state->getValue('app_secret'))
      // Clear out the token if there was one since it's invalid now.
      ->set('access_token', '')
      ->set('page_access_token', '')
      ->save();

    // Clear out FB page if there was one it's invalid now.
    $this->config('fb_instant_articles.base_settings')
      ->set('page_id', '')
      ->set('page_name', '')
      ->save();

    $form_state->setRedirect('fb_instant_articles_api.settings_form');
  }

  /**
   * Generates state of FB account connection for Module Activation section.
   *
   * @param array $form
   *   FAPI array.
   * @param string $app_id
   *   Facebook application id.
   * @param string $app_secret
   *   Facebook application secret.
   *
   * @return array
   *   FAPI array.
   */
  protected function moduleActivationConnectFbAccount(array $form, $app_id, $app_secret) {
    $fb = new Facebook([
      'app_id' => $app_id,
      'app_secret' => $app_secret,
      'default_graph_version' => 'v2.5',
    ]);

    $permissions = ['pages_show_list', 'pages_manage_instant_articles'];
    $helper = $fb->getRedirectLoginHelper();

    $redirect_uri = Url::fromRoute('fb_instant_articles_api.login_callback', [], ['absolute' => TRUE])->toString();
    $login_url = $helper->getLoginUrl($redirect_uri, $permissions);

    $form['module_activation'] = [
      '#type' => 'details',
      '#title' => $this->t('Module activation'),
      '#open' => TRUE,
    ];
    $form['module_activation']['app_settings'] = [
      '#markup' => '
        <p>' . $this->t('Your Facebook App ID is <strong>@app_id</strong>. <a href="?edit=fb_app_settings">Update Facebook app id</a>.', ['@app_id' => $app_id]) . '</p>
        <p>' . $this->t('Login to Facebook and then select the Facebook Page where you will publish Instant Articles.') . '</p>
      ',
    ];

    $form['module_activation']['login_button'] = [
      '#markup' => '<p>' . $this->t('<a class="button button--secondary" href="@login_url">Login with Facebook</a>', ['@login_url' => $login_url]) . '</p>',
    ];

    return $form;
  }

  /**
   * Generates state of FB account connection for Module Activation section.
   *
   * @param array $form
   *   FAPI array.
   * @param string $app_id
   *   Facebook application id.
   * @param string $app_secret
   *   Facebook application secret.
   * @param string $access_token
   *   Facebook access token.
   * @param string $page_id
   *   Facebook page id.
   *
   * @return array
   *   FAPI array.
   */
  protected function moduleActivationSelectFbPage(array $form, $app_id, $app_secret, $access_token, $page_id) {
    $fb = new Facebook([
      'app_id' => $app_id,
      'app_secret' => $app_secret,
      'default_graph_version' => 'v2.5',
    ]);

    $expires = time() + 60 * 60 * 2;
    $access_token = new AccessToken($access_token, $expires);

    $sdk_helper = new Helper($fb);
    $pages = $sdk_helper->getPagesAndTokens($access_token);

    $form['module_activation'] = [
      '#type' => 'details',
      '#title' => t('Module activation'),
      '#open' => TRUE,
    ];
    $form['module_activation']['fb_app_settings'] = [
      '#markup' => '<p>' . $this->t('Your Facebook App ID is <strong>@app_id</strong>. <a href="?edit=fb_app_settings">Update Facebook app id</a>.', ['@app_id' => $app_id]) . '</p>',
    ];

    $page_options = [];
    foreach ($pages as $page) {
      $page_options[$page->getField('id')] = $page->getField('name');
    }

    $form['module_activation']['page_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Facebook page'),
      '#description' => $this->t('Select the Facebook page where you will publish Instant Articles.'),
      '#options' => $page_options,
      '#default_value' => $page_id,
    ];

    $form['module_activation']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#submit' => [
        [$this, 'fbPageSubmit'],
      ],
    ];

    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   FAPI array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function fbPageSubmit(array &$form, FormStateInterface $form_state) {
    $base_config = $this->config('fb_instant_articles.base_settings');
    $api_config = $this->config('fb_instant_articles_api.settings');
    $app_id = $api_config->get('app_id');
    $app_secret = $api_config->get('app_secret');

    $fb = new Facebook([
      'app_id' => $app_id,
      'app_secret' => $app_secret,
      'default_graph_version' => 'v2.5',
    ]);

    $access_token = $api_config->get('access_token');
    $expires = time() + 60 * 60 * 2;
    $access_token = new AccessToken($access_token, $expires);

    $sdk_helper = new Helper($fb);
    $pages = $sdk_helper->getPagesAndTokens($access_token);

    $page_id = $form_state->getValue('page_id');
    foreach ($pages as $page) {
      if ($page->getField('id') === $page_id) {
        $page_name = $page->getField('name');
        $page_access_token = $page->getField('access_token');
        break;
      }
    }
    if ($page_name && $page_access_token) {
      $base_config
        ->set('page_id', $page_id)
        ->set('page_name', $page_name)
        ->save();
      $api_config
        ->set('page_access_token', $page_access_token)
        ->save();
      drupal_set_message('Success! This Instant Articles module has been activated.');
      $form_state->setRedirect('fb_instant_articles_api.settings_form');
    }
    else {
      drupal_set_message('There was an error connecting with your Facebook page. Try again.', 'error');
    }
  }

  /**
   * Generates summary state of Module Activation section.
   *
   * @param array $form
   *   FAPI array.
   * @param string $app_id
   *   Facebook application id.
   * @param string $page_id
   *   Facebook page id.
   *
   * @return array
   *   FAPI array.
   */
  protected function moduleActivationSummary(array $form, $app_id, $page_id) {
    $page_name = $this->config('fb_instant_articles.base_settings')->get('page_name');

    $form['module_activation'] = [
      '#type' => 'details',
      '#title' => t('Module activation'),
      '#open' => TRUE,
    ];
    $markup = [
      '<p>' . $this->t('Your Facebook App ID is <strong>@app_id</strong>. <a href="?edit=fb_app_settings">Update Facebook app id</a>.', ['@app_id' => $app_id]) . '</p>',
    ];
    if ($page_name) {
      $markup[] = '<p>' . $this->t('Your Facebook Page is <strong>@page_name</strong>. <a href="?edit=fb_page">Update facebook page</a>.', ['@page_name' => $page_name]) . '</p>';
    }
    elseif ($page_id) {
      $markup[] = '<p>' . $this->t('Your Facebook Page ID is <strong>@page_id</strong>. <a href="?edit=fb_page">Update facebook page</a>.', ['@page_id' => $page_id]) . '</p>';
    }
    $form['module_activation']['fb_app_settings'] = [
      '#markup' => implode('', $markup),
    ];

    return $form;
  }

  /**
   * Add the publishing settings sub-section.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   Form array.
   */
  protected function moduleConfigPublishingBuildForm(array $form, FormStateInterface $form_state) {
    $form['module_config']['publishing'] = [
      '#type' => 'details',
      '#title' => $this->t('Publishing settings'),
      '#open' => TRUE,
    ];

    $form['module_config']['publishing']['development_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Development Mode'),
      '#default_value' => $this->config('fb_instant_articles_api.settings')->get('development_mode'),
      '#description' => $this->t('When publishing in development, none of your articles will be made live, and they will be saved as drafts you can edit in the Instant Articles library on your Facebook Page. Whether in development mode or not, articles will not be published live until you have submitted a sample batch to Facebook and passed a one-time review.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('fb_instant_articles_api.settings')
      ->set('development_mode', $form_state->getValue('development_mode'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
