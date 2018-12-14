<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Plugin\Field\FieldFormatter\FormatterBase;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\Ad;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Tests the instant article ad formatter.
 *
 * @group fb_instant_articles
 */
class AdFormatterTest extends FormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Setup entity view display with default settings.
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_ad',
      'settings' => [],
    ]);
    $this->display->save();
  }

  /**
   * Test the instant article ad formatter.
   */
  public function testAdFormatter() {
    $ad_url = 'http://example.com/ad';

    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}->value = $ad_url;

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_HEADER, $this->normalizerMock);

    $ads = $article->getHeader()->getAds();
    $this->assertEquals(1, count($ads));
    $this->assertTrue($ads[0] instanceof Ad);
    $this->assertEquals(300, $ads[0]->getWidth());
    $this->assertEquals(250, $ads[0]->getHeight());
    $this->assertEquals($ad_url, $ads[0]->getSource());

    // Test an embedded HTML ad.
    $ad_html = '<script src="http://example.com/ad.js"></script>';
    $entity->{$this->fieldName}->value = $ad_html;
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_ad',
      'settings' => [
        'source_type' => FormatterBase::SOURCE_TYPE_HTML,
      ],
    ]);
    $this->display->save();
    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_HEADER, $this->normalizerMock);

    $ads = $article->getHeader()->getAds();
    $this->assertEquals(1, count($ads));
    $this->assertEquals(300, $ads[0]->getWidth());
    $this->assertEquals(250, $ads[0]->getHeight());
    $this->assertEquals($ad_html, $ads[0]->getHtml()->textContent);
  }

}
