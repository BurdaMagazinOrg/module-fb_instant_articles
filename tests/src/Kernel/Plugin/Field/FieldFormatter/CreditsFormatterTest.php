<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Tests the CreditsFormatter.
 *
 * @group fb_instant_articles
 */
class CreditsFormatterTest extends FormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Setup entity view display with default settings.
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_credits',
      'settings' => [],
    ]);
    $this->display->save();
  }

  /**
   * Test the instant article credits formatter.
   */
  public function testCreditsFormatter() {
    $value_alpha = 'Here are some credits.';
    $value_beta = 'Moar credits.';

    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}[] = ['value' => $value_alpha];
    $entity->{$this->fieldName}[] = ['value' => $value_beta];

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);

    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_FOOTER);

    $credits = $article->getFooter()->getCredits();
    $this->assertEquals(2, count($credits));
    $this->assertEquals($value_alpha, $credits[0]->getPlainText());
    $this->assertEquals($value_beta, $credits[1]->getPlainText());
  }

}
