<?php

/**
 * @file
 * Contains \Drupal\fb_instant_articles_api\DrupalClient.
 */

namespace Drupal\fb_instant_articles_api;

use Facebook\InstantArticles\Client\Client;
use Facebook\Exceptions\FacebookResponseException;

/**
 * Encapsulates any Drupal-specific logic when using the Client.
 *
 * Class DrupalClient
 * @package Drupal\fb_instant_articles_api
 */
class DrupalClient extends Client {

  /**
   * Facebook Graph API Permision Error Code
   **/
  const FB_INSTANT_ARTICLES_ERROR_CODE_PERMISSION = 200;

  /**
   * Facebook Graph API Page Not Approved Error Code
   **/
  const FB_INSTANT_ARTICLES_ERROR_CODE_PAGE_NOT_APPROVED = 1888205;

  /**
   * Get an Instant Articles API client instance configured with sitewide Drupal settings
   */
  public static function get() {
    $appID = variable_get('fb_instant_articles_api_app_id');
    $appSecret = variable_get('fb_instant_articles_api_app_secret');
    $accessToken = variable_get('fb_instant_articles_api_access_token');
    $pageID = variable_get('fb_instant_articles_page_id');
    $developmentMode = (bool)variable_get('fb_instant_articles_api_development_mode', FALSE);
    
    return parent::create($appID, $appSecret, $accessToken, $pageID, $developmentMode);
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
