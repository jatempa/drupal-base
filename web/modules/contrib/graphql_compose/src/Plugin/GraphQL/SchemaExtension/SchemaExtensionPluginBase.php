<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_compose\GraphQL\StandardisedMutationSchemaTrait;

/**
 * Base class that can be used for GraphQL Compose schema extension plugins.
 */
abstract class SchemaExtensionPluginBase extends SdlSchemaExtensionPluginBase {

  use StandardisedMutationSchemaTrait;

}
