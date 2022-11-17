<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\file\FileInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns an image style derivative of an image.
 *
 * @DataProducer(
 *   id = "field_image",
 *   name = @Translation("Image"),
 *   description = @Translation("Returns image and derivatives."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Image properties")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       required = FALSE
 *     ),
 *   }
 * )
 */
class FieldImage extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('renderer')
    );
  }

  /**
   * ImageDerivative constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
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
   * @param \Drupal\file\FileInterface $entity
   * @param string $style
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return array|null
   */
  public function resolve(FileInterface $entity = NULL, RefinableCacheableDependencyInterface $metadata) {
    // Return if we dont have an entity.
    if (!$entity) {
      return NULL;
    }

    $access = $entity->access('view', NULL, TRUE);
    $metadata->addCacheableDependency($access);
    if ($access->isAllowed()) {
      $width = $entity->width;
      $height = $entity->height;

      // @todo Not sure why PHPStan complains here, this should be refactored to
      // check the entity properties first.
      // @phpstan-ignore-next-line
      if (empty($width) || empty($height)) {
        /** @var \Drupal\Core\Image\ImageInterface $image */
        $image = \Drupal::service('image.factory')->get($entity->getFileUri());
        if ($image->isValid()) {
          $width = $image->getWidth();
          $height = $image->getHeight();
        }
      }

      $styles = \Drupal::entityTypeManager()->getStorage('image_style')->loadMultiple();
      $derivatives = [];
      foreach($styles as $key => $style) {
        $image_style = ImageStyle::load($key);
        // @todo Not sure why PHPStan complains here, this should be refactored to
        // check the entity properties first.
        // @phpstan-ignore-next-line
        if (empty($width) || empty($height)) {
          /** @var \Drupal\Core\Image\ImageInterface $image */
          $image = \Drupal::service('image.factory')->get($entity->getFileUri());
          if ($image->isValid()) {
            $width = $image->getWidth();
            $height = $image->getHeight();
          }
        }

        // Determine the dimensions of the styled image.
        $dimensions = [
          'width' => $width,
          'height' => $height,
        ];

        $image_style->transformDimensions($dimensions, $entity->getFileUri());
        $metadata->addCacheableDependency($image_style);

        // The underlying URL generator that will be invoked will leak cache
        // metadata, resulting in an exception. By wrapping within a new render
        // context, we can capture the leaked metadata and make sure it gets
        // incorporated into the response.
        $context = new RenderContext();
        $url = $this->renderer->executeInRenderContext($context, function () use ($image_style, $entity) {
          return $image_style->buildUrl($entity->getFileUri());
        });

        if (!$context->isEmpty()) {
          $metadata->addCacheableDependency($context->pop());
        }

        $derivatives[] = [
          'style' => $key,
          'url' => $url,
          'width' => $dimensions['width'],
          'height' => $dimensions['height'],
        ];
      }

      return [
        'url' => \Drupal::service('file_url_generator')->generateAbsoluteString($entity->getFileUri()),
        'width' => $width,
        'height' => $height,
        'styles' => $derivatives,
      ];
    }

    return NULL;
  }

}
