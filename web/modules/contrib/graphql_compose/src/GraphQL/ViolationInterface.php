<?php

declare(strict_types=1);

namespace Drupal\graphql_compose\GraphQL;

/**
 * A violation indicating there was an error in a mutation input.
 */
interface ViolationInterface extends \JsonSerializable {}
