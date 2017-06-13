<?php

namespace Drupal\fb_instant_articles\Normalizer;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
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
class ContentEntityNormalizer extends SerializerAwareNormalizer implements NormalizerInterface {
  use StringTranslationTrait;

  const FORMAT = 'fbia';

  protected $baseSettings;

  protected $entityTypeManager;

  /**
   * ContentEntityNormalizer constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(ConfigFactoryInterface $config, EntityTypeManagerInterface $entity_type_manager) {
    $this->baseSettings = $config->get('fb_instant_articles.base_settings');
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
    return $article;
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
    if ($override = $this->baseSettings->get('canonical_url_override')) {
      $article->withCanonicalURL($override . $entity->toUrl('canonical')->toString());
    }
    else {
      $article->withCanonicalURL($entity->toUrl('canonical', ['absolute' => TRUE])->toString());
    }
    return $article;
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
    }
    if ($label = $entity->label()) {
      $header->withTitle($label);
    }
    // Set a created date if available.
    if ($created = $entity->get('created')) {
      $header->withPublishTime(
        Time::create(Time::PUBLISHED)
          ->withDatetime(
            \DateTime::createFromFormat('U', $created->value)
          )
      );
    }
    // Set a changed date if available.
    if ($entity instanceof EntityChangedInterface && ($changed = $entity->getChangedTime())) {
      $header->withModifyTime(
        Time::create(Time::MODIFIED)
          ->withDatetime(
            \DateTime::createFromFormat('U', $changed)
          )
      );
    }
    // Default the article author to the username.
    if ($entity instanceof EntityOwnerInterface && ($owner = $entity->getOwner())) {
      $header->addAuthor(
        Author::create()
          ->withName($owner->getDisplayName())
      );
    }
    return $article;
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
    if ($analytics_embed_code = $this->baseSettings->get('analytics_embed_code')) {
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
    $ads_type = $this->baseSettings->get('ads.type');
    if (!$ads_type || $ads_type === FB_INSTANT_ARTICLES_AD_TYPE_NONE) {
      return $article;
    }
    $width = 300;
    $height = 250;
    $dimensions_match = [];
    $dimensions_raw = $this->baseSettings->get('ads.dimensions');
    if (preg_match('/^(?:\s)*(\d+)x(\d+)(?:\s)*$/', $dimensions_raw, $dimensions_match)) {
      $width = intval($dimensions_match[1]);
      $height = intval($dimensions_match[2]);
    }
    $ad = Ad::create()
      ->enableDefaultForReuse()
      ->withWidth($width)
      ->withHeight($height);
    $header = $article->getHeader();

    switch ($ads_type) {
      case FB_INSTANT_ARTICLES_AD_TYPE_FBAN:
        $an_placement_id = $this->baseSettings->get('ads.an_placement_id');
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

      case FB_INSTANT_ARTICLES_AD_TYPE_SOURCE_URL:
        $iframe_url = $this->baseSettings->get('ads.iframe_url');
        if ($iframe_url) {
          $ad->withSource(
            $iframe_url
          );
          $header->addAd($ad);
        }
        break;

      case FB_INSTANT_ARTICLES_AD_TYPE_EMBED_CODE:
        $embed_code = $this->baseSettings->get('ads.embed_code');
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

}
