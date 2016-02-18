<?php

/**
 * @file
 * Contains Drupal\facebook_instant_articles\Plugin\views\row\RssFields.
 */

namespace Drupal\facebook_instant_articles\Plugin\views\row;

use Drupal\views\Plugin\views\row\RowPluginBase;

/**
 * Renders an RSS item based on fields.
 *
 * @ViewsRow(
 *   id = "fiafields",
 *   title = @Translation("FIA Fields"),
 *   help = @Translation("Display fields as FIA (facebook instant articles) items."),
 *   theme = "views_view_row_fia",
 *   display_types = {"feed"}
 * )
 */
class FiaFields extends RowPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render($row) {
    // Create the OPML item array.
    $item = array();

    $build = array(
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $this->options,
      '#row' => $item,
      '#field_alias' => isset($this->field_alias) ? $this->field_alias : '',
    );
    return $build;
  }

}