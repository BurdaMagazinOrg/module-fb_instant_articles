<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Paragraph;

/**
 * Tests the instant article paragraph field formatter.
 *
 * @group fb_instant_articles
 */
class ParagraphFormatterTest extends FormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_paragraph',
      'settings' => [],
    ]);
    $this->display->save();
  }

  /**
   * Test the instant article paragraph formatter.
   */
  public function testParagraphFormatter() {
    $value = 'I am a random value.';

    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}->value = $value;

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_CONTENT, $this->normalizerMock);

    $children = $article->getChildren();
    $this->assertEquals(1, count($children));
    $this->assertTrue($children[0] instanceof Paragraph);
  }

}
