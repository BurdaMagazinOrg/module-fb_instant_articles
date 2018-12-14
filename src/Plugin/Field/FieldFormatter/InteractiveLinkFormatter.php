<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Facebook\InstantArticles\Elements\Interactive;

/**
 * Plugin implementation of the 'fbia_interactive_link' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_interactive_link",
 *   label = @Translation("FBIA Interactive"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class InteractiveLinkFormatter extends InteractiveFormatter {

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
    $margin = $this->getSetting('margin');
    $summary[] = $this->t('Margin setting: @margin', ['@margin' => $margin === Interactive::NO_MARGIN ? $this->t('No margin') : $this->t('Column width')]);
    return $summary;
  }

  /**
   * Return the value for the interactive embed that we are interested in.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   Field item.
   *
   * @return mixed
   *   The value of the given field item that stores the Ad value we're
   *   interested in.
   */
  protected function getItemValue(FieldItemInterface $item) {
    return $item->uri;
  }

}
