<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

/**
 * Plugin implementation of the 'fbia_copyright' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_copyright",
 *   label = @Translation("FBIA Copyright"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class CopyrightFormatter extends FormatterBase {}
