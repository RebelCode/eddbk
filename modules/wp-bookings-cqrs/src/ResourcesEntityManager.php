<?php

namespace RebelCode\Storage\Resource\WordPress;

use ArrayAccess;
use DateTime;
use DateTimeZone;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerGetPathCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\ContainerSetCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Factory\FactoryInterface;
use Dhii\Storage\Resource\DeleteCapableInterface;
use Dhii\Storage\Resource\InsertCapableInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Storage\Resource\UpdateCapableInterface;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Time\CreateDateTimeZoneCapableTrait;
use stdClass;
use Traversable;

/**
 * An entity manager implementation specific for resource and their session rules.
 *
 * @since [*next-version*]
 */
class ResourcesEntityManager extends BaseCqrsEntityManager
{
    /* @since [*next-version*] */
    use ContainerGetPathCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use ContainerSetCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateDateTimeZoneCapableTrait;

    /**
     * The key in resource DB records where the resource data is stored.
     *
     * May be a path, delimited by forward slashes.
     *
     * @since [*next-version*]
     */
    const K_RECORD_DATA = 'data';

    /**
     * The key in resource entities where the resource data is stored.
     *
     * May be a path, delimited by forward slashes.
     *
     * @since [*next-version*]
     */
    const K_ENTITY_DATA = 'data';

    /**
     * The key in resource DB records where the resource name is stored.
     *
     * This is used for searching for resources by name.
     *
     * @since [*next-version*]
     */
    const K_RECORD_NAME = 'name';

    /**
     * The key in resource entities where availability rules are stored.
     *
     * May be a path, delimited by forward slashes.
     *
     * @since [*next-version*]
     */
    const K_ENTITY_SESSION_RULES = 'availability/rules';

    /**
     * The key in resource DB records where the timezone is stored.
     *
     * May be a path, delimited by forward slashes.
     *
     * @since [*next-version*]
     */
    const K_RECORD_TIMEZONE = 'timezone';

    /**
     * The key in resource entities where the timezone is stored.
     *
     * May be a path, delimited by forward slashes.
     *
     * @since [*next-version*]
     */
    const K_ENTITY_TIMEZONE = 'availability/timezone';

    /**
     * The key in resource DB records where the image ID is stored.
     *
     * May be a path, delimited by forward slashes.
     *
     * @since [*next-version*]
     */
    const K_RECORD_IMAGE_ID = 'data/imageId';

    /**
     * The key in resource entities where the image URL is stored.
     *
     * May be a path, delimited by forward slashes.
     *
     * @since [*next-version*]
     */
    const K_ENTITY_IMAGE_URL = 'data/imageUrl';

    /**
     * The query field to use for searching for resources by name.
     *
     * @since [*next-version*]
     */
    const RESOURCES_NAME_SEARCH_FIELD = 'search';

    /**
     * The default resource timezone for resources that do not explicitly have a timezone.
     *
     * @since [*next-version*]
     */
    const DEFAULT_TIMEZONE = 'UTC';

    /**
     * The session rules SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @var SelectCapableInterface
     */
    protected $rulesSelectRm;

    /**
     * The session rules INSERT resource model.
     *
     * @since [*next-version*]
     *
     * @var InsertCapableInterface
     */
    protected $rulesInsertRm;

    /**
     * The session rules UPDATE resource model.
     *
     * @since [*next-version*]
     *
     * @var UpdateCapableInterface
     */
    protected $rulesUpdateRm;

