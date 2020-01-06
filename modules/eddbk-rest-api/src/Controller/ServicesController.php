<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\CreateInternalExceptionCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\Factory\FactoryAwareTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Dhii\Validation\ValidatorInterface;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Entity\EntityManagerInterface;
use RebelCode\Modular\Events\CreateEventCapableTrait;
use RebelCode\Modular\Events\EventFactoryAwareTrait;
use stdClass;

/**
 * The API controller for services.
 *
 * @since [*next-version*]
 */
class ServicesController extends AbstractBaseController
{
    /* @since [*next-version*] */
    use FactoryAwareTrait {
        _getFactory as _getIteratorFactory;
        _setFactory as _setIteratorFactory;
    }

    /* @since [*next-version*] */
    use CreateEventCapableTrait;

    /* @since [*next-version*] */
    use EventFactoryAwareTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInternalExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The default number of items to return per page.
     *
     * @since [*next-version*]
     */
    const DEFAULT_NUM_ITEMS_PER_PAGE = 20;

    /**
     * The default page number.
     *
     * @since [*next-version*]
     */
    const DEFAULT_PAGE_NUMBER = 1;

    /**
     * The services manager.
     *
     * @since [*next-version*]
     *
     * @var EntityManagerInterface
     */
    protected $servicesManager;

    /**
     * The validator for validating if a requester has access to hidden services.
     *
     * @since [*next-version*]
     *
     * @var ValidatorInterface
     */
    protected $hiddenServicesAuthVal;

