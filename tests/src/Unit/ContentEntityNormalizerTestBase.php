<?php

namespace Drupal\Tests\fb_instant_articles\Unit;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\fb_instant_articles\Normalizer\InstantArticleContentEntityNormalizer;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;

/**
 * Base class for Instant Articles content entity normalizers.
 */
class ContentEntityNormalizerTestBase extends UnitTestCase {

  /**
   * Helper function to create a new ContentEntityNormalizer for testing.
   *
   * @param array $settings
   *   Global config settings.
   * @param array $components
   *   Entity view display components.
   *
   * @return \Drupal\fb_instant_articles\Normalizer\InstantArticleContentEntityNormalizer
   *   Content entity normalizer object to test against.
   */
  protected function getContentEntityNormalizer(array $settings, array $components) {
    $config_factory = $this->getConfigFactoryStub([
      'fb_instant_articles.settings' => $settings,
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
    $content_entity_normalizer = $this->getMockBuilder($this->getContentEntityNormalizerClassName())
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
   * Helper function to get the content entity normalizer class name.
   *
   * @return string
   *   Content entity normalizer class name.
   */
  protected function getContentEntityNormalizerClassName() {
    return InstantArticleContentEntityNormalizer::class;
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
