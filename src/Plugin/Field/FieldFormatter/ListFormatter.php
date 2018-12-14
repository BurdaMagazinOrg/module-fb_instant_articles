<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\ListElement;
use Facebook\InstantArticles\Elements\ListItem;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Plugin implementation of the 'fbia_list' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_list",
 *   label = @Translation("FBIA List"),
 *   field_types = {
 *     "string",
 *     "string_long",
 *     "list_string",
 *     "list_integer",
 *     "list_float",
 *   }
 * )
 */
class ListFormatter extends FormatterBase {

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

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, NormalizerInterface $normalizer, $langcode = NULL) {
    if (!$items->isEmpty()) {
      if ($this->getSetting('is_ordered')) {
        $list = ListElement::createOrdered();
      }
      else {
        $list = ListElement::createUnordered();
      }
      foreach ($items as $delta => $item) {
        $list->addItem(
          ListItem::create()
            ->appendText($item->value)
        );
      }
      // Lists can only be added to the content part of an instant article, so
      // we ignore $region.
      $article->addChild($list);
    }
  }

}
