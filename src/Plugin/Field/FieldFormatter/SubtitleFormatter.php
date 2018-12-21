<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\fb_instant_articles\Transformer;
use Facebook\InstantArticles\Elements\H2;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Transformer\Rules\AnchorRule;
use Facebook\InstantArticles\Transformer\Rules\BoldRule;
use Facebook\InstantArticles\Transformer\Rules\ItalicRule;
use Facebook\InstantArticles\Transformer\Rules\PassThroughRule;
use Facebook\InstantArticles\Transformer\Rules\TextNodeRule;
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
   * Transformer.
   *
   * @var \Drupal\fb_instant_articles\Transformer
   */
  protected $transformer;

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
   *   Facebook transformer.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, RendererInterface $renderer, Transformer $transformer) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->renderer = $renderer;
    $this->transformer = $transformer;
    $this->transformer->setRules([
      TextNodeRule::createFrom([]),
      PassThroughRule::createFrom(['selector' => '//html|//body|//p|//div|//blockquote|//h1|//h2|//h3|//h4']),
      ItalicRule::createFrom(['selector' => 'i']),
      ItalicRule::createFrom(['selector' => 'em']),
      BoldRule::createFrom(['selector' => 'b']),
      BoldRule::createFrom(['selector' => 'strong']),
      AnchorRule::createFrom([
        'selector' => 'a',
        'properties' => [
          AnchorRule::PROPERTY_ANCHOR_HREF => [
            'type' => 'string',
            'selector' => 'a',
            'attribute' => 'href',
          ],
          AnchorRule::PROPERTY_ANCHOR_REL => [
            'type' => 'string',
            'selector' => 'a',
            'attribute' => 'rel',
          ],
        ],
      ]),
    ]);
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
      $container->get('fb_instant_articles.transformer')
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
      // For formatted text, pass the text through the filters and then through
      // the FBIA transformer, before adding it to the article.
      // in the subtitle.
      if (in_array($items->getFieldDefinition()->getType(), [
        'text',
        'text_long',
        'text_with_summary',
      ])) {
        $subtitle_render_array = [
          '#type' => 'processed_text',
          '#text' => $item->value,
          '#format' => $item->format,
          '#langcode' => $item->getLangcode(),
        ];
        $subtitle_string = (string) $this->renderer->renderPlain($subtitle_render_array);
        // Here we create a Facebook H2 element, passing it as context to the
        // transformer. It will therefore append any allowed elements, per the
        // rules defined in the constructor (only a, i, b, em and strong tags),
        // to the header. What that means is that tags will be stripped from the
        // input string except a, i, b, em and strong.
        $subtitle = H2::create();
        $this->transformer->transformString($subtitle, $subtitle_string);
      }
      else {
        $subtitle = $item->getString();
      }
      $header->withSubTitle($subtitle);
    }
  }

}
