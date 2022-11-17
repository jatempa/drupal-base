<?php

namespace Drupal\graphql_compose\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;

class QueryParagraphTypeDataProducer extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $paragraphTypes = \Drupal::service('graphql_compose.datamanager')->getDefinitions('paragraph');
    foreach ($paragraphTypes as $paragraphType) {
      $this->derivatives[$paragraphType['id']] = $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
