<?php

namespace Drupal\fb_instant_articles;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Facebook\InstantArticles\Transformer\Rules\AnchorRule;
use Facebook\InstantArticles\Transformer\Rules\BlockquoteRule;
use Facebook\InstantArticles\Transformer\Rules\BoldRule;
use Facebook\InstantArticles\Transformer\Rules\CaptionRule;
use Facebook\InstantArticles\Transformer\Rules\H1Rule;
use Facebook\InstantArticles\Transformer\Rules\H2Rule;
use Facebook\InstantArticles\Transformer\Rules\ImageRule;
use Facebook\InstantArticles\Transformer\Rules\InteractiveRule;
use Facebook\InstantArticles\Transformer\Rules\ItalicRule;
use Facebook\InstantArticles\Transformer\Rules\LineBreakRule;
use Facebook\InstantArticles\Transformer\Rules\ListElementRule;
use Facebook\InstantArticles\Transformer\Rules\ListItemRule;
use Facebook\InstantArticles\Transformer\Rules\ParagraphRule;
use Facebook\InstantArticles\Transformer\Rules\PassThroughRule;
use Facebook\InstantArticles\Transformer\Rules\TextNodeRule;

/**
 * Handles loading and altering of Transformer rules.
 */
class TransformerRulesManager {

  protected $moduleHandler;

  /**
   * TransformerRulesManager constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler service.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * Get the transformer rules.
   *
   * @return array
   *   An array of rule definitions, where each rule definition is an
   *   associative array with the following keys:
   *   - class: Class name of the Rule, which should be a subclass of
   *     \Facebook\InstantArticles\Transformer\Rules\Rule
   *   - selector: String selector used to match elements in an HTML document to
   *     which apply the rule. Can be a simple element selector, a CSS selector
   *     or a XPath selector.
   *   - properties: Associative array or properties for the rules and how to
   *     get their values.
   *
   * @see https://developers.facebook.com/docs/instant-articles/sdk/transformer-rules
   */
  public function getRules() {
    $rules = $this->defaultRules();
    $this->moduleHandler->alter('fb_instant_articles_transformer_rules', $rules);
    return $rules;
  }

  /**
   * Return a default set of rules that are sensible for most use cases.
   */
  protected function defaultRules() {
    return [
      [
        'class' => TextNodeRule::class,
      ],
      [
        'class' => PassThroughRule::class,
        'selector' => 'html',
      ],
      [
        'class' => PassThroughRule::class,
        'selector' => 'head',
      ],
      [
        'class' => PassThroughRule::class,
        'selector' => 'body',
      ],
      [
        'class' => PassThroughRule::class,
        'selector' => 'code',
      ],
      [
        'class' => PassThroughRule::class,
        'selector' => 'del',
      ],
      [
        'class' => PassThroughRule::class,
        'selector' => 'span',
      ],
      [
        'class' => ParagraphRule::class,
        'selector' => 'div',
      ],
      [
        'class' => ParagraphRule::class,
        'selector' => 'p',
      ],
      [
        'class' => LineBreakRule::class,
        'selector' => 'br',
      ],
      [
        'class' => AnchorRule::class,
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
      ],
      [
        'class' => BoldRule::class,
        'selector' => 'b',
      ],
      [
        'class' => BoldRule::class,
        'selector' => 'strong',
      ],
      [
        'class' => ItalicRule::class,
        'selector' => 'i',
      ],
      [
        'class' => ItalicRule::class,
        'selector' => 'em',
      ],
      [
        'class' => BlockquoteRule::class,
        'selector' => 'blockquote',
      ],
      [
        'class' => InteractiveRule::class,
        'selector' => 'dl',
        'properties' => [
          InteractiveRule::PROPERTY_IFRAME => [
            'type' => 'element',
            'selector' => 'dl',
          ],
        ],
      ],
      [
        'class' => ImageRule::class,
        'selector' => '//p[img]',
        'properties' => [
          ImageRule::PROPERTY_IMAGE_URL => [
            'type' => 'string',
            'selector' => 'img',
            'attribute' => 'src',
          ],
        ],
      ],
      [
        'class' => ImageRule::class,
        'selector' => 'img',
        'properties' => [
          ImageRule::PROPERTY_IMAGE_URL => [
            'type' => 'string',
            'selector' => 'img',
            'attribute' => 'src',
          ],
        ],
      ],
      [
        'class' => CaptionRule::class,
        'selector' => 'img',
        'properties' => [
          CaptionRule::PROPERTY_DEFAULT => [
            'type' => 'string',
            'selector' => 'img',
            'attribute' => 'alt',
          ],
        ],
      ],
      [
        'class' => ListItemRule::class,
        'selector' => 'li',
      ],
      [
        'class' => ListElementRule::class,
        'selector' => 'ul',
      ],
      [
        'class' => ListElementRule::class,
        'selector' => 'ol',
      ],
      [
        'class' => H1Rule::class,
        'selector' => 'h1',
      ],
      [
        'class' => H1Rule::class,
        'selector' => 'title',
      ],
      [
        'class' => H2Rule::class,
        'selector' => 'h2',
      ],
      [
        'class' => InteractiveRule::class,
        'selector' => 'iframe',
        'properties' => [
          InteractiveRule::PROPERTY_URL => [
            'type' => 'string',
            'selector' => 'iframe',
            'attribute' => 'src',
          ],
          InteractiveRule::PROPERTY_IFRAME => [
            'type' => 'children',
            'selector' => 'iframe',
          ],
        ],
      ],
      [
        'class' => InteractiveRule::class,
        'selector' => '//p[iframe]',
        'properties' => [
          InteractiveRule::PROPERTY_URL => [
            'type' => 'string',
            'selector' => 'iframe',
            'attribute' => 'src',
          ],
          InteractiveRule::PROPERTY_IFRAME => [
            'type' => 'children',
            'selector' => 'iframe',
          ],
        ],
      ],
      [
        'class' => InteractiveRule::class,
        'selector' => 'div.embed',
        'properties' => [
          InteractiveRule::PROPERTY_IFRAME => [
            'type' => 'children',
            'selector' => 'div.embed',
          ],
        ],
      ],
      [
        'class' => InteractiveRule::class,
        'selector' => 'div.oembed',
        'properties' => [
          InteractiveRule::PROPERTY_IFRAME => [
            'type' => 'children',
            'selector' => 'div.oembed',
          ],
        ],
      ],
      [
        'class' => ImageRule::class,
        'selector' => '//p[a[img]]|//a[img]',
        'properties' => [
          ImageRule::PROPERTY_IMAGE_URL => [
            'type' => 'string',
            'selector' => 'img',
            'attribute' => 'src',
          ],
        ],
      ],
    ];
  }

}
