<?php

/**
 * @file
 * Contains \Drupal\fb_instant_articles_display\InstantArticle.
 */

namespace Drupal\fb_instant_articles_display;

use Drupal\fb_instant_articles\Transformer;
use Facebook\InstantArticles\Elements\Ad;
use Facebook\InstantArticles\Elements\Analytics;
use Facebook\InstantArticles\Elements\Author;
use Facebook\InstantArticles\Elements\Blockquote;
use Facebook\InstantArticles\Elements\Caption;
use Facebook\InstantArticles\Elements\Element;
use Facebook\InstantArticles\Elements\Footer;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\Image;
use Facebook\InstantArticles\Elements\Interactive;
use Facebook\InstantArticles\Elements\ListElement;
use Facebook\InstantArticles\Elements\ListItem;
use Facebook\InstantArticles\Elements\Pullquote;
use Facebook\InstantArticles\Elements\SocialEmbed;
use Facebook\InstantArticles\Elements\TextContainer;
use Facebook\InstantArticles\Elements\Time;
use Facebook\InstantArticles\Elements\Video;

/**
 * Facebook Instant Article node wrapper class.  Builds up an InstantArticle
 * object using field formatters.
 *
 * Class InstantArticle
 * @package Drupal\fb_instant_articles_display
 */
class InstantArticle extends \Drupal\fb_instant_articles\InstantArticle {

  /**
   * Facebook Instant Articles version number.
   */
  const FB_INSTANT_ARTICLES_VERSION = '0.1.0';

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
   * @var InstantArticle
   */
  private $instantArticle;

  /**
   * @param stdClass $node
   * @param $instantArticle
   */
  private function __construct($node, $layoutSettings, $instantArticle) {
    parent::__construct();
    $this->node = $node;
    $this->layoutSettings = $layoutSettings;
    $this->instantArticle = $instantArticle;
  }

  /**
   * @param stdClass $node
   * @return \Drupal\fb_instant_articles_display\InstantArticle
   */
  public static function create($node, $layoutSettings) {
    // InstantArticle object for the node.  This will be built up by any field
    // formatters and rendered out in hook_preprocess_node().
    $instantArticle = parent::create()
      ->addMetaProperty('op:generator:application', 'drupal/fb_instant_articles')
      ->addMetaProperty('op:generator:application:version', self::FB_INSTANT_ARTICLES_VERSION)
      ->withCanonicalUrl(url('node/' . $node->nid, array('absolute' => TRUE)))
      // @todo where best to store this for alter?
      ->withStyle('default');
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
    // Default the article autor to the username.
    $author = user_load($node->uid);
    if ($author) {
      $header->addAuthor(
        Author::create()
          ->withName($author->name)
      );
    }
    $instantArticle->withHeader($header);

    return new InstantArticle($node, $layoutSettings, $instantArticle);
  }

  /**
   * @return InstantArticle
   */
  public function render() {
    // Give anyone a chance to alter the InstantArticle object prior to
    // rendering.
    drupal_alter('fb_instant_articles_display_instant_article', $this->instantArticle, $this->node);
    return $this->instantArticle->render('<!doctype html>', TRUE);
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
    $header->withSubTitle(strip_tags($items[0]['value']));
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
    $header->withKicker($items[0]['value']);
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
    $footer->withCredits($items[0]['value']);
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
    $footer->withCopyright($items[0]['value']);
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
      $image_url = image_style_url($settings['style'], $item['uri']);
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
      $output = '<!doctype html><html><head><meta charset="utf-8" /></head><body>' . $output . '</body>';
      @$document->loadHTML($output);
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
}
