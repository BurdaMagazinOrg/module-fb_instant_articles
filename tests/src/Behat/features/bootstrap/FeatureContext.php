<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;

/**
 * Defines application features used by all features.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Install Facbook Instant Articles module.
   *
   * @BeforeSuite
   */
  public static function prepare(BeforeSuiteScope $scope) {
    \Drupal::service('module_installer')->install(['fb_instant_articles_views']);
  }

}
