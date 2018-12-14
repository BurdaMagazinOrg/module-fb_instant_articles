<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Paragraph;

/**
 * Plugin implementation of the 'fbia_paragraph' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_paragraph",
 *   label = @Translation("FBIA Paragraph"),
 *   field_types = {
 *     "string",
 *     "string_long"
 *   }
 * )
 */
class ParagraphFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, $langcode = NULL) {
    foreach ($items as $delta => $item) {
      // Paragraphs are only allowed in the content region, add it there
      // regardless of the given $region. Note that the FBIA SDK will sanitize
      // the value.
      $article->addChild(
        Paragraph::create()
          ->appendText($item->value)
      );
    }
  }

}
