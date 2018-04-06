<?php

namespace Drupal\fb_instant_articles;

use Facebook\InstantArticles\Transformer\Logs\TransformerLog;
use Psr\Log\LogLevel;

/**
 * Useful when you are making use of the FBIA Transformer.
 */
trait TransformerLoggingTrait {

  /**
   * FBIA SDK transformer object.
   *
   * @var \Drupal\fb_instant_articles\Transformer
   */
  protected $transformer;

  /**
   * Logger for transformer messages.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Set the transformer log level according to the FBIA setting.
   */
  protected function setTransformerLogLevel() {
    if ($log_level = $this->configFactory->get('fb_instant_articles.settings')->get('transformer_logging_level')) {
      TransformerLog::setLevel($log_level);
    }
    else {
      TransformerLog::setLevel(TransformerLog::ERROR);
    }
  }

  /**
   * Store the transformer logs if any.
   */
  protected function storeTransformerLogs() {
    $level_map = [
      TransformerLog::DEBUG => LogLevel::DEBUG,
      TransformerLog::ERROR => LogLevel::ERROR,
      TransformerLog::INFO => LogLevel::INFO,
    ];
    if ($logs = $this->transformer->getLogs()) {
      foreach ($logs as $log) {
        $this->logger->log($level_map[$log->getLevel()], $log->getMessage());
      }
    }
  }

}
