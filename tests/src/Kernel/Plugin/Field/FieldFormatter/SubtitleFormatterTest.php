<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Regions;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Tests the SubtitleFormatterTest.
 *
 * @group fb_instant_articles
 */
class SubtitleFormatterTest extends FormatterTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['text', 'filter', 'filter_test'];

  /**
   * Field name of the text field.
   *
   * @var string
   */
  protected $textFieldName;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->textFieldName = Unicode::strtolower($this->randomMachineName());

    $field_storage = FieldStorageConfig::create([
      'field_name' => $this->textFieldName,
      'entity_type' => $this->entityType,
      'type' => 'text_long',
    ]);
    $field_storage->save();

    $instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $this->bundle,
      'label' => $this->randomMachineName(),
    ]);
    $instance->save();

    $this->installConfig(['text', 'filter', 'filter_test']);

    // We have to save the display that's been partially setup already and then
    // reload it, b/c the display caches field definitions at initialization.
    // This way the field defined above will be allowed to be configured on the
    // display.
    $this->display->save();
    $this->display = EntityViewDisplay::load($this->display->id());

    // Setup entity view display with default settings.
    $this->display->setComponent($this->textFieldName, [
      'type' => 'fbia_subtitle',
      'settings' => [],
    ]);
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_subtitle',
      'settings' => [],
    ]);
    $this->display->save();
  }

  /**
   * Test the instant article subtitle formatter.
   */
  public function testSubtitleFormatter() {
    $value_alpha = 'Inspiring subtitle to set the tone';
    $value_beta = 'Another subtitle, which should never be seen.';

    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}[] = ['value' => $value_alpha];
    $entity->{$this->fieldName}[] = ['value' => $value_beta];

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);

    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_HEADER, $this->normalizerMock);
    $this->assertEquals($value_alpha, $article->getHeader()->getSubtitle()->getPlainText());
  }

  /**
   * Test the instant article subtitle formatter on a text_long field.
   */
  public function testSubtitleFormatterTextLong() {
    $value = '<p>Inspiring <strong>subtitle</strong> to set <a href="https://en.wikipedia.org/wiki/Tone_(literature)">the tone</a>.</p>';

    $entity = EntityTest::create([]);
    $entity->{$this->textFieldName}[] = ['value' => $value, 'format' => 'full_html'];

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->textFieldName);

    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->textFieldName}, $article, Regions::REGION_HEADER, $this->normalizerMock);
    $subtitle = $article->getHeader()->getSubtitle();
    $subtitle_dom = $subtitle->toDOMElement();
    $actual_string = $subtitle_dom->ownerDocument->saveHTML($subtitle_dom);
    $this->assertEquals('<h2>Inspiring <b>subtitle</b> to set <a href="https://en.wikipedia.org/wiki/Tone_(literature)">the tone</a>.</h2>', $actual_string);
  }

}
