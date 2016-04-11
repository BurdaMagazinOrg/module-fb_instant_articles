<?php

/**
 * @file
 * Contains \Drupal\fb_instant_articles\Transformer.
 */

namespace Drupal\fb_instant_articles;

/**
 * Encapsulates Drupal-specific logic when using the Transformer class.
 *
 * Class Transformer
 * @package Drupal\fb_instant_articles
 */
class Transformer extends \Facebook\InstantArticles\Transformer\Transformer {

  /**
   * Transformer constructor.
   *
   * Adds Drupal hook to allow transformer rules to be added by other modules.
   * We invoke the hook in a constructor - as opposed to inside transform() or a
   * new invokeRules() method - because of the way Transformer is normally used.
   *
   * @see hook_fb_instant_articles_transformer_rules()
   *
   * Note the parent class does not have a constructor, so we do not call
   * @code parent::__construct() @endcode.
   */
  public function __construct() {
    $rules = module_invoke_all('fb_instant_articles_transformer_rules');
    $this->setRules($rules);
  }

}
