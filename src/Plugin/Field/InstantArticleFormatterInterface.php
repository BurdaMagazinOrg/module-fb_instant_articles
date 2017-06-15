<?php

namespace Drupal\fb_instant_articles\Plugin\Field;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterInterface;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Interface to define an operation to manipulate an InstantArticle object.
 */
interface InstantArticleFormatterInterface extends FormatterInterface {

  /**
   * Modifies the given instant article with the contents of this field.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field values to be rendered.
   * @param \Facebook\InstantArticles\Elements\InstantArticle $article
   *   Instant article object to modify, rendering the contents of this field
   *   into it.
   * @param string $region
   *   The Instant Article region name that the contents of this field should be
   *   rendered into.
   * @param string $langcode
   *   (optional) The language that should be used to render the field. Defaults
   *   to the current content language.
   *
   * @return \Facebook\InstantArticles\Elements\InstantArticle
   *   Modified instant article.
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, $langcode = NULL);

}
