<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;
use Drupal\fb_instant_articles\Transformer;
use Drupal\fb_instant_articles\TransformerLoggingTrait;
use Facebook\InstantArticles\Elements\InstantArticle;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'fbia_transformer' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_transformer",
 *   label = @Translation("FBIA Transformer"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class TransformerFormatter extends FormatterBase implements InstantArticleFormatterInterface {

  use TransformerLoggingTrait;

  /**
   * Create a new instance of TransformerFormatter.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\fb_instant_articles\Transformer $transformer
   *   FBIA SDK transformer object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger for transformer messages.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, RendererInterface $renderer, Transformer $transformer, ConfigFactoryInterface $config_factory, LoggerInterface $logger) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $renderer);
    $this->transformer = $transformer;
    $this->configFactory = $config_factory;
    $this->logger = $logger;
    $this->setTransformerLogLevel();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('renderer'),
      $container->get('fb_instant_articles.transformer'),
      $container->get('config.factory'),
      $container->get('logger.channel.fbia')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, $langcode = NULL) {
    foreach ($items as $delta => $item) {
      $markup = [
        '#type' => 'processed_text',
        '#text' => $item->value,
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      ];
      $markup = (string) $this->renderer->renderPlain($markup);

      // Note that by passing $article as the first argument, we are implicitly
      // ignoring the $region param and assuming this content goes into the
      // body are exclusively. The Facebook SDK currently only supports using
      // the transformer for body elements.
      $this->transformer->transformString($article, $markup);
      $this->storeTransformerLogs();
    }
  }

}
