<?php

/**
 * @file
 * Contains Drupal\fb_instant_articles\Plugin\views\row\RssFields.
 */

namespace Drupal\fb_instant_articles\Plugin\views\row;

use \Drupal\views\Plugin\views\row\EntityRow;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

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

class FiaFields extends EntityRow {

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager) {
    $configuration['entity_type'] = 'node';
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager, $language_manager);
    $this->options['view_mode'] = 'fb_instant_articles_rss';
  }


  /**
   * {@inheritdoc}
   */
  public function render($row) {
    /**
     * @var \Drupal\views\ResultRow $row
     */

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
    $item = parent::render($row);

    $options['langcode'] = \Drupal::languageManager()->getCurrentLanguage()->getId();

    switch (true) {
      default:
      case ($entity instanceof \Drupal\node\Entity\Node):
        $options['row'] = $row;

        /**
         * @var \Drupal\node\Entity\Node $entity
         */
        $options['title'] = $entity->getTitle();
        $options['author'] = $entity->getOwner()->getAccountName();
        $options['created'] = '@'.$entity->getCreatedTime();
        $options['modified'] = '@'.$entity->getChangedTime();
        $options['link'] = $entity->toLink(NULL, 'canonical', ['absolute'=>true]);
        $options['guid'] = $entity->uuid();

        /**
         * @var \Drupal\user\UserInterface $author
         */
        $author = $entity->getOwner();
        $options['author'] = $author->toLink(NULL,'canonical',['absolute'=>true]);

    }

    $build = [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $options,
      '#row' => $item,
      '#field_alias' => isset($this->field_alias) ? $this->field_alias : '',
    ];

    return $build;
  }

}