<?php

namespace Drupal\fb_instant_articles;

use Facebook\InstantArticles\Client\Client as FbiaClient;
use Facebook\Exceptions\FacebookResponseException;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Encapsulate Drupal-specific logic for FBIA Client.
 *
 * Class DrupalClient
 *
 * @package Drupal\fb_instant_articles
 */
class DrupalClient extends FbiaClient {

  /**
   * Facebook Graph API Permision Error Code
   **/
  const FB_INSTANT_ARTICLES_ERROR_CODE_PERMISSION = 200;

  /**
   * Facebook Graph API Page Not Approved Error Code
   **/
  const FB_INSTANT_ARTICLES_ERROR_CODE_PAGE_NOT_APPROVED = 1888205;

  /**
   * FBIA config object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * DrupalClient constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   */
  public function __construct(ConfigFactoryInterface $config) {
    $this->config = $config;
  }

  /**
   * Get an Instant Articles API client instance configured with sitewide Drupal settings
   */
  public function get() {
    $apiConfig = $this->config->get('fb_instant_articles_api.settings');
    $baseConfig = $this->config->get('fb_instant_articles.base_settings');

    $appId = $apiConfig->get('fb_instant_articles_api_app_id');
    $appSecret = $apiConfig->get('fb_instant_articles_api_app_secret');
    $accessToken = $apiConfig->get('fb_instant_articles_api_access_token');
    $pageId = $baseConfig->get('fb_instant_articles_page_id');
    $developmentMode = (bool) $apiConfig->get('fb_instant_articles_api_development_mode', FALSE);

    return parent::create($appId, $appSecret, $accessToken, $pageId, $developmentMode);
  }

  /**
   * {@inheritdoc}
   *
   * Additionally try to catch an attempted import call.
   *
   * @todo Revisit this try/catch wrapper if the API gives an API option to see
   *   if the page has passed review. This may in fact be the best way, but that
   *   is still in discussion by the Facebook team.
   */
  public function importArticle($article, $takeLive = FALSE) {
    try {
      parent::importArticle($article, $takeLive);
    }
    catch(FacebookResponseException $e) {
      // If this was an authorization exception and the error code indicates
      // that the page has not yet passed review, try posting the article
      // unpublished. Only try again if the article was intended to be
      // published, so we don't try to post unpublished twice.
      if ($e->getCode() === self::FB_INSTANT_ARTICLES_ERROR_CODE_PERMISSION && $e->getSubErrorCode() === self::FB_INSTANT_ARTICLES_ERROR_CODE_PAGE_NOT_APPROVED && $takeLive) {
        parent::importArticle($article, FALSE);
      }
    }
  }

}