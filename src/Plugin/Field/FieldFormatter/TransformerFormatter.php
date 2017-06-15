<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

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
class TransformerFormatter extends FormatterBase {}
