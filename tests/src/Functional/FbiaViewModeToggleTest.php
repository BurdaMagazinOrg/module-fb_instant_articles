<?php

namespace Drupal\Tests\fb_instant_articles\Functional;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the view mode toggle functionality.
 *
 * @group fb_instant_articles
 */
class FbiaViewModeToggleTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'fb_instant_articles',
    'node',
    'field_ui',
  ];

  /**
   * Permissions to grant admin user.
   *
   * @var array
   */
  protected $permissions = [
    'access administration pages',
    'administer content types',
    'administer display modes',
    'administer node display',
    'administer site configuration',
    'administer fb_instant_articles',
  ];

  /**
   * An user with administration permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Name of a test field.
   *
   * @var string
   */
  protected $testField;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create Article node type.
    $this->createContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);

    // Create a field storage with settings to validate.
    $this->testField = Unicode::strtolower($this->randomMachineName());
    $field_storage = FieldStorageConfig::create([
      'field_name' => $this->testField,
      'entity_type' => 'node',
      'type' => 'string',
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'article',
    ]);
    $field->save();
  }

  /**
   * Test the FBIA view mode toggle.
   */
  public function testFbiaViewModeToggle() {

    // Login as an admin user.
    $this->adminUser = $this->drupalCreateUser($this->permissions);
    $this->drupalLogin($this->adminUser);

    // Check that the FBIA view mode is available.
    $view_modes_url = Url::fromRoute('entity.entity_view_mode.collection')->toString();
    $this->drupalGet($view_modes_url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Facebook Instant Articles');

    // Enable FBIA display on article content.
    $article_url = Url::fromRoute('entity.node_type.edit_form', ['node_type' => 'article'])->toString();
    $this->drupalGet($article_url);
    $this->assertSession()->statusCodeEquals(200);
    $edit = ['fb_instant_articles_enabled' => '1'];
    $this->submitForm($edit, t('Save content type'));

    // Check that the FBIA view mode has been turned on.
    $article_display_url = Url::fromRoute('entity.entity_view_display.node.default', ['node_type' => 'article'])->toString();
    $this->drupalGet($article_display_url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->checkboxChecked('edit-display-modes-custom-fb-instant-articles');

    // Check that the additional regions show up on the Manage Display UI.
    $article_display_url = Url::fromRoute('entity.entity_view_display.node.view_mode', [
      'node_type' => 'article',
      'view_mode_name' => 'fb_instant_articles',
    ])->toString();
    $this->drupalGet($article_display_url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Header');
    $this->assertSession()->pageTextContains('Body');
    $this->assertSession()->pageTextContains('Footer');
    $this->assertSession()->pageTextContains('No fields are displayed in this region.');
    $edit = ['fields[' . $this->testField . '][region]' => 'content'];
    $this->submitForm($edit, t('Save'));
    $this->assertSession()->fieldValueEquals('fields[' . $this->testField . '][region]', 'content');

    // Disable the FBIA view mode.
    $article_url = Url::fromRoute('entity.node_type.edit_form', ['node_type' => 'article'])->toString();
    $this->drupalGet($article_url);
    $this->assertSession()->statusCodeEquals(200);
    $edit = ['fb_instant_articles_enabled' => '0'];
    $this->submitForm($edit, t('Save content type'));

    // Check that the FBIA view mode has been turned off.
    $article_display_url = Url::fromRoute('entity.entity_view_display.node.default', ['node_type' => 'article'])->toString();
    $this->drupalGet($article_display_url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->checkboxNotChecked('edit-display-modes-custom-fb-instant-articles');
  }

}
