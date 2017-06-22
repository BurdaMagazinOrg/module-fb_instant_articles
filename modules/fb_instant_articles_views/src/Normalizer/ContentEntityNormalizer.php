<?php

namespace Drupal\fb_instant_articles_views\Normalizer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\fb_instant_articles\Normalizer\ContentEntityNormalizer as BaseContentEntityNormalizer;

/**
 * Extends the content entity normalizer that ships with the base module.
 *
 * Supports the wrapping RSS scafolding for outputing an RSS feed.
 */
class ContentEntityNormalizer extends BaseContentEntityNormalizer {

  /**
   * Name of the format that this normalizer deals with.
   */
  const FORMAT = 'fbia_rss';

  /**
   * {@inheritdoc}
   */
  public function normalize($data, $format = NULL, array $context = []) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $data */
    $normalized = [
      'title' => $data->label(),
      'link' => $this->entityCanonicalUrl($data),
      'guid' => $data->uuid(),
      'content:encoded' => parent::normalize($data, $format, $context),
    ];
    // Add author if applicable.
    if ($author = $this->entityAuthor($data)) {
      $normalized['author'] = $author;
    }
    // Add created date if applicable.
    if ($created = $this->entityCreatedTime($data)) {
      $normalized['created'] = $created->format('c');
    }
    // Add changed date if applicable.
    if ($changed = $this->entityChangedTime($data)) {
      $normalized['modified'] = $changed->format('c');
    }

    return $normalized;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    // Only consider this normalizer if we are trying to normalize a content
    // entity into the 'fbia_rss' format.
    return $format === static::FORMAT && $data instanceof ContentEntityInterface;
  }

}
