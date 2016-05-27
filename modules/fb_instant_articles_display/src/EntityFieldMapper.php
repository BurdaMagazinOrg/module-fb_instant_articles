<?php

/**
 * @file
 * Contains \Drupal\fb_instant_articles_display\EntityFieldMapper.
 */

namespace Drupal\fb_instant_articles_display;

use Drupal\fb_instant_articles\TransformerExtender;
use Facebook\InstantArticles\Elements\Ad;
use Facebook\InstantArticles\Elements\Analytics;
use Facebook\InstantArticles\Elements\Author;
use Facebook\InstantArticles\Elements\Blockquote;
use Facebook\InstantArticles\Elements\Caption;
use Facebook\InstantArticles\Elements\Element;
use Facebook\InstantArticles\Elements\Footer;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\Image;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Interactive;
use Facebook\InstantArticles\Elements\ListElement;
use Facebook\InstantArticles\Elements\ListItem;
use Facebook\InstantArticles\Elements\Pullquote;
use Facebook\InstantArticles\Elements\SocialEmbed;
use Facebook\InstantArticles\Elements\TextContainer;
use Facebook\InstantArticles\Elements\Video;

/**
 * Class EntityFieldMapper
 * @package Drupal\fb_instant_articles_display
 */
class EntityFieldMapper {

  /**
   * Layout settings which map fields to the Facebook Instant Article region
   * (header, footer, body).
   *
   * @var \stdClass
   */
  private $layoutSettings;

  /**
   * Facebook Instant Articles object that encapsulates the structure and
   * content of the Entity object we are mapping.
   *
   * @var InstantArticle
   */
  private $instantArticle;

  /**
   * EntityFieldMapper constructor.
   *
   * @param \stdClass $layoutSettings
   * @param InstantArticle $instantArticle
   * @return EntityFieldMapper
   */
  private function __construct(\stdClass $layoutSettings, InstantArticle $instantArticle) {
    $this->layoutSettings = $layoutSettings;
    $this->instantArticle = $instantArticle;
  }

  /**
   * @param \stdClass $layoutSettings
   * @param InstantArticle $instantArticle
   * @return EntityFieldMapper
   */
  public static function create(\stdClass $layoutSettings, InstantArticle $instantArticle) {
    return new EntityFieldMapper($layoutSettings, $instantArticle);
  }

  /**
   * Builds up an InstantArticle object using field formatters.
   *
   * @param array $field
   * @param array $instance
   * @param string $langcode
   * @param array $items
   * @param array $display
   *
   * @see fb_instant_articles_display_field_formatter_view()
   * @see fb_instant_articles_display_declare_entity_preprocess_hooks()
   */
  public function map($field, $instance, $langcode, $items, $display) {
    $settings = $display['settings'];
    $active_region = 'none';
    $header = $this->instantArticle->getHeader();
    $footer = $this->instantArticle->getFooter();

    // Determine which region this field belongs to.
    $regions = $this->layoutSettings->settings['regions'];
    foreach ($regions as $region => $fields) {
      if (in_array($field['field_name'], $fields)) {
        $active_region = $region;
        break;
      }
    }

    // We might not have added a footer. If the active region is a footer,
    // ensure it exists before continuing.
    if ($active_region === 'footer' && empty($footer)) {
      $footer = Footer::create();
      $this->instantArticle->withFooter($footer);
    }

    // For each FBIA formatter, place according to the set region, only if it
    // actually makes sense.  ie you can't put a Kicker into the footer.
    switch ($display['type']) {
      case 'fbia_subtitle_formatter':
        if ($active_region === 'header') {
          $this->fieldFormatSubtitle($items, $header);
        }
        break;
      case 'fbia_kicker_formatter':
        if ($active_region === 'header') {
          $this->fieldFormatKicker($items, $header);
        }
        break;
      case 'fbia_author_formatter':
        if ($active_region === 'header') {
          $this->fieldFormatAuthor($items, $header);
        }
        break;
      case 'fbia_ad_formatter':
        if ($active_region === 'header') {
          $this->fieldFormatAdElement($items, $header, $settings);
        }
        break;
      case 'fbia_image_formatter':
        // Images are only allowed in the header and body.
        $pass_region = null;
        if ($active_region === 'header') {
          $pass_region = $header;
        }
        elseif ($active_region === 'body') {
          $pass_region = $this->instantArticle;
        }
        if ($pass_region) {
          $this->fieldFormatImageElement($items, $pass_region, $settings);
        }
        break;
      case 'fbia_video_formatter':
        $pass_region = null;
        if ($active_region === 'header') {
          $pass_region = $header;
        }
        else if ($active_region === 'body') {
          $pass_region = $this->instantArticle;
        }
        if ($pass_region) {
          $this->fieldFormatVideoElement($items, $pass_region, $settings);
        }
        break;
      case 'fbia_blockquote_formatter':
        if ($active_region === 'body') {
          $this->fieldFormatTextContainer($items, $this->instantArticle, Blockquote::create());
        }
        break;
      case 'fbia_pullquote_formatter':
        if ($active_region === 'body') {
          $this->fieldFormatTextContainer($items, $this->instantArticle, PullQuote::create());
        }
        break;
      case 'fbia_analytics_formatter':
        if ($active_region === 'body') {
          $this->fieldFormatAnalyticsElement($items, $this->instantArticle, $settings);
        }
        break;
      case 'fbia_interactive_formatter':
        if ($active_region === 'body') {
          $this->fieldFormatInteractiveElement($items, $this->instantArticle, $settings);
        }
        break;
      case 'fbia_list_formatter':
        if ($active_region === 'body') {
          $this->fieldFormatListElement($items, $this->instantArticle, $settings);
        }
        break;
      case 'fbia_social_formatter':
        if ($active_region === 'body') {
          $this->fieldFormatSocialElement($items, $this->instantArticle);
        }
        break;
      case 'fbia_credits_formatter':
        if ($active_region === 'footer') {
          $this->fieldFormatCredits($items, $footer);
        }
        break;
      case 'fbia_copyright_formatter':
        if ($active_region === 'footer') {
          $this->fieldFormatCopyright($items, $footer);
        }
        break;
      case 'fbia_transformer_formatter':
        if ($active_region === 'body') {
          $this->fieldFormatTransfomer($items, $this->instantArticle, $instance, $langcode);
        }
        break;
    }
  }

