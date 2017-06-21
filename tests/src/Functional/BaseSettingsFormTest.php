<?php

namespace Drupal\Tests\fb_instant_articles\Functional;

use Drupal\fb_instant_articles\AdTypes;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the FBIA config form.
 *
 * @group fb_instant_articles
 */
class BaseSettingsFormTest extends BrowserTestBase {

  protected $settingsForm;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'fb_instant_articles',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->drupalLogin($this->rootUser);
  }

  /**
   * Verify form has all the fields.
   */
  public function testBuildForm() {
    $this->drupalGet('/admin/config/services/fb_instant_articles');
    $assert = $this->assertSession();
    $assert->statusCodeEquals(200);
    // Required fields.
    $assert->fieldExists('page_id');
    $assert->fieldExists('style');

    // Optional fields.
    $assert->fieldExists('ads_type');
    $assert->fieldExists('analytics_embed_code');
    $assert->fieldExists('enable_logging');
    $assert->fieldExists('canonical_url_override');
  }

  /**
   * Try posting the form.
   */
  public function testPostForm() {
    // Post some values to the form.
    $this->drupalGet('/admin/config/services/fb_instant_articles');
    $values = [
      'page_id' => 'test_page_id',
      'style' => 'test_style',
      'ads_type' => AdTypes::AD_TYPE_FBAN,
      'ads_an_placement_id' => '1234_',
      'ads_dimensions' => '300x250',
      'analytics_embed_code' => 'test_analytics_embed_code',
      'enable_logging' => TRUE,
      'canonical_url_override' => 'test_canonical_url_override',
    ];
    $this->drupalPostForm(NULL, $values, t('Save configuration'));

    // Verify that posted values show up in form reload.
    $this->drupalGet('/admin/config/services/fb_instant_articles');
    $assert = $this->assertSession();
    $assert->fieldValueEquals('page_id', 'test_page_id');
    $assert->fieldValueEquals('style', 'test_style');
    $assert->fieldValueEquals('ads_type', AdTypes::AD_TYPE_FBAN);
    $assert->fieldValueEquals('ads_an_placement_id', '1234_');
    $assert->fieldValueEquals('ads_dimensions', '300x250');
    $assert->fieldValueEquals('analytics_embed_code', 'test_analytics_embed_code');
    $assert->fieldValueEquals('enable_logging', TRUE);
    $assert->fieldValueEquals('canonical_url_override', 'test_canonical_url_override');

    // Test invalid ads placement id.
    $values['ads_an_placement_id'] = 'invalid';
    $this->drupalPostForm(NULL, $values, t('Save configuration'));
    $assert = $this->assertSession();
    $assert->pageTextContains('You must specify a valid Placement ID');

    // Test invalid ads source URL.
    $values['ads_type'] = AdTypes::AD_TYPE_SOURCE_URL;
    $values['ads_iframe_url'] = 'invalid';
    $this->drupalPostForm(NULL, $values, t('Save configuration'));
    $assert = $this->assertSession();
    $assert->pageTextContains('You must specify a valid source URL');

    // Test invalid ads embed code.
    $values['ads_type'] = AdTypes::AD_TYPE_EMBED_CODE;
    $this->drupalPostForm(NULL, $values, t('Save configuration'));
    $assert = $this->assertSession();
    $assert->pageTextContains('You must specify Embed Code');
  }

}
