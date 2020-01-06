<?php

namespace RebelCode\Storage\Resource\WordPress\Module;

use ArrayAccess;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Object\DataStoreAwareContainerTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Output\TemplateFactoryInterface;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use mysqli;
use Psr\Container\ContainerInterface;
use RuntimeException;
use stdClass;

/**
 * Performs database migrations through WPDB.
 *
 * @since [*next-version*]
 */
class Migrator
{
    /* @since [*next-version*] */
    use DataStoreAwareContainerTrait {
        _getDataStore as _getPlaceholderValues;
        _setDataStore as _setPlaceholderValues;
    }

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The filename for "up" migrations.
     *
     * @since [*next-version*]
     */
    const UP_MIGRATION_FILENAME = 'up.sql';

    /**
     * The filename for "down" migrations.
     *
     * @since [*next-version*]
     */
    const DOWN_MIGRATION_FILENAME = 'down.sql';

    /**
     * The direction value for migrating upwards (upgrading).
     *
     * @since [*next-version*]
     */
    const DIRECTION_UP = 1;

    /**
     * The direction value for migrating downwards (downgrading).
     *
     * @since [*next-version*]
     */
    const DIRECTION_DOWN = -1;

    /**
     * The migrations directory path.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $migrationsDir;

    /**
     * The current database version.
     *
     * @since [*next-version*]
     *
     * @var int|string|Stringable
     */
    protected $dbVersion;

    /**
     * The mysqli handle.
     *
     * @since [*next-version*]
     *
     * @var mysqli
     */
    protected $mysqli;

    /**
     * The template factory.
     *
     * @since [*next-version*]
     *
     * @var TemplateFactoryInterface|null
     */
    protected $templateFactory;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param mysqli                                        $mysqli            The mysqli handle.
     * @param string|Stringable                             $migrationsDir     The migrations directory path.
     * @param int|string|Stringable                         $dbVersion         The current database version.
     * @param TemplateFactoryInterface                      $templateFactory   The factory for creating SQL templates.
     * @param array|stdClass|ArrayAccess|ContainerInterface $placeholderValues The replacement values for placeholders
     *                                                                         in SQL templates.
     */
    public function __construct(
        $mysqli,
        $migrationsDir,
        $dbVersion,
        TemplateFactoryInterface $templateFactory,
        $placeholderValues
    ) {
        $this->mysqli = $mysqli;
        $this->_setMigrationsDir($migrationsDir);
        $this->_setDbVersion($dbVersion);
        $this->_setTemplateFactory($templateFactory);
        $this->_setPlaceholderValues($placeholderValues);
    }

    /**
     * Retrieves the mysqli handle.
     *
     * @since [*next-version*]
     *
     * @return mysqli The mysqli handle.
     */
    protected function _getMysqli()
    {
        return $this->mysqli;
    }

    /**
     * Sets the mysqli handle.
     *
     * @since [*next-version*]
     *
     * @param mysqli $mysqli The mysqli handle.
     */
    protected function _setMysqli($mysqli)
    {
        if (!($mysqli instanceof mysqli)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a mysqli handle'), null, null, $mysqli
            );
        }

