<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

/**
 * Plugin implementation of the 'fbia_video' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_video",
 *   label = @Translation("FBIA Video"),
 *   field_types = {
 *     "file",
 *   }
 * )
 */
class VideoFormatter extends FbiaFormatterBase {}
