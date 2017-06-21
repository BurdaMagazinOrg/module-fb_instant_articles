<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\Blockquote;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Paragraph;

/**
 * Tests the TransformerFormatter.
 *
 * @group fb_instant_articles
 */
class TransformerFormatterTest extends FormatterTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['text', 'filter', 'filter_test'];

  /**
   * {@inheritdoc}
   */
  protected function getFieldType() {
    return 'text_long';
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['text', 'filter', 'filter_test']);

    // Turn off transformer logging.
    \Logger::configure([
      'rootLogger' => [
        'appenders' => [
          'facebook-instantarticles-transformer',
        ],
      ],
      'appenders' => [
        'facebook-instantarticles-transformer' => ['class' => 'LoggerAppenderNull'],
      ],
    ]);

    // Setup entity view display with default settings.
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_transformer',
      'settings' => [],
    ]);
    $this->display->save();
  }

  /**
   * Test the instant article transformer formatter.
   */
  public function testTransformerFormatter() {
    $value_alpha = '<p>I would drape myself in velvet if it were socially acceptable.</p><p>Puddy: I painted my face Elaine: You painted your face? Puddy: Yeah. Elaine: <strong>Why?</strong> Puddy: Well, you know, support the team.</p>';
    $value_beta = '<p>Newman: I mean parcels are rarely damaged during shipping. Jerry: Define rarely. Newman: Frequently.</p><blockquote>Six years I’ve had this t-shirt. It’s my best one. I call him Golden Boy.</blockquote>';

    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}[] = ['value' => $value_alpha, 'format' => 'full_html'];
    $entity->{$this->fieldName}[] = ['value' => $value_beta, 'format' => 'full_html'];

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);

    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_HEADER);
    $children = $article->getChildren();
    $this->assertEquals(4, count($children));
    $this->assertTrue($children[0] instanceof Paragraph);
    $this->assertTrue($children[3] instanceof Blockquote);
  }

}
