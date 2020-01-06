<?php

namespace RebelCode\Storage\Resource\WordPress\Module;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeStringableCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\EventManager\EventInterface;

class UpdateDbVersionHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use NormalizeStringableCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    protected $dbVersionOptionName;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $dbVersionOptionName
     */
    public function __construct($dbVersionOptionName)
    {
        $this->dbVersionOptionName = $this->_normalizeStringable($dbVersionOptionName);
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
                $this->__('Argument is not an event instance')
            );
        }

        $target = $event->getParam('target');
        $option = $c->get('wp_bookings_cqrs/migrations/db_version_option');

        \update_option($option, $target);
    }
}
