<?php

namespace RebelCode\EddBookings\Services\Module;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventManager\EventInterface;
use RebelCode\Entity\QueryCapableManagerInterface;

/**
 * The handler that changes the host custom post type query to exclude services.
 *
 * @since [*next-version*]
 */
class HideServicesFromDownloadsHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The slug of the services post type.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $postType;

    /**
     * The services entity manager.
     *
     * @since [*next-version*]
     *
     * @var QueryCapableManagerInterface
     */
    protected $servicesManager;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable            $postType        The slug of the services post type.
     * @param QueryCapableManagerInterface $servicesManager The services entity manager.
     */
    public function __construct($postType, $servicesManager)
    {
        $this->postType = $this->_normalizeString($postType);
        $this->servicesManager = $servicesManager;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $event = func_get_arg(0);

        if (!($event instanceof EventInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an event instance'), null, null, $event
            );
        }

        if (!is_admin() || !function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();
        $query = $event->getParam(0);

        if ($screen->post_type !== $this->postType || $screen->id !== 'edit-download' || $query === null) {
            return;
        }

        // Ignore queries originate from the services manager
        if (isset($query->query_vars['meta_query']['bookings_enabled'])) {
            return;
        }

        $serviceIds = [];
        $services = $this->servicesManager->query();
        foreach ($services as $_service) {
            try {
                $serviceIds[] = $this->_containerGet($_service, 'id');
            } catch (NotFoundExceptionInterface $exception) {
                continue;
            }
        }

        $query->query_vars['post__not_in'] = $serviceIds;
    }
}
