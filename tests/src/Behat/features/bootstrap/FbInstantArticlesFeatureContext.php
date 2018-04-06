<?php

use Behat\Gherkin\Node\TableNode;
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

    // Clear the FBIA Config so we start from scratch each time.
    // This is mostly handy when developing these tests locally.
    \Drupal::configFactory()->getEditable('fb_instant_articles.settings')->delete();

  }

  /**
   * @Given I disable HTML 5 required validation on the :field field
   */
  public function iDisableHtmlRequiredValidationOnTheField($field) {
    $id = $this->getSession()->getPage()->findField($field)->getAttribute('id');
    $this->getSession()->evaluateScript("jQuery('#$id').removeAttr('required');");
  }

  /**
   * @Given I disable HTML 5 required validation on the fields:
   */
  public function iDisableHtmlRequiredValidationOnTheFields(TableNode $fields) {
    foreach ($fields->getHash() as $key => $value) {
      $field = trim($value['field']);
      $this->iDisableHtmlRequiredValidationOnTheField($field);
    }
  }

  /**
   * @Given I open the Facebook Instant Articles Settings form
   */
  public function iOpenTheFacebookInstantArticlesSettingsForm() {
    $this->visitPath('admin/config/services/fb_instant_articles');
  }

}
