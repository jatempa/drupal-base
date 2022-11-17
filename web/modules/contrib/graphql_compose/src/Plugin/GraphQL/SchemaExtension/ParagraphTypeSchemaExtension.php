<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use function Symfony\Component\String\u;

/**
 * Adds Node data to the GraphQL Compose GraphQL API.
 *
 * @SchemaExtension(
 *   id = "paragraph_schema_extension",
 *   name = "Paragraph Schema Extension",
 *   description = "Paragraph GraphQL Schema Extension.",
 *   schema = "graphql_compose"
 * )
 */
class ParagraphTypeSchemaExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();

    // Inject service from container.
    if (\Drupal::moduleHandler()->moduleExists('paragraphs')) {
      $this->addQueryFields($registry, $builder);
      $this->addTypeFields($registry, $builder);
    }
    // Inject service from container.
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
    $paragraphTypes = \Drupal::service('graphql_compose.datamanager')->getDefinitions('paragraph');
    foreach ($paragraphTypes as $paragraphType) {
      $registry->addFieldResolver(
        'Query',
        $paragraphType['type_plural'],
        $builder->produce('query_paragraph_type:'.$paragraphType['id'])
          ->map('after', $builder->fromArgument('after'))
          ->map('before', $builder->fromArgument('before'))
          ->map('first', $builder->fromArgument('first'))
          ->map('last', $builder->fromArgument('last'))
          ->map('reverse', $builder->fromArgument('reverse'))
          ->map('sortKey', $builder->fromArgument('sortKey'))
      );

      // @TODO fix not loading paragraph entity
      $registry->addFieldResolver(
        'Query',
        $paragraphType['type'],
        $builder->produce('entity_load_by_uuid')
          ->map('type', $builder->fromValue('paragraph'))
          ->map('bundles', $builder->fromValue([$paragraphType['id']]))
          ->map('uuid', $builder->fromArgument('id'))
      );
    }
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
    $paragraphTypes = \Drupal::service('graphql_compose.datamanager')->getDefinitions('paragraph');
    foreach ($paragraphTypes as $paragraphType) {
      foreach ($paragraphType['fields'] as $field) {
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
          $paragraphType['type_sdl'],
          $field['name_sdl'],
          $builder->compose(
            ...array_values($builders)
          )
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
   *   The definition based on Drupal ParagraphTypes or NULL if it was empty.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function loadDefinitionFile($type) {
    return \Drupal::service('graphql_compose.datamanager')->getSdlByStorage('paragraph', $type);
  }

}
