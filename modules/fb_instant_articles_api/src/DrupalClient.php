<?php

/**
 * @file
 * Contains \Drupal\fb_instant_articles_api\DrupalClient.
 */

namespace Drupal\fb_instant_articles_api;

use Facebook\InstantArticles\Client\Client;
use Facebook\Exceptions\FacebookAuthorizationException;

/**
 * Encapsulates any Drupal-specific logic when using the Client.
 *
 * Class DrupalClient
 * @package Drupal\fb_instant_articles_api
 */
class DrupalClient extends Client {

  /**
   * {@inheritdoc}
   *
   * Additionally extend the parent method to simplify sitewide Drupal settings.
   *
   */
  public static function create() {
    $appID = variable_get('fb_instant_articles_api_app_id');
    $appSecret = variable_get('fb_instant_articles_api_app_secret');
    $accessToken = variable_get('fb_instant_articles_api_access_token');
    $pageID = variable_get('fb_instant_articles_page_id');
    $developmentMode = variable_get('fb_instant_articles_api_development_mode', TRUE);
    
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
    catch(FacebookAuthorizationException $e) {
      // A Facebook authorization exception probably means the page has not yet
      // passed review. In that case, try posting the article unpublished. Only
      // try again if the article was intended to pe published, so we don't try
      // to post unpublished twice.
      if ($takeLive) {
        parent::importArticle($article, FALSE);
      }
    }
  }

}
