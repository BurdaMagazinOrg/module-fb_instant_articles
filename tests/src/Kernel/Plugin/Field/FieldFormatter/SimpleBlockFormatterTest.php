<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\Blockquote;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Paragraph;
use Facebook\InstantArticles\Elements\Pullquote;

/**
 * Tests instant article block formatters.
 *
 * Including BlockquoteFormatter, ParagraphFormatter, and PullquoteFormatter,
 * as they are all very simple.
 *
 * @group fb_instant_articles
 */
class SimpleBlockFormatterTest extends FormatterTestBase {

  /**
   * Test BlockquoteFormatter, ParagraphFormatter, and PullquoteFormatter.
   */
  public function testSimpleBlockFormatter() {
    $value_alpha = 'I am a random value.';
    $value_beta = 'I am another random value.';

    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}[] = ['value' => $value_alpha];
    $entity->{$this->fieldName}[] = ['value' => $value_beta];

    $formatter_tests = [
      ['fbia_blockquote', Blockquote::class],
      ['fbia_paragraph', Paragraph::class],
      ['fbia_pullquote', Pullquote::class],
    ];

    foreach ($formatter_tests as $formatter_test) {
      list ($formatter_name, $element_class) = $formatter_test;
      $this->display->setComponent($this->fieldName, [
        'type' => $formatter_name,
        'settings' => [],
      ]);
      $this->display->save();

      /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
      $formatter = $this->display->getRenderer($this->fieldName);
      $article = InstantArticle::create();
      $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_CONTENT, $this->normalizerMock);
      $children = $article->getChildren();
      $this->assertEquals(2, count($children));
      $this->assertTrue($children[0] instanceof $element_class);
      $this->assertEquals($value_alpha, $children[0]->getTextChildren()[0]);
    }
  }

}
