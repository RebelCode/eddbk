<?php

namespace RebelCode\EddBookings\Services\Module;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\EventManager\EventInterface;
use stdClass;
use Traversable;
use wpdb;

/**
 * A handler that updates the session types to the new format after they have been migrated from session lengths.
 *
 * @since [*next-version*]
 */
class SessionTypesMigrationHandler implements InvocableInterface
{
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
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The meta key for service session types.
     *
     * @since [*next-version*]
     */
    const SESSION_TYPES_META_KEY = 'session_types';

    /**
     * The WordPress DB adapter.
     *
     * @since [*next-version*]
     *
     * @var wpdb
     */
    protected $wpdb;

    /**
     * The post type for services.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
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
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param wpdb              $wpdb       The WordPress DB adapter.
     * @param Stringable|string $postType   The post type for services.
     * @param Stringable|string $metaPrefix The services post meta prefix.
     */
    public function __construct($wpdb, $postType, $metaPrefix)
    {
        $this->wpdb       = $wpdb;
        $this->postType   = $postType;
        $this->metaPrefix = $metaPrefix;
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

        $records = $this->_getSessionTypesMeta();
        $metaKey = $this->metaPrefix . static::SESSION_TYPES_META_KEY;

        foreach ($records as $record) {
            $id       = $record['id'];
            $lengths  = unserialize($record['value']);
            $newTypes = array_map([$this, '_convertSessionTypeMeta'], $lengths);

            $this->_wpUpdatePostMeta($id, $metaKey, $newTypes);
        }
    }

    /**
     * Retrieves all the session type records for all services from the database.
     *
     * @since [*next-version*]
     *
     * @return array|null|object The session type records.
     */
    protected function _getSessionTypesMeta()
    {
        $wpdb       = $this->wpdb;
        $key        = $this->metaPrefix . static::SESSION_TYPES_META_KEY;
        $type       = $this->postType;
        $metaTable  = $wpdb->postmeta;
        $postsTable = $wpdb->posts;

        $query = $this->wpdb->prepare('
            SELECT p.ID AS `id`, pm.meta_value as `value` FROM %1$s pm
            LEFT JOIN %2$s p ON p.ID = pm.post_id
            WHERE pm.meta_key = "%3$s" 
            AND p.post_type = "%4$s"
        ', $metaTable, $postsTable, $key, $type);

        $results = $this->wpdb->get_results($query, ARRAY_A);

        return $results;
    }

    /**
     * Converts the given old format session type meta data into the new format.
     *
     * @since [*next-version*]
     *
     * @param array $meta The old format session type meta data.
     *
     * @return array The converted session type meta data.
     */
    protected function _convertSessionTypeMeta($meta)
    {
        // Detect lack of old key or presence of new key, to stop conversion if the meta is already up-to-date
        if (!$this->_containerHas($meta, 'sessionLength') || $this->_containerHas($meta, 'type')) {
            return $meta;
        }

        return [
            'id'    => null,
            'label' => '',
            'price' => $this->_containerGet($meta, 'price'),
            'type'  => 'fixed_duration',
            'data'  => [
                'duration' => $this->_containerGet($meta, 'sessionLength'),
            ],
        ];
    }

    /**
     * Updates the meta data for the post with a given ID.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable      $id    The ID of the post to update.
     * @param string|Stringable          $key   The meta key.
     * @param array|stdClass|Traversable $value The meta value.
     */
    protected function _wpUpdatePostMeta($id, $key, $value)
    {
        $id  = $this->_normalizeInt($id);
        $key = $this->_normalizeString($key);

        \update_post_meta($id, $key, $value);
    }
}
