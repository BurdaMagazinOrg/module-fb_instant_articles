<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'fbia_ad' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_ad",
 *   label = @Translation("FBIA Advertisement"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class AdFormatter extends FbiaFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'source_type' => self::SOURCE_TYPE_URL,
      'width' => 320,
      'height' => 50,
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

}
