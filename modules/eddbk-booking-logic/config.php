<?php

use RebelCode\EddBookings\Logic\Module\BookingStatusInterface as S;
use RebelCode\EddBookings\Logic\Module\BookingTransitionInterface as T;

return [
    'booking_logic' => [
        'transition_event_format' => 'on_booking_transition',
        'statuses'                => [
            S::STATUS_NONE,
            S::STATUS_IN_CART,
            S::STATUS_DRAFT,
            S::STATUS_PENDING,
            S::STATUS_APPROVED,
            S::STATUS_REJECTED,
            S::STATUS_SCHEDULED,
            S::STATUS_COMPLETED,
            S::STATUS_CANCELLED,
        ],
        'non_blocking_statuses' => [
            S::STATUS_IN_CART,
            S::STATUS_DRAFT,
            S::STATUS_CANCELLED,
        ],
        'status_transitions'      => [
            S::STATUS_NONE      => [
                T::TRANSITION_CART  => S::STATUS_IN_CART,
                T::TRANSITION_DRAFT => S::STATUS_DRAFT,
            ],
            S::STATUS_IN_CART   => [
                T::TRANSITION_SUBMIT => S::STATUS_PENDING,
                T::TRANSITION_CART   => S::STATUS_IN_CART,
            ],
            S::STATUS_DRAFT     => [
                T::TRANSITION_SUBMIT => S::STATUS_PENDING,
                T::TRANSITION_DRAFT  => S::STATUS_DRAFT,
            ],
            S::STATUS_PENDING   => [
                T::TRANSITION_APPROVE => S::STATUS_APPROVED,
                T::TRANSITION_REJECT  => S::STATUS_REJECTED,
                T::TRANSITION_SUBMIT  => S::STATUS_PENDING,
            ],
            S::STATUS_APPROVED  => [
                T::TRANSITION_SCHEDULE => S::STATUS_SCHEDULED,
                T::TRANSITION_CANCEL   => S::STATUS_CANCELLED,
                T::TRANSITION_APPROVE  => S::STATUS_APPROVED,
            ],
            S::STATUS_REJECTED  => [
                T::TRANSITION_SUBMIT => S::STATUS_PENDING,
                T::TRANSITION_REJECT => S::STATUS_REJECTED,
            ],
            S::STATUS_SCHEDULED => [
                T::TRANSITION_COMPLETE => S::STATUS_COMPLETED,
                T::TRANSITION_CANCEL   => S::STATUS_CANCELLED,
                T::TRANSITION_SCHEDULE => S::STATUS_SCHEDULED,
            ],
            S::STATUS_COMPLETED => [
                T::TRANSITION_DRAFT    => S::STATUS_DRAFT,
                T::TRANSITION_SUBMIT   => S::STATUS_PENDING,
                T::TRANSITION_APPROVE  => S::STATUS_APPROVED,
                T::TRANSITION_SCHEDULE => S::STATUS_SCHEDULED,
                T::TRANSITION_CANCEL   => S::STATUS_CANCELLED,
                T::TRANSITION_COMPLETE => S::STATUS_COMPLETED,
            ],
            S::STATUS_CANCELLED => [
                T::TRANSITION_DRAFT    => S::STATUS_DRAFT,
                T::TRANSITION_SUBMIT   => S::STATUS_PENDING,
                T::TRANSITION_APPROVE  => S::STATUS_APPROVED,
                T::TRANSITION_SCHEDULE => S::STATUS_SCHEDULED,
                T::TRANSITION_COMPLETE => S::STATUS_COMPLETED,
                T::TRANSITION_CANCEL   => S::STATUS_CANCELLED,
            ],
        ],
    ],
];
