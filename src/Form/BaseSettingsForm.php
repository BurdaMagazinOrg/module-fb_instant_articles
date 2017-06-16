<?php

namespace Drupal\fb_instant_articles\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\fb_instant_articles\AdTypes;

/**
 * Facebook Instant Articles base settings form.
 */
class BaseSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'fb_instant_articles.base_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'base_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('fb_instant_articles.base_settings');

    // Add the page id configuration.
    $args = [
      '@claim_url' => 'https://developers.facebook.com/docs/instant-articles/claim-url',
    ];
    $form['page_id'] = [
      '#title' => t('Facebook Page ID'),
      '#type' => 'textfield',
      '#default_value' => $config->get('page_id'),
      '#required' => TRUE,
      '#description' => $this->t('In order to designate the domain that will host your articles you must add your Facebook page ID to a metatag in the HEAD tag of your HTML page. Entering your Facebook Page ID here will add the metatag automatically. See <a href="@claim_url">Claiming your URL</a>.', $args),
    ];

    // Add the style configuration.
    $form['style'] = [
      '#type' => 'textfield',
      '#title' => t('Article Style'),
      '#default_value' => $config->get('style') ? $config->get('style') : 'default',
      '#size' => 30,
      '#required' => TRUE,
      '#description' => $this->t('Assign your Instant Articles a custom style. To begin, customize a template using the <a href="@style_url" target="_blank">Style Editor</a>. Next, input the name of the style above. <strong>Note</strong>: if this field is left blank, the module will enable the “Default” style. Learn more about Instant Articles style options in the <a href="@design_url" target="_blank">Design Guide</a>.', ['@style_url' => '', '@design_url' => 'https://developers.facebook.com/docs/instant-articles/guides/design']),
    ];

    // Add the Ads sub-section.
    $form['ads'] = [
      '#type' => 'details',
      '#title' => t('Ads'),
      '#open' => TRUE,
      '#description' => t('Choose your preferred method for displaying ads in your Instant Articles and input the code in the boxes below. Learn more about your options for <a href="@ads_url">advertising in Instant Articles</a>', ['@ads_url' => 'https://developers.facebook.com/docs/instant-articles/ads-analytics']),
    ];

    $form['ads']['ads_type'] = [
      '#type' => 'select',
      '#title' => t('Ad Type'),
      '#default_value' => $config->get('ads.type') ? $config->get('ads.type') : AdTypes::AD_TYPE_NONE,
      '#options' => [
        AdTypes::AD_TYPE_NONE => t('None'),
        AdTypes::AD_TYPE_FBAN => t('Facebook Audience Network'),
        AdTypes::AD_TYPE_SOURCE_URL => t('Source URL'),
        AdTypes::AD_TYPE_EMBED_CODE => t('Embed Code'),
      ],
      '#description' => t('<strong>Note:</strong> this module will automatically place the ads within your articles.'),
      '#attributes' => ['class' => ['ad-type']],
    ];

    $form['ads']['ads_iframe_url'] = [
      '#type' => 'textfield',
      '#title' => t('Ad Source URL'),
      '#default_value' => $config->get('ads.iframe_url'),
      '#description' => t('<strong>Note:</strong> Instant Articles only supports Direct Sold ads. No programmatic ad networks, other than Facebook\'s Audience Network, are permitted.'),
      '#size' => 80,
      '#element_validate' => [
        [$this, 'validateAdSourceUrl'],
      ],
      '#states' => [
        'visible' => [
          '[name=ads_type]' => ['value' => AdTypes::AD_TYPE_SOURCE_URL],
        ],
      ],
    ];

    $form['ads']['ads_an_placement_id'] = [
      '#type' => 'textfield',
      '#title' => t('Audience Network Placement ID'),
      '#default_value' => $config->get('ads.an_placement_id'),
      '#description' => t('Find your <a href="@placement_id_url" target="_blank">Placement ID</a> on your app\'s <a href="@audience_network_url" target="_blank">Audience Network Portal</a>.', ['@placement_id_url' => '', '@audience_netowrk_url' => '']),
      '#size' => 30,
      '#element_validate' => [
        [$this, 'validateAnPlacementId'],
      ],
      '#states' => [
        'visible' => [
          '[name=ads_type]' => ['value' => AdTypes::AD_TYPE_FBAN],
        ],
      ],
    ];

    $form['ads']['ads_embed_code'] = [
      '#type' => 'textarea',
      '#title' => t('Ad Embed Code'),
      '#default_value' => $config->get('ads.embed_code'),
      '#description' => t('Add code to be used for displayed ads in your Instant Articles.'),
      '#size' => 30,
      '#element_validate' => [
        [$this, 'validateAdEmbedCode'],
      ],
      '#states' => [
        'visible' => [
          '[name=ads_type]' => ['value' => AdTypes::AD_TYPE_EMBED_CODE],
        ],
      ],
    ];

    $form['ads']['ads_dimensions'] = [
      '#type' => 'select',
      '#title' => t('Ad Dimensions'),
      '#options' => [
        '300x250' => t('Large (300 x 250)'),
      ],
      '#default_value' => $config->get('ads.dimensions'),
      '#states' => [
        'invisible' => [
          '[name=ads_type]' => ['value' => AdTypes::AD_TYPE_NONE],
        ],
      ],
    ];

    // Add the Analytics sub-section.
    $form['analytics'] = [
      '#type' => 'details',
      '#title' => t('Analytics'),
      '#open' => TRUE,
      '#description' => t('Enable 3rd-party analytics to be used with Instant Articles. You can use an embed code to insert your own trackers and analytics. Learn more about <a href="@analytics_url">analytics in Instant Articles</a>.', ['@analytics_url' => 'https://developers.facebook.com/docs/instant-articles/ads-analytics#analytics']),
    ];

    $form['analytics']['analytics_embed_code'] = [
      '#type' => 'textarea',
      '#title' => t('Analytics Embed Code'),
      '#default_value' => $config->get('analytics.embed_code'),
      '#description' => t('Add code for any analytics services you wish to use. <strong>Note:</strong> you do not need to include any &lt;op-tracker&gt; tags. The module will automatically include them in the article markup.'),
      '#size' => 30,
    ];

    // Add the Debug Configuration.
    $form['enable_logging'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable FB Instant Articles SDK logging?'),
      '#default_value' => $config->get('enable_logging'),
      '#description' => $this->t('Sends Facebook Instant Articles SDK logging messages to Drupal watchdog.'),
    ];

    // Add the Canonical URL override.
    $form['canonical_url_override'] = [
      '#type' => 'textfield',
      '#title' => t('Canonical URL override'),
      '#default_value' => $config->get('canonical_url_override', ''),
      '#description' => t('If you need to override the base url portion of the canonical URL, you can do so here. This may be helpful for development domains or necessary if admin users perform tasks that trigger Facebook requests from alternate domains. This URL should not include a trailing slash (e.g. http://drupal.org).'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Validate the Ad Source Url field.
   *
   * @param array $form
   *   FAPI array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function validateAdSourceUrl(array &$form, FormStateInterface $form_state) {
    // Only validate if Source URL is selected as ad type.
    if ($form_state->getValue('ads_type') != AdTypes::AD_TYPE_SOURCE_URL) {
      return;
    }

    $ads_iframe_url = $form_state->getValue('ads_iframe_url');
    if (empty($ads_iframe_url)) {
      $form_state->setErrorByName('ads_iframe_url', $this->t('You must specify a valid source URL for your Ads when using the Source URL ad type.'));
    }

    if (!UrlHelper::isValid($ads_iframe_url, TRUE)) {
      $form_state->setErrorByName('ads_iframe_url', $this->t('You must specify a valid source URL for your Ads when using the Source URL ad type.'));
    }
  }

  /**
   * Validate the Audience Network placement id.
   *
   * @param array $form
   *   FAPI array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function validateAnPlacementId(array &$form, FormStateInterface $form_state) {
    // Only validate if Audience Network is selected as ad type.
    if ($form_state->getValue('ads_type') != AdTypes::AD_TYPE_FBAN) {
      return;
    }

    $ads_an_placement_id = $form_state->getValue('ads_an_placement_id');
    if (empty($ads_an_placement_id)) {
      $form_state->setErrorByName('ads_an_placement_id', $this->t('You must specify a valid Placement ID when using the Audience Network ad type.'));
    }

    if (preg_match('/^[\d_]+$/', $ads_an_placement_id) !== 1) {
      $form_state->setErrorByName('ads_an_placement_id', $this->t('You must specify a valid Placement ID when using the Audience Network ad type.  To find or set your placement id, you will need to go to your Audience Network account for Instant Articles. In the account, navigate to ‘Placements’ and create a ‘Placement of Banner’ type. You will only need one placement.'));
    }
  }

  /**
   * Validate the Ad Embed code.
   *
   * @param array $form
   *   FAPI array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function validateAdEmbedCode(array &$form, FormStateInterface $form_state) {
    // Only validate if Embed Code is selected as ad type.
    if ($form_state->getValue('ads_type') != AdTypes::AD_TYPE_EMBED_CODE) {
      return;
    }

    $ads_embed_code = $form_state->getValue('ads_embed_code');
    if (empty($ads_embed_code)) {
      $form_state->setErrorByName('ads_embed_code', $this->t('You must specify Embed Code for your Ads when using the Embed Code ad type.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('fb_instant_articles.base_settings')
      ->set('page_id', $form_state->getValue('page_id'))
      ->set('style', $form_state->getValue('style'))
      ->set('ads.type', $form_state->getValue('ads_type'))
      ->set('ads.iframe_url', $form_state->getValue('ads_iframe_url'))
      ->set('ads.an_placement_id', $form_state->getValue('ads_an_placement_id'))
      ->set('ads.embed_code', $form_state->getValue('ads_embed_code'))
      ->set('ads.dimensions', $form_state->getValue('ads_dimensions'))
      ->set('analytics.embed_code', $form_state->getValue('analytics_embed_code'))
      ->set('enable_logging', $form_state->getValue('enable_logging'))
      ->set('canonical_url_override', $form_state->getValue('canonical_url_override'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
