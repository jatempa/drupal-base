<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\Schema;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\graphql_compose\GraphQL\ResolverRegistry;

/**
 * The provider of the schema base for the GraphQL Compose GraphQL API.
 *
 * Provides a target schema for GraphQL Schema extensions. Schema Extensions
 * should implement `SdlSchemaExtensionPluginBase` and should not subclass this
 * class.
 *
 * This class implements the resolver mapping for common data types and
 * interfaces. It uses a modified resolver registry that allows falling back to
 * an interface's field mapping reducing duplication for common object types
 * (such as Connections and Edges).
 *
 * This class borrows from the ComposableSchema example but intentionally does
 * not implement the extension configuration that that schema provides. Instead
 * the SdlSchemaPluginBase loads the schema extensions for all GraphQL Compose
 * features that are enabled.
 *
 * @Schema(
 *   id = "graphql_compose",
 *   name = "GraphQL Compose Schema"
 * )
 */
class GraphqlComposeBaseSchema extends SdlSchemaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry() {
    return new ResolverRegistry();
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema(ResolverRegistryInterface $registry) {
    // Add GraphQL Compose base types to the schema.
    $this->getBaseSchema($registry);

    // Instantiate the schema and add all extensions.
    return parent::getSchema($registry);
  }

  /**
   * Provides a base schema for GraphQL Compose.
   *
   * This ensures that other modules have common types available to them to
   * build on.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The resolver registry.
   */
  protected function getBaseSchema(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();

    // DateTime fields.
    // @todo https://www.drupal.org/project/social/issues/3191615
    $registry->addFieldResolver('DateTime', 'timestamp',
      $builder->fromParent()
    );

    // Root Query fields.
    $registry->addFieldResolver('Query', 'graphQLComposeInformation',
      $builder->produce('information')
    );

    // Connection fields.
    $registry->addFieldResolver('Connection', 'edges',
      $builder->produce('connection_edges')
        ->map('connection', $builder->fromParent())
    );

    $registry->addFieldResolver('Connection', 'nodes',
      $builder->produce('connection_nodes')
        ->map('connection', $builder->fromParent())
    );

    $registry->addFieldResolver('Connection', 'pageInfo',
      $builder->produce('connection_page_info')
        ->map('connection', $builder->fromParent())
    );

    // Edge fields.
    $registry->addFieldResolver('Edge', 'cursor',
      $builder->produce('edge_cursor')
        ->map('edge', $builder->fromParent())
    );
    $registry->addFieldResolver('Edge', 'node',
      $builder->produce('edge_node')
        ->map('edge', $builder->fromParent())
    );

    // MetaTag module enabled.
    $registry->addTypeResolver(
      'MetaTagUnion',
      function ($value) {
        if ($value['tag'] === 'link') {
          return 'MetaTagLink';
        }

        if ($value['tag'] === 'meta') {
          $attributes = array_keys($value['attributes']);
          if (in_array('name', $attributes)) {
            return 'MetaTagValue';
          }
          if (in_array('property', $attributes)) {
            return 'MetaTagProperty';
          }
        }

        // @TODO throw new Error('Could not resolve meta tag for: '. $tag);
      }
    );

  }

}
