<?php

namespace RebelCode\Storage\Resource\WordPress\Module;

use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Factory\GenericCallbackFactory;
use Dhii\Storage\Resource\Sql\Expression\SqlLogicalTypeInterface as SqlLogType;
use Dhii\Storage\Resource\Sql\Expression\SqlOperatorInterface as SqlOp;
use Dhii\Storage\Resource\Sql\Expression\SqlRelationalTypeInterface as SqlRelType;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface;
use RebelCode\Expression\Builder\ExpressionBuilder;
use RebelCode\Expression\EntityFieldTerm;
use RebelCode\Expression\Expression;
use RebelCode\Expression\LiteralTerm;
use RebelCode\Expression\LogicalExpression;
use RebelCode\Expression\Renderer\Sql\SqlBetweenExpressionTemplate;
use RebelCode\Expression\Renderer\Sql\SqlFunctionExpressionTemplate;
use RebelCode\Expression\Renderer\Sql\SqlGenericFunctionExpressionTemplate;
use RebelCode\Expression\Renderer\Sql\SqlLiteralTermTemplate;
use RebelCode\Expression\Renderer\Sql\SqlOperatorExpressionTemplate;
use RebelCode\Expression\Renderer\Sql\SqlReferenceTermTemplate;
use RebelCode\Expression\SqlExpressionMasterTemplate;
use RebelCode\Expression\VariableTerm;
use RebelCode\Modular\Module\AbstractBaseModule;
use RebelCode\Storage\Resource\Sql\Order;

/**
 * WordPress CQRS module class.
 *
 * @since [*next-version*]
 */
