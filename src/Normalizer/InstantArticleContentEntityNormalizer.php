<?php

namespace Drupal\fb_instant_articles\Normalizer;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\fb_instant_articles\AdTypes;
use Drupal\fb_instant_articles\Form\EntityViewDisplayEditForm;
use Drupal\user\EntityOwnerInterface;
use Facebook\InstantArticles\Elements\Ad;
use Facebook\InstantArticles\Elements\Analytics;
use Facebook\InstantArticles\Elements\Author;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Time;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Facebook Instant Articles content entity normalizer.
 *
 * Takes a content entity and normalizes it into a
 * \Facebook\InstantArticles\Elements\InstantArticle object.
 */
class InstantArticleContentEntityNormalizer extends SerializerAwareNormalizer implements NormalizerInterface {
  use StringTranslationTrait;

  /**
   * Name of the format that this normalizer deals with.
   */
  const FORMAT = 'fbia';

  /**
   * Instant articles config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Entity type manager serivice.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ContentEntityNormalizer constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Config factory service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(ConfigFactoryInterface $config, EntityFieldManagerInterface $entity_field_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->config = $config->get('fb_instant_articles.settings');
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

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
    /** @var \Drupal\Core\Entity\ContentEntityInterface $data */
    $article = InstantArticle::create();
    $this->normalizeCanonicalUrl($article, $data);
    $this->normalizeDefaultHeader($article, $data);
    $this->analyticsFromSettings($article);
    $this->adsFromSettings($article);

    $context += [
      'instant_article' => $article,
    ];
    // If we're given an entity_view_display object as context, use that as a
    // mapping to guide the normalization.
    if ($display = $this->entityViewDisplay($data, $context)) {
      $context['entity_view_display'] = $display;
      $components = $this->getApplicableComponents($display);
      uasort($components, [SortArray::class, 'sortByWeightElement']);
      // @todo sort by region as well header, content, body
      foreach ($components as $name => $options) {
        $this->serializer->normalize($data->get($name), $format, $context);
      }
    }
    else {
      foreach ($data as $name => $field) {
        $this->serializer->normalize($field, $format, $context);
      }
    }

