<?php

namespace Drupal\Tests\fb_instant_articles\Kernel\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * Base class for common functionality between field formatter tests.
 */
class FormatterTestBase extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'user',
    'field',
    'fb_instant_articles',
    'entity_test',
    'system',
    'serialization',
    'user',
  ];

  /**
   * Entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * Bundle name.
   *
   * @var string
   */
  protected $bundle;

  /**
   * Field name.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * Entity view display object used in the tests.
   *
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected $display;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['system', 'field']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('user');

    $this->entityType = 'entity_test';
    $this->bundle = $this->entityType;
    $this->fieldName = Unicode::strtolower($this->randomMachineName());

    $field_storage = FieldStorageConfig::create([
      'field_name' => $this->fieldName,
      'entity_type' => $this->entityType,
      'type' => $this->getFieldType(),
    ]);
    $field_storage->save();

    $instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $this->bundle,
      'label' => $this->randomMachineName(),
    ]);
    $instance->save();

    $this->display = EntityViewDisplay::create([
      'targetEntityType' => $this->entityType,
      'bundle' => $this->bundle,
      'mode' => 'default',
      'status' => TRUE,
    ]);
  }

  /**
   * Get the field type of the test field to create.
   *
   * Most of the field formatters apply to string type fields. There are a
   * couple exceptions, so child classes can override this method if need be.
   *
   * @return string
   *   Machine name of a field type.
   */
  protected function getFieldType() {
    return 'string';
  }

}
