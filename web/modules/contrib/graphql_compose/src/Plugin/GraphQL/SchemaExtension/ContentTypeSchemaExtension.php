<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\SchemaExtension;

use Drupal\node\NodeInterface;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use function Symfony\Component\String\u;

/**
 * Adds Node data to the GraphQL Compose GraphQL API.
 *
 * @SchemaExtension(
 *   id = "node_schema_extension",
 *   name = "Node Schema Extension",
 *   description = "Node GraphQL Schema Extension.",
 *   schema = "graphql_compose"
 * )
 */
class ContentTypeSchemaExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();

    $this->addQueryFields($registry, $builder);
    $this->addTypeFields($registry, $builder);
  }

   /**
   * Registers type and field resolvers in the query type.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The resolver registry.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The resolver builder.
   */
  protected function addQueryFields(ResolverRegistryInterface $registry, ResolverBuilder $builder) {
    $contentTypes = \Drupal::service('graphql_compose.datamanager')->getDefinitions('node');
    foreach ($contentTypes as $contentType) {

      $registry->addFieldResolver(
        'Query',
        $contentType['type_plural'],
        $builder->produce('query_content_type:'.$contentType['id'])
          ->map('after', $builder->fromArgument('after'))
          ->map('before', $builder->fromArgument('before'))
          ->map('first', $builder->fromArgument('first'))
          ->map('last', $builder->fromArgument('last'))
          ->map('reverse', $builder->fromArgument('reverse'))
          ->map('sortKey', $builder->fromArgument('sortKey'))
      );

      $registry->addFieldResolver(
        'Query',
        $contentType['type'],
        $builder->produce('entity_load_by_uuid')
          ->map('type', $builder->fromValue('node'))
          ->map('bundles', $builder->fromValue([$contentType['id']]))
          ->map('uuid', $builder->fromArgument('id'))
      );

    }

    $registry->addTypeResolver('NodeContentUnion', function ($value) {
      if ($value instanceof NodeInterface) {
        return u($value->bundle())->title()->prepend('Node')->toString();
      }

      throw new Error('Could not resolve content type.');
    });

    $registry->addFieldResolver(
      'Query',
      'nodeByPath',
      $builder->compose(
        $builder->produce('route_load')
          ->map('path', $builder->fromArgument('path')),
        $builder->produce('route_entity')
          ->map('url', $builder->fromParent())
      )
    );

  }

  /**
   * Registers type and field resolvers in the shared registry.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The resolver registry.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The resolver builder.
   */
  protected function addTypeFields(ResolverRegistryInterface $registry, ResolverBuilder $builder) {

    $entityTypes = [
      'paragraph' => "\Drupal\paragraphs\ParagraphInterface",
    ];

    $contentTypes = \Drupal::service('graphql_compose.datamanager')->getDefinitions('node');
    foreach ($contentTypes as $contentType) {
      foreach ($contentType['fields'] as $field) {
        $builders = [];
        foreach ($field['producers'] as $producer) {
          if ($producer['type'] === 'dataProducer') {
            $customBuilder = $builder->produce($producer['id']);
            foreach ($producer['map'] as $map) {
              if ($map['id'] === 'fromParent') {
                $customBuilder->map($map['key'], $builder->fromParent());
              }
              if ($map['id'] === 'fromValue') {
                $mapValue = u($map['value'])->replace('{field_name}', $field['name'])->toString();
                $customBuilder->map($map['key'], $builder->fromValue($mapValue));
              }
            }
            $builders[] = $customBuilder;
          }
          if ($producer['type'] === 'fromPath') {
            $argsPath = u($producer['args']['path'])->replace('{field_name}', $field['name'])->toString();
            $builders[] =  $builder->fromPath($producer['args']['type'], $argsPath);
          }
        }

        $registry->addFieldResolver(
          $contentType['type_sdl'],
          $field['name_sdl'],
          $builder->compose(
            ...array_values($builders)
          )
        );
      }

      foreach ($contentType['unions'] as $unionType => $union) {
        $mapping = $union['mapping'];
        $mappingType = $entityTypes[$union['type']];
        $registry->addTypeResolver(
          $unionType, 
          function ($value) use ($mappingType, $mapping) {
            if ($value instanceof $mappingType) {
              return $mapping[$value->bundle()];
            }

            // Throw exception if $value->bundle() not found.
            return NULL;
          }
        );
      }
      
    }
  }

  /**
   * Loads a schema definition file.
   *
   * @param string $type
   *   The type of the definition file to load.
   *
   * @return string|null
   *   The definition based on Drupal ContentTypes or NULL if it was empty.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function loadDefinitionFile($type) {
    return \Drupal::service('graphql_compose.datamanager')->getSdlByStorage('node', $type);
  }

}
