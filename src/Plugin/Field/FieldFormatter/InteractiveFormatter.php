<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;
use Facebook\InstantArticles\Elements\Interactive;

/**
 * Plugin implementation of the 'fbia_interactive' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_interactive",
 *   label = @Translation("FBIA Interactive"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class InteractiveFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'source_type' => self::SOURCE_TYPE_URL,
      'width' => '',
      'height' => '',
      'margin' => Interactive::NO_MARGIN,
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
    $element['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#description' => $this->t('The width of your interactive graphic.'),
      '#default_value' => $this->getSetting('width'),
    ];
    $element['height'] = [
      '#type' => 'number',
      '#title' => $this->t('Height'),
      '#description' => $this->t('The height of your interactive graphic.'),
      '#default_value' => $this->getSetting('height'),
    ];
    $element['margin'] = [
      '#type' => 'select',
      '#title' => t('Margin'),
      '#description' => t('The margin setting of your intereactive graphic.'),
      '#default_value' => $this->getSetting('margin'),
      '#options' => [
        Interactive::NO_MARGIN => $this->t('No margin'),
        Interactive::COLUMN_WIDTH => t('Column width'),
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
    if ($width = $this->getSetting('width')) {
      $summary[] = $this->t('Width: @width', ['@width' => $width]);
    }
    if ($height = $this->getSetting('height')) {
      $summary[] = $this->t('Height: @height', ['@height' => $height]);
    }
    $margin = $this->getSetting('margin');
    $summary[] = $this->t('Margin setting: @margin', ['@margin' => $margin === Interactive::NO_MARGIN ? $this->t('No margin') : $this->t('Column width')]);
    return $summary;
  }

}
