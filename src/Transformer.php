<?php

namespace Drupal\fb_instant_articles;

use Drupal\Core\Config\ConfigFactoryInterface;
use Facebook\InstantArticles\Transformer\Transformer as FbiaTransformer;

/**
 * Encapsulates Drupal-specific logic when using the Transformer class.
 */
class Transformer extends FbiaTransformer {

  /**
   * Transformer rules manager service.
   *
   * @var \Drupal\fb_instant_articles\TransformerRulesManager
   */
  protected $transformerRulesManager;

  /**
   * Transformer constructor.
   *
   * Wraps the Transformer object from the SDK to introduce a default set of
   * rules any time it's instantiated, including an opportunity for other
   * modules to alter the set of rules that are used.
   *
   * Note the parent class does not have a constructor, so we do not call
   * @code parent::__construct() @endcode.
   *
   * @param \Drupal\fb_instant_articles\TransformerRulesManager $transformer_rules_manager
   *   Transformer rules manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   *
   * @throws \ReflectionException
   *   In case the class specified by one of the loaded $rules cannot be found.
   *
   * @see hook_fb_instant_articles_transformer_rules_alter()
   */
  public function __construct(TransformerRulesManager $transformer_rules_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct();
    $this->transformerRulesManager = $transformer_rules_manager;
    $this->addRules($this->transformerRulesManager->getRules());

    // Override the default timezone according to the site wide timezone.
    $config_data_default_timezone = $config_factory->get('system.date')->get('timezone.default');
    $default_time_zone = !empty($config_data_default_timezone) ? $config_data_default_timezone : @date_default_timezone_get();
    $this->setDefaultDateTimeZone(new \DateTimeZone($default_time_zone));
  }

  /**
   * Adds rules from an array of rule information.
   *
   * @param array $rules
   *   An array of transformer rule arrays. This is a PHP array representation
   *   of the JSON list of Rules information expected by parent::loadRules().
   *
   * @throws \ReflectionException
   *   In case the class specified by one of the given $rules cannot be found.
   *
   * @see Transformer::__construct()
   * @see \Facebook\InstantArticles\Transformer\Transformer::loadRules()
   */
  public function addRules(array $rules) {
    foreach ($rules as $rule) {
      $class = $rule['class'];
      try {
        $factory_method = new \ReflectionMethod($class, 'createFrom');
      }
      catch (\ReflectionException $e) {
        $factory_method =
          new \ReflectionMethod(
            'Facebook\\InstantArticles\\Transformer\\Rules\\' . $class,
            'createFrom'
          );
      }
      $this->addRule($factory_method->invoke(NULL, $rule));
    }
  }

}
