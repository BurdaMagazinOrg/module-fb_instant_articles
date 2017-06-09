<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

/**
 * Plugin implementation of the 'fbia_pullquote' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_pullquote",
 *   label = @Translation("FBIA Pullquote"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class PullquoteFormatter extends FbiaFormatterBase {}
