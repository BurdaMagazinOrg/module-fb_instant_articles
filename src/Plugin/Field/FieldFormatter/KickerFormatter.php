<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Plugin implementation of the 'fbia_kicker' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_kicker",
 *   label = @Translation("FBIA Kicker"),
 *   field_types = {
 *     "string",
 *     "string_long"
 *   }
 * )
 */
class KickerFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, $langcode = NULL) {
    // Kickers are only added to the header. Get the header, creating it if need
    // be.
    $header = $article->getHeader();
    if (!$header) {
      $header = Header::create();
      $article->withHeader($header);
    }
    // Note that there can only be one kicker. We use the first value as the
    // kicker.
    if ($item = $items->get(0)) {
      $header->withKicker($items->get(0)->value);
    }
  }

}
