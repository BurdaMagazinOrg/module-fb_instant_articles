<?php

namespace Drupal\Tests\fb_instant_articles\Unit;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\fb_instant_articles\Normalizer\InstantArticleRssContentEntityNormalizer;
use Drupal\node\NodeInterface;
use Facebook\InstantArticles\Elements\InstantArticle;
use Symfony\Component\Serializer\Serializer;

/**
 * Tests the fbia content entity normalizer class.
 *
 * @coversDefaultClass \Drupal\fb_instant_articles\Normalizer\InstantArticleRssContentEntityNormalizer
 *
 * @group fb_instant_articles
 */
class InstantArticleRssContentEntityNormalizerTest extends ContentEntityNormalizerTestBase {

  /**
   * Tests the supportsNormalization() method.
   *
   * @covers ::supportsNormalization
   */
  public function testSupportsNormalization() {
    $content_entity = $this->getMockBuilder(ContentEntityInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $config_entity = $this->getMockBuilder(ConfigEntityInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $normalizer = $this->getContentEntityNormalizer();
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

  /**
   * Helper function to create a new FBIA RSS normalizer for testing.
   *
   * @param array $settings
   *   Global config settings.
   * @param array $components
   *   Entity view display components.
   *
   * @return \Drupal\fb_instant_articles\Normalizer\InstantArticleContentEntityNormalizer
   *   Content entity normalizer object to test against.
   */
  protected function getContentEntityNormalizer(array $settings = [], array $components = []) {
    $config_factory = $this->getConfigFactoryStub([
      'fb_instant_articles.settings' => $settings,
    ]);
    $content_entity_normalizer = $this->getMockBuilder(InstantArticleRssContentEntityNormalizer::class)
      ->setConstructorArgs([$config_factory])
      ->setMethods(['getApplicableComponents', 'getApplicationVersion'])
      ->getMock();
    $content_entity_normalizer->method('getApplicableComponents')
      ->willReturn($components);
    $content_entity_normalizer->method('getApplicationVersion')
      ->willReturn('8.x-2.x');
    $serializer = $this->createMock(Serializer::class);
    $serializer->method('normalize')
      ->willReturn(InstantArticle::create());
    $content_entity_normalizer->setSerializer($serializer);

    return $content_entity_normalizer;
  }

}
