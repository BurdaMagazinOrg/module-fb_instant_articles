<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\InstantArticle;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Plugin implementation of the 'fbia_subtitle' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_subtitle",
 *   label = @Translation("FBIA Subtitle"),
 *   field_types = {
 *     "string",
 *     "string_long"
 *   }
 * )
 */
class SubTitleFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, NormalizerInterface $normalizer, $langcode = NULL) {
    // Subtitles only go in the header. Create one if it doesn't exist yet and
    // ignore the given $region.
    $header = $article->getHeader();
    if (!$header) {
      $header = Header::create();
      $article->withHeader($header);
    }
    // Note that there can only be one subtitle. We use the first value as the
    // subtitle.
    if ($item = $items->get(0)) {
      $header->withSubTitle($items->get(0)->value);
    }
  }

}
