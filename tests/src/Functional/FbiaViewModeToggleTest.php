<?php

namespace Drupal\Tests\fb_instant_articles\Functional;

use Drupal\Core\Url;
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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create Article node type.
    $this->createContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);
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
