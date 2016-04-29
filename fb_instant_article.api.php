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
 * @param array $context
 *   An associative array of all contextual information for use in altering the
 *   InstantArticle object. When interacting with ArticleWrapper directly,
 *   modules may set additional context with ArticleWrapper::setContext().
 *
 * @see \Drupal\fb_instant_articles\ArticleWrapper::getArticle()
 * @see \Drupal\fb_instant_articles\ArticleWrapper::setContext()
 */
function hook_fb_instant_articles_article_alter($instantArticle, $context) {
  // Check to see if a module like fb_instant_articles_display has set Entity
  // context.
  if (isset($context['entity_type']) && isset($context['entity'])) {
    // Example: Set the author using a custom callback.
    $byline = custom_fb_instant_articles_byline_callback($context['entity_type'], $context['entity']);
    $instantArticle
      ->getHeader()
      ->addAuthor(
        \Facebook\InstantArticles\Elements\Author::create()
          ->withName($byline)
      );
  }
}

/**
 * @} End of "addtogroup hooks".
 */
