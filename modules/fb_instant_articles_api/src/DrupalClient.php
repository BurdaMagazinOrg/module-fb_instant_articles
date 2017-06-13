<?php

namespace Drupal\fb_instant_articles_api;

use Facebook\InstantArticles\Client\Client as FbiaClient;
use Facebook\Exceptions\FacebookResponseException;

/**
 * Encapsulate Drupal-specific logic for FBIA Client.
 */
class DrupalClient extends FbiaClient {

  /**
   * Facebook Graph API Permision Error Code.
   */
  const FB_INSTANT_ARTICLES_ERROR_CODE_PERMISSION = 200;

  /**
   * Facebook Graph API Page Not Approved Error Code.
   */
  const FB_INSTANT_ARTICLES_ERROR_CODE_PAGE_NOT_APPROVED = 1888205;

  /**
   * {@inheritdoc}
   *
   * Additionally try to catch an attempted import call.
   *
   * @todo Revisit this try/catch wrapper if the API gives an API option to see
   *   if the page has passed review. This may in fact be the best way, but that
   *   is still in discussion by the Facebook team.
   */
  public function importArticle($article, $published = FALSE, $forceRescrape = FALSE, $formatOutput = FALSE) {
    try {
      parent::importArticle($article, $published, $forceRescrape, $formatOutput);
    }
    catch (FacebookResponseException $e) {
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