<?php

namespace Drupal\Tests\fb_instant_articles\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the FBIA API settings form.
 *
 * @group fb_instant_articles
 */
class ApiSettingsFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'fb_instant_articles',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->drupalLogin($this->rootUser);
  }

  /**
   * Test the various stages of form input for the API settings form.
   */
  public function testBuildForm() {
    $this->drupalGet('/admin/config/services/fb_instant_articles/api_settings');
    $this->assertSession()->statusCodeEquals(200);

    // Initially we should show the App ID and App Secret fields.
    $this->assertSession()->fieldExists('app_id');
    $this->assertSession()->fieldExists('app_secret');

    // Clicking Next right away should produce an error.
    $this->drupalPostForm(NULL, [], t('Next'));
    $this->assertSession()->pageTextContains('You must enter the App ID before proceeding');

    // Clicking Next with invalid input should produce an error.
    $this->drupalPostForm(NULL, ['app_id' => 'invalid'], t('Next'));
    $this->assertSession()->pageTextContains('The App ID that you entered is invalid');

    // Set valid values for app_id and app_secret.
    $app_id = '1234';
    $app_secret = 'secret';
    $this->drupalPostForm(NULL, ['app_id' => $app_id, 'app_secret' => $app_secret], t('Next'));
    $this->drupalGet('/admin/config/services/fb_instant_articles/api_settings');
    $this->assertSession()->pageTextContains('Your Facebook App ID is ' . $app_id);

    // Clicking "Update Facebook app id." should allow us to change the app_id
    // and secret again.
    $this->clickLink('Update Facebook app id');
    $this->assertSession()->fieldExists('app_id');
    $this->assertSession()->fieldExists('app_secret');

    // Set an access_token and page_id in order to test the summary page.
    $page_id = '1234';
    \Drupal::configFactory()->getEditable('fb_instant_articles.settings')
      ->set('page_id', $page_id)
      ->set('access_token', 'token')
      ->save();
    $this->drupalGet('/admin/config/services/fb_instant_articles/api_settings');
    $this->assertSession()->pageTextContains('Your Facebook App ID is ' . $app_id);
    $this->assertSession()->pageTextContains('Your Facebook Page ID is ' . $page_id);
  }

}
