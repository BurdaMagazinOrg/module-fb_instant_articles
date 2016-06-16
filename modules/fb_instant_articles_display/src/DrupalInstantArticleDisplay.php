<?php

/**
 * @file
 * Contains \Drupal\fb_instant_articles_display\DrupalInstantArticleDisplay.
 */

namespace Drupal\fb_instant_articles_display;

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
use Facebook\InstantArticles\Elements\Time;
use Facebook\InstantArticles\Elements\Video;
use Facebook\InstantArticles\Transformer\Transformer;

/**
 * Facebook Instant Article node wrapper class.  Builds up an InstantArticle
 * object using field formatters.
 *
 * Class DrupalInstantArticleDisplay
 * @package Drupal\fb_instant_articles_display
 */
class DrupalInstantArticleDisplay {

  /**
   * Facebook Instant Articles version number.
   */
  const FB_INSTANT_ARTICLES_VERSION = '7.x-1.0-rc1';

  /**
   * Node object for which we are building an InstantArticle object.
   *
   * @var \stdClass
   */
  private $node;

  /**
   * Layout settings which map fields to the Facebook Instant Article region
   * (header, footer, body).
   *
   * @var array
   */
  private $layoutSettings;

  /**
   * Facebook Instant Articles object that encapsulates the structure and
   * content of the node object we are wrapping.
   *
   * @var \Facebook\InstantArticles\Elements\InstantArticle
   */
  private $instantArticle;

  /**
   * @param \stdClass $node
   * @param \Facebook\InstantArticles\Elements\InstantArticle $instantArticle
   */
  private function __construct($node, $layoutSettings, $instantArticle) {
    $this->node = $node;
    $this->layoutSettings = $layoutSettings;
    $this->instantArticle = $instantArticle;
  }

  /**
   * @param \stdClass $node
   * @return \Drupal\fb_instant_articles_display\DrupalInstantArticleDisplay
   */
  public static function create($node, $layoutSettings) {
    // InstantArticle object for the node.  This will be built up by any field
    // formatters and rendered out in hook_preprocess_node().
    $instantArticle = InstantArticle::create()
      ->addMetaProperty('op:generator:application', 'drupal/fb_instant_articles')
      ->addMetaProperty('op:generator:application:version', self::FB_INSTANT_ARTICLES_VERSION)
      ->withCanonicalUrl(url('node/' . $node->nid, array('absolute' => TRUE)))
      ->withStyle(variable_get('fb_instant_articles_style', 'default'));
    // InstantArticles header, at this point, only have publish an modify
    // times to add.
    $header = Header::create()
      ->withTitle($node->title)
      ->withPublishTime(
        Time::create(Time::PUBLISHED)
          ->withDatetime(
            \DateTime::createFromFormat('U', $node->created)
          )
      )
      ->withModifyTime(
        Time::create(Time::MODIFIED)
          ->withDatetime(
            \DateTime::createFromFormat('U', $node->changed)
          )
      );
    // Default the article author to the username.
    $author = user_load($node->uid);
    if ($author) {
      $header->addAuthor(
        Author::create()
          ->withName($author->name)
      );
    }
    $instantArticle->withHeader($header);

    $display = new DrupalInstantArticleDisplay($node, $layoutSettings, $instantArticle);
    $display->addAnalyticsFromSettings();
    $display->addAdsFromSettings();
    return $display;
  }

  /**
   * Gets the wrapped InstantArticle object.
   *
   * Also invokes a hook to allow other modules to alter the InstantArticle
   * object before render or any other operation.
   *
   * @see hook_fb_instant_articles_display_instant_article_alter()
   *
   * @return \Facebook\InstantArticles\Elements\InstantArticle
   */
  public function getArticle() {
    drupal_alter('fb_instant_articles_display_instant_article', $this->instantArticle, $this->node);
    return $this->instantArticle;
  }

  /**
   * @deprecated
   *
   * Instead use DrupalInstantArticleDisplay->getArticle()->render().
   */
  public function render() {
    return $this->getArticle()->render('<!doctype html>', TRUE);
  }

