<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

/**
 * Plugin implementation of the 'fbia_subtitle' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_subtitle",
 *   label = @Translation("FBIA Subtitle"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class SubtitleFormatter extends FbiaFormatterBase {}
