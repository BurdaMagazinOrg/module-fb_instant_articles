<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'fbia_list' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_list",
 *   label = @Translation("FBIA List"),
 *   field_types = {
 *     "list_string",
 *     "list_integer",
 *     "list_float",
 *   }
 * )
 */
class ListFormatter extends FbiaFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'is_ordered' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['is_ordered'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Ordered list'),
      '#description' => $this->t('Should this list be ordered or not?'),
      '#default_value' => $this->getSetting('is_ordered'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return [$this->getSetting('is_ordered') ? $this->t('Ordered') : $this->t('Unordered')];
  }

}
