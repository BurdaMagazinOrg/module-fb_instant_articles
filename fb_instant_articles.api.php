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
 * @see \Drupal\fb_instant_articles\ArticleWrapper::create()
 * @see \Drupal\fb_instant_articles\ArticleWrapper::__construct()
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
 * Add Transformer rule definitions.
 *
 * @return array
 *   An array of transformer rule definitions. This is a PHP array
 *   representation of the JSON list of Rules definitions expected by
 *   \Facebook\InstantArticles\Transformer\Transformer::loadRules().
 *
 * @see \Drupal\fb_instant_articles\TransformerExtender::__construct()
 * @see \Drupal\fb_instant_articles\TransformerExtender::addRules()
 */
function hook_fb_instant_articles_transformer_rules() {
  // Example: Rule for Facebook post external embed via embed_external.module.
  $rules[] = array(
    'class' => 'SocialEmbedRule',
    'selector' => "//div[div[div]]",
    'properties' => array(
      'socialembed.url' => array(
        'type' => 'string',
        'selector' => "div > div > div.fb-post",
        'attribute' => 'data-href',
      ),
    ),
  );

  return $rules;
}

/**
 * Transform rule definitions.
 *
 * @param array $rules
 *   An array of transformer rule definitions.
 *
 * @see hook_fb_instant_articles_transformer_rules()
 */
function hook_fb_instant_articles_transformer_rules_alter($rules) {
  // Example: Remove caption rule from list of base rules.
  foreach ($rules as $rule) {
    if ($rule['class'] == 'CaptionRule' && $rule['selector'] == 'img') {
      unset($rules[$rule]);
    }
  }
}

/**
 * @} End of "addtogroup hooks".
 */