class WpCqrsModule extends AbstractBaseModule
{
    /*
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /*
     * @since [*next-version*]
     */
    use ContainerHasCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeKeyCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable           $key                  The module key.
     * @param array|string[]|Stringable[] $dependencies         The module dependencies.
     * @param ContainerFactoryInterface   $configFactory        The config factory.
     * @param ContainerFactoryInterface   $containerFactory     The container factory.
     * @param ContainerFactoryInterface   $compContainerFactory The composite container factory.
     */
    public function __construct(
        $key,
        $dependencies,
        ContainerFactoryInterface $configFactory,
        ContainerFactoryInterface $containerFactory,
        ContainerFactoryInterface $compContainerFactory
    ) {
        $this->_initModule($key, $dependencies, $configFactory, $containerFactory, $compContainerFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function setup()
    {
        global $wpdb;

        return $this->_setupContainer(
            [   // Config
                'wpdb_prefix' => $wpdb->prefix,
            ],
            [
                // Services

                /*
                 * The WordPress database connection adapter.
                 *
                 * @since [*next-version*]
                 */
                'wpdb' => function (ContainerInterface $c) use ($wpdb) {
                    return $wpdb;
                },

                /*==============================================================*
                 *   SQL Expression Rendering                                   |
                 *==============================================================*/

                /*
                 * Master SQL expression template.
                 *
                 * @since [*next-version*]
                 */
                'sql_expression_template' => function (ContainerInterface $c) {
                    return new SqlExpressionMasterTemplate($c->get('sql_expression_template_container'));
                },

                /*
                 * The container of SQL expression templates
                 *
                 * @since [*next-version*]
                 */
                'sql_expression_template_container' => function (ContainerInterface $c) {
                    $templates = [
                        'literal' => function (ContainerInterface $c) {
                            return new SqlLiteralTermTemplate();
                        },
                        'set' => function (ContainerInterface $c) {
                            return new SqlFunctionExpressionTemplate('', $c->get('sql_expression_template'));
                        },
                        'variable' => function (ContainerInterface $c) {
                            return new SqlReferenceTermTemplate();
                        },
                        'entity_field' => function (ContainerInterface $c) {
                            return new SqlReferenceTermTemplate();
                        },
                        'is' => function (ContainerInterface $c) {
                            return new SqlOperatorExpressionTemplate('IS', $c->get('sql_expression_template'));
                        },
                        SqlExpressionMasterTemplate::K_GENERIC_FN_TEMPLATE => function (ContainerInterface $c) {
                            return new SqlGenericFunctionExpressionTemplate($c->get('sql_expression_template'));
                        },
                        SqlLogType::T_AND => function (ContainerInterface $c) {
                            return new SqlOperatorExpressionTemplate(
                                SqlOp::OP_AND, $c->get('sql_expression_template'));
                        },
                        SqlLogType::T_OR => function (ContainerInterface $c) {
                            return new SqlOperatorExpressionTemplate(
                                SqlOp::OP_OR, $c->get('sql_expression_template'));
                        },
                        SqlLogType::T_NOT => function (ContainerInterface $c) {
                            return new SqlFunctionExpressionTemplate(
                                SqlOp::OP_NOT, $c->get('sql_expression_template')
                            );
                        },
                        SqlRelType::T_EQUAL_TO => function (ContainerInterface $c) {
                            return new SqlOperatorExpressionTemplate(
                                SqlOp::OP_EQUAL_TO, $c->get('sql_expression_template')
                            );
                        },
                        SqlRelType::T_GREATER_THAN => function (ContainerInterface $c) {
                            return new SqlOperatorExpressionTemplate(
                                SqlOp::OP_GREATER_THAN, $c->get('sql_expression_template')
                            );
                        },
                        SqlRelType::T_GREATER_EQUAL_TO => function (ContainerInterface $c) {
                            return new SqlOperatorExpressionTemplate(
                                SqlOp::OP_GREATER_EQUAL_TO, $c->get('sql_expression_template')
                            );
                        },
                        SqlRelType::T_LESS_THAN => function (ContainerInterface $c) {
                            return new SqlOperatorExpressionTemplate(
                                SqlOp::OP_SMALLER, $c->get('sql_expression_template')
                            );
                        },
                        SqlRelType::T_LESS_EQUAL_TO => function (ContainerInterface $c) {
                            return new SqlOperatorExpressionTemplate(
                                SqlOp::OP_SMALLER_EQUAL_TO, $c->get('sql_expression_template')
                            );
                        },
                        SqlRelType::T_LIKE => function (ContainerInterface $c) {
                            return new SqlOperatorExpressionTemplate(
                                SqlOp::OP_LIKE, $c->get('sql_expression_template')
                            );
                        },
                        SqlRelType::T_IN => function (ContainerInterface $c) {
                            return new SqlOperatorExpressionTemplate(
                                SqlOp::OP_IN, $c->get('sql_expression_template'));
                        },
                        SqlRelType::T_BETWEEN => function (ContainerInterface $c) {
                            return new SqlBetweenExpressionTemplate($c->get('sql_expression_template'));
                        },
                        SqlRelType::T_EXISTS => function (ContainerInterface $c) {
                            return new SqlFunctionExpressionTemplate(
                                SqlOp::OP_EXISTS, $c->get('sql_expression_template')
                            );
                        },
                    ];

                    return $c->get('container_factory')->make([ContainerFactoryInterface::K_DATA => $templates]);
                },

                /*==============================================================*
                 *   SQL Expression Builder                                     |
                 *==============================================================*/

                /*
                 * SQL expression builder.
                 *
                 * @since [*next-version*]
                 */
                'sql_expression_builder' => function (ContainerInterface $c) {
                    return new ExpressionBuilder($c->get('sql_expression_builder_factories'));
                },

                /*
                 * The SQL expression builder factory mapping (called magic method name to factory service key).
                 *
                 * @since [*next-version*]
                 */
                'sql_expression_builder_factories' => function (ContainerInterface $c) {
                    return [
                        'lit'  => $c->get('sql_literal_expression_builder_factory'),
                        'var'  => $c->get('sql_variable_expression_builder_factory'),
                        'ef'   => $c->get('sql_entity_field_expression_builder_factory'),
                        'is'   => $c->get('sql_is_expression_builder_factory'),
                        'and'  => $c->get('sql_and_expression_builder_factory'),
                        'or'   => $c->get('sql_or_expression_builder_factory'),
                        'not'  => $c->get('sql_not_expression_builder_factory'),
                        'like' => $c->get('sql_like_expression_builder_factory'),
                        'eq'   => $c->get('sql_equal_to_expression_builder_factory'),
                        'gt'   => $c->get('sql_greater_than_expression_builder_factory'),
                        'gte'  => $c->get('sql_greater_equal_to_expression_builder_factory'),
                        'lt'   => $c->get('sql_less_than_expression_builder_factory'),
                        'lte'  => $c->get('sql_less_equal_to_expression_builder_factory'),
                        'in'   => $c->get('sql_in_expression_builder_factory'),
                        'set'  => $c->get('sql_set_expression_builder_factory'),
                        'fn'   => $c->get('sql_fn_expression_builder_factory')
                    ];
                },
                /*
                 * The SQL literal expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_literal_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');

                        return new LiteralTerm($arguments[0], 'literal');
                    });
                },
                /*
                 * The SQL literal set expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_set_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) use ($c) {
                        $arguments = $this->_containerGet($config, 'arguments');
                        $values = [];

                        if (count($arguments) > 0) {
                            $values = is_array($arguments[0])
                                ? $arguments[0]
                                : $arguments;
                        }

                        $terms = array_map(function ($value) use ($c) {
                            return $c->get('sql_expression_builder')->lit($value);
                        }, $values);

                        return new Expression($terms, 'set');
                    });
                },
                /*
                 * The SQL variable expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_variable_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');

                        return new VariableTerm($arguments[0], 'variable');
                    });
                },
                /*
                 * The SQL entity field expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_entity_field_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');

                        return new EntityFieldTerm($arguments[0], $arguments[1], 'entity_field');
                    });
                },
                /*
                 * The SQL IS expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_is_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');

                        return new LogicalExpression($arguments, false, 'is');
                    });
                },
                /*
                 * The SQL AND expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_and_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');

                        return new LogicalExpression($arguments, false, SqlLogType::T_AND);
                    });
                },
                /*
                 * The SQL OR expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_or_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');

                        return new LogicalExpression($arguments, false, SqlLogType::T_OR);
                    });
                },
                /*
                 * The SQL NOT expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_not_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');

                        return new LogicalExpression($arguments, false, SqlLogType::T_NOT);
                    });
                },
                /*
                 * The SQL LIKE expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_like_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');
                        $negation = is_bool($arguments[0])
                            ? $arguments[0]
                            : false;
                        $terms = is_bool($arguments[0])
                            ? array_slice($arguments, 1)
                            : $arguments;

                        return new LogicalExpression($terms, $negation, SqlLogType::T_LIKE);
                    });
                },
                /*
                 * The SQL "equal to" expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_equal_to_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');
                        $negation = is_bool($arguments[0])
                            ? $arguments[0]
                            : false;
                        $terms = is_bool($arguments[0])
                            ? array_slice($arguments, 1)
                            : $arguments;

                        return new LogicalExpression($terms, $negation, SqlLogType::T_EQUAL_TO);
                    });
                },
                /*
                 * The SQL "greater than" expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_greater_than_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');

                        return new LogicalExpression($arguments, false, SqlLogType::T_GREATER_THAN);
                    });
                },
                /*
                 * The SQL "greater than or equal to" expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_greater_equal_to_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');

                        return new LogicalExpression($arguments, false, SqlLogType::T_GREATER_EQUAL_TO);
                    });
                },
                /*
                 * The SQL "less than" expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_less_than_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');

                        return new LogicalExpression($arguments, false, SqlLogType::T_LESS_THAN);
                    });
                },
                /*
                 * The SQL "less than or equal to" expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_less_equal_to_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');

                        return new LogicalExpression($arguments, false, SqlLogType::T_LESS_EQUAL_TO);
                    });
                },
                /*
                 * The SQL "IN" expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_in_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');

                        return new LogicalExpression($arguments, false, SqlLogType::T_IN);
                    });
                },
                /*
                 * The SQL generic function expression builder factory service.
                 *
                 * @since [*next-version*]
                 */
                'sql_fn_expression_builder_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $arguments = $this->_containerGet($config, 'arguments');

                        if (count($arguments) === 0) {
                            throw $this->_createOutOfRangeException(
                                $this->__('Must have at least a function name argument for SQL function expression factory'),
                                null, null, $arguments
                            );
                        }

                        $type = 'sql_fn_' . $arguments[0];
                        $terms = array_slice($arguments, 1);

                        return new Expression($terms, $type);
                    });
                },

                /*==============================================================*
                 *   Misc. SQL Factories                                        |
                 *==============================================================*/

                /*
                 * The factory that creates SQL OrderInterface instances.
                 *
                 * @since [*next-version*]
                 */
                'sql_order_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) {
                        $field = $this->_containerGet($config, 'field');
                        $entity = $this->_containerHas($config, 'entity')
                            ? $this->_containerGet($config, 'entity')
                            : null;
                        $ascending = $this->_containerHas($config, 'ascending')
                            ? $this->_containerGet($config, 'ascending')
                            : true;

                        return new Order($entity, $field, $ascending);
                    });
                },
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
    }
}
