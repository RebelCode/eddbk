<?php

namespace RebelCode\Storage\Resource\WordPress\Module;

use Dhii\Config\ConfigFactoryInterface;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\InternalException;
use Dhii\Output\PlaceholderTemplateFactory;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use mysqli;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Expression\EntityFieldTerm;
use RebelCode\Modular\Module\AbstractBaseModule;
use RebelCode\Storage\Resource\WordPress\BookingsEntityManager;
use RebelCode\Storage\Resource\WordPress\ResourcesEntityManager;
use RebelCode\Storage\Resource\WordPress\Wpdb\BookingsSelectResourceModel;
use RebelCode\Storage\Resource\WordPress\Wpdb\BookingStatusWpdbSelectResourceModel;
use RebelCode\Storage\Resource\WordPress\Wpdb\SessionsSelectResourceModel;
use RebelCode\Storage\Resource\WordPress\Wpdb\SessionsWpdbInsertResourceModel;
use RebelCode\Storage\Resource\WordPress\Wpdb\UnbookedSessionsWpdbSelectResourceModel;
use RebelCode\Storage\Resource\WordPress\Wpdb\WpdbDeleteResourceModel;
use RebelCode\Storage\Resource\WordPress\Wpdb\WpdbInsertResourceModel;
use RebelCode\Storage\Resource\WordPress\Wpdb\WpdbSelectResourceModel;
use RebelCode\Storage\Resource\WordPress\Wpdb\WpdbUpdateResourceModel;

/**
 * The WordPress Bookings CQRS Module.
 *
 * @since [*next-version*]
 */
