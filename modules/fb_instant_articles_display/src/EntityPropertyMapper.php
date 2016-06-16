<?php

/**
 * @file
 * Contains \Drupal\fb_instant_articles_display\EntityPropertyMapper.
 */

namespace Drupal\fb_instant_articles_display;

use Facebook\InstantArticles\Elements\Ad;
use Facebook\InstantArticles\Elements\Analytics;
use Facebook\InstantArticles\Elements\Author;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Time;

/**
 * Class EntityPropertyMapper
 * @package Drupal\fb_instant_articles_display
 */
class EntityPropertyMapper {

  /**
   * Entity object for which we are building an InstantArticle object.
   *
   * @var \stdClass
   */
  private $entity;
  /**
   * The Entity type.
   *
   * We store this because there is no core $entity->entity_type property.
   * See @link https://www.drupal.org/node/1042822 this d.o issue for status. @endlink
   *
   * @see $entity
   *
   * @var string
   */
  private $entity_type;

  /**
   * Facebook Instant Articles object that encapsulates the structure and
   * content of the Entity object we are wrapping.
   *
   * @var InstantArticle
   */
  private $instantArticle;

  /**
   * EntityPropertyMapper constructor.
   *
   * @param string $entity_type
   * @param \stdClass|mixed $entity
   * @param \Facebook\InstantArticles\Elements\InstantArticle $instantArticle
   */
  private function __construct($entity_type, $entity, InstantArticle $instantArticle) {
    $this->entity_type = $entity_type;
    $this->entity = $entity;
    $this->instantArticle = $instantArticle;
  }

  /**
   * @param string $entity_type
   * @param \stdClass $entity
   * @param InstantArticle $instantArticle
   * @return EntityPropertyMapper
   */
  public static function create($entity_type, $entity, InstantArticle $instantArticle) {
    return new EntityPropertyMapper($entity_type, $entity, $instantArticle);
  }

  /**
   * Maps Drupal Entity properties to a FB Instant Article SDK InstantArticle.
   */
  public function map() {
    $this->addCanonicalURL();
    $this->addHeaderFromProperties();
    $this->addAnalyticsFromSettings();
    $this->addAdsFromSettings();
  }

  private function addCanonicalURL() {
    $canonical_override = variable_get('fb_instant_articles_canonical_url_override', '');
    $path = entity_uri($this->entity_type, $this->entity);
    if (empty($canonical_override)) {
      $canonical_url = url($path['path'], array('absolute' => TRUE));
    }
    else {
      $canonical_url = $canonical_override . url($path['path']);
    }
    $this->instantArticle->withCanonicalUrl($canonical_url);
  }

  private function addHeaderFromProperties() {
    $header = Header::create();
    if ($label = entity_label($this->entity_type, $this->entity)) {
      $header->withTitle($label);
    }

    // Support specific Drupal Entity keys to map to the Instant SDK PUBLISHED
    // and MODIFIED concepts.
    if (isset($this->entity->created)) {
      $header->withPublishTime(
        Time::create(Time::PUBLISHED)
          ->withDatetime(
            \DateTime::createFromFormat('U', $this->entity->created)
          )
      );
    }
    if (isset($this->entity->changed)) {
      $header->withModifyTime(
        Time::create(Time::MODIFIED)
          ->withDatetime(
            \DateTime::createFromFormat('U', $this->entity->changed)
          )
      );
    }

    // Default the article author to the username.
    if (isset($this->entity->uid)) {
      $author = user_load($this->entity->uid);
      if ($author) {
        $header->addAuthor(
          Author::create()
            ->withName($author->name)
        );
      }
    }

    $this->instantArticle->withHeader($header);
  }

  /**
   * Add analytics tracking code if configured in settings
   */
  private function addAnalyticsFromSettings() {
    $analytics_embed_code = variable_get('fb_instant_articles_analytics_embed_code');
    if ($analytics_embed_code) {
      $document = new \DOMDocument();
      $fragment = $document->createDocumentFragment();
      $valid_html = @$fragment->appendXML($analytics_embed_code);
      if ($valid_html) {
        $this->instantArticle
          ->addChild(
            Analytics::create()
              ->withHTML(
                $fragment
              )
          );
      }
    }
  }

  /**
   * Add ads if configured in settings
   */
  public function addAdsFromSettings() {
    $ad_type = variable_get('fb_instant_articles_ad_type', FB_INSTANT_ARTICLES_AD_TYPE_NONE);
    if ($ad_type === FB_INSTANT_ARTICLES_AD_TYPE_NONE) {
      return;
    }
    $width = 300;
    $height = 250;
    $dimensions_match = array();
    $dimensions_raw = variable_get('fb_instant_articles_ads_dimensions');
    if (preg_match('/^(?:\s)*(\d+)x(\d+)(?:\s)*$/', $dimensions_raw, $dimensions_match)) {
      $width = intval($dimensions_match[1]);
      $height = intval($dimensions_match[2]);
    }
    $ad = Ad::create()
      ->enableDefaultForReuse()
      ->withWidth($width)
      ->withHeight($height);
    $header = $this->instantArticle->getHeader();
    switch ($ad_type) {
      case FB_INSTANT_ARTICLES_AD_TYPE_FBAN:
        $an_placement_id = variable_get('fb_instant_articles_ads_an_placement_id');
        if ($an_placement_id) {
          $ad->withSource(
            url('https://www.facebook.com/adnw_request', array(
              'query' => array(
                'placement' => $an_placement_id,
                'adtype' => 'banner' . $width . 'x' . $height,
              ),
            ))
          );
          $header->addAd($ad);
        }
        break;
      case FB_INSTANT_ARTICLES_AD_TYPE_SOURCE_URL:
        $iframe_url = variable_get('fb_instant_articles_ads_iframe_url');
        if ($iframe_url) {
          $ad->withSource(
            $iframe_url
          );
          $header->addAd($ad);
        }
        break;
      case FB_INSTANT_ARTICLES_AD_TYPE_EMBED_CODE:
        $embed_code = variable_get('fb_instant_articles_ads_embed_code');
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
    $this->instantArticle->enableAutomaticAdPlacement();
  }
}