  /**
   * @param $entity_type
   * @param $entity
   * @param $field
   * @param $instance
   * @param $langcode
   * @param $items
   * @param $display
   */
  public function fieldFormatterView($field, $instance, $langcode, $items, $display) {
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
          $pass_region = $header;
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
   * @param $items
   * @param Header $header
   */
  private function fieldFormatSubtitle($items, $header) {
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
   * @param $region
   * @param $items
   * @param Header $header
   */
  private function fieldFormatKicker($items, $header) {
    // We can only have a single kicker, so just take the first delta to
    // be the kicker.
    if (!empty($items[0]['value'])) {
      $header->withKicker($items[0]['value']);
    }
  }

  /**
   * Formatter for standard elements.
   *
   * @param $items
   * @param InstantArticle $body
   * @param TextContainer $text_container
   */
  private function fieldFormatTextContainer($items, $body, $text_container) {
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
   * @param $items
   * @param Header $header
   */
  private function fieldFormatAuthor($items, $header) {
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
   * @param $items
   * @param Footer $footer
   */
  private function fieldFormatCredits($items, $footer) {
    // We can only have a single credits group.
    // @todo sanitize text before sending off to FB IA SDK?
    if (!empty($items[0]['value'])) {
      $footer->withCredits($items[0]['value']);
    }
  }

  /**
   * Formatter for copyright.
   *
   * @param $items
   * @param Footer $footer
   */
  private function fieldFormatCopyright($items, $footer) {
    // We can only have a single copyright line.
    // @todo sanitize text before sending off to FB IA SDK?
    if (!empty($items[0]['value'])) {
      $footer->withCopyright($items[0]['value']);
    }
  }

  /**
   * Formatter for the Ad element.
   *
   * @param $items
   * @param Header $header
   * @param $settings
   */
  private function fieldFormatAdElement($items, $header, $settings) {
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
   * @param $items
   * @param InstantArticle $body
   * @param $settings
   */
  private function fieldFormatAnalyticsElement($items, $body, $settings) {
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
   * @param $items
   * @param Element $region
   * @param $settings
   */
  private function fieldFormatImageElement($items, $region, $settings) {
    foreach ($items as $delta => $item) {
      if (!empty($settings['style'])) {
        if (empty($item['uri']) && !empty($item['fid'])) {
          // Ensure images work, without requiring a full node load.
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
   * @param $items
   * @param InstantArticle $body
   * @param $instance
   * @param $langcode
   */
  private function fieldFormatTransfomer($items, $body, $instance, $langcode) {
    $transformer = new Transformer();
    $transformer->loadRules(file_get_contents(__DIR__ . '/../transformer_config.json'));
    drupal_alter('fb_instant_articles_display_transformer', $transformer);
    foreach($items as $delta => $item) {
      // @see _text_sanitize().
      if (isset($item['safe_value'])) {
        $output = $item['safe_value'];
      }
      else {
        $output = $instance['settings']['text_processing'] ? check_markup($item['value'], $item['format'], $langcode) : check_plain($item['value']);
      }

      // Pass the markup through Transformer::transform().
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
   * @param $items
   * @param InstantArticle $body
   * @param $settings
   */
  private function fieldFormatInteractiveElement($items, $body, $settings) {
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
   * @param $items
   * @param InstantArticle $body
   * @param $settings
   */
  private function fieldFormatListElement($items, $body, $settings) {
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
   * @param $items
   * @param Element $region
   * @param $settings
   */
  private function fieldFormatVideoElement($items, $region, $settings) {
    foreach ($items as $delta => $item) {
      // A video url may already be provided for external videos files.
      $video_url = !empty($item['url']) ? $item['url'] : file_create_url($item['uri']);
      $video = Video::create()
        ->withURL($video_url);

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
   * @param $items
   * @param InstantArticle $body
   */
  private function fieldFormatSocialElement($items, $body) {
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
