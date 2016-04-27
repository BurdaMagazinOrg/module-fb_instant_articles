<?php

/**
 * @file
 * Hooks provided by Facebook Instant Articles Base module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the InstantArticle object before retrieving from the ArticleWrapper.
 *
 * The InstantArticle object is provided by the FB Instant SDK. See their
 * @link https://developers.facebook.com/docs/instant-articles documentation @endlink
 * for details on how to work with the SDK to customize your Instant Articles.
 *
 * @param \Facebook\InstantArticles\Elements\InstantArticle $instantArticle
 *   The SDK InstantArticle object before it is retrieved from ArticleWrapper.
 *
 * @see \Drupal\fb_instant_articles\ArticleWrapper::getArticle()
 */
function hook_fb_instant_articles_article_alter(\Facebook\InstantArticles\Elements\InstantArticle $instantArticle) {
  // @todo Provide useful examples here or a better link.
}

/**
 * @} End of "addtogroup hooks".
 */
