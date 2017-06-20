<?php

namespace Drupal\Tests\fb_instant_articles\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\fb_instant_articles\Normalizer\ContentEntityNormalizer;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;
use Facebook\InstantArticles\Elements\Analytics;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Tests the fbia content entity normalizer class.
 *
 * @coversDefaultClass \Drupal\fb_instant_articles\Normalizer\ContentEntityNormalizer
 *
 * @group fb_instant_articles
 */
class ContentEntityNormalizerTest extends UnitTestCase {

  /**
   * Tests the supportsNormalization() method.
   *
   * @covers ::supportsNormalization
   */
  public function testSupportsNormalization() {
    $config_factory = $this->getMockBuilder(ConfigFactoryInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entity_field_manager = $this->getMockBuilder(EntityFieldManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entity_type_manager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $content_entity = $this->getMockBuilder(ContentEntityInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $config_entity = $this->getMockBuilder(ConfigEntityInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $normalizer = new ContentEntityNormalizer($config_factory, $entity_field_manager, $entity_type_manager);
    $this->assertTrue($normalizer->supportsNormalization($content_entity, 'fbia'));
    $this->assertFalse($normalizer->supportsNormalization($content_entity, 'json'));
    $this->assertFalse($normalizer->supportsNormalization($config_entity, 'fbia'));
  }

  /**
   * Tests the normalize() method.
   *
   * @covers ::normalize
   */
  public function testNormalize() {
    // Test the global settings effect on the output.
    $normalizer = $this->getContentEntityNormalizer([
      'canonical_url_override' => 'http://example.com',
      'analytics_embed_code' => 'analytics embed code',
      'ads.type' => 'source_url',
      'ads.iframe_url' => 'http://example.com',
      'ads.dimensions' => '300x250',
    ], []);

    $now = time();
    $entity = $this->getContentEntity(NodeInterface::class, '/node/1', 'Test entity', $now, $now, 'Joe Mayo');

    $article = $normalizer->normalize($entity, 'fbia');
    $this->assertTrue($article instanceof InstantArticle);
    $this->assertEquals('http://example.com/node/1', $article->getCanonicalURL());
    $this->assertEquals('Test entity', $article->getHeader()->getTitle()->getPlainText());
    $this->assertEquals($now, $article->getHeader()->getPublished()->getDatetime()->format('U'));
    $this->assertEquals($now, $article->getHeader()->getModified()->getDatetime()->format('U'));
    $this->assertEquals('Joe Mayo', $article->getHeader()->getAuthors()[0]->getName());
    $children = $article->getChildren();
    /** @var \Facebook\InstantArticles\Elements\Analytics $analytics */
    $analytics = $children[0];
    $this->assertTrue($analytics instanceof Analytics);
    $this->assertEquals('analytics embed code', $analytics->getHtml()->ownerDocument->saveHTML($analytics->getHtml()));
    $ads = $article->getHeader()->getAds();
    $this->assertEquals(1, count($ads));
    $this->assertEquals($ads[0]->getWidth(), 300);
    $this->assertEquals($ads[0]->getHeight(), 250);
    $this->assertEquals($ads[0]->getSource(), 'http://example.com');
  }

  /**
   * Helper function to create a new ContentEntityNormalizer for testing.
   *
   * @param array $settings
   *   Global config settings.
   * @param array $components
   *   Entity view display components.
   *
   * @return \Drupal\fb_instant_articles\Normalizer\ContentEntityNormalizer
   *   Content entity normalizer object to test against.
   */
  protected function getContentEntityNormalizer(array $settings, array $components) {
    $config_factory = $this->getConfigFactoryStub([
      'fb_instant_articles.base_settings' => $settings,
    ]);
    $entity_field_manager = $this->getMockBuilder(EntityFieldManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entity_storage = $this->getMock(EntityStorageInterface::class);
    $entity_type_manager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entity_type_manager->method('getStorage')
      ->willReturn($entity_storage);
    $content_entity_normalizer = $this->getMockBuilder(ContentEntityNormalizer::class)
      ->setConstructorArgs([
        $config_factory,
        $entity_field_manager,
        $entity_type_manager,
      ])
      ->setMethods(['getApplicableComponents'])
      ->getMock();
    $content_entity_normalizer->method('getApplicableComponents')
      ->willReturn($components);

    return $content_entity_normalizer;
  }

  /**
   * Get a content entity to test with.
   *
   * @param string $class_name
   *   Type of content entity to create.
   * @param string $relative_uri
   *   Relative URI of the created entity, eg. /node/1.
   * @param string $label
   *   Entity label.
   * @param int $created_timestamp
   *   UNIX timestamp for created.
   * @param int $changed_timestamp
   *   UNIX timestamp for changed.
   * @param string $author_name
   *   Display name for the author of the returned entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   Content entity stub.
   */
  protected function getContentEntity($class_name, $relative_uri, $label, $created_timestamp, $changed_timestamp, $author_name) {
    // Mock a URL object for getUrl method to return.
    $url = $this->getMockBuilder(Url::class)
      ->disableOriginalConstructor()
      ->getMock();
    $url->method('toString')
      ->willReturn($relative_uri);

    // Mock an entity according to the given class name. For some tests, we want
    // to be more specific than ContentEntityInterface.
    $entity = $this->getMock($class_name);
    $entity->method('toUrl')
      ->willReturn($url);
    $entity->method('label')
      ->willReturn($label);

    // Mock created timestamp return.
    $created = $this->getMock(FieldItemListInterface::class);
    $created->method('__get')
      ->willReturnMap([
        ['value', $created_timestamp],
      ]);
    $entity->method('get')
      ->willReturnMap([
        ['created', $created],
      ]);

    $entity->method('getChangedTime')
      ->willReturn($changed_timestamp);
    $author = $this->getMock(UserInterface::class);
    $author->method('getDisplayName')
      ->willReturn($author_name);
    $entity->method('getOwner')
      ->willReturn($author);

    return $entity;
  }

}
