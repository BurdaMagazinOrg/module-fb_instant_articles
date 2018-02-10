<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;

/**
 * Plugin implementation of the 'fbia_analytics_link' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_analytics_link",
 *   label = @Translation("FBIA Analytics"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class AnalyticsLinkFormatter extends AnalyticsFormatter implements InstantArticleFormatterInterface {

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
    return [];
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
    return $item->uri;
  }

}
