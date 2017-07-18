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
   * Test the instant article transformer formatter default ruleset.
   *
   * @param string $markup
   *   Markup for the value of a text field.
   * @param int $expected_child_count
   *   Expected number of Facebook elements generated.
   * @param array $expected_child_instances
   *   Expected child instance class names.
   *
   * @dataProvider transformerFormatterDataProvider
   */
  public function testTransformerFormatter($markup, $expected_child_count, array $expected_child_instances) {
    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}[] = ['value' => $markup, 'format' => 'full_html'];

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);

    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_CONTENT);
    $children = $article->getChildren();
    $this->assertEquals($expected_child_count, count($children));
    foreach ($expected_child_instances as $i => $instance) {
      $this->assertInstanceOf($instance, $children[$i]);
    }
  }

  /**
   * Data provider for the testNormalizeWithTransformer test.
   *
   * Note that as this passes markup through the Transformer in the same way as
   * a field that is configured with a non-FBIA formatter, this is a great way
   * to test markup that may be encountered by other field types as well.
   *
   * @return array
   *   Array of an array of arguments for testNormalizeWithTransformer().
   */
  public function transformerFormatterDataProvider() {
    return [
      [
        '<p>I would drape myself in velvet if it were socially acceptable.</p><p>Puddy: I painted my face Elaine: You painted your face? Puddy: Yeah. Elaine: <strong>Why?</strong> Puddy: Well, you know, support the team.</p>',
        2,
        [
          Paragraph::class,
          Paragraph::class,
        ],
      ],
      [
        '<p>Newman: I mean parcels are rarely damaged during shipping. Jerry: Define rarely. Newman: Frequently.</p><blockquote>Six years I’ve had this t-shirt. It’s my best one. I call him Golden Boy.</blockquote>',
        2,
        [
          Paragraph::class,
          Blockquote::class,
        ],
      ],
      [
        '<div><a href="http://example.com"></a></div>',
        1,
        [Paragraph::class],
      ],
    ];
  }

}
