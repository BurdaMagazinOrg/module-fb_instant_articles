<?php

/**
 * @file
 * Contains \Drupal\FBInstantArticles\DrupalClient.
 */

namespace Drupal\FBInstantArticles\DrupalClient;

use Facebook\InstantArticles\Client;
use Facebook\Exceptions\FacebookAuthorizationException;

/**
 * Encapsulates any Drupal-specific logic when using the Client.
 *
 * Class DrupalClient
 * @package Drupal\FBInstantArticles\DrupalClient
 */
class DrupalClient extends Client {

  /**
   * {@inheritdoc}
   *
   * Additionally extend the parent method to simplify sitewide Drupal settings.
   *
   * @todo Wait for an answer from Facebook on the legal viability of using the
   *   app_id|app_secret app static token method.
   *
   * See also @link https://developers.facebook.com/docs/facebook-login/access-tokens#apptokens Facebook documentation note about the app static token method. @endlink
   */
  public static function create() {
    $appID = variable_get('fb_instant_articles_api_app_id');
    $appSecret = variable_get('fb_instant_articles_api_app_secret');
    $accessToken = $appID . '|' . $appSecret;
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
      // passed review. In that case, try posting the article unpublished.
      if (!$takeLive) {
        parent::importArticle($article, FALSE);
      }
    }
  }

}
