<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Collection\MapInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\EventManager\EventInterface;
use stdClass;
use Traversable;

/**
 * Handler for adding transitions information to state on bookings page.
 * It will add map of available statuses transitions and transitions translations.
 *
 * @since [*next-version*]
 */
class BookingsStateStatusTransitionsHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * Map of transitions that should be applied when changing status.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $statusTransitions;

    /**
     * Map of status key to map of transition keys to labels for transitions.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $statusesTransitionsLabels;

    /**
     * Map of status key to list of hidden transition keys for given status.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $hiddenStatusesTransitions;

    /**
     * StatusesHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|MapInterface $statusTransitions         Map of transitions for changing statuses.
     * @param array|stdClass|MapInterface $statusesTransitionsLabels Map of status key to map of transition keys to labels for transitions.
     * @param array|stdClass|MapInterface $hiddenStatusesTransitions Map of status key to list of hidden transition keys for given status.
     */
    public function __construct(
        $statusTransitions,
        $statusesTransitionsLabels,
        $hiddenStatusesTransitions
    ) {
        $this->statusTransitions         = $statusTransitions;
        $this->statusesTransitionsLabels = $statusesTransitionsLabels;
        $this->hiddenStatusesTransitions = $hiddenStatusesTransitions;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        /* @var $event EventInterface */
        $event = func_get_arg(0);

        if (!($event instanceof EventInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an event instance'), null, null, $event
            );
        }

        $event->setParams(array_merge(
            $event->getParams(),
            $this->_getTransitionParams()
        ));
    }

    /**
     * Get transitions related parameters to attach on event.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function _getTransitionParams()
    {
        return [
            /*
             * Transitions for changing booking status
             */
            'statusTransitions' => $this->_prepareStatusTransitions($this->statusTransitions),

            /*
             * Map of transitions keys to transition labels
             */
            'statusesTransitionsLabels' => $this->_prepareTransitionsLabels($this->statusesTransitionsLabels),

            /*
             * Map of status keys to list of hidden transitions keys
             */
            'hiddenStatusesTransitions' => $this->_prepareHiddenTransitions($this->hiddenStatusesTransitions),
        ];
    }

    /**
     * Get map of status transitions.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $statusTransitions Map of status transitions.
     *
     * @return array Prepared status transitions to be rendered in UI.
     */
    protected function _prepareStatusTransitions($statusTransitions)
    {
        $resultingTransitions = [];
        foreach ($statusTransitions as $status => $transitionsMap) {
            $resultingTransitions[$status] = $this->_normalizeArray($transitionsMap);
        }

        return $resultingTransitions;
    }

    /**
     * Retrieve map of translated transitions labels.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $transitionsLabels Labels of available transitions.
     *
     * @return array Map of status keys to map of translated labels for transitions.
     */
    protected function _prepareTransitionsLabels($transitionsLabels)
    {
        $translatedLabels = [];
        foreach ($transitionsLabels as $statusKey => $transitionsLabelsMap) {
            $translatedLabels[$statusKey] = [];
            foreach ($transitionsLabelsMap as $transitionKey => $transitionLabel) {
                $translatedLabels[$statusKey][$transitionKey] = $this->__($transitionLabel);
            }
        }

        return $translatedLabels;
    }

    /**
     * Transform map of statuses to list of hidden transitions into array.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $hiddenStatusesTransitions Map of status key to list of hidden transition keys for given status.
     *
     * @return array Map of statuses keys to list of hidden transitions.
     */
    protected function _prepareHiddenTransitions($hiddenStatusesTransitions)
    {
        $hiddenTransitions = [];
        foreach ($hiddenStatusesTransitions as $statusKey => $hiddenTransitionsKey) {
            $hiddenTransitions[$statusKey] = $this->_normalizeArray($hiddenTransitionsKey);
        }

        return $hiddenTransitions;
    }
}
