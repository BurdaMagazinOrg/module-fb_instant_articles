<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\InstantArticle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Plugin implementation of the 'fbia_subtitle' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_subtitle",
 *   label = @Translation("FBIA Subtitle"),
 *   field_types = {
 *     "string",
 *     "string_long",
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class SubtitleFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, RendererInterface $renderer) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->renderer = $renderer;
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
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, NormalizerInterface $normalizer, $langcode = NULL) {
    // Subtitles only go in the header. Create one if it doesn't exist yet and
    // ignore the given $region.
    $header = $article->getHeader();
    if (!$header) {
      $header = Header::create();
      $article->withHeader($header);
    }
    // Note that there can only be one subtitle. We use the first value as the
    // subtitle.
    if (!$items->isEmpty()) {
      $item = $items->get(0);
      if (in_array($items->getFieldDefinition()->getType(), [
        'text',
        'text_long',
        'text_with_summary',
      ])) {
        $subtitle = [
          '#type' => 'processed_text',
          '#text' => $item->value,
          '#format' => $item->format,
          '#langcode' => $item->getLangcode(),
        ];
        $subtitle = (string) $this->renderer->renderPlain($subtitle);
        $subtitle = Xss::filter($subtitle, []);
      }
      else {
        $subtitle = $item->getString();
      }
      $header->withSubTitle($subtitle);
    }
  }

}
