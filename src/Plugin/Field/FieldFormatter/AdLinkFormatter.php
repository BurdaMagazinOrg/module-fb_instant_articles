<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'fbia_ad_link' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_ad_link",
 *   label = @Translation("FBIA Advertisement"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class AdLinkFormatter extends AdFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $default_settings = parent::defaultSettings();
    unset($default_settings['source_type']);
    return $default_settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    unset($element['source_type']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
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
  protected function getItemValue(FieldItemInterface $item) {
    return $item->uri;
  }

}
