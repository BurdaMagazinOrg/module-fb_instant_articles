<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;
use Facebook\InstantArticles\Elements\Blockquote;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Plugin implementation of the 'fbia_blockquote' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_blockquote",
 *   label = @Translation("FBIA Blockquote"),
 *   field_types = {
 *     "string",
 *     "string_long"
 *   }
 * )
 */
class BlockquoteFormatter extends FormatterBase implements InstantArticleFormatterInterface {

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, $langcode = NULL) {
    foreach ($items as $delta => $item) {
      // Blockquotes are only allowed in the content region, add it there
      // regardless of the given $region. Note that the FBIA SDK will sanitize
      // the value.
      $article->addChild(
        Blockquote::create()
          ->appendText($item->value)
      );
    }
  }

}
