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
   * Facebook Instant Articles module version number.
   */
  const FB_INSTANT_ARTICLES_VERSION = '7.x-1.0-rc1';


  /**
   * ArticleWrapper constructor.
   *
   * Instantiates the InstantArticle object, and adds Drupal Base module configs
   * where appropriate.
   *
   * Note modules making use of this wrapper must set the required Canonical URL
   * with:
   * @code
   * ArticleWrapper::create()->getArticle()->withCanonicalUrl($url);
   * @endcode
   */
  private function __construct() {
    $this->instantArticle = InstantArticle::create()
      ->addMetaProperty('op:generator:application', 'drupal/fb_instant_articles')
      ->addMetaProperty('op:generator:application:version', self::getApplicationVersion())
      ->withStyle(variable_get('fb_instant_articles_style', 'default'));

  }

  /**
   * Creates a Drupal wrapper for an InstantArticle object.
   *
   * @return \Drupal\fb_instant_articles\ArticleWrapper
   */
  public static function create() {
    return new ArticleWrapper();
  }

  /**
   * Gets the wrapped InstantArticle object.
   *
   * Also invokes a hook to allow other modules to alter the InstantArticle
   * object before render or any other operation.
   *
   * @see hook_fb_instant_articles_instant_article_alter()
   *
   * @return \Facebook\InstantArticles\Elements\InstantArticle
   */
  public function getArticle() {
    drupal_alter('fb_instant_articles_instant_article', $this->instantArticle);
    return $this->instantArticle;
  }

  /**
   * Gets the Drupal module (or core compatibility) version number.
   *
   * @todo Do we need to set this meta property? I can not find a reference to
   *   op:generator:application:version online anywhere except in our module.
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
