<?php

/**
 * @file
 * Contains \Drupal\fb_instant_articles\ArticleWrapper.
 */

namespace Drupal\fb_instant_articles;

use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Wraps a FB Instant SDK article object with Drupal Base module configs and
 * hooks for extensibility (without class inheritance, which the FB Instant SDK
 * prevents by design).
 *
 * Class ArticleWrapper
 * @package Drupal\fb_instant_articles\ArticleWrapper
 */
class ArticleWrapper {

  /**
   * A stateful FB Instant Articles SDK singleton for Drupal integration.
   *
   * @see getArticle()
   *
   * @var \Facebook\InstantArticles\Elements\InstantArticle
   */
  private $instantArticle;

  /**
   * ArticleWrapper constructor.
   *
   * Instantiates the InstantArticle object, adding Drupal Base module configs
   * where appropriate. Also invokes a hook to allow other modules to alter the
   * InstantArticle object before render or any other operation.
   *
   * @param array $context
   *   An associative array of contextual information altering the
   *   InstantArticle object.
   *
   * @see hook_fb_instant_articles_instant_article_alter()
   *
   * Note modules making use of this wrapper must set the required Canonical URL
   * with:
   * @code
   * ArticleWrapper::create()->getArticle()->withCanonicalUrl($url);
   * @endcode
   */
  private function __construct($context = array()) {
    $this->instantArticle = InstantArticle::create()
      ->addMetaProperty('op:generator:application', 'drupal/fb_instant_articles')
      ->addMetaProperty('op:generator:application:version', self::getApplicationVersion())
      ->withStyle(variable_get('fb_instant_articles_style', 'default'));
    drupal_alter('fb_instant_articles_article', $this->instantArticle, $context);
  }

  /**
   * Creates a Drupal wrapper for an InstantArticle object.
   *
   * @param array $context
   *   An associative array of contextual information altering the
   *   InstantArticle object.
   *
   * @return \Drupal\fb_instant_articles\ArticleWrapper
   */
  public static function create($context) {
    return new ArticleWrapper($context);
  }

  /**
   * Gets the wrapped InstantArticle object.
   *
   * @return \Facebook\InstantArticles\Elements\InstantArticle
   */
  public function getArticle() {
    return $this->instantArticle;
  }

  /**
   * Gets the Drupal module (or core compatibility) version number.
   *
   * @return string
   *   Either the module version defined by Drupal.org's packaging system, or
   *   (in the case of a direct git branch checkout) the Drupal core
   *   compatibility version (example: 7.x or 8.x).
   */
  private function getApplicationVersion() {
    $module = 'fb_instant_articles';
    $filename = drupal_get_path('module', $module) . '/' . $module . '.info';
    $info = drupal_parse_info_file($filename);
    return isset($info['version']) ? $info['version'] : DRUPAL_CORE_COMPATIBILITY;
  }

}
