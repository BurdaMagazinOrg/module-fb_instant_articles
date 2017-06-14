<?php

namespace Drupal\fb_instant_articles\Normalizer;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;
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
    /** @var \Drupal\Core\Field\FieldItemInterface $object */
    if (!isset($context['instant_article'])) {
      return;
    }
    /** @var \Facebook\InstantArticles\Elements\InstantArticle $article */
    $article = $context['instant_article'];

    // If we're given an entity_view_display object as context, use that as a
    // mapping to guide the normalization.
    if (isset($context['entity_view_display'])) {
      /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $display */
      $display = $context['entity_view_display'];
      $renderer = $display->getRenderer($object->getName());
      if ($renderer instanceof InstantArticleFormatterInterface) {
        $component = $display->getComponent($object->getName());
        $renderer->viewInstantArticle($object, $article, $component['region']);
      }
    }
    // @todo take a crack at doing the conversion without a mapping?
  }

}
