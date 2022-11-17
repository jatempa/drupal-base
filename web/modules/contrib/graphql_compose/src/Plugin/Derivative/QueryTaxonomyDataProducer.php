<?php

namespace Drupal\graphql_compose\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;

class QueryTaxonomyDataProducer extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $taxonomyVocabularies = \Drupal::service('graphql_compose.datamanager')->getDefinitions('taxonomy_term');
    foreach ($taxonomyVocabularies as $taxonomyVocabulary) {
      $this->derivatives[$taxonomyVocabulary['id']] = $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