    /**
     * The validator for validating if a requester has access to sensitive information.
     *
     * @since [*next-version*]
     *
     * @var ValidatorInterface
     */
    protected $sensitiveInfoAuthVal;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param EntityManagerInterface $servicesManager       The services manager.
     * @param FactoryInterface       $iteratorFactory       The iterator factory to use for the results.
     * @param ValidatorInterface     $hiddenServicesAuthVal The validator for validating if a requester has access to
     *                                                      hidden services.
     * @param ValidatorInterface     $sensitiveInfoAuthVal  The validator for validating if a requester has access to
     *                                                      sensitive information.
     * @param EventFactoryInterface  $eventFactory         The event factory for creating event instances.
     */
    public function __construct(
        EntityManagerInterface $servicesManager,
        FactoryInterface $iteratorFactory,
        ValidatorInterface $hiddenServicesAuthVal,
        ValidatorInterface $sensitiveInfoAuthVal,
        EventFactoryInterface $eventFactory
    ) {
        $this->servicesManager = $servicesManager;
        $this->hiddenServicesAuthVal = $hiddenServicesAuthVal;
        $this->sensitiveInfoAuthVal = $sensitiveInfoAuthVal;
        $this->_setIteratorFactory($iteratorFactory);
        $this->_setEventFactory($eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createResultsIterator($results)
    {
        try {
            $this->sensitiveInfoAuthVal->validate($this->params);
            $auth = true;
        } catch (ValidationFailedExceptionInterface $exception) {
            $auth = false;
        }

        return $this->_getResultsIteratorFactory($results)->make([
            'items' => $results,
            'core_only' => !$auth,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function _get($params = [])
    {
        // Get number of items per page
        $numPerPage = $this->_containerGetDefault($params, 'numItems', static::DEFAULT_NUM_ITEMS_PER_PAGE);
        $numPerPage = $this->_normalizeInt($numPerPage);
        if ($numPerPage < 1) {
            throw $this->_createControllerException($this->__('Invalid number of items per page'), 400, null, $this);
        }

        // Get page number
        $pageNum = $this->_containerGetDefault($params, 'page', static::DEFAULT_PAGE_NUMBER);
        $pageNum = $this->_normalizeInt($pageNum);
        if ($pageNum < 1) {
            throw $this->_createControllerException($this->__('Invalid page number'), 400, null, $this);
        }

        // Calculate query offset
        $offset = ($pageNum - 1) * $numPerPage;

        // Use the service data in params as query filters
        $query = $this->_paramsToServiceData($params);

        // Check for the `s` search term
        if ($this->_containerHas($params, 's')) {
            $query['s'] = $this->_containerGet($params, 's');
        }

        try {
            $this->hiddenServicesAuthVal->validate($params);
        } catch (ValidationFailedExceptionInterface $exception) {
            $query['status'] = 'publish';
        }

        return $this->servicesManager->query($query, $numPerPage, $offset);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _post($params = [])
    {
        $data = $this->_paramsToServiceData($params);
        $id = $this->servicesManager->add($data);

        $this->_scheduleSessionGeneration($id);

        return $this->_get(['id' => $id]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _put($params = [])
    {
        $data = $this->_paramsToServiceData($params);

        try {
            $id = $this->_containerGet($data, 'id');
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createControllerException(
                $this->__('A service ID must be specified'), 400, $exception, $this
            );
        }

        $this->servicesManager->set($id, $data);

        $this->_scheduleSessionGeneration($id);

        return $this->_get(['id' => $id]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _patch($params = [])
    {
        $data = $this->_paramsToServiceData($params);

        try {
            $id = $this->_containerGet($data, 'id');
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createControllerException(
                $this->__('A service ID must be specified'), 400, $exception, $this
            );
        }

        $this->servicesManager->update($id, $data);

        $this->_scheduleSessionGeneration($id);

        return $this->_get(['id' => $id]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _delete($params = [])
    {
        $data = $this->_paramsToServiceData($params);

        try {
            $id = $this->_containerGet($data, 'id');
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createControllerException(
                $this->__('A service ID must be specified'), 400, $exception, $this
            );
        }

        $this->servicesManager->delete($id);

        return [];
    }

    /**
     * Extracts and creates service data from request params.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The request parameters.
     *
     * @return array The resulting service data.
     */
    protected function _paramsToServiceData($params = [])
    {
        $mapping = $this->_getServiceDataParamMapping();
        $data = [];

        foreach ($mapping as $_key => $_map) {
            try {
                $_value = $this->_containerGet($params, $_key);
            } catch (NotFoundExceptionInterface $exception) {
                continue;
            }

            // Get the optional transformation callback
            $_transform = isset($_map['transform']) ? $_map['transform'] : null;
            // Transform the value
            if ($_transform !== null) {
                $_value = call_user_func_array($_transform, [$_value]);
            }
            // Get the field name
            $_field = $_map['field'];
            // Save in data
            $data[$_field] = $_value;
        }

        return $data;
    }

    /**
     * Retrieves the data param mapping.
     *
     * @since [*next-version*]
     *
     * @return array A mapping of param keys to sub-arrays that contain a `field` (the key used by the service manager)
     *               and an optional `transform` callback for transforming a value for this field.
     */
    protected function _getServiceDataParamMapping()
    {
        return [
            'id' => [
                'field' => 'id',
                'transform' => function ($id) {
                    return $this->_normalizeInt($id);
                },
            ],
            'name' => [
                'field' => 'name',
                'transform' => function ($name) {
                    return $this->_normalizeString($name);
                },
            ],
            'description' => [
                'field' => 'description',
                'transform' => function ($desc) {
                    return $this->_normalizeString($desc);
                },
            ],
            'status' => [
                'field' => 'status',
                'transform' => function ($status) {
                    return $this->_normalizeString($status);
                },
            ],
            'bookingsEnabled' => [
                'field' => 'bookings_enabled',
                'transform' => function ($bkEn) {
                    return (int) (bool) $bkEn;
                },
            ],
            'sessionTypes' => [
                'field' => 'session_types',
            ],
            'displayOptions' => [
                'field' => 'display_options',
            ],
            'color' => [
                'field' => 'color',
            ],
            'timezone' => [
                'field' => 'timezone',
                'transform' => function ($timezone) {
                    return $this->_normalizeString($timezone);
                },
            ],
            'availability' => [
                'field' => 'availability',
            ],
            'imageId' => [
                'field' => 'image_id',
                'transform' => function ($imageId) {
                    return $this->_normalizeInt($imageId);
                },
            ],
        ];
    }

    /**
     * Schedules session generation for a service.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $serviceId The ID of the service for which to generate.
     */
    protected function _scheduleSessionGeneration($serviceId)
    {
        $event = $this->_createEvent('eddbk_generate_sessions', [
            'service_id' => $serviceId,
        ]);

        $this->_wpScheduleJob(time(), $event->getName(), [$event]);
    }

    /**
     * Schedules a WordPress cron job.
     *
     * @since [*next-version*]
     *
     * @param int    $time  The UTC timestamp for when to run the event.
     * @param string $event The name of the hook to execute when the event is run.
     * @param array  $args  Arguments to pass to the hook's callback function.
     */
    protected function _wpScheduleJob($time, $event, $args)
    {
        \wp_schedule_single_event($time, $event, $args);
    }

    /**
     * Retrieves a value from a container or data set, defaulting to a given value if not found.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable              $key       The key of the value to retrieve.
     * @param mixed                                         $default   Optional value to default to.
     *
     * @throws InvalidArgumentException    If container is invalid.
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     *
     * @return mixed The value mapped to the given key, or the $default value if the key was not found.
     */
    protected function _containerGetDefault($container, $key, $default = null)
    {
        try {
            return $this->_containerGet($container, $key);
        } catch (NotFoundExceptionInterface $exception) {
            return $default;
        }
    }
}
