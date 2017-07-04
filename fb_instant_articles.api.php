<?php

/**
 * @file
 * Document hooks.
 */

/**
 * Alter the list of rules used by the FBIA PHP SDK Transformer.
 *
 * The transformer is used by the FBIA Transformer field formatter to transform
 * artbitrary HTML markup into distinct FBIA elements.
 *
 * You can use this hook to add/update/remove rules for the transformer. The
 * module ships with a default set of rules, but it's very likely that you'll
 * have custom markup for elements of your design that do not map to the
 * appropriate FBIA elements by default. In that case, implement this hook in
 * your module and add your own rules. You can even implement your own custom
 * Rule classes for use here. See the Facebook documentation below and the
 * default rules for more details.
 *
 * @param array $rules
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
 * @see https://developers.facebook.com/docs/instant-articles/reference#elements
 * @see https://developers.facebook.com/docs/instant-articles/sdk/transformer-rules
 * @see \Drupal\fb_instant_articles\TransformerRulesManager::defaultRules()
 */
function hook_fb_instant_articles_transformer_rules_alter(array &$rules) {
  $rules[] = [
    'class' => \Facebook\InstantArticles\Elements\Blockquote::class,
    'selector' => 'dev.my-custom-blockquote',
  ];

  $rules[] = [
    'class' => \Facebook\InstantArticles\Transformer\Rules\ImageRule::class,
    'selector' => '//div.image[img]',
    'properties' => [
      \Facebook\InstantArticles\Transformer\Rules\ImageRule::PROPERTY_IMAGE_URL => [
        'type' => 'string',
        'selector' => 'img',
        'attribute' => 'src',
      ],
    ],
  ];
}
