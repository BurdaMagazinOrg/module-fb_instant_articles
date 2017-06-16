<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\ListElement;

/**
 * Tests the instant article list field formatter.
 *
 * @group fb_instant_articles
 */
class ListFormatterTest extends FormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_list',
      'settings' => [
        'is_ordered' => FALSE,
      ],
    ]);
    $this->display->save();
  }

  /**
   * Test the instant article paragraph formatter.
   */
  public function testListFormatter() {
    $value_alpha = 'I am a random value.';
    $value_beta = 'I am another random value.';

    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}[] = ['value' => $value_alpha];
    $entity->{$this->fieldName}[] = ['value' => $value_beta];

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_CONTENT);

    $children = $article->getChildren();
    $this->assertEquals(1, count($children));
    $this->assertTrue($children[0] instanceof ListElement);

    /** @var \Facebook\InstantArticles\Elements\ListElement $list */
    $list = $children[0];
    $list_items = $list->getItems();
    $this->assertEquals(2, count($list_items));
    $this->assertEquals($value_alpha, $list_items[0]->getPlainText());
  }

}
