<?php

namespace RebelCode\Expression;

use Dhii\Expression\Renderer\AbstractBaseSelfDelegateExpressionTemplate;
use Dhii\Expression\TermInterface;
use Psr\Container\ContainerInterface;
use RebelCode\Expression\Renderer\Sql\SqlGenericFunctionExpressionTemplate;

/**
 * A self-delegating master expression template.
 *
 * Given any template it delegates rendering to another template, retrieved by expression type from a container.
 *
 * @since [*next-version*]
 */
class SqlExpressionMasterTemplate extends AbstractBaseSelfDelegateExpressionTemplate
{
    /**
     * The key for the SQL generic function template.
     *
     * @since [*next-version*]
     */
    const K_GENERIC_FN_TEMPLATE = 'function';

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $container The delegate container.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->_setTermTypeRendererContainer($container);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getTermDelegateRenderer(TermInterface $term, $context = null)
    {
        $type = $term->getType();

        // Detect generic function prefix and use generic function template as the type
        if (stripos($type, SqlGenericFunctionExpressionTemplate::PREFIX) === 0) {
            $type = static::K_GENERIC_FN_TEMPLATE;
        }

        return $this->_getTermTypeRenderer($type);
    }
}
