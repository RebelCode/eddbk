<?php

namespace RebelCode\EddBookings\Services\Module;

use Dhii\Config\ConfigFactoryInterface;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\InternalException;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Module\AbstractBaseModule;

/**
 * Module class.
 *
 * @since [*next-version*]
 */
class EddBkServicesModule extends AbstractBaseModule
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable         $key                  The module key.
     * @param string[]|Stringable[]     $dependencies         The module dependencies.
     * @param ConfigFactoryInterface    $configFactory        The config factory.
     * @param ContainerFactoryInterface $containerFactory     The container factory.
     * @param ContainerFactoryInterface $compContainerFactory The composite container factory.
     * @param EventManagerInterface     $eventManager         The event manager.
     * @param EventFactoryInterface     $eventFactory         The event factory.
     */
    public function __construct(
        $key,
        $dependencies,
        $configFactory,
        $containerFactory,
        $compContainerFactory,
        $eventManager,
        $eventFactory
    ) {
        $this->_initModule($key, $dependencies, $configFactory, $containerFactory, $compContainerFactory);
        $this->_initModuleEvents($eventManager, $eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @throws InternalException If an error occurred while reading the config file.
     */
    public function setup()
    {
        return $this->_setupContainer(
            $this->_loadPhpConfigFile(RCMOD_EDDBK_SERVICES_CONFIG_FILE),
            $this->_loadPhpConfigFile(RCMOD_EDDBK_SERVICES_SERVICES_FILE)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        if ($c === null) {
            return;
        }

        $this->_attachMigrationHandlers($c);

        // Event for deleting service-related entities when a Download is deleted
        $this->_attach('before_delete_post', $c->get('eddbk_admin_delete_service_handler'));

        // Event for the filtering a Download's price
        $this->_attach('edd_get_download_price', $c->get('eddbk_get_service_price_handler'));

        // Event for the filtering a Download's price options
        $this->_attach('edd_get_variable_prices', $c->get('eddbk_get_service_price_options_handler'));

        // Event for the filtering a Download's price options flag
        $this->_attach('edd_has_variable_prices', $c->get('eddbk_get_service_has_price_options_handler'));

        // Event for filtering the query to hide services from the Downloads list
        $this->_attach('parse_query', $c->get('eddbk_hide_services_from_downloads_list_handler'));

        // Filter the Download status counts on the list page to remove service counts from them
        $this->_attach('wp_count_posts', function (EventInterface $event) use ($c) {
            if ($event->getParam(1) !== 'download') {
                return;
            }

            $counts   = $event->getParam(0);
            $services = $c->get('eddbk_services_manager')->query();

            foreach ($services as $_service) {
                $counts->{$_service['status']}--;
            }

            return $counts;
        });

        // Remove the "Mine" status filter that shows up on the Downloads list page when the above filter is applied
        $this->_attach('views_edit-download', function (EventInterface $event) {
            $views = $event->getParam(0);
            unset($views['mine']);
            $event->setParams([0 => $views]);
        });
    }

    /**
     * Attaches the migration handlers.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c The container from which to retrieve event handlers.
     */
    protected function _attachMigrationHandlers(ContainerInterface $c)
    {
        // Event for after migrating to DB version 3 (changes session lengths to session types)
        $this->_attach('wp_bookings_cqrs_after_up_migration_to_3', $c->get('eddbk_services_session_types_migration_3_handler'));
    }
}
