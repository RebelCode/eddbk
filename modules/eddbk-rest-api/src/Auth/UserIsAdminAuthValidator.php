<?php

namespace RebelCode\EddBookings\RestApi\Auth;

use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Validation\AbstractValidatorBase;
use Dhii\Validation\ValidatorInterface;

/**
 * An authorization validator that validates whether a user is an administrator.
 *
 * @since [*next-version*]
 */
class UserIsAdminAuthValidator extends AbstractValidatorBase implements ValidatorInterface
{
    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /**
     * The WordPress capability that determines if a user is an admin.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $adminCapability;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $adminCapability The WordPress capability that determines if a user is an admin.
     */
    public function __construct($adminCapability)
    {
        $this->adminCapability = $adminCapability;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getValidationErrors($request)
    {
        $userId = $this->_getCurrentUserId();

        // If user is not logged in, return with a single error
        if ($userId === 0) {
            return [
                $this->__('Not a user or not logged in')
            ];
        }

        // If user is an admin, return without errors
        if ($this->_isUserAdmin($this->_getCurrentUserId())) {
            return [];
        }

        // Otherwise, return with error
        return [
            $this->__('User is not an administrator')
        ];
    }

    /**
     * Retrieves the currently logged in WordPress user's ID.
     *
     * @since [*next-version*]
     *
     * @return int The current user ID, or `0` if not logged in.
     */
    protected function _getCurrentUserId()
    {
        return get_current_user_id();
    }

    /**
     * Checks if a WordPress user is an admin, by ID.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $userId The ID of the user to check.
     *
     * @return bool True if the user is a WordPress admin, false if not.
     */
    protected function _isUserAdmin($userId)
    {
        return $this->_wpUserCan($userId, $this->_normalizeString($this->adminCapability));
    }

    /**
     * Checks if a WordPress user, by ID, has a specific capability.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $userId     The ID of the user to check.
     * @param string|Stringable     $capability The capability to check for.
     *
     * @return bool True if the user has the capability, false if not.
     */
    protected function _wpUserCan($userId, $capability)
    {
        return user_can($userId, $capability);
    }
}
