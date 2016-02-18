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
    GLOBAL $base_url;

    /**
     * @var \Drupal\Core\Entity\ContentEntityInterface $entity
     */
    $entity = $row->_entity;
    /**
     * @var []string $options
     */
    $options = $this->options;

    // Create the OPML item array.
    $item = [];
    $header = [];


    switch (true) {
      default:
      case ($entity instanceof Drupal\node\Entity\Node):
        /**
         * @var \Drupal\node\Entity\Node $entity
         */
        $header['title'] = $entity->getTitle();
        $header['author'] = $entity->getOwner()->getAccountName();
        $header['created'] = '@'.$entity->getCreatedTime();
        $header['modified'] = '@'.$entity->getChangedTime();
        $header['link'] = $entity->getOriginalId();

        $options['header'] = $header;

        $item = $entity->getTitle();
    }

    $build = [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $options,
      '#row' => $item,
      '#header' => $header,
      '#field_alias' => isset($this->field_alias) ? $this->field_alias : '',
    ];

    return $build;
  }

}