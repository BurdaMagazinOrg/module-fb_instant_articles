<?php

namespace Drupal\fb_instant_articles\Normalizer;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\fb_instant_articles\AdTypes;
use Drupal\fb_instant_articles\Form\EntityViewDisplayEditForm;
use Drupal\serialization\Normalizer\NormalizerBase;
use Facebook\InstantArticles\Elements\Ad;
use Facebook\InstantArticles\Elements\Analytics;
use Facebook\InstantArticles\Elements\Author;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Time;

/**
 * Facebook Instant Articles content entity normalizer.
 *
 * Takes a content entity and normalizes it into a
 * \Facebook\InstantArticles\Elements\InstantArticle object.
 */
class InstantArticleContentEntityNormalizer extends NormalizerBase {
  use StringTranslationTrait;
  use EntityHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = 'Drupal\Core\Entity\ContentEntityInterface';

  /**
   * {@inheritdoc}
   */
  protected $format = 'fbia';

  /**
   * Entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Info parser.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Current language.
   *
   * @var \Drupal\Core\Language\LanguageInterface
   */
  protected $currentLanguage;

  /**
   * ContentEntityNormalizer constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Config factory service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   Info parser.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager interface.
   */
  public function __construct(ConfigFactoryInterface $config, EntityFieldManagerInterface $entity_field_manager, EntityTypeManagerInterface $entity_type_manager, InfoParserInterface $info_parser, ModuleHandlerInterface $module_handler, LanguageManagerInterface $language_manager) {
    $this->config = $config->get('fb_instant_articles.settings');
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->infoParser = $info_parser;
    $this->moduleHandler = $module_handler;
    $this->currentLanguage = $language_manager->getCurrentLanguage();
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($data, $format = NULL, array $context = []) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $data */
    if (isset($context['instant_article'])) {
      $article = $context['instant_article'];
    }
    else {
      $article = InstantArticle::create()
        ->addMetaProperty('op:generator:application', 'drupal/fb_instant_articles')
        ->addMetaProperty('op:generator:application:version', $this->getApplicationVersion());
      // RTL support.
      if ($this->currentLanguage->getDirection() === LanguageInterface::DIRECTION_RTL) {
        $article->enableRTL();
      }
      // Configured style.
      if ($style = $this->config->get('style')) {
        $article->withStyle($style);
      }
      $this->normalizeCanonicalUrl($article, $data);
      $this->normalizeDefaultHeader($article, $data);
      $this->analyticsFromSettings($article);
      $this->adsFromSettings($article);
      $context += [
        'instant_article' => $article,
      ];
    }

    // If we're given an entity_view_display object as context, use that as a
    // mapping to guide the normalization.
    if ($display = $this->entityViewDisplay($data, $context)) {
      // Declare a dependency on the view mode configuration if we are rendering
      // in the context of a views REST export.
      if (isset($context['views_style_plugin'])) {
        $context['views_style_plugin']->displayHandler->display['cache_metadata']['tags'] = Cache::mergeTags($context['views_style_plugin']->displayHandler->display['cache_metadata']['tags'], $display->getCacheTags());
      }
      $context['entity_view_display'] = $display;
      $components = $this->getApplicableComponents($display);
      uasort($components, [$this, 'sortComponents']);
      foreach ($components as $name => $options) {
        $this->serializer->normalize($data->get($name), $format, $context);
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
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function entityViewDisplay(ContentEntityInterface $entity, array $context) {
    $fbia_display_id = $entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . EntityViewDisplayEditForm::FBIA_VIEW_MODE;
    $default_display_id = $entity->getEntityTypeId() . '.' . $entity->bundle() . '.default';
    $storage = $this->entityTypeManager->getStorage('entity_view_display');

    /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $display */
    // If there is a display passed via $context, use that one.
    if (isset($context['entity_view_display'])) {
      return $context['entity_view_display'];
    }
    elseif (isset($context['view_mode']) &&
      ($display = $storage->load($entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $context['view_mode']))) {
      return $display;
    }
    // Try loading the fb_instant_articles entity view display.
    elseif (($display = $storage->load($fbia_display_id)) && $display->status()) {
      return $display;
    }
    // Try loading the default entity view display.
    elseif ($display = $storage->load($default_display_id)) {
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
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function normalizeCanonicalUrl(InstantArticle $article, ContentEntityInterface $entity) {
    // Set the canonical URL.
    $article->withCanonicalURL($this->entityCanonicalUrl($entity));
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
    if ($analytics_embed_code = $this->config->get('analytics.embed_code')) {
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

  /**
   * Sorts a structured array by region then by weight elements.
   *
   * @param array $a
   *   First item for comparison. The compared items should be associative
   *   arrays that include a 'region' element and optionally include a 'weight'
   *   element. For items without a 'weight' element, a default value of 0 will
   *   be used.
   * @param array $b
   *   Second item for comparison.
   *
   * @return int
   *   The comparison result for uasort().
   */
  public static function sortComponents(array $a, array $b) {
    $regions = [
      'header' => 0,
      'content' => 1,
      'footer' => 2,
    ];
    $a_region = $a['region'];
    $b_region = $b['region'];
    $a_weight = isset($a['weight']) ? $a['weight'] : 0;
    $b_weight = isset($b['weight']) ? $b['weight'] : 0;

    // Element $a's region comes before element $b.
    if ($regions[$a_region] < $regions[$b_region]) {
      return -1;
    }
    // Element $a's region comes after element $b.
    elseif ($regions[$a_region] > $regions[$b_region]) {
      return 1;
    }
    // Elements are in the same region.
    else {
      if ($a_weight == $b_weight) {
        return 0;
      }
      return ($a_weight < $b_weight) ? -1 : 1;
    }
  }

  /**
   * Pull out the module version from the info file.
   *
   * @return string
   *   Module version.
   */
  protected function getApplicationVersion() {
    $path = $this->moduleHandler->getModule('fb_instant_articles')->getPath();
    $info = $this->infoParser->parse($path . '/fb_instant_articles.info.yml');
    if (isset($info['version'])) {
      return $info['version'];
    }
    else {
      return '8.x-2.x-dev';
    }
  }

}
