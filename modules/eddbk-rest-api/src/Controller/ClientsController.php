<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Factory\FactoryAwareTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use EDD_DB_Customers;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * The API controller for clients.
 *
 * @since [*next-version*]
 */
class ClientsController extends AbstractBaseController implements ControllerInterface
{
    /* @since [*next-version*] */
    use FactoryAwareTrait {
        _getFactory as _getIteratorFactory;
        _setFactory as _setIteratorFactory;
    }

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The EDD Customers DB adapter.
     *
     * @since [*next-version*]
     *
     * @var EDD_DB_Customers
     */
    protected $eddCustomersDb;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param FactoryInterface $iteratorFactory The iterator factory to use for the results.
     * @param EDD_DB_Customers $customers       The EDD customers DB adapter.
     */
    public function __construct(FactoryInterface $iteratorFactory, EDD_DB_Customers $customers)
    {
        $this->_setIteratorFactory($iteratorFactory);
        $this->eddCustomersDb = $customers;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _get($params = [])
    {
        $queryArgs = $this->_generateEddCustomersQueryArgs($params);
        $customers = $this->eddCustomersDb->get_customers($queryArgs);

        return $this->_getIteratorFactory()->make([
            'items' => $customers,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _post($params = [])
    {
        $name  = $this->_containerGet($params, 'name');
        $email = $this->_containerGet($params, 'email');

        $eddCustomer = new \EDD_Customer($email);

        if (!empty($eddCustomer->id)) {
            return [];
        }

        $newClientData = [
            'name'  => $name,
            'email' => $email,
        ];
        // check if a WP user exists with this email
        $userId = email_exists($email);
        // Add to customer data to link the WP user with this EDD customer
        if ($userId !== false) {
            $newClientData['user_id'] = $userId;
        }
        // Attempt to create
        $newClientId = $eddCustomer->create($newClientData);

        return $this->_get(['id' => $newClientId]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _put($params = [])
    {
        throw $this->_createControllerException($this->__('Not implemented'), 405, null, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _patch($params = [])
    {
        throw $this->_createControllerException($this->__('Not implemented'), 405, null, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _delete($params = [])
    {
        throw $this->_createControllerException($this->__('Not implemented'), 405, null, $this);
    }

    /**
     * Generates the EDD customers query args from the params given.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The params.
     *
     * @return array
     */
    protected function _generateEddCustomersQueryArgs($params)
    {
        if ($this->_containerHas($params, 'search')) {
            return [
                'name'           => $this->_containerGet($params, 'search'),
                'search_columns' => ['name', 'email'],
            ];
        }

        return [
            'id'      => $this->_containerHas($params, 'id') ? $this->_containerGet($params, 'id') : null,
            'name'    => $this->_containerHas($params, 'name') ? $this->_containerGet($params, 'name') : null,
            'email'   => $this->_containerHas($params, 'email') ? $this->_containerGet($params, 'email') : null,
            'orderby' => 'id',
            'order'   => 'ASC',
        ];
    }
}
