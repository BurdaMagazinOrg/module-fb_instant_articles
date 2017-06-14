<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;

/**
 * Plugin implementation of the 'fbia_ad_link' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_ad_link",
 *   label = @Translation("FBIA Advertisement"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class AdLinkFormatter extends AdFormatter implements InstantArticleFormatterInterface {

  /**
   * {@inheritdoc}
   */
  protected function getItemValue(FieldItemInterface $item) {
    return $item->uri;
  }

}
