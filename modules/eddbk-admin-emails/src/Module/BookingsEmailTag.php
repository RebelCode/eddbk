<?php

namespace RebelCode\EddBookings\Emails\Module;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Output\TemplateAwareTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;

/**
 * The EDD tag that gets replaced with the information of all bookings in the related payment.
 *
 * @since [*next-version*]
 */
class BookingsEmailTag implements InvocableInterface
{
    /* @since [*next-version*] */
    use TemplateAwareTrait;

    /* @since [*next-version*] */
    use CountIterableCapableTrait;

    /* @since [*next-version*] */
    use ResolveIteratorCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The bookings SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @var SelectCapableInterface
     */
    protected $bookingsSelectRm;

    /**
     * The SQL expression builder.
     *
     * @since [*next-version*]
     *
     * @var object
     */
    protected $exprBuilder;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TemplateInterface      $template         The template to render for this tag.
     * @param SelectCapableInterface $bookingsSelectRm The bookings SELECT resource model.
     * @param object                 $exprBuilder      The SQL expression builder.
     */
    public function __construct(TemplateInterface $template, SelectCapableInterface $bookingsSelectRm, $exprBuilder)
    {
        $this->_setTemplate($template);
        $this->bookingsSelectRm = $bookingsSelectRm;
        $this->exprBuilder      = $exprBuilder;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $paymentId = func_get_arg(0);

        // No payment ID - do not render anything
        if (empty($paymentId)) {
            return '';
        }

        $b = $this->exprBuilder;

        $bookings = $this->bookingsSelectRm->select(
            $b->eq(
                $b->ef('booking', 'payment_id'),
                $b->lit($paymentId)
            )
        );

        // No bookings purchased for this payment - do not render anything
        if ($this->_countIterable($bookings) === 0) {
            return '';
        }

        return $this->_getTemplate()->render([
            'bookings' => $bookings,
        ]);
    }
}
