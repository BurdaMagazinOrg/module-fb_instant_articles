<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\Caption;
use Facebook\InstantArticles\Elements\Image;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Tests for the ImageFormatter.
 *
 * @group fb_instant_articles
 */
class ImageFormatterTest extends FormatterTestBase {

  public static $modules = ['image', 'file'];

  /**
   * {@inheritdoc}
   */
  protected function getFieldType() {
    return 'image';
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
      'type' => 'fbia_image',
      'settings' => [],
    ]);
    $this->display->save();
  }

  /**
   * Tests the instant article image formatter.
   */
  public function testImageFormatter() {
    $entity = EntityTest::create([]);
    // Handy method to populate the field with a real value.
    // @see ImageItem::generateSampleValue()
    $entity->{$this->fieldName}->generateSampleItems(2);

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_HEADER, $this->normalizerMock);

    // Assert that a cover image was added.
    $image = $article->getHeader()->getCover();
    $this->assertTrue($image instanceof Image);
    // Default settings for the image formatter are no captions and an empty
    // presentation value.
    $this->assertNull($image->getCaption());
    $this->assertNull($image->getPresentation());

    // Test config with everything turned on.
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_image',
      'settings' => [
        'caption' => TRUE,
        'presentation' => Image::ASPECT_FIT,
      ],
    ]);
    $this->display->save();
    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_HEADER, $this->normalizerMock);

    // Assert that a cover image was added.
    $image = $article->getHeader()->getCover();
    $this->assertTrue($image instanceof Image);
    // Assert settings are reflected in the output.
    $this->assertTrue($image->getCaption() instanceof Caption);
    $this->assertEquals(Image::ASPECT_FIT, $image->getPresentation());

    // Test adding the image to the body.
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_CONTENT, $this->normalizerMock);

    $children = $article->getChildren();
    $this->assertEquals(2, count($children));
    $this->assertTrue($children[0] instanceof Image);
  }

}
