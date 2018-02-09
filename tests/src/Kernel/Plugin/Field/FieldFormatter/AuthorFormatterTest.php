<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\fb_instant_articles\Regions;
use Facebook\InstantArticles\Elements\Author;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Tests the instant article author field formatter.
 *
 * @group fb_instant_articles
 */
class AuthorFormatterTest extends FormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Setup entity view display with default values.
    $this->display->setComponent($this->fieldName, [
      'type' => 'fbia_author',
      'settings' => [],
    ]);
    $this->display->save();
  }

  /**
   * Test the instant article author formatter.
   */
  public function testAuthorFormatter() {
    $value_alpha = 'Joe Mayo';
    $value_beta = 'J. Peterman';

    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}[] = ['value' => $value_alpha];
    $entity->{$this->fieldName}[] = ['value' => $value_beta];

    /** @var \Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface $formatter */
    $formatter = $this->display->getRenderer($this->fieldName);
    $article = InstantArticle::create();
    $formatter->viewInstantArticle($entity->{$this->fieldName}, $article, Regions::REGION_CONTENT);

    $authors = $article->getHeader()->getAuthors();
    $this->assertEquals(2, count($authors));
    $this->assertTrue($authors[0] instanceof Author);
    $this->assertEquals($value_alpha, $authors[0]->getName());
    $this->assertEquals($value_beta, $authors[1]->getName());
  }

}
