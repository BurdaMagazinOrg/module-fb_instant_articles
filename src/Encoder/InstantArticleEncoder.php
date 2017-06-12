<?php

namespace Drupal\fb_instant_articles\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * Facebook Instant Article encoder class.
 *
 * Takes a \Facebook\InstantArticles\Elements\InstantArticle object and encodes
 * it as a string.
 */
class InstantArticleEncoder implements EncoderInterface {

  /**
   * The format that this encoder supports.
   *
   * @var string
   */
  protected static $format = 'fbia';

  /**
   * {@inheritdoc}
   */
  public function supportsEncoding($format) {
    return $format === static::$format;
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data, $format, array $context = []) {
    /** @var \Facebook\InstantArticles\Elements\InstantArticle $data */
    return $data->render();
  }

}
