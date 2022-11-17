<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer\Field;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\typed_data\DataFetcherTrait;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Generates the metatags.
 *
 * @DataProducer(
 *   id = "meta_tag",
 *   name = @Translation("Metatag"),
 *   description = @Translation("return metatags."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Path")
 *   ),
 *   consumes = {
 *     "value" = @ContextDefinition("any",
 *       label = @Translation("Root value")
 *     ),
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Root type"),
 *       required = FALSE
 *     )
 *   }
 * )
 */

class FieldMetaTag extends DataProducerPluginBase implements ContainerFactoryPluginInterface
{
    use TypedDataTrait;
    use DataFetcherTrait;

     /**
      * The rendering service.
      *
      * @var \Drupal\Core\Render\RendererInterface
      */
    protected $renderer;

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition)
    {
        return new static(
            $configuration,
            $pluginId,
            $pluginDefinition,
            $container->get('renderer')
        );
    }

    /**
     * MetaTag constructor.
     *
     * @param array                                 $configuration
     *   The plugin configuration array.
     * @param string                                $pluginId
     *   The plugin id.
     * @param mixed                                 $pluginDefinition
     *   The plugin definition.
     * @param \Drupal\Core\Render\RendererInterface $renderer
     *   The renderer service.
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        array $configuration,
        $pluginId,
        $pluginDefinition,
        RendererInterface $renderer
    ) {
        parent::__construct($configuration, $pluginId, $pluginDefinition);
        $this->renderer = $renderer;
    }
    /**
     * Resolver.
     *
     * @param mixed                                                    $value
     * @param string|null                                              $type
     * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
     *
     * @return mixed
     */
    public function resolve($value, $type, RefinableCacheableDependencyInterface $metadata)
    {

        if (!($value instanceof TypedDataInterface) && !empty($type)) {
            $manager = $this->getTypedDataManager();
            $definition = $manager->createDataDefinition($type);
            $value = $manager->create($definition, $value);
        }

        if (!($value instanceof TypedDataInterface)) {
            throw new \BadMethodCallException('Could not derive typed data type.');
        }

        $context = new RenderContext();
        $path = 'metatag';
        $bubbleable = new BubbleableMetadata();
        $fetcher = $this->getDataFetcher();

        $result = $this->renderer->executeInRenderContext(
            $context, function () use ($fetcher, $value, $path, $bubbleable) {

                $output = $fetcher->fetchDataByPropertyPath(
                    $value,
                    $path,
                    $bubbleable
                )->getValue();

                return $output;
            }
        );

        if (!$context->isEmpty()) {
            $metadata->addCacheableDependency($context->pop());
        }

        return $result;
    }

}