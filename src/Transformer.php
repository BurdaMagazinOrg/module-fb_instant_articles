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
    $this->addRules($rules);
  }

  public function addRules(array $rules) {
    foreach ($rules as $rules) {
      $clazz = $rules['class'];
      try {
        $factory_method = new \ReflectionMethod($clazz, 'createFrom');
      }
      catch (\ReflectionException $e) {
        $factory_method =
          new \ReflectionMethod(
            'Facebook\\InstantArticles\\Transformer\\Rules\\'.$clazz,
            'createFrom'
          );
      }
      $this->addRule($factory_method->invoke(null, $rules));
    }
  }

}
