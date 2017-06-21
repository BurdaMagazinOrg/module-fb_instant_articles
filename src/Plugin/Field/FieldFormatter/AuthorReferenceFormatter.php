<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;
use Drupal\user\Plugin\Field\FieldFormatter\AuthorFormatter as DrupalAuthorFormatter;
use Facebook\InstantArticles\Elements\Author;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Plugin implementation of the 'fbia_author_reference' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_author_reference",
 *   label = @Translation("FBIA Author"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class AuthorReferenceFormatter extends DrupalAuthorFormatter implements InstantArticleFormatterInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'link' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['link'] = [
      '#title' => t('Link author to the referenced entity'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('link'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->getSetting('link') ? t('Link to the referenced entity') : t('No link');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Do nothing. Our field formatters are unique in that they are not meant
    // to generate HTML on their own, but only signal to the Serialization API
    // the fate of this field in the FBIA document.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, $langcode = NULL) {
    // Need to call parent::prepareView() to populate the entities since it's
    // not otherwise getting called.
    $this->prepareView([$items->getEntity()->id() => $items]);

    /* @var \Drupal\user\UserInterface $entity */
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      $author = Author::create()
        ->withName($entity->getDisplayName());
      if ($this->getSetting('link')) {
        $author->withURL($entity->toUrl('canonical', ['absolute' => TRUE])->toString());
      }
      // Author's are added to the header of an instant article regardless of
      // the given $region.
      $header = $article->getHeader();
      if (!$header) {
        $header = Header::create();
      }
      $header->addAuthor($author);
    }
  }

}
