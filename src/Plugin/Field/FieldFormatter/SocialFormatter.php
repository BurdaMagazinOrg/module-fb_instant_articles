<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

/**
 * Plugin implementation of the 'fbia_social' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_social",
 *   label = @Translation("FBIA Social"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class SocialFormatter extends FbiaFormatterBase {}
