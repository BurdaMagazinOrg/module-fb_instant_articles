<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Plugin\Field\FieldFormatter\FormatterBase;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Tests the instant article interactive formatter.
 *
 * @group fb_instant_articles
 */
class InteractiveFormatterTest extends FormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Setup entity view display with default settings.
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_interactive',
      'settings' => [],
    ]);
    $this->display->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldType() {
    return 'string_long';
  }

  /**
   * Test the instant article interactive formatter.
   */
  public function testInteractiveFormatter() {
    $interactive_formatter_alpha = 'https://youtu.be/VTz5MtxrDTA';
    $interactive_formatter_beta = 'https://www.instagram.com/p/BVa1DzNFIkT/';

    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}[] = ['value' => $interactive_formatter_alpha];
    $entity->{$this->fieldName}[] = ['value' => $interactive_formatter_beta];

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_CONTENT, $this->normalizerMock);

    $children = $article->getChildren();
    $this->assertEquals(2, count($children));
    $this->assertNull($children[0]->getWidth());
    $this->assertNull($children[0]->getHeight());
    $this->assertEquals($interactive_formatter_alpha, $children[0]->getSource());

    // Test an embedded HTML interactive.
    $interactive_html_alpha = '<blockquote class="instagram-media" data-instgrm-version="7" style=" background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:658px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);"><div style="padding:8px;"> <div style=" background:#F8F8F8; line-height:0; margin-top:40px; padding:62.5% 0; text-align:center; width:100%;"> <div style=" background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACwAAAAsCAMAAAApWqozAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAMUExURczMzPf399fX1+bm5mzY9AMAAADiSURBVDjLvZXbEsMgCES5/P8/t9FuRVCRmU73JWlzosgSIIZURCjo/ad+EQJJB4Hv8BFt+IDpQoCx1wjOSBFhh2XssxEIYn3ulI/6MNReE07UIWJEv8UEOWDS88LY97kqyTliJKKtuYBbruAyVh5wOHiXmpi5we58Ek028czwyuQdLKPG1Bkb4NnM+VeAnfHqn1k4+GPT6uGQcvu2h2OVuIf/gWUFyy8OWEpdyZSa3aVCqpVoVvzZZ2VTnn2wU8qzVjDDetO90GSy9mVLqtgYSy231MxrY6I2gGqjrTY0L8fxCxfCBbhWrsYYAAAAAElFTkSuQmCC); display:block; height:44px; margin:0 auto -44px; position:relative; top:-22px; width:44px;"></div></div><p style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; line-height:17px; margin-bottom:0; margin-top:8px; overflow:hidden; padding:8px 0 7px; text-align:center; text-overflow:ellipsis; white-space:nowrap;"><a href="https://www.instagram.com/p/BUcj6FwBK1F/" style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:normal; line-height:17px; text-decoration:none;" target="_blank">A post shared by Jack Black (@jackblack)</a> on <time style=" font-family:Arial,sans-serif; font-size:14px; line-height:17px;" datetime="2017-05-23T19:16:38+00:00">May 23, 2017 at 12:16pm PDT</time></p></div></blockquote> <script async defer src="//platform.instagram.com/en_US/embeds.js"></script>';
    $interactive_html_beta = '<blockquote class="twitter-tweet" data-lang="en"><p lang="en" dir="ltr">I&#39;ve been using Vim for about 2 years now, mostly because I can&#39;t figure out how to exit it.</p>&mdash; I Am Devloper (@iamdevloper) <a href="https://twitter.com/iamdevloper/status/435555976687923200">February 17, 2014</a></blockquote><script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>';
    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}[] = ['value' => $interactive_html_alpha];
    $entity->{$this->fieldName}[] = ['value' => $interactive_html_beta];
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_interactive',
      'settings' => [
        'source_type' => FormatterBase::SOURCE_TYPE_HTML,
      ],
    ]);
    $this->display->save();
    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_HEADER, $this->normalizerMock);

    $children = $article->getChildren();
    $this->assertEquals(2, count($children));
    $this->assertEquals($interactive_html_alpha, $children[0]->getHtml()->textContent);
    $this->assertEquals($interactive_html_beta, $children[1]->getHtml()->textContent);
  }

}
