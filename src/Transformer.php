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
   * Settings for the module.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

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
   * @see hook_fb_instant_articles_transformer_rules_alter()
   */
  public function __construct(TransformerRulesManager $transformer_rules_manager, ConfigFactoryInterface $config_factory) {
    $this->transformerRulesManager = $transformer_rules_manager;
    $this->addRules($this->transformerRulesManager->getRules());
    $this->config = $config_factory->get('fb_instant_articles.settings');
    $this->transformerLogging();
  }

  /**
   * Adds rules from an array of rule information.
   *
   * @param array $rules
   *   An array of transformer rule arrays. This is a PHP array representation
   *   of the JSON list of Rules information expected by parent::loadRules().
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

  /**
   * Setup transformer logging or not.
   */
  protected function transformerLogging() {
    $appender = [
      'class' => $this->config->get('enable_logging') ? '\Drupal\fb_instant_articles\DrupalLoggerAppender' : 'LoggerAppenderNull',
      'layout' => [
        'class' => 'LoggerLayoutSimple',
      ],
    ];
    $configuration = [
      'rootLogger' => [
        'appenders' => [
          'facebook-instantarticles-transformer',
          'facebook-instantarticles-client',
        ],
      ],
      'appenders' => [
        'facebook-instantarticles-transformer' => $appender,
        'facebook-instantarticles-client' => $appender,
      ],
    ];
    \Logger::configure($configuration);
  }

}
