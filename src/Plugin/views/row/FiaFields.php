<?php

/**
 * @file
 * Contains Drupal\facebook_instant_articles\Plugin\views\row\RssFields.
 */

namespace Drupal\facebook_instant_articles\Plugin\views\row;

use \Drupal\views\Plugin\views\row\EntityRow;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Form\FormStateInterface;

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

  /**
  * Does the row plugin support adding fields to its output.
  *
  * @var bool
  */
  protected $usesFields = TRUE;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager) {
    $configuration['entity_type'] = 'node';
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager, $language_manager);
    $this->options['view_mode'] = 'facebook_instant_articles_rss';
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['author_field'] = array('default' => 'user');
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $view_fields_labels = $this->displayHandler->getFieldLabels();
    $author_fields_labels = array_merge(array('user' => $this->t('User')), $view_fields_labels);

    $form['author_field'] = array(
      '#type' => 'select',
      '#title' => $this->t('Author field'),
      '#description' => $this->t('Selecting "User" will use the name of the User who created the node for the author field. Otherwise select the field that is going to be used as the author field for each row.'),
      '#options' => $author_fields_labels,
      '#default_value' => $this->options['author_field'],
      '#required' => TRUE,
    );
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

    /**
     * @var static int $row_index
    */
    static $row_index;
    if (!isset($row_index)) {
      $row_index = 0;
    }

    switch (true) {
      default:
      case ($entity instanceof \Drupal\node\Entity\Node):
        $options['row'] = $row;

        /**
         * @var \Drupal\node\Entity\Node $entity
         */
        $options['title'] = $entity->getTitle();
        if($this->options['author_field'] == "user") {
          $options['author'] = $entity->getOwner()->toLink(NULL,'canonical',['absolute'=>true]);
        }
        else {
          $options['author'] = $this->getField($row_index, $this->options['author_field']);
        }
        $options['created'] = '@'.$entity->getCreatedTime();
        $options['modified'] = '@'.$entity->getChangedTime();
        $options['link'] = $entity->toLink(NULL, 'canonical', ['absolute'=>true]);
        $options['guid'] = $entity->uuid();
    }

    $row_index++;

    $build = [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $options,
      '#row' => $item,
      '#field_alias' => isset($this->field_alias) ? $this->field_alias : '',
    ];

    return $build;
  }


  /**
   * Retrieves a views field value from the style plugin.
   *
   * @param $index
   *   The index count of the row as expected by views_plugin_style::getField().
   * @param $field_id
   *   The ID assigned to the required field in the display.
   *
   * @return string|null|\Drupal\Component\Render\MarkupInterface
   *   An empty string if there is no style plugin, or the field ID is empty.
   *   NULL if the field value is empty. If neither of these conditions apply,
   *   a MarkupInterface object containing the rendered field value.
   */
  public function getField($index, $field_id) {
    if (empty($this->view->style_plugin) || !is_object($this->view->style_plugin) || empty($field_id)) {
      return '';
    }
    return $this->view->style_plugin->getField($index, $field_id);
  }
}