class WpBookingsCqrsModule extends AbstractBaseModule
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

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
        ConfigFactoryInterface $configFactory,
        ContainerFactoryInterface $containerFactory,
        ContainerFactoryInterface $compContainerFactory,
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
     * @throws InternalException If an error occurred while reading from the config file.
     */
    public function setup()
    {
        return $this->_setupContainer(
            $this->_loadPhpConfigFile(RC_WP_BOOKINGS_CQRS_MODULE_CONFIG_FILE),
            [
                /*==============================================================*
                 *   Booking RMs                                                |
                 *==============================================================*/

                /*
                 * The bookings entity manager.
                 *
                 * @since [*next-version*]
                 */
                'bookings_entity_manager' => function (ContainerInterface $c) {
                    return new BookingsEntityManager(
                        $c->get('bookings_select_rm'),
                        $c->get('bookings_insert_rm'),
                        $c->get('bookings_update_rm'),
                        $c->get('bookings_delete_rm'),
                        $c->get('booking_resources_insert_rm'),
                        $c->get('booking_resources_delete_rm'),
                        $c->get('sql_order_factory'),
                        $c->get('sql_expression_builder')
                    );
                },

                /*
                 * The SELECT resource model for bookings.
                 *
                 * @since [*next-version*]
                 */
                'bookings_select_rm'            => function (ContainerInterface $c) {
                    $joinsCfg   = $this->_normalizeArray($c->get('cqrs/bookings/select/joins'));
                    $joinArrays = array_map(function ($key) use ($c) {
                        return $c->get($key);
                    }, $joinsCfg);

                    $joins = [];
                    foreach ($joinArrays as $joinArray) {
                        $joins = array_merge($joins, $this->_normalizeArray($joinArray));
                    }

                    $fieldColumnMap = [];
                    foreach ($c->get('cqrs/bookings/select/field_column_map') as $_field => $_column) {
                        $fieldColumnMap[$_field] = is_array($_column)
                            ? new EntityFieldTerm($_column[0], $_column[1])
                            : $_column;
                    }

                    return new BookingsSelectResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('map_factory'),
                        $this->_normalizeArray($c->get('cqrs/bookings/select/tables')),
                        $fieldColumnMap,
                        $c->get('cqrs/booking_resources/table'),
                        $c->get('sql_expression_builder'),
                        $joins,
                        $c->get('bookings_select_rm_grouping')
                    );
                },

                /*
                 * The INSERT resource model for bookings.
                 *
                 * @since [*next-version*]
                 */
                'bookings_insert_rm'            => function (ContainerInterface $c) {
                    return new WpdbInsertResourceModel(
                        $c->get('wpdb'),
                        $c->get('cqrs/bookings/insert/table'),
                        $this->_normalizeArray($c->get('cqrs/bookings/insert/field_column_map')),
                        $c->get('cqrs/bookings/insert/insert_bulk')
                    );
                },

                /*
                 * The UPDATE resource model for bookings.
                 *
                 * @since [*next-version*]
                 */
                'bookings_update_rm'            => function (ContainerInterface $c) {
                    return new WpdbUpdateResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('cqrs/bookings/insert/table'),
                        $this->_normalizeArray($c->get('cqrs/bookings/insert/field_column_map'))
                    );
                },

                /*
                 * The DELETE resource model for bookings.
                 *
                 * @since [*next-version*]
                 */
                'bookings_delete_rm'            => function (ContainerInterface $c) {
                    return new WpdbDeleteResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('cqrs/bookings/insert/table'),
                        $this->_normalizeArray($c->get('cqrs/bookings/insert/field_column_map'))
                    );
                },

                /**
                 * The JOIN condition for bookings and their resources.
                 *
                 * @since [*next-version*]
                 */
                'bookings_select_rm_resources_join' => function (ContainerInterface $c) {
                    $exp = $c->get('sql_expression_builder');
                    $bkr = $c->get('cqrs/booking_resources/table');

                    return [
                        $bkr => $exp->eq(
                            $exp->ef('booking', 'id'),
                            $exp->ef($bkr, 'booking_id')
                        )
                    ];
                },

                /**
                 * The grouping for the bookings SELECT RM.
                 *
                 * @since [*next-version*]
                 */
                'bookings_select_rm_grouping' => function (ContainerInterface $c) {
                    $e = $c->get('sql_expression_builder');

                    return [$e->ef('booking', 'id')];
                },

                /*==============================================================*
                 *   Resources RMs                                              |
                 *==============================================================*/

                /*
                 * The resources entity manager.
                 *
                 * @since [*next-version*]
                 */
                'resources_entity_manager' => function (ContainerInterface $c) {
                    return new ResourcesEntityManager(
                        $c->get('resources_select_rm'),
                        $c->get('resources_insert_rm'),
                        $c->get('resources_update_rm'),
                        $c->get('resources_delete_rm'),
                        $c->get('session_rules_select_rm'),
                        $c->get('session_rules_insert_rm'),
                        $c->get('session_rules_update_rm'),
                        $c->get('session_rules_delete_rm'),
                        $c->get('sql_order_factory'),
                        $c->get('sql_expression_builder')
                    );
                },

                /*
                 * The SELECT resource model for resources.
                 *
                 * @since [*next-version*]
                 */
                'resources_select_rm'            => function (ContainerInterface $c) {
                    return new WpdbSelectResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('map_factory'),
                        $this->_normalizeArray($c->get('cqrs/resources/select/tables')),
                        $this->_normalizeArray($c->get('cqrs/resources/select/field_column_map')),
                        $this->_normalizeArray($c->get('cqrs/resources/select/joins'))
                    );
                },

                /*
                 * The INSERT resource model for resources.
                 *
                 * @since [*next-version*]
                 */
                'resources_insert_rm'            => function (ContainerInterface $c) {
                    return new WpdbInsertResourceModel(
                        $c->get('wpdb'),
                        $c->get('cqrs/resources/insert/table'),
                        $this->_normalizeArray($c->get('cqrs/resources/insert/field_column_map')),
                        $c->get('cqrs/resources/insert/insert_bulk')
                    );
                },

                /*
                 * The UPDATE resource model for resources.
                 *
                 * @since [*next-version*]
                 */
                'resources_update_rm'            => function (ContainerInterface $c) {
                    return new WpdbUpdateResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('cqrs/resources/insert/table'),
                        $this->_normalizeArray($c->get('cqrs/resources/insert/field_column_map'))
                    );
                },

                /*
                 * The DELETE resource model for resources.
                 *
                 * @since [*next-version*]
                 */
                'resources_delete_rm'            => function (ContainerInterface $c) {
                    return new WpdbDeleteResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('cqrs/resources/insert/table'),
                        $this->_normalizeArray($c->get('cqrs/resources/insert/field_column_map'))
                    );
                },

                /*==============================================================*
                 *   Booking-Resources RMs                                      |
                 *==============================================================*/

                /*
                 * The SELECT resource model for booking-resources.
                 *
                 * @since [*next-version*]
                 */
                'booking_resources_select_rm'            => function (ContainerInterface $c) {
                    return new WpdbSelectResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('map_factory'),
                        $this->_normalizeArray($c->get('cqrs/booking_resources/select/tables')),
                        $this->_normalizeArray($c->get('cqrs/booking_resources/select/field_column_map')),
                        $this->_normalizeArray($c->get('cqrs/booking_resources/select/joins'))
                    );
                },

                /*
                 * The INSERT resource model for booking-resources.
                 *
                 * @since [*next-version*]
                 */
                'booking_resources_insert_rm'            => function (ContainerInterface $c) {
                    return new WpdbInsertResourceModel(
                        $c->get('wpdb'),
                        $c->get('cqrs/booking_resources/insert/table'),
                        $this->_normalizeArray($c->get('cqrs/booking_resources/insert/field_column_map')),
                        $c->get('cqrs/booking_resources/insert/insert_bulk')
                    );
                },

                /*
                 * The UPDATE resource model for booking-resources.
                 *
                 * @since [*next-version*]
                 */
                'booking_resources_update_rm'            => function (ContainerInterface $c) {
                    return new WpdbUpdateResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('cqrs/booking_resources/insert/table'),
                        $this->_normalizeArray($c->get('cqrs/booking_resources/insert/field_column_map'))
                    );
                },

                /*
                 * The DELETE resource model for booking-resources.
                 *
                 * @since [*next-version*]
                 */
                'booking_resources_delete_rm'            => function (ContainerInterface $c) {
                    return new WpdbDeleteResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('cqrs/booking_resources/insert/table'),
                        $this->_normalizeArray($c->get('cqrs/booking_resources/insert/field_column_map'))
                    );
                },

                /*==============================================================*
                 *   Booking Status RMs                                         |
                 *==============================================================*/

                /*
                 * The SELECT resource model for booking statuses and their counts.
                 *
                 * @since [*next-version*]
                 */
                'booking_status_select_rm' => function(ContainerInterface $c) {
                    return new BookingStatusWpdbSelectResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('map_factory'),
                        $this->_normalizeArray($c->get('cqrs/bookings/select/tables')),
                        [
                            'status'       => 'status',
                            'status_count' => $c->get('sql_expression_builder')->fn(
                                'count', $c->get('sql_expression_builder')->ef('booking', 'status')
                            ),
                        ],
                        ['status'],
                        []
                    );
                },

                /*==============================================================*
                 *   Booking Transition Log RMs                                 |
                 *==============================================================*/

                /*
                 * The SELECT resource model for transition logs.
                 *
                 * @since [*next-version*]
                 */
                'transition_logs_select_rm' => function (ContainerInterface $c) {
                    return new WpdbSelectResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('map_factory'),
                        $this->_normalizeArray($c->get('cqrs/transition_logs/select/tables')),
                        $this->_normalizeArray($c->get('cqrs/transition_logs/select/field_column_map')),
                        $this->_normalizeArray($c->get('cqrs/transition_logs/select/joins'))
                    );
                },

                /*
                 * The INSERT resource model for transition logs.
                 *
                 * @since [*next-version*]
                 */
                'transition_logs_insert_rm' => function (ContainerInterface $c) {
                    return new WpdbInsertResourceModel(
                        $c->get('wpdb'),
                        $c->get('cqrs/transition_logs/insert/table'),
                        $this->_normalizeArray($c->get('cqrs/transition_logs/insert/field_column_map')),
                        $c->get('cqrs/transition_logs/insert/insert_bulk')
                    );
                },

                /*
                 * The UPDATE resource model for transition logs.
                 *
                 * @since [*next-version*]
                 */
                'transition_logs_update_rm' => function (ContainerInterface $c) {
                    return new WpdbUpdateResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('cqrs/transition_logs/update/table'),
                        $this->_normalizeArray($c->get('cqrs/transition_logs/update/field_column_map'))
                    );
                },

                /*
                 * The DELETE resource model for transition logs.
                 *
                 * @since [*next-version*]
                 */
                'transition_logs_delete_rm' => function (ContainerInterface $c) {
                    return new WpdbDeleteResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('cqrs/transition_logs/delete/table'),
                        $this->_normalizeArray($c->get('cqrs/transition_logs/delete/field_column_map'))
                    );
                },

                /*==============================================================*
                 *   Session RMs                                                |
                 *==============================================================*/

                /*
                 * The SELECT resource model for sessions.
                 *
                 * @since [*next-version*]
                 */
                'sessions_select_rm'            => function (ContainerInterface $c) {
                    $joinsCfg = $this->_normalizeArray($c->get('cqrs/sessions/select/joins'));
                    $joinArrays = array_map(function ($key) use ($c) {
                        return $c->get($key);
                    }, $joinsCfg);

                    $joins = [];
                    foreach ($joinArrays as $joinArray) {
                        $joins = array_merge($joins, $this->_normalizeArray($joinArray));
                    }

                    return new SessionsSelectResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('map_factory'),
                        $this->_normalizeArray($c->get('cqrs/sessions/select/tables')),
                        $this->_normalizeArray($c->get('cqrs/sessions/select/field_column_map')),
                        $c->get('sql_expression_builder'),
                        $joins
                    );
                },

                /**
                 * The JOIN condition for sessions and their resources.
                 *
                 * @since [*next-version*]
                 */
                'sessions_select_rm_resources_join' => function (ContainerInterface $c) {
                    $exp = $c->get('sql_expression_builder');
                    $ssr = $c->get('cqrs/session_resources/table');

                    return [
                        // Join with session_resources table
                        // On session.id = session_resources.session_id
                        $ssr => $exp->eq(
                            $exp->ef('session', 'id'),
                            $exp->ef($ssr, 'session_id')
                        )
                    ];
                },

                /*
                 * The SELECT resource model for unbooked sessions.
                 *
                 * @since [*next-version*]
                 */
                'unbooked_sessions_select_rm'   => function (ContainerInterface $c) {
                    $joinsCfg = $this->_normalizeArray($c->get('cqrs/unbooked_sessions/select/joins'));
                    $joinArrays = array_map(function ($key) use ($c) {
                        return $c->get($key);
                    }, $joinsCfg);

                    $joins = [];
                    foreach ($joinArrays as $joinArray) {
                        $joins = array_merge($joins, $this->_normalizeArray($joinArray));
                    }

                    return new UnbookedSessionsWpdbSelectResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('map_factory'),
                        $this->_normalizeArray($c->get('cqrs/unbooked_sessions/select/tables')),
                        $this->_normalizeArray($c->get('cqrs/unbooked_sessions/select/field_column_map')),
                        $joins,
                        $c->get('wp_unbooked_sessions_condition'),
                        $c->get('wp_unbooked_sessions_grouping_fields'),
                        $c->get('sql_expression_builder')
                    );
                },

                /*
                 * The condition for the unbooked sessions SELECT resource model.
                 *
                 * @since [*next-version*]
                 */
                'wp_unbooked_sessions_condition' => function (ContainerInterface $c) {
                    $b  = $c->get('sql_expression_builder');
                    $bt = $c->get('cqrs/bookings/table');

                    return $b->is(
                        $b->ef($bt, 'id'),
                        $b->lit(null)
                    );
                },

                /*
                 * The fields to group by for the unbooked sessions SELECT resource model.
                 *
                 * @since [*next-version*]
                 */
                'wp_unbooked_sessions_grouping_fields' => function (ContainerInterface $c) {
                    $e = $c->get('sql_expression_builder');

                    return [$e->ef('session', 'id')];
                },

                /*
                 * The join conditions for unbooked sessions SELECT resource model.
                 *
                 * @since [*next-version*]
                 */
                'unbooked_sessions_select_join_conditions' => function (ContainerInterface $c) {
                    // Expression builder
                    $exp = $c->get('sql_expression_builder');
                    // The table names
                    $bk  = $c->get('cqrs/bookings/table');
                    $br = $c->get('cqrs/booking_resources/table');
                    $sn = 'session';
                    // Booking start and end fields
                    $b_s = $exp->ef($bk, 'start');
                    $b_e = $exp->ef($bk, 'end');
                    // Session start and end fields
                    $s_s = $exp->ef($sn, 'start');
                    $s_e = $exp->ef($sn, 'end');

                    return [
                        // Join with booking table
                        $bk => $exp->and(
                            // With bookings that overlap
                            // (Booking starts during session period or session starts during booking period)
                            $exp->or(
                                $exp->and($exp->gte($b_s, $s_s), $exp->lt($b_s, $s_e)),
                                $exp->and($exp->gte($s_s, $b_s), $exp->lt($s_s, $b_e))
                            )
                        ),
                        // Join with booking resources table
                        $br => $exp->and(
                            // Booking ID from booking table and booking-resources table are equal
                            $exp->eq(
                                $exp->ef($bk, 'id'),
                                $exp->ef($br, 'booking_id')
                            ),
                            // The booking resource ID is in the session's resource ID list
                            $exp->fn(
                                'FIND_IN_SET',
                                $exp->ef($br, 'resource_id'),
                                $exp->ef($sn, 'resource_ids')
                            )
                        )
                    ];
                },

                /*
                 * The INSERT resource model for sessions.
                 *
                 * @since [*next-version*]
                 */
                'sessions_insert_rm'            => function (ContainerInterface $c) {
                    return new SessionsWpdbInsertResourceModel(
                        $c->get('wpdb'),
                        $c->get('cqrs/sessions/insert/table'),
                        $this->_normalizeArray($c->get('cqrs/sessions/insert/field_column_map')),
                        $c->get('cqrs/sessions/insert/insert_bulk')
                    );
                },

                /*
                 * The UPDATE resource model for sessions.
                 *
                 * @since [*next-version*]
                 */
                'sessions_update_rm'            => function (ContainerInterface $c) {
                    return new WpdbUpdateResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('cqrs/sessions/update/table'),
                        $this->_normalizeArray($c->get('cqrs/sessions/update/field_column_map'))
                    );
                },

                /*
                 * The DELETE resource model for sessions.
                 *
                 * @since [*next-version*]
                 */
                'sessions_delete_rm'            => function (ContainerInterface $c) {
                    return new WpdbDeleteResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('cqrs/sessions/delete/table'),
                        $this->_normalizeArray($c->get('cqrs/sessions/delete/field_column_map'))
                    );
                },

                /*==============================================================*
                 *   Session Rules RMs                                          |
                 *==============================================================*/

                /*
                 * The SELECT resource model for session rules.
                 *
                 * @since [*next-version*]
                 */
                'session_rules_select_rm'            => function (ContainerInterface $c) {
                    return new WpdbSelectResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('map_factory'),
                        $this->_normalizeArray($c->get('cqrs/session_rules/select/tables')),
                        $this->_normalizeArray($c->get('cqrs/session_rules/select/field_column_map')),
                        $this->_normalizeArray($c->get('cqrs/session_rules/select/joins'))
                    );
                },

                /*
                 * The INSERT resource model for session rules.
                 *
                 * @since [*next-version*]
                 */
                'session_rules_insert_rm'            => function (ContainerInterface $c) {
                    return new WpdbInsertResourceModel(
                        $c->get('wpdb'),
                        $c->get('cqrs/session_rules/insert/table'),
                        $this->_normalizeArray($c->get('cqrs/session_rules/insert/field_column_map')),
                        $c->get('cqrs/session_rules/insert/insert_bulk')
                    );
                },

                /*
                 * The UPDATE resource model for session rules.
                 *
                 * @since [*next-version*]
                 */
                'session_rules_update_rm'            => function (ContainerInterface $c) {
                    return new WpdbUpdateResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('cqrs/session_rules/update/table'),
                        $this->_normalizeArray($c->get('cqrs/session_rules/update/field_column_map'))
                    );
                },

                /*
                 * The DELETE resource model for session rules.
                 *
                 * @since [*next-version*]
                 */
                'session_rules_delete_rm'            => function (ContainerInterface $c) {
                    return new WpdbDeleteResourceModel(
                        $c->get('wpdb'),
                        $c->get('sql_expression_template'),
                        $c->get('cqrs/session_rules/delete/table'),
                        $this->_normalizeArray($c->get('cqrs/session_rules/delete/field_column_map'))
                    );
                },

                /*==============================================================*
                 *   Migration Services                                         |
                 *==============================================================*/

                /*
                 * The mysqli database connection instance.
                 *
                 * @since [*next-version*]
                 */
                'wp_bookings_mysqli'   => function (ContainerInterface $c) {
                    return new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                },

                /*
                 * The migrator instance.
                 *
                 * @since [*next-version*]
                 */
                'wp_bookings_migrator' => function (ContainerInterface $c) {
                    return new Migrator(
                        $c->get('wp_bookings_mysqli'),
                        RC_WP_BOOKINGS_CQRS_MIGRATIONS_DIR,
                        \get_option($c->get('wp_bookings_cqrs/migrations/db_version_option'), 0),
                        $c->get('wp_bookings_sql_placeholder_template_factory'),
                        $c
                    );
                },

                /*
                 * The SQL placeholder template factory - used to create SQL templates with placeholder tokens.
                 *
                 * @since [*next-version*]
                 */
                'wp_bookings_sql_placeholder_template_factory' => function (ContainerInterface $c) {
                    return new PlaceholderTemplateFactory(
                        'Dhii\Output\PlaceholderTemplate',
                        $c->get('wp_bookings_cqrs/migrations/placeholder_token_start'),
                        $c->get('wp_bookings_cqrs/migrations/placeholder_token_end'),
                        $c->get('wp_bookings_cqrs/migrations/placeholder_default_value')
                    );
                },

                /*
                 * The auto migrations handlers.
                 *
                 * @since [*next-version*]
                 */
                'wp_bookings_cqrs_auto_migrations_handler' => function (ContainerInterface $c) {
                    return new AutoMigrationsHandler(
                        $c->get('wp_bookings_migrator'),
                        $c->get('wp_bookings_cqrs/migrations/target_db_version'),
                        \get_option($c->get('wp_bookings_cqrs/migrations/db_version_option'), 0),
                        $c->get('event_manager'),
                        $c->get('event_factory')
                    );
                },

                /*
                 * The migration error notice handler.
                 *
                 * @since [*next-version*]
                 */
                'wp_bookings_migration_error_notice_handler' => function (ContainerInterface $c) {
                    return new MigrationErrorNoticeHandler(
                        $c->get('event_manager'),
                        $c->get('event_factory')
                    );
                },

                /*==============================================================*
                 *   Misc. Services                                             |
                 *==============================================================*/

                /*
                 * The callback that returns the ID of the user to use for new transition logs.
                 *
                 * @since [*next-version*]
                 */
                'transition_logs_user_id_callback' => function (ContainerInterface $c) {
                    return 'get_current_user_id';
                }
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        // Handler to auto migrate to the latest DB version
        $this->_attach('init', $c->get('wp_bookings_cqrs_auto_migrations_handler'));

        // Update the database version after migrating
        $this->_attach('wp_bookings_cqrs_after_migration', function (EventInterface $event) use ($c) {
            $target = $event->getParam('target');
            $option = $c->get('wp_bookings_cqrs/migrations/db_version_option');

            \update_option($option, $target);
        });

        // The migration error notice handler
        $this->_attach('wp_bookings_cqrs_on_migration_failed', $c->get('wp_bookings_migration_error_notice_handler'));
    }
}
