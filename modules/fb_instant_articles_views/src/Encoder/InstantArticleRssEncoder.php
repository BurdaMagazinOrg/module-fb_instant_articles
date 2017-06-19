<?php

namespace Drupal\fb_instant_articles_views\Encoder;

use Drupal\serialization\Encoder\XmlEncoder;
use Facebook\InstantArticles\Elements\InstantArticle;
use Symfony\Component\Serializer\Encoder\XmlEncoder as BaseXmlEncoder;

/**
 * Facebook instant articles FBIA RSS encoder.
 */
class InstantArticleRssEncoder extends XmlEncoder {

  /**
   * The format that this encoder supports.
   *
   * @var string
   */
  protected static $format = ['fbia_rss'];

  /**
   * Create a Roku Universal Search Encoder.
   */
  public function __construct() {
    $this->setBaseEncoder(new BaseXmlEncoder('rss'));
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDecoding($format) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data, $format, array $context = []) {
    foreach ($data as $delta => $item) {
      if (!empty($item['content:encoded']) && $item['content:encoded'] instanceof InstantArticle) {
        $data[$delta]['content:encoded'] = $item['content:encoded']->render();
      }
    }
    // Wrapping tags.
    $data = [
      '@version' => '2.0',
      '@xmlns:content' => 'http://purl.org/rss/1.0/modules/content/',
      'channel' => [
        'item' => $data,
      ],
    ];
    return parent::encode($data, $format, $context);
  }

}
