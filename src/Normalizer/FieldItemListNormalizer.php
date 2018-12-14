<?php

namespace Drupal\fb_instant_articles\Normalizer;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;
use Drupal\fb_instant_articles\Regions;
use Drupal\fb_instant_articles\Transformer;
use Drupal\fb_instant_articles\TransformerLoggingTrait;
use Facebook\InstantArticles\Elements\Footer;
use Facebook\InstantArticles\Elements\Header;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Normalize FieldItemList object into an Instant Article object.
 */
class FieldItemListNormalizer extends SerializerAwareNormalizer implements NormalizerInterface {
  use TransformerLoggingTrait;

  /**
   * Name of the format that this normalizer deals with.
   */
  const FORMAT = 'fbia';

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * FieldItemListNormalizer constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\fb_instant_articles\Transformer $transformer
   *   FBIA SDK transformer object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger for transformer messages.
   */
  public function __construct(RendererInterface $renderer, Transformer $transformer, ConfigFactoryInterface $config_factory, LoggerInterface $logger) {
    $this->renderer = $renderer;
    $this->transformer = $transformer;
    $this->configFactory = $config_factory;
    $this->logger = $logger;
    $this->setTransformerLogLevel();
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    // Only consider this normalizer if we are trying to normalize a field item
    // list into the 'fbia' format.
    return $format === self::FORMAT && $data instanceof FieldItemListInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    /** @var \Drupal\Core\Field\FieldItemListInterface $object */
    if (!isset($context['instant_article'])) {
      return;
    }
    /** @var \Facebook\InstantArticles\Elements\InstantArticle $article */
    $article = $context['instant_article'];

    // If we're given an entity_view_display object as context, use that as a
    // mapping to guide the normalization.
    if (isset($context['entity_view_display'])) {
      /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $display */
      $display = $context['entity_view_display'];
      $formatter = $display->getRenderer($object->getName());
      $component = $display->getComponent($object->getName());
      if ($formatter instanceof InstantArticleFormatterInterface) {
        $formatter->viewInstantArticle($object, $article, $component['region'], $this->serializer);
      }
      elseif ($formatter instanceof FormatterInterface) {
        $formatter->prepareView([$object->getEntity()->id() => $object]);
        $render_array = $formatter->view($object);
        if ($markup = (string) $this->renderer->renderPlain($render_array)) {

          // Determine correct context for transformation.
          $transformer_context = $article;
          if ($component['region'] === Regions::REGION_HEADER) {
            $header = $article->getHeader();
            if (!$header) {
              $header = Header::create();
              $article->withHeader($header);
            }
            $transformer_context = $header;
          }
          elseif ($component['region'] === Regions::REGION_FOOTER) {
            $footer = $article->getFooter();
            if (!$footer) {
              $footer = Footer::create();
              $article->withFooter($footer);
            }
            $transformer_context = $footer;
          }

          // Region-aware transformation of rendered markup.
          // Pass the markup through the Transformer.
          $this->transformer->transformString($transformer_context, $markup);
          $this->storeTransformerLogs();
        }
      }
    }
  }

}
