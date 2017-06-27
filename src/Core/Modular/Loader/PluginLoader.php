<?php

namespace RebelCode\EddBookings\Core\Modular\Loader;

use Dhii\I18n\StringTranslatingTrait;
use Dhii\Modular\Loader\ModuleLoaderInterface;
use InvalidArgumentException;
use RebelCode\EddBookings\Core\Modular\Config\PluginConfigInterface;
use RebelCode\EddBookings\Core\Modular\Module\PluginInterface;

class PluginLoader implements ModuleLoaderInterface
{
    use StringTranslatingTrait;

    /**
     * The path to the WordPress plugin functions file.
     *
     * @since [*next-version*]
     */
    const WP_PLUGIN_FUNCTIONS_FILE = 'wp-admin/includes/plugin.php';

    /**
     * Any errors that occured during loading.
     *
     * @since[*next-version*]
     *
     * @var string[]
     */
    protected $loadErrors;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     */
    public function __construct()
    {
        $this->_ensurePluginFunctionsLoaded();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function load($plugin = array())
    {
        if (!$plugin instanceof PluginInterface) {
            throw new InvalidArgumentException();
        }

        $errors = $this->_checkDependencies($plugin);

        if (count($errors) === 0) {
            return;
        }

        foreach ($errors as $_error) {
            add_action('admin_notices', function () use ($_error) {
                printf('<div class="notice notice-error"><p>%s</p></div>', $_error);
            });
        }

        \deactivate_plugins($plugin->getKey());
    }

    /**
     * Checks if a plugin's dependencies are satisfied.
     *
     * @since[*next-version*]
     *
     * @param PluginInterface $plugin The plugin to check.
     *
     * @return array An array of dependencies that were not satisfied.
     */
    protected function _checkDependencies(PluginInterface $plugin)
    {
        $errors = array();

        foreach ($plugin->getDependencies() as $_dependency) {
            $_slug    = $_dependency[PluginConfigInterface::K_DEPENDENCY_SLUG];
            $_name    = $_dependency[PluginConfigInterface::K_DEPENDENCY_NAME];
            $_version = $_dependency[PluginConfigInterface::K_DEPENDENCY_VERSION];

            if (!$this->_isPluginActiveAtVersion($_slug, $_version)) {
                $errors[] = $this->_createMissingDependencyError($plugin, $_name, $_version);
            }
        }

        return $errors;
    }

    /**
     * Gets the version of a particular plugin, by slug.
     *
     * @since[*next-version*]
     *
     * @param string $slug The plugin slug.
     *
     * @return string|false The version of the plugin or false if the plugin was not not found or is inactive.
     */
    protected function _getPluginVersion($slug)
    {
        if (!\is_plugin_active($slug)) {
            return false;
        }

        $data    = \get_plugin_data($slug);
        $version = $data[static::K_PLUGIN_DATA_VERSION];

        return $version;
    }

    /**
     * Checks if a specific plugin, by slug, is activated and compares its version.
     *
     * @since[*next-version*]
     *
     * @param string $slug    The slug of the plugin.
     * @param string $version The minimum version required.
     *
     * @return bool
     */
    protected function _isPluginActiveAtVersion($slug, $version)
    {
        return ($pluginVersion = $this->_getPluginVersion($slug)) &&
            \version_compare($pluginVersion, $version, '>=');
    }

    /**
     * Creates an error message for a missing dependency error.
     *
     * @since[*next-version*]
     *
     * @param PluginInterface $plugin
     * @param $depName
     * @param $depVersion
     *
     * @return string
     */
    protected function _createMissingDependencyError(PluginInterface $plugin, $depName, $depVersion)
    {
        return $this->__('%1$s requires %2$s at version %3$s or later.', [
            $plugin->getName(),
            $depName,
            $depVersion
        ]);
    }

    /**
     * Ensures that the WordPress plugin functions are loaded.
     *
     * @since[*next-version*]
     *
     * @return $this
     */
    protected function _ensurePluginFunctionsLoaded()
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . static::WP_PLUGIN_FUNCTIONS_FILE;
        }

        return $this;
    }
}
