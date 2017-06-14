<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Paragraph;

/**
 * Plugin implementation of the 'fbia_paragraph' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_paragraph",
 *   label = @Translation("FBIA Paragraph"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *     "string",
 *     "string_long"
 *   }
 * )
 */
class ParagraphFormatter extends FormatterBase implements InstantArticleFormatterInterface {

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $langcode = NULL) {
    foreach ($items as $delta => $item) {
      $article->addChild(
        Paragraph::create()
          ->appendText(Html::escape($item->value))
      );
    }
  }

}
