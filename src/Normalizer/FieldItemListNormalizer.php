<?php

namespace Drupal\fb_instant_articles\Normalizer;

use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Normalize FieldItemList object into an Instant Article object.
 */
class FieldItemListNormalizer extends SerializerAwareNormalizer implements NormalizerInterface {

  const FORMAT = 'fbia';

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    // Only consider this normalizer if we are trying to normalize a field item
    // list into the 'fbia' format.
    return $format === static::FORMAT && $data instanceof FieldItemListInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {

  }

}