    /**
     * The session rules DELETE resource model.
     *
     * @since [*next-version*]
     *
     * @var DeleteCapableInterface
     */
    protected $rulesDeleteRm;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @param SelectCapableInterface $rulesSelectRm The session rules SELECT resource model.
     * @param InsertCapableInterface $rulesInsertRm The session rules INSERT resource model.
     * @param UpdateCapableInterface $rulesUpdateRm The session rules UPDATE resource model.
     * @param DeleteCapableInterface $rulesDeleteRm The session rules DELETE resource model.
     */
    public function __construct(
        SelectCapableInterface $selectRm,
        InsertCapableInterface $insertRm,
        UpdateCapableInterface $updateRm,
        DeleteCapableInterface $deleteRm,
        SelectCapableInterface $rulesSelectRm,
        InsertCapableInterface $rulesInsertRm,
        UpdateCapableInterface $rulesUpdateRm,
        DeleteCapableInterface $rulesDeleteRm,
        FactoryInterface $orderFactory,
        $exprBuilder
    ) {
        parent::__construct($selectRm, $insertRm, $updateRm, $deleteRm, $orderFactory, $exprBuilder);

        $this->rulesSelectRm = $rulesSelectRm;
        $this->rulesInsertRm = $rulesInsertRm;
        $this->rulesUpdateRm = $rulesUpdateRm;
        $this->rulesDeleteRm = $rulesDeleteRm;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function add($entity)
    {
        $id = parent::add($entity);

        // Update the session rules
        $this->_updateSessionRules($id, $entity);

        return $id;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function delete($id)
    {
        parent::delete($id);

        // Delete the session rules
        $this->rulesDeleteRm->delete($this->_createResourceIdExpression($id));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function set($id, $entity)
    {
        $this->update($id, $entity);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function update($id, $data)
    {
        $changeset = $this->_entityToRecord($data);

        try {
            $this->updateRm->update($changeset, $this->_createIdExpression($id), null, 1);
        } catch (InvalidArgumentException $exception) {
            // The change set is empty
        }

        // Update the session rules
        $this->_updateSessionRules($id, $data);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _recordToEntity($record)
    {
        $resource = $this->_normalizeArray($record);

        // Move timezone according to defined paths
        $tzPath   = $this->_getRecordTimezonePath();
        $timezone = $this->_arrayGetPath($resource, $tzPath, null);
        if ($timezone !== null) {
            $this->_arrayUnsetPath($resource, $tzPath);
            $this->_arraySetPath($resource, $this->_getEntityTimezonePath(), $timezone);
        }

        // Retrieve rules for resource
        $rules = $this->rulesSelectRm->select($this->_createResourceIdExpression($resource['id']));
        // Store rules in resource according to path
        $this->_arraySetPath($resource, $this->_getEntitySessionRulesPath(), $rules);

        $rDataPath = $this->_getRecordDataPath();
        $eDataPath = $this->_getEntityDataPath();
        // Get data from record
        // Unserialize the data if present
        $dataStr = $this->_arrayGetPath($resource, $rDataPath, null);
        $data    = ($dataStr !== null) ? unserialize($dataStr) : [];
        // Remove old data string and add new unserialized data
        $this->_arrayUnsetPath($resource, $rDataPath);
        $this->_arraySetPath($resource, $eDataPath, $data);

        // Get image ID from record
        $imageId = $this->_arrayGetPath($resource, $this->_getRecordImageIdPath(), null);
        // If found, set image URL in entity
        if ($imageId !== null) {
            $this->_arraySetPath($resource, $this->_getEntityImageUrlPath(), $this->_wpGetImageUrl($imageId));
        }

        return $resource;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _entityToRecord($entity)
    {
        $record = $this->_normalizeArray($entity);

        $this->_arrayUnsetPath($record, $this->_getEntitySessionRulesPath());
        $this->_arrayUnsetPath($record, $this->_getEntityImageUrlPath());

        // Move timezone out of availability to root of record
        $timezone = $this->_arrayGetPath($record, $this->_getEntityTimezonePath());
        $this->_arraySetPath($record, $this->_getRecordTimezonePath(), $timezone);
        $this->_arrayUnsetPath($record, $this->_getEntityTimezonePath());

        $dataPath = $this->_getEntityDataPath();

        $data = $this->_arrayGetPath($record, $dataPath, null);
        if ($data !== null) {
            $this->_arraySetPath($record, $dataPath, serialize($data));
        }

        $record = array_map($fn = function ($e) use (&$fn) {
            if (is_array($e)) {
                return array_filter(array_map($fn, $e));
            }

            return $e;
        }, $record);
        $record = array_filter($record);

        return $record;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _buildFieldQueryCompareExpression($key, $value)
    {
        if ($key === static::RESOURCES_NAME_SEARCH_FIELD) {
            $b = $this->exprBuilder;

            return $b->like(
                $b->var(static::K_RECORD_NAME),
                $b->lit('%' . $value . '%')
            );
        }

        return parent::_buildFieldQueryCompareExpression($key, $value);
    }

    /**
     * Retrieves the path for resource data in records.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] An array of path segments.
     */
    protected function _getRecordDataPath()
    {
        return explode('/', static::K_RECORD_DATA);
    }

    /**
     * Retrieves the path for resource data in entities.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] An array of path segments.
     */
    protected function _getEntityDataPath()
    {
        return explode('/', static::K_ENTITY_DATA);
    }

    /**
     * Retrieves the path for the session rules in entities.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] An array of path segments.
     */
    protected function _getEntitySessionRulesPath()
    {
        return explode('/', static::K_ENTITY_SESSION_RULES);
    }

    /**
     * Retrieves the path for the timezone in entities.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] An array of path segments.
     */
    protected function _getEntityTimezonePath()
    {
        return explode('/', static::K_ENTITY_TIMEZONE);
    }

    /**
     * Retrieves the path for the timezone in records.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] An array of path segments.
     */
    protected function _getRecordTimezonePath()
    {
        return explode('/', static::K_RECORD_TIMEZONE);
    }

    /**
     * Retrieves the DB record path for the image ID in records.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] An array of path segments.
     */
    protected function _getRecordImageIdPath()
    {
        return explode('/', static::K_RECORD_IMAGE_ID);
    }

    /**
     * Retrieves the entity path for the image URL in entities.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] An array of path segments.
     */
    protected function _getEntityImageUrlPath()
    {
        return explode('/', static::K_ENTITY_IMAGE_URL);
    }

    /**
     * Creates an expression for matching an entity by its resource ID.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $id The resource ID.
     *
     * @return LogicalExpressionInterface The expression.
     */
    protected function _createResourceIdExpression($id)
    {
        return $this->exprBuilder->eq(
            $this->exprBuilder->var('resource_id'),
            $this->exprBuilder->lit($id)
        );
    }

    /**
     * Updates the session rules for the resource.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable                         $id   The ID of the service.
     * @param array|stdClass|ArrayAccess|ContainerInterface $data The resource data.
     */
    protected function _updateSessionRules($id, $data)
    {
        $b = $this->exprBuilder;

        try {
            $rules = $this->_containerGetPath($data, $this->_getEntitySessionRulesPath());
        } catch (NotFoundExceptionInterface $exception) {
            $rules = [];
        }

        try {
            $timezone = $this->_containerGetPath($data, $this->_getEntityTimezonePath());
        } catch (NotFoundExceptionInterface $exception) {
            $timezone = static::DEFAULT_TIMEZONE;
        }

        $ruleIds = [];

        foreach ($rules as $_ruleData) {
            $_rule   = $this->_processSessionRuleData($id, $_ruleData, $timezone);
            $_ruleId = $this->_containerHas($_rule, 'id')
                ? $this->_containerGet($_rule, 'id')
                : null;

            // If rule has no ID, insert as a new rule
            if ($_ruleId === null) {
                $_newRuleIds = $this->rulesInsertRm->insert([$_rule]);
                $_ruleId     = $_newRuleIds[0];
            } else {
                // If rule has an ID, update the existing rule
                $_ruleExp = $b->eq(
                    $b->var('id'),
                    $b->lit($_ruleId)
                );

                $this->rulesUpdateRm->update($_rule, $_ruleExp);
            }

            $ruleIds[] = $_ruleId;
        }

        // Expression for matching the rules by their resource ID
        $deleteExpr = $b->eq($b->var('resource_id'), $b->lit($id));

        // If rules were added/updated, ignore them in the condition
        if (count($ruleIds) > 0) {
            $deleteExpr = $b->and(
                $deleteExpr,
                $b->not(
                    $b->in(
                        $b->var('id'),
                        $b->set($ruleIds)
                    )
                )
            );
        }

        // Delete the sessions rules according to the above condition
        $this->rulesDeleteRm->delete($deleteExpr);
    }

    /**
     * Processes the session rule data that was received in the request.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable      $resourceId The ID of the resource.
     * @param array|stdClass|Traversable $ruleData   The session rule data that was received.
     * @param string|Stringable          $serviceTz  The service timezone name.
     *
     * @return array|stdClass|ArrayAccess|ContainerInterface The processed session rule data.
     */
    protected function _processSessionRuleData($resourceId, $ruleData, $serviceTz)
    {
        $allDay = $this->_containerGet($ruleData, 'isAllDay');

        // Parse the service timezone name into a timezone object
        $timezoneName = $this->_normalizeString($serviceTz);
        $timezone     = empty($timezoneName) ? null : $this->_createDateTimeZone($timezoneName);

        // Get the start ISO 8601 string, parse it and normalize it to the beginning of the day if required
        $startIso8601  = $this->_containerGet($ruleData, 'start');
        $startDatetime = new DateTime($startIso8601, $timezone);

        // Get the end ISO 8601 string, parse it and normalize it to the end of the day if required
        $endIso8601  = $this->_containerGet($ruleData, 'end');
        $endDateTime = new DateTime($endIso8601, $timezone);

        $data = [
            'id'                  => $this->_containerHas($ruleData, 'id')
                ? $this->_containerGet($ruleData, 'id')
                : null,
            'resource_id'         => $resourceId,
            'start'               => $startDatetime->getTimestamp(),
            'end'                 => $endDateTime->getTimestamp(),
            'all_day'             => $allDay,
            'repeat'              => $this->_containerGet($ruleData, 'repeat'),
            'repeat_period'       => $this->_containerGet($ruleData, 'repeatPeriod'),
            'repeat_unit'         => $this->_containerGet($ruleData, 'repeatUnit'),
            'repeat_until'        => $this->_containerGet($ruleData, 'repeatUntil'),
            'repeat_until_period' => $this->_containerGet($ruleData, 'repeatUntilPeriod'),
            'repeat_until_date'   => strtotime($this->_containerGet($ruleData, 'repeatUntilDate')),
            'repeat_weekly_on'    => implode(',', $this->_containerGet($ruleData, 'repeatWeeklyOn')),
            'repeat_monthly_on'   => implode(',', $this->_containerGet($ruleData, 'repeatMonthlyOn')),
        ];

        $excludeDates = [];
        foreach ($this->_containerGet($ruleData, 'excludeDates') as $_excludeDate) {
            $excludeDates[] = $this->_processExcludeDate($_excludeDate, $timezone);
        }

        $data['exclude_dates'] = implode(',', $excludeDates);

        return $data;
    }

    /**
     * Processes an excluded date to transform it into a timestamp.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $excludeDate The exclude date string, in ISO8601 format.
     * @param DateTimeZone      $timezone    The service timezone.
     *
     * @return int|false The timestamp.
     */
    protected function _processExcludeDate($excludeDate, $timezone)
    {
        $datetime  = new DateTime($this->_normalizeString($excludeDate), $timezone);
        $timestamp = $datetime->getTimestamp();

        return $timestamp;
    }

    /**
     * Retrieves the URL for a WordPress image, by ID.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $id The ID of the image for which to retrieve the URL.
     *
     * @return null|string The URL, or null if no image with the given ID was found.
     */
    protected function _wpGetImageUrl($id)
    {
        $url = wp_get_attachment_url($id);

        return is_string($url) ? $url : null;
    }

    /**
     * Utility method for retrieving a deep value from an array using a path.
     *
     * @since [*next-version*]
     *
     * @param array      $array   The array.
     * @param array      $path    The path.
     * @param mixed|null $default The default value to return if a value does not exist at the given path.
     *
     * @return mixed
     */
    protected function _arrayGetPath(&$array, $path, $default = null)
    {
        $head = array_shift($path);

        if ($head === null || !array_key_exists($head, $array)) {
            return $default;
        }

        return count($path) > 0
            ? $this->_arrayGetPath($array[$head], $path, $default)
            : $array[$head];
    }

    /**
     * Utility method for setting a deep value in an array using a path.
     *
     * @since [*next-version*]
     *
     * @param array $array The array.
     * @param array $path  The path.
     */
    protected function _arraySetPath(&$array, $path, $value)
    {
        $head = array_shift($path);

        if ($head === null) {
            return;
        }

        if (count($path) > 0) {
            if (!isset($array[$head])) {
                $array[$head] = [];
            }

            $this->_arraySetPath($array[$head], $path, $value);

            return;
        }

        $array[$head] = $value;
    }

    /**
     * Utility method for removing a deep value in an array using a path.
     *
     * @since [*next-version*]
     *
     * @param array $array The array.
     * @param array $path  The path.
     */
    protected function _arrayUnsetPath(&$array, $path)
    {
        $head = array_shift($path);

        if ($head === null || !isset($array[$head])) {
            return;
        }

        if (count($path) > 0) {
            $this->_arrayUnsetPath($array[$head], $path);

            return;
        }

        unset($array[$head]);
    }
}
