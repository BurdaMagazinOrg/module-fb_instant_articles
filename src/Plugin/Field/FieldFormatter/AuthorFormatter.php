<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

/**
 * Plugin implementation of the 'fbia_author' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_author",
 *   label = @Translation("FBIA Author"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *     "list_text",
 *   }
 * )
 */
class AuthorFormatter extends FbiaFormatterBase {}
