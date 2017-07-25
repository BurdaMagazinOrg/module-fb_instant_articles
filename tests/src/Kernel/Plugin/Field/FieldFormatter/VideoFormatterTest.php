<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Video;

/**
 * Tests for the VideoFormatter.
 *
 * @group fb_instant_articles
 *
 * @coversDefaultClass \Drupal\fb_instant_articles\Plugin\Field\FieldFormatter\VideoFormatter
 */
class VideoFormatterTest extends FormatterTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['file'];

  /**
   * {@inheritdoc}
   */
  protected function getFieldType() {
    return 'file';
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);

    // Setup entity view display with default settings.
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_video',
      'settings' => [],
    ]);
    $this->display->save();
  }

  /**
   * Tests the instant article video formatter.
   *
   * @covers ::viewInstantArticle
   */
  public function testVideoFormatter() {
    $entity = EntityTest::create([]);
    // Handy method to populate the field with a real value.
    // @see FileItem::generateSampleValue()
    $entity->{$this->fieldName}->generateSampleItems(2);

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_HEADER);

    // Assert that a cover video was added.
    $video = $article->getHeader()->getCover();
    $this->assertTrue($video instanceof Video);
    // Assert default settings for the video field formatter.
    $this->assertNull($video->getPresentation());
    $this->assertFalse($video->isLikeEnabled());
    $this->assertFalse($video->isCommentsEnabled());
    $this->assertFalse($video->isControlsShown());
    $this->assertTrue($video->isAutoplay());

    // Test config with everything inverted.
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_video',
      'settings' => [
        'presentation' => Video::ASPECT_FIT,
        'likes' => TRUE,
        'comments' => TRUE,
        'controls' => TRUE,
        'autoplay' => FALSE,
        'feed_cover' => TRUE,
      ],
    ]);
    $this->display->save();
    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_HEADER);

    // Assert that a cover video was added.
    $video = $article->getHeader()->getCover();
    $this->assertTrue($video instanceof Video);
    // Assert settings are reflected in the output.
    $this->assertEquals(Video::ASPECT_FIT, $video->getPresentation());
    $this->assertTrue($video->isLikeEnabled());
    $this->assertTrue($video->isCommentsEnabled());
    $this->assertTrue($video->isControlsShown());
    $this->assertFalse($video->isAutoplay());

    // Test adding the video to the body.
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_CONTENT);

    $children = $article->getChildren();
    $this->assertEquals(2, count($children));
    $this->assertTrue($children[0] instanceof Video);
  }

  /**
   * Tests the instant article video formatter when a canonical URL is in play.
   *
   * @covers ::viewInstantArticle
   */
  function testVideoFormatterCanonicalUrl() {
    $entity = EntityTest::create([]);
    // Handy method to populate the field with a real value.
    // @see FileItem::generateSampleValue()
    $entity->{$this->fieldName}->generateSampleItems(1);

    // Test with a canonical URL set.
    $config = $this->config('fb_instant_articles.settings');
    $config->set('canonical_url_override', 'http://example.com/override')
      ->save();
    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_CONTENT);
    $children = $article->getChildren();
    $this->assertStringStartsWith('http://example.com/override', $children[0]->getUrl());
  }

}
