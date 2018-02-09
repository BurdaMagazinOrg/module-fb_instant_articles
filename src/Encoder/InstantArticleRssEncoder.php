<?php

namespace Drupal\fb_instant_articles\Encoder;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\serialization\Encoder\XmlEncoder;
use Facebook\InstantArticles\Elements\InstantArticle;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\XmlEncoder as BaseXmlEncoder;

/**
 * Facebook instant articles FBIA RSS encoder.
 */
class InstantArticleRssEncoder extends XmlEncoder {
  use StringTranslationTrait;

  /**
   * The format that this encoder supports.
   *
   * @var string
   */
  protected static $format = ['fbia_rss'];

  /**
   * The current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Instant articles settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Create a Instant Article RSS encoder.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory interface.
   */
  public function __construct(RequestStack $request_stack, ConfigFactoryInterface $config_factory) {
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->config = $config_factory->get('fb_instant_articles.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseEncoder() {
    // Overridden to set rss as the type.
    if (!isset($this->baseEncoder)) {
      $this->baseEncoder = new BaseXmlEncoder('rss');
      $this->baseEncoder->setSerializer($this->serializer);
    }

    return $this->baseEncoder;
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
    // Force $data into an array of numeric keys.
    if (!ctype_digit(implode('', array_keys($data)))) {
      $data = [$data];
    }

    // Render all each InstantArticle object.
    foreach ($data as $delta => $item) {
      if (!empty($item['content:encoded']) && $item['content:encoded'] instanceof InstantArticle) {
        $data[$delta]['content:encoded'] = $item['content:encoded']->render();
      }
    }
    // Wrapping tags.
    $feed_title = $this->t('Facebook Instant Articles RSS Feed');
    $feed_description = '';
    if (isset($context['views_style_plugin'])) {
      /** @var \Drupal\rest\Plugin\views\style\Serializer $style */
      $style = $context['views_style_plugin'];
      $feed_title = $style->view->getTitle();
      $feed_description = $style->view->storage->get('description');
    }
    $encoded = [
      '@version' => '2.0',
      '@xmlns:content' => 'http://purl.org/rss/1.0/modules/content/',
      'channel' => [
        'title' => $feed_title,
        'link' => $this->getLink(),
        'lastBuildDate' => date('c', time()),
      ],
    ];
    if (!empty($feed_description)) {
      $encoded['channel']['description'] = $feed_description;
    }
    $encoded['channel']['item'] = $data;
    return parent::encode($encoded, $format, $context);
  }

  /**
   * Helper function to get the URL of the site for the RSS feed <link> tag.
   *
   * @return string
   *   URL of the site.
   */
  protected function getLink() {
    if ($override = $this->config->get('canonical_url_override')) {
      return $override;
    }
    else {
      return $this->currentRequest->getSchemeAndHttpHost();
    }
  }

}
