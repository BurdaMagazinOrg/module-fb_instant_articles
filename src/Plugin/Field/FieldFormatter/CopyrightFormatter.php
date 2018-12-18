<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Facebook\InstantArticles\Elements\Footer;
use Facebook\InstantArticles\Elements\InstantArticle;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Plugin implementation of the 'fbia_copyright' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_copyright",
 *   label = @Translation("FBIA Copyright"),
 *   field_types = {
 *     "string",
 *     "string_long"
 *   }
 * )
 */
class CopyrightFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, NormalizerInterface $normalizer, $langcode = NULL) {
    foreach ($items as $delta => $item) {
      // Copyright can only go in the footer, put it there and ignore the given
      // $region.
      $footer = $article->getFooter();
      if (!$footer) {
        $footer = Footer::create();
        $article->withFooter($footer);
      }
      // The instant article only takes a single value for copyright. If a value
      // is already set, append to it. Note that the value might also be an
      // object of type Small, in which case we can't append to it, so we just
      // replace it with a string.
      $copyright = $footer->getCopyright();
      if (is_string($copyright)) {
        $copyright .= ' ' . $item->value;
      }
      else {
        $copyright = $item->value;
      }
      $footer->withCopyright($copyright);
    }
  }

}
