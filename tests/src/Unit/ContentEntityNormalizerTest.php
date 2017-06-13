<?php

namespace Drupal\Tests\fb_instant_articles\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\fb_instant_articles\Normalizer\ContentEntityNormalizer;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the fbia content entity normalizer class.
 *
 * @coversDefaultClass \Drupal\fb_instant_articles\Normalizer\ContentEntityNormalizer
 *
 * @group fb_instant_articles
 */
class ContentEntityNormalizerTest extends UnitTestCase {

  /**
   * Setup method.
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * Tests the supportsNormalization() method.
   *
   * @covers ::supportsNormalization
   */
  public function testSupportsNormalization() {
    $config_factory = $this->prophesize(ConfigFactoryInterface::class)->reveal();
    $entity_type_manager = $this->prophesize(EntityTypeManager::class)->reveal();
    $content_entity = $this->prophesize(ContentEntityInterface::class)->reveal();
    $config_entity = $this->prophesize(ConfigEntityInterface::class)->reveal();

    $normalizer = new ContentEntityNormalizer($config_factory, $entity_type_manager);
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
    // Setup a mock config object.
    $config = $this->prophesize(ImmutableConfig::class);
    $config_factory = $this->prophesize(ConfigFactoryInterface::class);
    $config_factory->get('fb_instant_articles.base_settings')
      ->willReturn($config->reveal());
    // Setup a mock entity type manager.
    $storage = $this->prophesize(EntityStorageInterface::class);
    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager->getStorage('user')
      ->willReturn($storage->reveal());
    // Setup a mock content entity.
    $url = $this->prophesize(Url::class);
    $url->toString()
      ->willReturn('http://example.com');
    $content_entity = $this->prophesize(ContentEntityInterface::class);
    $content_entity->toUrl('canonical', ['absolute' => TRUE])
      ->willReturn($url->reveal());
    $content_entity->label()
      ->willReturn('Foo bar');

    $normalizer = new ContentEntityNormalizer($config_factory->reveal(), $entity_type_manager->reveal());
    $article = $normalizer->normalize($content_entity->reveal(), 'fbia', []);
    $this->assertEquals('http://example.com', $article->getCanonicalURL());
    $this->assertEquals('Foo bar', $article->getHeader()->getTitle()->getPlainText());


  }

}
