<?php

namespace RebelCode\EddBookings\Services\Module;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Storage\Resource\DeleteCapableInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\EventManager\EventInterface;

/**
 * Handler for handling the deletion of services.
 *
 * @since [*next-version*]
 */
class AdminDeleteServiceHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The sessions DELETE resource model.
     *
     * @since [*next-version*]
     *
     * @var DeleteCapableInterface
     */
    protected $sessionsDeleteRm;

    /**
     * The session rules DELETE resource model.
     *
     * @since [*next-version*]
     *
     * @var DeleteCapableInterface
     */
    protected $sessionRulesDeleteRm;

    /**
     * The expression builder.
     *
     * @since [*next-version*]
     *
     * @var object
     */
    protected $exprBuilder;

    /**
     * The slug of the services post type.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $postType;

    /**
     * The services post meta prefix.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $metaPrefix;

    /**
     * The resources DELETE resource model.
     *
     * @since [*next-version*]
     *
     * @var DeleteCapableInterface
     */
    protected $resourcesDeleteRm;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable      $postType             The slug of the services post type.
     * @param string|Stringable      $metaPrefix           The services post meta prefix.
     * @param DeleteCapableInterface $resourcesDeleteRm    The resources DELETE resource model.
     * @param DeleteCapableInterface $sessionsDeleteRm     The sessions DELETE resource model.
     * @param DeleteCapableInterface $sessionRulesDeleteRm The session rules DELETE resource model.
     * @param object                 $exprBuilder          The expression builder.
     */
    public function __construct(
        $postType,
        $metaPrefix,
        DeleteCapableInterface $resourcesDeleteRm,
        DeleteCapableInterface $sessionsDeleteRm,
        DeleteCapableInterface $sessionRulesDeleteRm,
        $exprBuilder
    ) {
        $this->postType             = $this->_normalizeString($postType);
        $this->metaPrefix           = $this->_normalizeString($metaPrefix);
        $this->resourcesDeleteRm    = $resourcesDeleteRm;
        $this->sessionsDeleteRm     = $sessionsDeleteRm;
        $this->sessionRulesDeleteRm = $sessionRulesDeleteRm;
        $this->exprBuilder          = $exprBuilder;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        /* @var $event EventInterface */
        $event = func_get_arg(0);

        if (!($event instanceof EventInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an event instance'), null, null, $event
            );
        }

        $postId   = $event->getParam(0);
        $postType = $this->_getPostType($postId);

        if ($postType === $this->postType) {
            $b = $this->exprBuilder;

            $scheduleId = $this->_getPostMeta($postId, $this->metaPrefix . 'schedule_id', $postId);

            $this->resourcesDeleteRm->delete(
                $b->eq(
                    $b->var('id'),
                    $b->lit($scheduleId)
                )
            );

            $this->sessionsDeleteRm->delete(
                $b->eq(
                    $b->var('resource_id'),
                    $b->lit($scheduleId)
                )
            );
            $this->sessionRulesDeleteRm->delete(
                $b->eq(
                    $b->var('resource_id'),
                    $b->lit($scheduleId)
                )
            );
        }
    }

    /**
     * Retrieves meta data for a WordPress post.
     *
     * @since [*next-version*]
     *
     * @param int|string $id      The ID of the service.
     * @param string     $metaKey The meta key.
     * @param mixed      $default The default value to return.
     *
     * @return mixed The meta value.
     */
    protected function _getPostMeta($id, $metaKey, $default = '')
    {
        $metaValue = \get_post_meta($id, $metaKey, true);

        return ($metaValue === '')
            ? $default
            : $metaValue;
    }

    /**
     * Retrieves the post type for a WordPress post by ID.
     *
     * @since [*next-version*]
     *
     * @param int|string $postId The post ID.
     *
     * @return string The post type slug.
     */
    protected function _getPostType($postId)
    {
        return get_post_type($postId);
    }
}
