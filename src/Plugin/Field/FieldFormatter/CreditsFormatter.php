<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Facebook\InstantArticles\Elements\Footer;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Paragraph;

/**
 * Plugin implementation of the 'fbia_credits' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_credits",
 *   label = @Translation("FBIA Credits"),
 *   field_types = {
 *     "string",
 *     "string_long"
 *   }
 * )
 */
class CreditsFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, $langcode = NULL) {
    $credits = [];
    foreach ($items as $delta => $item) {
      $credits[] = Paragraph::create()
        ->appendText($item->value);
    }
    if (!empty($credits)) {
      // Copyright can only go in the footer, put it there and ignore the given
      // $region.
      $footer = $article->getFooter();
      if (!$footer) {
        $footer = Footer::create();
        $article->withFooter($footer);
      }
      $footer->withCredits($credits);
    }
  }

}
