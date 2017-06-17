<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Plugin\Field\FieldFormatter\FormatterBase;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\Analytics;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Test instant articles analytics field formatter.
 *
 * @group fb_instant_articles
 */
class AnalyticsFormatterTest extends FormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Setup entity view display with default settings.
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_analytics',
      'settings' => [],
    ]);
    $this->display->save();
  }

  /**
   * Test the instant article analytics formatter.
   */
  public function testAnalyticsFormatter() {
    $value = 'http://example.com/analytics';

    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}->value = $value;

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_CONTENT);

    $children = $article->getChildren();
    $this->assertEquals(1, count($children));
    $this->assertTrue($children[0] instanceof Analytics);
    /** @var \Facebook\InstantArticles\Elements\Analytics $analytics */
    $analytics = $children[0];
    $this->assertEquals($value, $analytics->getSource());

    // Test an embedded HTML ad.
    $analytics_html = '<script src="http://example.com/analytics.js"></script>';
    $entity->{$this->fieldName}->value = $analytics_html;
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_analytics',
      'settings' => [
        'source_type' => FormatterBase::SOURCE_TYPE_HTML,
      ],
    ]);
    $this->display->save();
    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_HEADER);

    $children = $article->getChildren();
    $this->assertEquals($analytics_html, $children[0]->getHtml()->textContent);
  }

}
