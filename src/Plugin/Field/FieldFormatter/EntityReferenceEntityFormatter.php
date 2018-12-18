<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;
use Facebook\InstantArticles\Elements\InstantArticle;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Instant articles field formatter for entity reference fields.
 *
 * Like its render array cousin,
 * \Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter
 * this will iterate over any entity references and render them each in turn,
 * only here the serializer is used to do the work.
 *
 * @FieldFormatter(
 *   id = "fbia_entity_reference_entity_view",
 *   label = @Translation("FBIA rendered entity"),
 *   description = @Translation("Append referenced entities to the instant article."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReferenceEntityFormatter extends EntityReferenceFormatterBase implements InstantArticleFormatterInterface {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Do nothing. Our field formatters are unique in that they are not meant
    // to generate HTML on their own, but only signal to the Serialization API
    // the fate of this field in the FBIA document.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, NormalizerInterface $normalizer, $langcode = NULL) {
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $items */
    $view_mode = $this->getSetting('view_mode');

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      $normalizer->normalize($entity, 'fbia', [
        'instant_article' => $article,
        'view_mode' => $view_mode,
      ]);
    }
  }

}
