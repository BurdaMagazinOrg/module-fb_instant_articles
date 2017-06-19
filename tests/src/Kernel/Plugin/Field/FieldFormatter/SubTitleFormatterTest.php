<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Tests the SubTitleFormatterTest.
 *
 * @group fb_instant_articles
 */
class SubTitleFormatterTest extends FormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Setup entity view display with default settings.
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_subtitle',
      'settings' => [],
    ]);
    $this->display->save();
  }

  /**
   * Test the instant article subtitle formatter.
   */
  public function testSubTitleFormatter() {
    $value_alpha = 'Inspiring subtitle to set the tone';
    $value_beta = 'Another subtitle, which should never be seen.';

    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}[] = ['value' => $value_alpha];
    $entity->{$this->fieldName}[] = ['value' => $value_beta];

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);

    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_HEADER);
    $this->assertEquals($value_alpha, $article->getHeader()->getSubtitle()->getPlainText());
  }

}
