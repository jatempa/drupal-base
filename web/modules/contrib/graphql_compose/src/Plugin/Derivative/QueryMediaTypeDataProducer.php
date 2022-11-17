<?php

namespace Drupal\graphql_compose\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;

class QueryMediaTypeDataProducer extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $mediaTypes = \Drupal::service('graphql_compose.datamanager')->getDefinitions('media');
    foreach ($mediaTypes as $mediaType) {
      $this->derivatives[$mediaType['id']] = $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
