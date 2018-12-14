<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\Footer;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Small;

/**
 * Tests the CopyrightFormatter.
 *
 * @group fb_instant_articles
 */
class CopyrightFormatterTest extends FormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Setup entity view display with default settings.
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_copyright',
      'settings' => [],
    ]);
    $this->display->save();
  }

  /**
   * Test the instant article copyright formatter.
   */
  public function testCopyrightFormatter() {
    $value_alpha = 'Copyright very important information ahead.';
    $value_beta = 'So much information, you won\'t read it.';

    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}[] = ['value' => $value_alpha];

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);

    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_FOOTER, $this->normalizerMock);
    $this->assertEquals($value_alpha, $article->getFooter()->getCopyright());

    $entity->{$this->fieldName}[] = ['value' => $value_beta];
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_FOOTER, $this->normalizerMock);
    $this->assertEquals($value_alpha . ' ' . $value_beta, $article->getFooter()->getCopyright());

    $article = InstantArticle::create();
    $article->withFooter(Footer::create()->withCopyright(Small::create()->appendText($value_alpha)));
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_FOOTER, $this->normalizerMock);
    $this->assertEquals($value_alpha . ' ' . $value_beta, $article->getFooter()->getCopyright());
  }

}