  /**
   * Formatter for the Subtitle element.
   *
   * @param array $items
   * @param Header $header
   */
  private function fieldFormatSubtitle($items, Header $header) {
    // We can only have a single subtitle, so just take the first delta to
    // be the subtitle.
    // @todo do we have to sanitize first, or will FB IA SDK take care of it?
    if (!empty($items[0]['value'])) {
      $header->withSubTitle(strip_tags($items[0]['value']));
    }
  }

  /**
   * Formatter for the Kicker element.
   *
   * @param array $items
   * @param Header $header
   */
  private function fieldFormatKicker($items, Header $header) {
    // We can only have a single kicker, so just take the first delta to
    // be the kicker.
    if (!empty($items[0]['value'])) {
      $header->withKicker($items[0]['value']);
    }
  }

  /**
   * Formatter for standard elements.
   *
   * @param array $items
   * @param InstantArticle $body
   * @param TextContainer $text_container
   */
  private function fieldFormatTextContainer($items, InstantArticle $body, TextContainer $text_container) {
    foreach ($items as $delta => $item) {
      // @todo sanitize text before sending off to FB IA SDK?
      // @todo how does this do with markup in $item['value']?
      $body->addChild(
        $text_container->appendText($item['value'])
      );
    }
  }

  /**
   * Formatter for authors.
   *
   * @param array $items
   * @param Header $header
   */
  private function fieldFormatAuthor($items, Header $header) {
    foreach ($items as $delta => $item) {
      // @todo sanitize text before sending off to FB IA SDK?
      $header->addAuthor(
        Author::create()
          ->withName($item['value'])
      );
    }
  }

  /**
   * Formatter for credits.
   *
   * @param array $items
   * @param Footer $footer
   */
  private function fieldFormatCredits($items, Footer $footer) {
    // We can only have a single credits group.
    // @todo sanitize text before sending off to FB IA SDK?
    if (!empty($items[0]['value'])) {
      $footer->withCredits($items[0]['value']);
    }
  }

  /**
   * Formatter for copyright.
   *
   * @param array $items
   * @param Footer $footer
   */
  private function fieldFormatCopyright($items, Footer $footer) {
    // We can only have a single copyright line.
    // @todo sanitize text before sending off to FB IA SDK?
    if (!empty($items[0]['value'])) {
      $footer->withCopyright($items[0]['value']);
    }
  }

  /**
   * Formatter for the Ad element.
   *
   * @param array $items
   * @param Header $header
   * @param array $settings
   */
  private function fieldFormatAdElement($items, Header $header, $settings) {
    foreach ($items as $delta => $item) {
      $ad = Ad::create();
      if (!empty($settings['height'])) {
        $ad->withHeight($settings['height']);
      }
      if (!empty($settings['width'])) {
        $ad->withWidth($settings['width']);
      }
      if ($settings['source'] === 'embed') {
        // @todo sanitize text before sending off to FB IA SDK?
        $ad->withHtml($item['value']);
      }
      else {
        // @todo sanitize text before sending off to FB IA SDK?
        $ad->withSource($item['value']);
      }
      $header->addAd($ad);
    }
  }

  /**
   * Formatter for the Analytics element.
   *
   * @param array $items
   * @param InstantArticle $body
   * @param array $settings
   */
  private function fieldFormatAnalyticsElement($items, InstantArticle $body, $settings) {
    foreach ($items as $delta => $item) {
      $analytics = Analytics::create();

      if ($settings['source'] === 'embed') {
        // @todo sanitize text before sending off to FB IA SDK?
        $analytics->withHTML($item['value']);
      }
      else {
        // @todo sanitize text before sending off to FB IA SDK?
        $analytics->withSource($item['value']);
      }
      $body->addChild($analytics);
    }
  }

