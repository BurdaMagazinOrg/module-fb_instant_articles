<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

/**
 * Plugin implementation of the 'fbia_kicker' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_kicker",
 *   label = @Translation("FBIA Kicker"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class KickerFormatter extends FbiaFormatterBase {}
