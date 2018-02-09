<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;

/**
 * Defines application features used by all features.
 *
 * @codingStandardsIgnoreStart
 */
class FbInstantArticlesFeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Install Facebook Instant Articles module.
   *
   * @BeforeSuite
   */
  public static function prepare(BeforeSuiteScope $scope) {
    /** @var \Drupal\Core\Extension\ModuleHandler $moduleHandler */
    $moduleHandler = \Drupal::service('module_handler');
    if (!$moduleHandler->moduleExists('fb_instant_articles_views')) {
      \Drupal::service('module_installer')->install(['fb_instant_articles_views']);
    }

    // Also uninstall the inline form errors module for easier testing.
    if ($moduleHandler->moduleExists('inline_form_errors')) {
      \Drupal::service('module_installer')->uninstall(['inline_form_errors']);
    }
  }

}
