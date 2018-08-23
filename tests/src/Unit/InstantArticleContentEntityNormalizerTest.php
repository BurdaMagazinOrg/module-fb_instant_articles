<?php

namespace Drupal\Tests\fb_instant_articles\Unit;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\fb_instant_articles\Normalizer\InstantArticleContentEntityNormalizer;
use Drupal\node\NodeInterface;
use Facebook\InstantArticles\Elements\Analytics;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Tests the fbia content entity normalizer class.
 *
 * @coversDefaultClass \Drupal\fb_instant_articles\Normalizer\InstantArticleContentEntityNormalizer
 *
 * @group fb_instant_articles
 */
class InstantArticleContentEntityNormalizerTest extends ContentEntityNormalizerTestBase {

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
      'analytics.embed_code' => 'analytics embed code',
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
   * Tests the normalize method on an RTL site.
   *
   * @covers ::normalize
   */
  public function testNormalizeRtl() {
    $normalizer = $this->getContentEntityNormalizer([], [], LanguageInterface::DIRECTION_RTL);
    $now = time();
    $entity = $this->getContentEntity(NodeInterface::class, '/node/1', 'Test entity', $now, $now, 'Joe Mayo');
    $article = $normalizer->normalize($entity, 'fbia');
    $this->assertTrue($article->isRTLEnabled());
  }

  /**
   * Tests the sortComponents() method.
   *
   * @dataProvider sortComponentsProvider
   * @covers ::sortComponents
   */
  public function testSortComponents($components, $expected) {
    uasort($components, [InstantArticleContentEntityNormalizer::class, 'sortComponents']);
    // Re-key the array for equality check.
    $components = array_values($components);
    $this->assertEquals($expected, $components);
  }

  /**
   * Data provider for testSortComponents.
   *
   * @return array
   *   Return an array or arrays of arguments to testSortComponents.
   */
  public function sortComponentsProvider() {
    return [
      [
        [
          ['region' => 'header', 'weight' => 0],
          ['region' => 'content', 'weight' => 1],
          ['region' => 'footer', 'weigth' => 2],
        ],
        [
          ['region' => 'header', 'weight' => 0],
          ['region' => 'content', 'weight' => 1],
          ['region' => 'footer', 'weigth' => 2],
        ],
      ],
      [
        [
          ['region' => 'header', 'weight' => 0],
          ['region' => 'footer', 'weight' => 2],
          ['region' => 'content', 'weight' => 1],
        ],
        [
          ['region' => 'header', 'weight' => 0],
          ['region' => 'content', 'weight' => 1],
          ['region' => 'footer', 'weight' => 2],
        ],
      ],
      [
        [
          ['region' => 'footer', 'weight' => 2],
          ['region' => 'header', 'weight' => 0],
          ['region' => 'content', 'weight' => 1],
        ],
        [
          ['region' => 'header', 'weight' => 0],
          ['region' => 'content', 'weight' => 1],
          ['region' => 'footer', 'weight' => 2],
        ],
      ],
      [
        [
          ['region' => 'header', 'weight' => 100],
          ['region' => 'content', 'weight' => -100],
          ['region' => 'footer', 'weight' => 0],
        ],
        [
          ['region' => 'header', 'weight' => 100],
          ['region' => 'content', 'weight' => -100],
          ['region' => 'footer', 'weight' => 0],
        ],
      ],
      [
        [
          ['region' => 'footer'],
          ['region' => 'content'],
          ['region' => 'header'],
        ],
        [
          ['region' => 'header'],
          ['region' => 'content'],
          ['region' => 'footer'],
        ],
      ],
    ];
  }

  /**
   * Helper function to create a new ContentEntityNormalizer for testing.
   *
   * @param array $settings
   *   Global config settings.
   * @param array $components
   *   Entity view display components.
   * @param string $language_direction
   *   Language direction.
   *
   * @return \Drupal\fb_instant_articles\Normalizer\InstantArticleContentEntityNormalizer
   *   Content entity normalizer object to test against.
   */
  protected function getContentEntityNormalizer(array $settings = [], array $components = [], $language_direction = LanguageInterface::DIRECTION_LTR) {
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
    $info_parser = $this->getMock(InfoParserInterface::class);
    $module_handler = $this->getMock(ModuleHandlerInterface::class);
    $this->currentLanguage = $this->getMockBuilder(Language::class)
      ->disableOriginalConstructor()
      ->setMethods(['getDirection'])
      ->getMock();
    $this->currentLanguage->expects($this->any())
      ->method('getDirection')
      ->willReturn($language_direction);
    $language_manager = $this->getMockBuilder(LanguageManager::class)
      ->disableOriginalConstructor()
      ->setMethods(['getCurrentLanguage'])
      ->getMock();
    $language_manager->expects($this->once())
      ->method('getCurrentLanguage')
      ->willReturn($this->currentLanguage);
    $content_entity_normalizer = $this->getMockBuilder(InstantArticleContentEntityNormalizer::class)
      ->setConstructorArgs([
        $config_factory,
        $entity_field_manager,
        $entity_type_manager,
        $info_parser,
        $module_handler,
        $language_manager,
      ])
      ->setMethods(['getApplicableComponents', 'getApplicationVersion'])
      ->getMock();
    $content_entity_normalizer->method('getApplicableComponents')
      ->willReturn($components);
    $content_entity_normalizer->method('getApplicationVersion')
      ->willReturn('8.x-2.x');

    return $content_entity_normalizer;
  }

}
