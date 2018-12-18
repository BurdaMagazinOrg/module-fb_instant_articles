<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Facebook\InstantArticles\Elements\Analytics;
use Facebook\InstantArticles\Elements\InstantArticle;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Plugin implementation of the 'fbia_analytics' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_analytics",
 *   label = @Translation("FBIA Analytics"),
 *   field_types = {
 *     "string",
 *     "string_long"
 *   }
 * )
 */
class AnalyticsFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'source_type' => self::SOURCE_TYPE_URL,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['source_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Source type'),
      '#description' => $this->t('Add your tracker specifying the URL or embed the full unescaped HTML'),
      '#default_value' => $this->getSetting('source_type'),
      '#options' => [
        self::SOURCE_TYPE_URL => $this->t('URL'),
        self::SOURCE_TYPE_HTML => $this->t('Embedded HTML'),
      ],
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($source_type = $this->getSetting('source_type')) {
      $summary[] = $source_type === self::SOURCE_TYPE_URL ? $this->t('URL') : $this->t('Embedded HTML');
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, NormalizerInterface $normalizer, $langcode = NULL) {
    foreach ($items as $delta => $item) {
      // Create the analytics object according to the field settings.
      $analytics = Analytics::create();
      if ($this->getSetting('source_type') === self::SOURCE_TYPE_HTML) {
        $analytics->withHTML($this->getItemValue($item));
      }
      else {
        $analytics->withSource($this->getItemValue($item));
      }
      // Ad the ad to the body regardless of the region requests. The analytics
      // element is only allowed in the body.
      $article->addChild($analytics);
    }
  }

  /**
   * Return the value for the ad that we are interested in.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   Field item.
   *
   * @return mixed
   *   The value of the given field item that stores the Ad value we're
   *   interested in.
   */
  protected function getItemValue(FieldItemInterface $item) {
    return $item->value;
  }

}
