<?php

/**
 * @file
 * LoggerAppenderDrupal
 */

/**
 * LoggerAppenderDrupal logs to the Drupal log using the watchdog() function
 */
class LoggerAppenderDrupal extends LoggerAppender {
  public function append(LoggerLoggingEvent $event) {
    watchdog(
      $event->getLoggerName(),
      $event->getRenderedMessage(),
      null,
      $event->getLevel()->getSyslogEquivalent() // watchdog levels follow RFC 3164
    );
  }
}
