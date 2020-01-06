<?php

namespace RebelCode\Storage\Resource\WordPress\Module;

use Dhii\Event\EventFactoryInterface;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;

/**
 * The handler for invoking automatic database migrations.
 *
 * @since [*next-version*]
 */
class AutoMigrationsHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use EventsConsumerTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /**
     * The migrator instance.
     *
     * @since [*next-version*]
     *
     * @var Migrator
     */
    protected $migrator;

    /**
     * The DB version to migrate to.
     *
     * @since [*next-version*]
     *
     * @var int|string|Stringable
     */
    protected $targetVersion;

    /**
     * The current DB version.
     *
     * @since [*next-version*]
     *
     * @var int|string|Stringable
     */
    protected $currentVersion;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param Migrator              $migrator       The migrator instance.
     * @param Stringable|int|string $targetVersion  The target DB version to migrate to.
     * @param Stringable|int|string $currentVersion The current DB version.
     * @param EventManagerInterface $eventManager   The event manager instance.
     * @param EventFactoryInterface $eventFactory   The event factory instance for creating events.
     */
    public function __construct(Migrator $migrator, $targetVersion, $currentVersion, $eventManager, $eventFactory)
    {
        $this->_setMigrator($migrator);
        $this->_setTargetVersion($targetVersion);
        $this->_setCurrentVersion($currentVersion);
        $this->_setEventManager($eventManager);
        $this->_setEventFactory($eventFactory);
    }

    /**
     * Retrieves the migrator instance.
     *
     * @since [*next-version*]
     *
     * @return Migrator The migrator instance.
     */
    protected function _getMigrator()
    {
        return $this->migrator;
    }

    /**
     * Sets the migrator instance.
     *
     * @since [*next-version*]
     *
     * @param Migrator $migrator The migrator instance.
     */
    protected function _setMigrator($migrator)
    {
        $this->migrator = $migrator;
    }

    /**
     * Retrieves the target DB migration version.
     *
     * @since [*next-version*]
     *
     * @return int|string|Stringable The target DB migration version.
     */
    protected function _getTargetVersion()
    {
        return $this->targetVersion;
    }

    /**
     * Sets the target DB migration version.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $targetVersion The target DB migration version.
     */
    protected function _setTargetVersion($targetVersion)
    {
        $this->targetVersion = $this->_normalizeString($targetVersion);
    }

    /**
     * Retrieves the current DB version.
     *
     * @since [*next-version*]
     *
     * @return int|string|Stringable The current DB version.
     */
    protected function _getCurrentVersion()
    {
        return $this->currentVersion;
    }

    /**
     * Sets the current DB version.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $currentVersion The current DB version.
     */
    protected function _setCurrentVersion($currentVersion)
    {
        $this->currentVersion = $this->_normalizeString($currentVersion);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $curr = $this->_normalizeInt($this->_getCurrentVersion());
        $ver  = $this->_normalizeInt($this->_getTargetVersion());
        $diff = $ver - $curr;
        $dir  = ($diff > 0) ? 'up' : 'down';

        $params   = ['target' => $ver, 'current' => $curr];
        $migrator = $this->_getMigrator();

        try {
            if ($diff !== 0) {
                // Trigger "before" events for:
                // - all migrations
                // - this specific direction
                // - this specific direction and target
                // - this specific direction and target from the current version
                $this->_trigger('wp_bookings_cqrs_before_migration', $params);
                $this->_trigger(sprintf('wp_bookings_cqrs_before_%s_migration', $dir), $params);
                $this->_trigger(sprintf('wp_bookings_cqrs_before_%s_migration_to_%d', $dir, $ver), $params);
                $this->_trigger(sprintf('wp_bookings_cqrs_before_migration_from_%d_to_%d', $curr, $ver), $params);
            }

            // Migrate
            $migrator->migrate($ver);

            if ($diff !== 0) {
                // Trigger "after" events (in reverse order) for:
                // - all migrations
                // - this specific direction
                // - this specific direction and target
                // - this specific direction and target from the current version
                $this->_trigger(sprintf('wp_bookings_cqrs_after_migration_from_%d_to_%d', $curr, $ver), $params);
                $this->_trigger(sprintf('wp_bookings_cqrs_after_%s_migration_to_%d', $dir, $ver), $params);
                $this->_trigger(sprintf('wp_bookings_cqrs_after_%s_migration', $dir), $params);
                $this->_trigger('wp_bookings_cqrs_after_migration', $params);
            }
        } catch (Exception $exception) {
            $this->_trigger('wp_bookings_cqrs_on_migration_failed', $params + ['exception' => $exception]);
        }
    }
}
