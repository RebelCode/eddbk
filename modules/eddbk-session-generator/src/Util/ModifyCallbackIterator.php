<?php

namespace RebelCode\EddBookings\Sessions\Util;

use Iterator;
use OuterIterator;

/**
 * An iterator that invokes a callback for each value of another iterator to allow for last minute modification of
 * the value.
 *
 * @since [*next-version*]
 */
class ModifyCallbackIterator implements OuterIterator
{
    /**
     * The inner iterator.
     *
     * @since [*next-version*]
     *
     * @var Iterator
     */
    protected $iterator;

    /**
     * The callback to invoke for each value.
     *
     * @since [*next-version*]
     *
     * @var callable
     */
    protected $callback;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param Iterator $iterator The inner iterator.
     * @param callable $callback The callback to invoke for each value.
     */
    public function __construct(Iterator $iterator, callable $callback)
    {
        $this->iterator = $iterator;
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function current()
    {
        return call_user_func_array($this->callback, [$this->iterator->current()]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getInnerIterator()
    {
        return $this->iterator;
    }
}
