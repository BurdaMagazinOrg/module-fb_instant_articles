<?php

namespace Drupal\Tests\fb_instant_articles\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Test toggling the FBIA view mode while creating the content type.
 *
 * @group fb_instant_articles
 */
class ContentTypeViewModeCreateTest extends BrowserTestBase {

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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Login as an admin user.
    $this->adminUser = $this->drupalCreateUser($this->permissions);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test creating a new content type with FBIA toggled on.
   */
  public function testCreateContentTypeFbia() {
    // Enable FBIA display on a new content type.
    $this->drupalGet(Url::fromRoute('node.type_add')->toString());
    $this->assertSession()->statusCodeEquals(200);
    $edit = [
      'name' => 'Test',
      'type' => 'test',
      'fb_instant_articles_enabled' => '1',
    ];
    $this->submitForm($edit, t('Save and manage fields'));
    $this->assertSession()->pageTextContains('The content type test has been added.');

    // Check that the FBIA view mode has been turned on.
    $view_mode_url = Url::fromRoute('entity.entity_view_display.node.default', ['node_type' => 'test'])->toString();
    $this->drupalGet($view_mode_url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->checkboxChecked('edit-display-modes-custom-fb-instant-articles');
  }

}
