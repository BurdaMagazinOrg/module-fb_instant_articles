<?php

namespace Drupal\Tests\fb_instant_articles_views\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\fb_instant_articles_views\Normalizer\ContentEntityNormalizer;
use Drupal\node\NodeInterface;
use Drupal\Tests\fb_instant_articles\Unit\ContentEntityNormalizerTestBase;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Tests the fbia content entity normalizer class.
 *
 * @coversDefaultClass \Drupal\fb_instant_articles_views\Normalizer\ContentEntityNormalizer
 *
 * @group fb_instant_articles_views
 */
class ContentEntityNormalizerTest extends ContentEntityNormalizerTestBase {

  /**
   * Helper function to get the content entity normalizer class name.
   *
   * @return string
   *   Content entity normalizer class name.
   */
  protected function getContentEntityNormalizerClassName() {
    return ContentEntityNormalizer::class;
  }

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
    $this->assertFalse($normalizer->supportsNormalization($content_entity, 'fbia'));
    $this->assertTrue($normalizer->supportsNormalization($content_entity, 'fbia_rss'));
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
    $now_date = \DateTime::createFromFormat('U', $now);
    $entity = $this->getContentEntity(NodeInterface::class, '/node/1', 'Test entity', $now, $now, 'Joe Mayo');

    $normalized = $normalizer->normalize($entity, 'fbia_rss');
    $this->assertTrue(is_array($normalized));
    $this->assertEquals('http://example.com/node/1', $normalized['link']);
    $this->assertEquals('Test entity', $normalized['title']);
    $this->assertEquals($now_date->format('c'), $normalized['created']);
    $this->assertEquals($now_date->format('c'), $normalized['modified']);
    $this->assertEquals('Joe Mayo', $normalized['author']);
    $this->assertTrue($normalized['content:encoded'] instanceof InstantArticle);
  }

}
