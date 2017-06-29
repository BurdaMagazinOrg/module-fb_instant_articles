<?php

namespace Drupal\fb_instant_articles;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\fb_instant_articles\Normalizer\InstantArticleContentEntityNormalizer;
use Facebook\InstantArticles\Client\Client as FbiaClient;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\InstantArticles\Client\InstantArticleStatus;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Encapsulate Drupal-specific logic for FBIA Client.
 */
class DrupalClient extends FbiaClient {
  use StringTranslationTrait;

  /**
   * Facebook Graph API Permision Error Code.
   */
  const FB_INSTANT_ARTICLES_ERROR_CODE_PERMISSION = 200;

  /**
   * Facebook Graph API Page Not Approved Error Code.
   */
  const FB_INSTANT_ARTICLES_ERROR_CODE_PAGE_NOT_APPROVED = 1888205;

  /**
   * Serializer service.
   *
   * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface
   */
  protected $serializer;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Instant article content entity normalizer.
   *
   * @var \Drupal\fb_instant_articles\Normalizer\InstantArticleContentEntityNormalizer
   */
  protected $iaNormalizer;

  /**
   * Set the serializer.
   *
   * @param \Symfony\Component\Serializer\Normalizer\NormalizerInterface $serializer
   *   Serializer service. Note that we are type hiting to the
   *   NormalizerInterface, b/c that is the functionality we actually want to
   *   use from the Serializer.
   */
  public function setSerializer(NormalizerInterface $serializer) {
    $this->serializer = $serializer;
  }

  /**
   * Set the logger.
   *
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger channel.
   */
  public function setLogger(LoggerChannelInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * Set the instant article content entity normalizer.
   *
   * @param \Drupal\fb_instant_articles\Normalizer\InstantArticleContentEntityNormalizer $ia_normalizer
   *   Instant article normalizer object.
   */
  public function setIaNormalizer(InstantArticleContentEntityNormalizer $ia_normalizer) {
    $this->iaNormalizer = $ia_normalizer;
  }

  /**
   * {@inheritdoc}
   *
   * Additionally try to catch an attempted import call that failed from an
   * authorization exception. In such a case, try again as unpublished if the
   * situation allows.
   */
  public function importArticle($article, $published = FALSE, $forceRescrape = FALSE, $formatOutput = FALSE) {
    try {
      parent::importArticle($article, $published, $forceRescrape, $formatOutput);
    }
    catch (FacebookResponseException $e) {
      // If this was an authorization exception and the error code indicates
      // that the page has not yet passed review, try posting the article
      // unpublished. Only try again if the article was intended to be
      // published, so we don't try to post unpublished twice.
      if ($e->getCode() === self::FB_INSTANT_ARTICLES_ERROR_CODE_PERMISSION && $e->getSubErrorCode() === self::FB_INSTANT_ARTICLES_ERROR_CODE_PAGE_NOT_APPROVED && $published) {
        parent::importArticle($article, FALSE);
      }
    }
  }

  /**
   * Import a content entity into Instant Articles.
   *
   * Runs the given entity through the serialization process and finally calls
   * importArticle() to send it to Instant Articles.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity to import into Instant Articles.
   */
  public function importEntity(ContentEntityInterface $entity) {
    // Ensure that we have the services we need.
    if (!isset($this->serializer)) {
      throw new \LogicException($this->t('Error importing entity to Facebook Instant Articles. Serializer is not defined.'));
    }

    /** @var \Facebook\InstantArticles\Elements\InstantArticle $article */
    $article = $this->serializer->normalize($entity, InstantArticleContentEntityNormalizer::FORMAT);
    // Default published status to TRUE for entities that don't implement
    // EntityPublishedInterface. For those that do implement
    // EntityPublishedInterface, go by the published status of the entity. Note
    // that the Development Mode setting under API settings will override this,
    // so ensure that it is turned off in production.
    $published = TRUE;
    if ($entity instanceof EntityPublishedInterface) {
      $published = $entity->isPublished();
    }
    $this->importArticle($article, $published);
    $this->logger->info($this->t('Imported %label (@entity_id) into Facebook Instant Articles.', ['%label' => $entity->label(), '@entity_id' => $entity->id()]));
  }

  /**
   * Remove a content entity from Instant Articles.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity to import into Instant Articles.
   *
   * @return \Facebook\InstantArticles\Client\InstantArticleStatus
   *   Status of the response.
   */
  public function removeEntity(ContentEntityInterface $entity) {
    $status = $this->removeArticle($this->iaNormalizer->entityCanonicalUrl($entity));
    if ($status->getStatus() === InstantArticleStatus::SUCCESS) {
      $this->logger->info($this->t('Removed %label (@entity_id) from Facebook Instant Articles.', ['%label' => $entity->label(), '@entity_id' => $entity->id()]));
    }
    return $status;
  }

}
