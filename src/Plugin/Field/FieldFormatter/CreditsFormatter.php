<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

/**
 * Plugin implementation of the 'fbia_credits' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_credits",
 *   label = @Translation("FBIA Credits"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class CreditsFormatter extends FbiaFormatterBase {}
