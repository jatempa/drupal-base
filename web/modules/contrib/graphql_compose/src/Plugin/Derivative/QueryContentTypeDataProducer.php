<?php

namespace Drupal\graphql_compose\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;

class QueryContentTypeDataProducer extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $contentTypes = \Drupal::service('graphql_compose.datamanager')->getDefinitions('node');
    foreach ($contentTypes as $contentType) {
      $this->derivatives[$contentType['id']] = $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
