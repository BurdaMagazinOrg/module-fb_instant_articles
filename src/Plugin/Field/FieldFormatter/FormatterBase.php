<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase as DrupalFormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;

/**
 * Base class for all of our FBIA field formatters.
 */
abstract class FormatterBase extends DrupalFormatterBase implements ContainerFactoryPluginInterface, InstantArticleFormatterInterface {

  const SOURCE_TYPE_URL = 'url';

  const SOURCE_TYPE_HTML = 'html';

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Do nothing. Our field formatters are unique in that they are not meant
    // to generate HTML on their own, but only signal to the Serialization API
    // the fate of this field in the FBIA document.
    return [];
  }

}