  /**
   * Formatter for the Image element.
   *
   * @param array $items
   * @param Element $region
   * @param array $settings
   */
  private function fieldFormatImageElement($items, Element $region, $settings) {
    foreach ($items as $delta => $item) {
      if (!empty($settings['style'])) {
        if (empty($item['uri']) && !empty($item['fid'])) {
          // Ensure images work, without requiring a full entity load.
          $item['uri'] = file_load($item['fid'])->uri;
        }
        $image_url = image_style_url($settings['style'], $item['uri']);
      }
      else {
        $image_url = file_create_url($item['uri']);
      }
      $image = Image::create()
        ->withURL($image_url);

      if (!empty($settings['caption']) && !empty($item['alt'])) {
        $image->withCaption(
          Caption::create()
            ->appendText($item['alt'])
        );
      }

      if (!empty($settings['likes'])) {
        $image->enableLike();
      }
      if (!empty($settings['comments'])) {
        $image->enableComments();
      }
      if (!empty($settings['fullscreen'])) {
        // @todo support other presentations.
        $image->withPresentation(Image::FULLSCREEN);
      }

      if ($region instanceof Header) {
        $region->withCover($image);
        // Header can only have one image, break after the first.
        break;
      }
      else if ($region instanceof InstantArticle) {
        $region->addChild($image);
      }
    }
  }

  /**
   * Formatter for any markup field that must needs be piped through the
   * Transfomer object.
   *
   * @param array $items
   * @param InstantArticle $body
   * @param array $instance
   * @param array $langcode
   */
  private function fieldFormatTransfomer($items, InstantArticle $body, $instance, $langcode) {
    $transformer = new TransformerExtender();
    foreach($items as $delta => $item) {
      // @see _text_sanitize().
      if (isset($item['safe_value'])) {
        $output = $item['safe_value'];
      }
      else {
        $output = $instance['settings']['text_processing'] ? check_markup($item['value'], $item['format'], $langcode) : check_plain($item['value']);
      }

      // Pass the markup through TransformerExtender::transform().
      $document = new \DOMDocument();
      // Before loading into DOMDocument, setup for success by taking care of
      // encoding issues.  Since we're dealing with HTML snippets, it will
      // always be missing a <meta charset="utf-8" /> or equivalent.
      $output = '<!doctype html><html><head><meta charset="utf-8" /></head><body>' . $output . '</body></html>';
      @$document->loadHTML(decode_entities($output));
      $transformer->transform($body, $document);

      // @todo store entity warnings so we can display them in the admin
    }
  }

  /**
   * Formatter for the Interactive element.
   *
   * @param array $items
   * @param InstantArticle $body
   * @param array $settings
   */
  private function fieldFormatInteractiveElement($items, InstantArticle $body, $settings) {
    foreach ($items as $delta => $item) {
      $interactive = Interactive::create();

      if (!empty($settings['height'])) {
        $interactive->withHeight($settings['height']);
      }
      if (!empty($settings['width'])) {
        $interactive->withWidth($settings['width']);
      }

      // @todo sanitize text before sending off to FB IA SDK?
      $interactive->withHTML($item['value']);
      $body->addChild($interactive);
    }
  }

  /**
   * Formatter for the List element.
   *
   * @param array $items
   * @param InstantArticle $body
   * @param array $settings
   */
  private function fieldFormatListElement($items, InstantArticle $body, $settings) {
    $list_type = !empty($settings['list_type']) ? $settings['list_type'] : 'ol';
    if ($list_type == 'ol') {
      $list_element = ListElement::createOrdered();
    }
    else {
      $list_element = ListElement::createUnordered();
    }

    foreach ($items as $delta => $item) {
      // @todo sanitize text before sending off to FB IA SDK?
      $list_item = ListItem::create()
        ->appendText($item['value']);
      $list_element->addItem($list_item);
    }
    $body->addChild($list_element);
  }

  /**
   * Formatter for the Video element.
   *
   * @param array $items
   * @param Element $region
   * @param array $settings
   */
  private function fieldFormatVideoElement($items, Element $region, $settings) {
    foreach ($items as $delta => $item) {
      $video = Video::create()
        ->withURL(file_create_url($item['uri']));

      if ($region instanceof Header) {
        $region->withCover($video);
        // If the video is for the cover, there can be only one.
        break;
      }
      else if ($region instanceof InstantArticle) {
        $region->addChild($video);
      }
    }
  }

  /**
   * Formatter for the Social element.
   *
   * @param array $items
   * @param InstantArticle $body
   */
  private function fieldFormatSocialElement($items, InstantArticle $body) {
    foreach ($items as $delta => $item) {
      // @todo sanitize text before sending off to FB IA SDK?
      $social = SocialEmbed::create()
        ->withHTML($item['value']);
      $body->addChild($social);
    }
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
