<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

/**
 * Plugin implementation of the 'fbia_blockquote' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_blockquote",
 *   label = @Translation("FBIA Blockquote"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class BlockquoteFormatter extends FormatterBase {}
