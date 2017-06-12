<?php

namespace Drupal\fb_instant_articles\Normalizer;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
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

  protected $config;

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
    $this->config = $config;
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
    $article = $this->initInstantArticle($data);
    return $article;
  }

  /**
   * Initialize an Instant Article object from a given content entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity from which to initialize an Instant Article.
   *
   * @return \Facebook\InstantArticles\Elements\InstantArticle
   *   Instant article object conversion of the given entity.
   */
  protected function initInstantArticle(ContentEntityInterface $entity) {
    $article = InstantArticle::create();
    $base_settings = $this->config->get('fb_instant_articles.base_settings');

    // Set the canonical URL.
    if ($override = $base_settings->get('canonical_url_override')) {
      $article->withCanonicalURL($override . $entity->toUrl('canonical')->toString());
    }
    else {
      $article->withCanonicalURL($entity->toUrl('canonical', ['absolute' => TRUE])->toString());
    }

    // Setup an initial header.
    $header = Header::create();
    $article->withHeader($header);
    if ($label = $entity->label()) {
      $header->withTitle($label);
    }
    // Set a created date if available.
    if (isset($entity->created)) {
      $header->withPublishTime(
        Time::create(Time::PUBLISHED)
          ->withDatetime(
            \DateTime::createFromFormat('U', $entity->created->value)
          )
      );
    }
    // Set a changed date if available.
    if (isset($entity->changed)) {
      $header->withModifyTime(
        Time::create(Time::MODIFIED)
          ->withDatetime(
            \DateTime::createFromFormat('U', $entity->changed->value)
          )
      );
    }
    // Default the article author to the username.
    if (isset($entity->uid)) {
      /** @var \Drupal\user\Entity\User $author */
      if ($author = $this->entityTypeManager->getStorage('user')->load($entity->uid->target_id)) {
        $header->addAuthor(
          Author::create()
            ->withName($author->getDisplayName())
        );
      }
    }

    // Add analytics from settings.
    if ($analytics_embed_code = $base_settings->get('analytics_embed_code')) {
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

    // Add ads from settings.
    $article = $this->addAdsFromSettings($article);

    return $article;
  }

  /**
   * Add ads if configured in settings.
   *
   * @param \Facebook\InstantArticles\Elements\InstantArticle $article
   *   Instant article.
   *
   * @return \Facebook\InstantArticles\Elements\InstantArticle
   *   Modified instant article with ads setup if applicable.
   */
  protected function addAdsFromSettings(InstantArticle $article) {
    $base_settings = $this->config->get('fb_instant_articles.base_settings');
    $ads_type = $base_settings->get('ads.type');
    if (!$ads_type || $ads_type === FB_INSTANT_ARTICLES_AD_TYPE_NONE) {
      return $article;
    }
    $width = 300;
    $height = 250;
    $dimensions_match = [];
    $dimensions_raw = $base_settings->get('ads.dimensions');
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
        $an_placement_id = $base_settings->get('ads.an_placement_id');
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
        $iframe_url = $base_settings->get('ads.iframe_url');
        if ($iframe_url) {
          $ad->withSource(
            $iframe_url
          );
          $header->addAd($ad);
        }
        break;

      case FB_INSTANT_ARTICLES_AD_TYPE_EMBED_CODE:
        $embed_code = $base_settings->get('ads.embed_code');
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
