<?php

namespace Drupal\graphql_compose\GraphQL;

use Drupal\graphql_compose\GraphQL\DecoratableTypeResolver;
use Drupal\user\UserInterface;

/**
 * Type resolver for User concrete class of Actor interface.
 */
class UserActorTypeResolver extends DecoratableTypeResolver {

  /**
   * {@inheritdoc}
   */
  protected function resolve($actor) : ?string {
    return $actor instanceof UserInterface ? 'User' : NULL;
  }

}
