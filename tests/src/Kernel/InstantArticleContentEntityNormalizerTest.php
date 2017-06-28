<?php

namespace Drupal\Tests\fb_instant_articles\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\token\Kernel\KernelTestBase;
use Drupal\user\Entity\User;
use Facebook\InstantArticles\Elements\Blockquote;
use Facebook\InstantArticles\Elements\Paragraph;

/**
 * Test the Drupal Client wrapper.
 *
 * @group fb_instant_articles_temp
 *
 * @coversDefaultClass \Drupal\fb_instant_articles\Normalizer\InstantArticleRssContentEntityNormalizer
 */
class InstantArticleContentEntityNormalizerTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'field',
    'serialization',
    'node',
    'user',
    'fb_instant_articles',
  ];

  /**
   * Entity view display object used in the tests.
   *
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected $display;

  /**
   * Instant article serializer.
   *
   * @var \Drupal\fb_instant_articles\Normalizer\InstantArticleContentEntityNormalizer
   */
  protected $serializer;

  /**
   * Name of the test user we are using.
   *
   * @var string
   */
  protected $userName;

  /**
   * User entity we are testing with.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->serializer = $this->container->get('serializer');

    $this->installSchema('system', 'sequences');

    $this->installConfig(['system', 'field']);

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    // Create a user to use for testing.
    $this->userName = $this->randomMachineName();
    $account = User::create(['name' => $this->userName, 'status' => 1]);
    $account->enforceIsNew();
    $account->save();
    $this->account = $account;

    // Create the node bundles required for testing.
    $type = NodeType::create([
      'type' => 'article',
      'name' => 'article',
    ]);
    $type->save();

    // Create a couple fields attached to entity_test entity type, which comes
    // from entity_test module.
    foreach (['field_one', 'field_two'] as $field_name) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => 'node',
        'type' => 'string_long',
      ]);
      $field_storage->save();

      $instance = FieldConfig::create([
        'field_storage' => $field_storage,
        'bundle' => 'article',
        'label' => $this->randomMachineName(),
      ]);
      $instance->save();
    }

    // Create a view mode for testing.
    $this->display = EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'article',
      'mode' => 'default',
      'status' => TRUE,
    ]);
  }

  /**
   * Tests the normalize method with real node objects.
   *
   * Validating that the end-to-end functionality of the normalizer works.
   *
   * @covers ::normalize
   */
  public function testNormalize() {
    // Setup a node with some simple values for testing.
    $title = $this->randomString();
    $node = Node::create([
      'title' => $title,
      'type' => 'article',
      'field_one' => [
        'value' => 'This is a value for the first field.',
      ],
      'field_two' => [
        'value' => 'This is a value for the second field.',
      ],
    ]);
    $node->setOwner($this->account);
    $node->save();

    // First test a case of using FBIA formatters.
    $this->display->setComponent('field_one', [
      'type' => 'fbia_paragraph',
      'settings' => [],
    ]);
    $this->display->setComponent('field_two', [
      'type' => 'fbia_blockquote',
      'settings' => [],
    ]);
    $this->display->save();

    $article = $this->serializer->normalize($node, 'fbia', ['entity_view_display' => $this->display]);
    $this->assertEquals($title, $article->getHeader()->getTitle()->getPlainText());
    $children = $article->getChildren();
    $this->assertEquals(2, count($children));
    $this->assertTrue($children[0] instanceof Paragraph);
    $this->assertTrue($children[1] instanceof Blockquote);

    // Next test with default field formatters.
    $this->display->setComponent('field_one', [
      'type' => 'basic_string',
      'settings' => [],
      'label' => 'hidden',
    ]);
    $this->display->setComponent('field_two', [
      'type' => 'basic_string',
      'settings' => [],
      'label' => 'hidden',
    ]);
    $this->display->save();

    $article = $this->serializer->normalize($node, 'fbia', ['entity_view_display' => $this->display]);
    $this->assertEquals($title, $article->getHeader()->getTitle()->getPlainText());
    $children = $article->getChildren();
    $this->assertEquals(2, count($children));
    $this->assertTrue($children[0] instanceof Paragraph);
    $this->assertTrue($children[1] instanceof Paragraph);
    $this->assertTrue('This is a value for the first field.', $children[0]->getPlainText());

    // Re-run the same test with labels.
    $this->display->setComponent('field_one', [
      'type' => 'basic_string',
      'settings' => [],
    ]);
    $this->display->setComponent('field_two', [
      'type' => 'basic_string',
      'settings' => [],
    ]);
    $this->display->save();

    $article = $this->serializer->normalize($node, 'fbia', ['entity_view_display' => $this->display]);
    $this->assertEquals($title, $article->getHeader()->getTitle()->getPlainText());
    $children = $article->getChildren();
    $this->assertEquals(4, count($children));
  }

}
