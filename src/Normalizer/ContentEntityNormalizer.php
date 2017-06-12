<?php

namespace Drupal\fb_instant_articles\Normalizer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Facebook Instant Articles content entity normalizer.
 *
 * Takes a content entity and normalizes it into a
 * \Facebook\InstantArticles\Elements\InstantArticle object.
 */
class ContentEntityNormalizer extends SerializerAwareNormalizer implements NormalizerInterface {
  use StringTranslationTrait;

  const FORMAT = 'fbia';

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    // Only consider this normalizer if we are trying to normalize a content
    // entity into the 'fbia' format.
    return $format === static::FORMAT && $data instanceof ContentEntityInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($data, $format = NULL, array $context = []) {

  }

}
