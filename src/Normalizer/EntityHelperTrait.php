<?php

namespace Drupal\fb_instant_articles\Normalizer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Common entity data getters shared between the normalizers.
 */
trait EntityHelperTrait {

  /**
   * Instant articles config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Convenience method for getting the right config object.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Facebook Instant Articles config object.
   */
  protected function config() {
    if (!$this->config) {
      $this->config = \Drupal::config('fb_instant_articles.settings');
    }
    return $this->config;
  }

  /**
   * Helper function to compute the canonical URL for a given entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity being normalized.
   *
   * @return string
   *   The canonical URL for the given entity.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function entityCanonicalUrl(ContentEntityInterface $entity) {
    if ($override = $this->config()->get('canonical_url_override')) {
      return $override . $entity->toUrl('canonical')->toString();
    }
    else {
      return $entity->toUrl('canonical', ['absolute' => TRUE])->toString();
    }
  }

  /**
   * Helper function to get the created time of the given entity if applicable.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity being normalized.
   *
   * @return bool|\DateTime
   *   Created time of the given entity.
   */
  protected function entityCreatedTime(ContentEntityInterface $entity) {
    if ($created = $entity->get('created')) {
      return \DateTime::createFromFormat('U', $created->value);
    }
  }

  /**
   * Helper function to get the changed time of the given entity if applicable.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity being normalized.
   *
   * @return bool|\DateTime
   *   Changed time of the given entity.
   */
  protected function entityChangedTime(ContentEntityInterface $entity) {
    if ($entity instanceof EntityChangedInterface && ($changed = $entity->getChangedTime())) {
      return \DateTime::createFromFormat('U', $changed);
    }
  }

  /**
   * Helper function to pull the author name out of an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity being normalized.
   *
   * @return string|null
   *   Author name if there is one.
   */
  protected function entityAuthor(ContentEntityInterface $entity) {
    // Default the article author to the username.
    if ($entity instanceof EntityOwnerInterface && ($owner = $entity->getOwner())) {
      return $owner->getDisplayName();
    }
  }

}
