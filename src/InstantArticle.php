<?php

/**
 * @file
 * Contains \Drupal\fb_instant_articles\InstantArticle.
 */

namespace Drupal\fb_instant_articles;

/**
 * Encapsulates Drupal-specific logic when using the InstantArticle class.
 *
 * Class InstantArticle
 * @package Drupal\fb_instant_articles\InstantArticle
 */
class InstantArticle extends \Facebook\InstantArticles\Elements\InstantArticle {

  /**
   * {@inheritdoc}
   *
   * Adds Drupal hook to allow DOM element manipulation before rendering.
   *
   * @see \Facebook\InstantArticles\Elements\Element::render()
   * @see hook_fb_instant_articles_render_alter()
   */
  public function render($doctype = '<!doctype html>', $format = false) {
    drupal_alter('fb_instant_articles_render', $this);
    return parent::render($doctype, $format);
  }

}
