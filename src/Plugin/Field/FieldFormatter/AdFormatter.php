<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\Ad;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Plugin implementation of the 'fbia_ad' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_ad",
 *   label = @Translation("FBIA Advertisement"),
 *   field_types = {
 *     "string",
 *     "string_long"
 *   }
 * )
 */
class AdFormatter extends FormatterBase implements InstantArticleFormatterInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'source_type' => self::SOURCE_TYPE_URL,
      'width' => 300,
      'height' => 250,
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
    $element['height'] = [
      '#type' => 'number',
      '#title' => $this->t('Height'),
      '#description' => $this->t('Height of the iframe element.'),
      '#default_value' => $this->getSetting('height'),
    ];
    $element['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#description' => $this->t('Width of the iframe element.'),
      '#default_value' => $this->getSetting('width'),
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
    if ($width = $this->getSetting('width')) {
      $summary[] = $this->t('Width: @width', ['@width' => $width]);
    }
    if ($height = $this->getSetting('height')) {
      $summary[] = $this->t('Height: @height', ['@height' => $height]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, $langcode = NULL) {
    foreach ($items as $delta => $item) {
      // Create the ad object according to the field settings.
      $ad = Ad::create();
      if ($width = $this->getSetting('width')) {
        $ad->withWidth((int) $width);
      }
      if ($height = $this->getSetting('height')) {
        $ad->withHeight((int) $height);
      }
      if ($this->getSetting('source_type') === self::SOURCE_TYPE_HTML) {
        $ad->withHTML($this->getItemValue($item));
      }
      else {
        $ad->withSource($this->getItemValue($item));
      }
      // Ad the ad to the appropriate region.
      if ($region === Regions::REGION_HEADER) {
        $header = $article->getHeader();
        if (!$header) {
          $header = Header::create();
        }
        $header->addAd($ad);
      }
      else {
        $article->addChild($ad);
      }
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
