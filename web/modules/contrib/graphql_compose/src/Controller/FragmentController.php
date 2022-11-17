<?php

namespace Drupal\graphql_compose\Controller;

use Drupal\graphql\Entity\ServerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for the GraphiQL query builder IDE.
 *
 * @codeCoverageIgnore
 */
class FragmentController {

  /**
   * Controller for the GraphiQL query builder IDE.
   *
   * @param \Drupal\graphql\Entity\ServerInterface $graphql_server
   *   The server.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array
   *   The render array.
   */
  public function show(ServerInterface $graphql_server, Request $request) {
    return [
      '#theme' => 'entity_fragments',
      '#entities' => \Drupal::service('graphql_compose.datamanager')->getFragments(),
    ];
  }
}