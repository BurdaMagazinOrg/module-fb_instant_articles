<?php

namespace Drupal\fb_instant_articles\Normalizer;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Extends the content entity normalizer that ships with the base module.
 *
 * Supports the wrapping RSS scaffolding for outputting an RSS feed.
 */
class InstantArticleRssContentEntityNormalizer extends SerializerAwareNormalizer implements NormalizerInterface {
  use EntityHelperTrait;

  /**
   * Name of the format that this normalizer deals with.
   */
  const FORMAT = 'fbia_rss';

  /**
   * ContentEntityNormalizer constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Config factory service.
   */
  public function __construct(ConfigFactoryInterface $config) {
    $this->config = $config->get('fb_instant_articles.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($data, $format = NULL, array $context = []) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $data */
    $normalized = [
      'title' => $data->label(),
      'link' => $this->entityCanonicalUrl($data),
      'guid' => $data->uuid(),
      'content:encoded' => $this->serializer->normalize($data, 'fbia', $context),
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
