<?php
/**
 * @file
 * Formatter output functions for Facebook Instant Articles Display.
 */

use Facebook\InstantArticles\Elements\Image;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Caption;
use Facebook\InstantArticles\Elements\Element;
use Facebook\InstantArticles\Elements\TextContainer;
use Facebook\InstantArticles\Elements\Author;
use Facebook\InstantArticles\Elements\Footer;
use Facebook\InstantArticles\Elements\Ad;
use Facebook\InstantArticles\Elements\Analytics;

/**
 * Formatter for the Subtitle element.
 *
 * @param $items
 * @param Header $header
 */
function fb_instant_articles_display_field_format_subtitle($items, $header) {
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
function fb_instant_articles_display_field_format_kicker($items, $header) {
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
function fb_instant_articles_display_field_format_text_container($items, $body, $text_container) {
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
function fb_instant_articles_display_field_format_author($items, $header) {
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
function fb_instant_articles_display_field_format_credits($items, $footer) {
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
function fb_instant_articles_display_field_format_copyright($items, $footer) {
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
function fb_instant_articles_display_field_format_ad_element($items, $header, $settings) {
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
function fb_instant_articles_display_field_format_analytics_element($items, $body, $settings) {
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
function fb_instant_articles_display_field_format_image_element($items, $region, $settings) {
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
 * Formatter for the Interactive element.
 */
function fb_instant_articles_display_field_format_interactive_element($items, $settings) {
  $element = array();

  foreach ($items as $delta => $item) {
    $attributes = "";

    if (!empty($settings['height'])) {
      $attributes .= ' height="' . $settings['height'] . '"';
    }
    if (!empty($settings['width'])) {
      $attributes .= ' class="' . $settings['width'] . '"';
    }

    $element[$delta]['#markup'] = '<figure class="op-interactive"><iframe src="' . $item['safe_value'] . '"' . $attributes . ' /></figure>';
  }
  return $element;
}

/**
 * Formatter for the List element.
 */
function fb_instant_articles_display_field_format_list_element($items, $settings) {
  $element = array();

  if (empty($items) || count($items) == 0) {
    return $element;
  }

  $list_type = !empty($settings['list_type']) ? $settings['list_type'] : 'ol';

  $list_markup = '<' . $list_type . '>';

  foreach ($items as $delta => $item) {
    $list_markup .= '<li>' . $item['value'] . '</li>';
  }

  $list_markup .= '</' . $list_type . '>';

  $element[$delta]['#markup'] = $list_markup;
  return $element;
}

/**
 * Formatter for the Video element.
 */
function fb_instant_articles_display_field_format_video_element($items, $settings) {
  $element = array();

  foreach ($items as $delta => $item) {
    $element[$delta]['#markup'] = '<figure><video><source src="' . file_create_url($item['uri']) . '" type="' . $item['filemime'] . '" /></video></figure>';
  }
  return $element;
}

/**
 * Formatter for the Social element.
 */
function fb_instant_articles_display_field_format_social_element($items, $settings) {
  $element = array();

  foreach ($items as $delta => $item) {
    $element[$delta]['#markup'] = '<figure class="op-social"><iframe>' . $item['safe_value'] . '</iframe></figure>';
  }

  return $element;
}