    return $article;
  }

  /**
   * Helper function to get the fb_instant_articles entity view display object.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity being normalized.
   * @param array $context
   *   Context array passed to the normalize method.
   *
   * @return \Drupal\Core\Entity\Entity\EntityViewDisplay
   *   Default entity view display object with the mapping for the given entity.
   */
  protected function entityViewDisplay(ContentEntityInterface $entity, array $context) {
    $display_id = $entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . EntityViewDisplayEditForm::FBIA_VIEW_MODE;
    if (isset($context['entity_view_display'])) {
      return $context['entity_view_display'];
    }
    elseif ($display = $this->entityTypeManager->getStorage('entity_view_display')->load($display_id)) {
      return $display;
    }
  }

  /**
   * Normalize the canonical URL into the Instant Article object.
   *
   * @param \Facebook\InstantArticles\Elements\InstantArticle $article
   *   Instant article object we are normalizing to.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity being normalized.
   *
   * @return \Facebook\InstantArticles\Elements\InstantArticle
   *   Modified instant article.
   */
  public function normalizeCanonicalUrl(InstantArticle $article, ContentEntityInterface $entity) {
    // Set the canonical URL.
    $article->withCanonicalURL($this->entityCanonicalUrl($entity));
    return $article;
  }

  /**
   * Helper function to compute the canonical URL for a given entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity being normalized.
   *
   * @return string
   *   The canonical URL for the given entity.
   */
  public function entityCanonicalUrl(ContentEntityInterface $entity) {
    if ($override = $this->config->get('canonical_url_override')) {
      return $override . $entity->toUrl('canonical')->toString();
    }
    else {
      return $entity->toUrl('canonical', ['absolute' => TRUE])->toString();
    }
  }

  /**
   * Normalize the default header of the instant article.
   *
   * Use known properties of the content entity to normalize default properties
   * of the instant article header.
   *
   * @param \Facebook\InstantArticles\Elements\InstantArticle $article
   *   Instant article object we are normalizing to.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity being normalized.
   *
   * @return \Facebook\InstantArticles\Elements\InstantArticle
   *   Modified instant article.
   */
  public function normalizeDefaultHeader(InstantArticle $article, ContentEntityInterface $entity) {
    $header = $article->getHeader();
    if (!$header) {
      $header = Header::create();
      $article->withHeader($header);
    }
    if ($label = $entity->label()) {
      $header->withTitle($label);
    }
    // Set a created date if available.
    if ($created = $this->entityCreatedTime($entity)) {
      $header->withPublishTime(
        Time::create(Time::PUBLISHED)
          ->withDatetime(
            $created
          )
      );
    }
    // Set a changed date if available.
    if ($changed = $this->entityChangedTime($entity)) {
      $header->withModifyTime(
        Time::create(Time::MODIFIED)
          ->withDatetime(
            $changed
          )
      );
    }
    // Default the article author to the username.
    if ($author = $this->entityAuthor($entity)) {
      $header->addAuthor(
        Author::create()
          ->withName($author)
      );
    }
    return $article;
  }

  /**
   * Helper function to get the created time of the given entity if applicable.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity being normalized.
   *
   * @return bool|\DateTime
   *   Created time of the given entity.
   */
  protected function entityCreatedTime(ContentEntityInterface $entity) {
    if ($created = $entity->get('created')) {
      return \DateTime::createFromFormat('U', $created->value);
    }
  }

  /**
   * Helper function to get the changed time of the given entity if applicable.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity being normalized.
   *
   * @return bool|\DateTime
   *   Changed time of the given entity.
   */
  protected function entityChangedTime(ContentEntityInterface $entity) {
    if ($entity instanceof EntityChangedInterface && ($changed = $entity->getChangedTime())) {
      return \DateTime::createFromFormat('U', $changed);
    }
  }

  /**
   * Helper function to pull the author name out of an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity being normalized.
   *
   * @return string|null
   *   Author name if there is one.
   */
  protected function entityAuthor(ContentEntityInterface $entity) {
    // Default the article author to the username.
    if ($entity instanceof EntityOwnerInterface && ($owner = $entity->getOwner())) {
      return $owner->getDisplayName();
    }
  }

  /**
   * Add analytics settings if any to the instant article normalize result.
   *
   * @param \Facebook\InstantArticles\Elements\InstantArticle $article
   *   Instant article object we are normalizing to.
   *
   * @return \Facebook\InstantArticles\Elements\InstantArticle
   *   Modified instant article.
   */
  public function analyticsFromSettings(InstantArticle $article) {
    // Add analytics from settings.
    if ($analytics_embed_code = $this->config->get('analytics_embed_code')) {
      $document = new \DOMDocument();
      $fragment = $document->createDocumentFragment();
      $valid_html = @$fragment->appendXML($analytics_embed_code);
      if ($valid_html) {
        $article
          ->addChild(
            Analytics::create()
              ->withHTML(
                $fragment
              )
          );
      }
    }
    return $article;
  }

  /**
   * Add ads if configured in settings to the instant article normalize result.
   *
   * @param \Facebook\InstantArticles\Elements\InstantArticle $article
   *   Instant article object we are normalizing to.
   *
   * @return \Facebook\InstantArticles\Elements\InstantArticle
   *   Modified instant article.
   */
  protected function adsFromSettings(InstantArticle $article) {
    $ads_type = $this->config->get('ads.type');
    if (!$ads_type || $ads_type === AdTypes::AD_TYPE_NONE) {
      return $article;
    }
    $width = 300;
    $height = 250;
    $dimensions_match = [];
    $dimensions_raw = $this->config->get('ads.dimensions');
    if (preg_match('/^(?:\s)*(\d+)x(\d+)(?:\s)*$/', $dimensions_raw, $dimensions_match)) {
      $width = intval($dimensions_match[1]);
      $height = intval($dimensions_match[2]);
    }
    $ad = Ad::create()
      ->enableDefaultForReuse()
      ->withWidth($width)
      ->withHeight($height);
    $header = $article->getHeader();
    if (!$header) {
      $header = Header::create();
      $article->withHeader($header);
    }

    switch ($ads_type) {
      case AdTypes::AD_TYPE_FBAN:
        $an_placement_id = $this->config->get('ads.an_placement_id');
        if ($an_placement_id) {
          $ad->withSource(
            Url::fromUri('https://www.facebook.com/adnw_request', [
              'query' => [
                'placement' => $an_placement_id,
                'adtype' => 'banner' . $width . 'x' . $height,
              ],
            ])->toString()
          );
          $header->addAd($ad);
        }
        break;

      case AdTypes::AD_TYPE_SOURCE_URL:
        $iframe_url = $this->config->get('ads.iframe_url');
        if ($iframe_url) {
          $ad->withSource(
            $iframe_url
          );
          $header->addAd($ad);
        }
        break;

      case AdTypes::AD_TYPE_EMBED_CODE:
        $embed_code = $this->config->get('ads.embed_code');
        if ($embed_code) {
          $document = new \DOMDocument();
          $fragment = $document->createDocumentFragment();
          $valid_html = @$fragment->appendXML($embed_code);
          if ($valid_html) {
            $ad->withHTML(
              $fragment
            );
            $header->addAd($ad);
          }
        }
        break;
    }
    $article->enableAutomaticAdPlacement();

    return $article;
  }

  /**
   * Helper function to get relevant components from an entity view display.
   *
   * This feels like it should be more straight forward, but alas. The
   * EntityViewDispaly object has a method getComponents(), which returns
   * display options for all fields. We're only interested in those which are
   * configurable, marked as visible, and not extra fields.
   *
   * @param \Drupal\Core\Entity\Entity\EntityViewDisplay $display
   *   Entity view display config entity.
   *
   * @return array
   *   Components that should be included in the Facebook Instant Article.
   *
   * @see \Drupal\field_layout\FieldLayoutBuilder::getFields()
   */
  protected function getApplicableComponents(EntityViewDisplay $display) {
    $components = $display->getComponents();

    // Get a list of all fields for the given entity view display.
    $field_definitions = $this->entityFieldManager->getFieldDefinitions($display->getTargetEntityTypeId(), $display->getTargetBundle());

    // Exclude any fields which have a non-configurable display.
    $fields_to_exclude = array_filter($field_definitions, function (FieldDefinitionInterface $field_definition) {
      return !$field_definition->isDisplayConfigurable('view');
    });

    // Ignore any extra fields from the list of field definitions. Field
    // definitions can have a non-configurable display, but all extra fields are
    // always displayed. We may want to re-visit including extra fields in the
    // future.
    $extra_fields = $this->entityFieldManager->getExtraFields($display->getTargetEntityTypeId(), $display->getTargetBundle());
    $extra_fields = isset($extra_fields['display']) ? $extra_fields['display'] : [];

    return array_diff_key($components, $fields_to_exclude, $extra_fields);
  }

}
