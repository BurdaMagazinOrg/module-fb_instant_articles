<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Pullquote;

/**
 * Plugin implementation of the 'fbia_pullquote' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_pullquote",
 *   label = @Translation("FBIA Pullquote"),
 *   field_types = {
 *     "string",
 *     "string_long"
 *   }
 * )
 */
class PullquoteFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, $langcode = NULL) {
    foreach ($items as $delta => $item) {
      // Blockquotes are only allowed in the content region, add it there
      // regardless of the given $region. Note that the FBIA SDK will sanitize
      // the value.
      $article->addChild(
        Pullquote::create()
          ->appendText($item->value)
      );
    }
  }

}
