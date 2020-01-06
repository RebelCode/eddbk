<?php

namespace RebelCode\Bookings\WordPress\Module;

use Dhii\Collection\MapInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerGetPathCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\ContainerHasPathCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface;
use stdClass;
use Traversable;

/**
 * Container for retrieving plugin settings.
 *
 * @since [*next-version*]
 */
class SettingsContainer implements ContainerInterface
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetPathCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasPathCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use IteratorToArrayRecursiveCapableTrait;

    /**
     * Map of settings keys to their default values.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $defaultValues;

    /**
     * List of settings fields that should be serialized.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $arrayFields;

    /**
     * Setting option prefix.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $prefix;

    /**
     * SettingsStateHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|MapInterface $defaultValues Map of settings keys to their default values.
     * @param array|stdClass|Traversable  $arrayFields   List of settings fields that should be consumed as array.
     * @param string|Stringable           $prefix        Setting option prefix.
     */
    public function __construct($defaultValues, $arrayFields, $prefix)
    {
        $this->defaultValues = $defaultValues;
        $this->arrayFields   = $this->_normalizeArray($arrayFields);
        $this->prefix        = $prefix;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function get($id)
    {
        $key   = $this->_getFieldKey($id);
        $value = get_option($key) ?: $this->_containerGetPath($this->defaultValues, explode('/', $id));

        if (in_array($id, $this->arrayFields)) {
            $value = $this->_iteratorToArrayRecursive($value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function has($id)
    {
        return $this->_containerHasPath($this->defaultValues, explode('/', $id));
    }

    /**
     * Prepare setting option key from field name.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $field Field name to prepare key from.
     *
     * @return string Prepared setting option key from field name.
     */
    protected function _getFieldKey($field)
    {
        $field = $this->_normalizeString($field);

        return $this->prefix . '/' . $field;
    }
}