        $this->mysqli = $mysqli;
    }

    /**
     * Retrieves the migrations directory path.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable The migrations directory path.
     */
    protected function _getMigrationsDir()
    {
        return $this->migrationsDir;
    }

    /**
     * Sets the migrations directory path.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $migrationsDir The migrations directory path.
     */
    protected function _setMigrationsDir($migrationsDir)
    {
        $this->migrationsDir = $this->_normalizeString($migrationsDir);
    }

    /**
     * Retrieves the current database version.
     *
     * @since [*next-version*]
     *
     * @return int|string|Stringable The current database version.
     */
    protected function _getDbVersion()
    {
        return $this->dbVersion;
    }

    /**
     * Sets the current database version.
     *
     * @since [*next-version*]
     *
     * @param int|string |Stringable $dbVersion The current database version.
     */
    protected function _setDbVersion($dbVersion)
    {
        $this->dbVersion = $this->_normalizeInt($dbVersion);
    }

    /**
     * Retrieves the template factory.
     *
     * @since [*next-version*]
     *
     * @return TemplateFactoryInterface|null The template factory instance.
     */
    protected function _getTemplateFactory()
    {
        return $this->templateFactory;
    }

    /**
     * Retrieves the template factory.
     *
     * @since [*next-version*]
     *
     * @param TemplateFactoryInterface|null $templateFactory The template factory instance.
     */
    protected function _setTemplateFactory($templateFactory)
    {
        if ($templateFactory !== null && !($templateFactory instanceof TemplateFactoryInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a template factory'), null, null, $templateFactory
            );
        }

        $this->templateFactory = $templateFactory;
    }

    /**
     * Performs database migration.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $target The migration target - can be a version, state, preset, etc.
     *
     * @throws InvalidArgumentException If the given target is invalid.
     * @throws RuntimeException If failed to migrate to the given target.
     */
    public function migrate($target)
    {
        $target     = $this->_normalizeInt($target);
        $current    = $this->_getDbVersion();
        $difference = $target - $current;

        // No migration needed
        if ($difference === 0) {
            return;
        }

        // Maximise the values to 0, since negative DB versions are not allowed
        $current = max(0, $current);
        $target  = max(0, $target);

        // Get direction, 1 for up and -1 for down
        $direction = (int) (absint($difference) / $difference);
        // Get the list of migration versions to run
        $migrations = ($direction === static::DIRECTION_UP)
            ? range($current + 1, $target)
            : range($current, $target + 1);
        // Determine the file names to look for, depending on migration direction
        $filename = ($direction === static::DIRECTION_UP)
            ? static::UP_MIGRATION_FILENAME
            : static::DOWN_MIGRATION_FILENAME;
        // The root migrations directory
        $directory = $this->_getMigrationsDir();

        foreach ($migrations as $_version) {
            $_path = implode(DIRECTORY_SEPARATOR, [$directory, sprintf('%1$s-%2$s', $_version, $filename)]);

            $this->_runMigrationFile($_path);
        }
    }

    /**
     * Runs the migration at a given file path.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $filePath The path to the migration file.
     *
     * @throws RuntimeException If failed to read the migration file or if the migration failed to execute.
     */
    protected function _runMigrationFile($filePath)
    {
        $mysqli = $this->_getMysqli();
        $mysqli->autocommit(false);

        $migrationSql = $this->_readSqlMigrationFile($filePath);
        $migrationSql = $this->_replaceSqlTokens($migrationSql, $this->_getPlaceholderValues());
        $sqlQueries   = explode(';', $migrationSql);
        $sqlQueries   = array_filter(array_map('trim', $sqlQueries));
        $errors       = [];

        $mysqli->begin_transaction();

        foreach ($sqlQueries as $query) {
            $result = $mysqli->query($query . ';');

            if (!(bool) $result) {
                $errors[] = $mysqli->error;
            }
        }

        // If errors occurred, roll back the database
        if (!empty($errors)) {
            $mysqli->rollback();

            throw $this->_createRuntimeException(implode("\n", $errors));
        }

        // If there were no errors, commit the transaction
        $mysqli->commit();
    }

    /**
     * Replaces placeholder tokens in the SQL.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable                             $sql    The SQL.
     * @param array|stdClass|ArrayAccess|ContainerInterface $values The placeholder values.
     *
     * @return string|Stringable The SQL with the replaced placeholder tokens.
     */
    protected function _replaceSqlTokens($sql, $values)
    {
        $factory = $this->_getTemplateFactory();

        if (!($factory instanceof TemplateFactoryInterface)) {
            throw $this->_createRuntimeException($this->__('Template factory is null'));
        }

        $template = $factory->make([
            TemplateFactoryInterface::K_TEMPLATE => $sql
        ]);

        return $template->render($values);
    }

    /**
     * Reads the SQL from a migration file.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $filePath The path to the migration file.
     *
     * @return string|Stringable The read SQL.
     *
     * @throws RuntimeException If failed to read the migration file.
     */
    protected function _readSqlMigrationFile($filePath)
    {
        $filePath = $this->_normalizeString($filePath);

        if (is_file($filePath) && is_readable($filePath)) {
            return file_get_contents($filePath);
        }

        throw $this->_createRuntimeException($this->__('Cannot read migration file "%s"', [$filePath]), null, null);
    }
}
