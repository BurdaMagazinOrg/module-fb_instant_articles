<?php

namespace Drupal\Tests\fb_instant_articles\Unit;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\fb_instant_articles\DrupalClient;
use Drupal\fb_instant_articles\Normalizer\InstantArticleContentEntityNormalizer;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Facebook\InstantArticles\Client\InstantArticleStatus;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Test the Drupal FBIA client wrapper.
 *
 * @group fb_instant_articles
 *
 * @coversDefaultClass \Drupal\fb_instant_articles\DrupalClient
 */
class DrupalClientTest extends UnitTestCase {

  protected $logger;

  protected $serializer;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->serializer = $this->getMock(NormalizerInterface::class);
    $this->logger = $this->getMock(LoggerChannelInterface::class);
  }

  /**
   * Test the import entity method.
   *
   * @covers ::importEntity
   */
  public function testImportEntity() {
    $client = $this->getMockBuilder(DrupalClient::class)
      ->disableOriginalConstructor()
      ->setMethods(['importArticle'])
      ->getMock();
    $client->setStringTranslation($this->getStringTranslationStub());
    $client->setSerializer($this->serializer);
    $client->setLogger($this->logger);

    $client->expects($this->once())
      ->method('importArticle')
      ->with($this->isNull(), $this->isTrue());

    $client->importEntity($this->getMock(ContentEntityInterface::class));

    // Test that importArticle is called with FALSE for an unpublished entity.
    $client = $this->getMockBuilder(DrupalClient::class)
      ->disableOriginalConstructor()
      ->setMethods(['importArticle'])
      ->getMock();
    $client->setStringTranslation($this->getStringTranslationStub());
    $client->setSerializer($this->serializer);
    $client->setLogger($this->logger);

    $client->expects($this->once())
      ->method('importArticle')
      ->with($this->isNull(), $this->isFalse());

    $entity = $this->getMockBuilder(NodeInterface::class)
      ->getMock();
    $entity->method('isPublished')
      ->willReturn(FALSE);

    $client->importEntity($entity);
  }

  /**
   * Test the remove entity method.
   *
   * @covers ::removeEntity
   */
  public function testRemoveEntity() {
    // Test that removeArticle is called with the canonical URL from the
    // iaNormalizer object.
    $client = $this->getMockBuilder(DrupalClient::class)
      ->disableOriginalConstructor()
      ->setMethods(['removeArticle'])
      ->getMock();
    $ia_status = $this->getMockBuilder(InstantArticleStatus::class)
      ->disableOriginalConstructor()
      ->getMock();
    $client->method('removeArticle')
      ->willReturn($ia_status);
    $client->setStringTranslation($this->getStringTranslationStub());
    $client->setLogger($this->logger);
    $ia_normalizer = $this->getMockBuilder(InstantArticleContentEntityNormalizer::class)
      ->disableOriginalConstructor()
      ->getMock();
    $ia_normalizer->method('entityCanonicalUrl')
      ->willReturn('http://www.example.com/node/1');
    $client->setIaNormalizer($ia_normalizer);

    $client->expects($this->once())
      ->method('removeArticle')
      ->with('http://www.example.com/node/1');

    $client->removeEntity($this->getMock(ContentEntityInterface::class));
  }

}
