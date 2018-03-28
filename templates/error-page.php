<?php

use RebelCode\EddBookings\Core\Util\ExceptionStackTrace;

/* @var $exception Exception */
?>

<style>
    body#error-page {
        max-width: 75%;
    }

    details.exception-trace-details {
        padding: 10px 0;
        border-top: 1px solid #dadada;
        border-bottom: 1px solid #dadada;
    }

    details.exception-trace-details summary {
        font-size: 14px;
    }

    pre.exception-trace {
        padding: 10px;
        overflow-x: auto;
        background: #f2f2f2;
    }
</style>

<h1><?= __('EDD Bookings Error') ?></h1>
<p><code><?= $exception->getMessage() ?></code></p>

<details class="exception-trace-details">
    <summary>
        <strong><?= __('Click to view error details:', 'eddbk'); ?></strong>
    </summary>
    <pre class="exception-trace"><?= new ExceptionStackTrace($exception); ?></pre>
</details>

<?php if (EDDBK_SAFE_EXCEPTION_HANDLING) : ?>
<p>
    <?= __('EDD Bookings experienced an error that could have broken your site.', 'eddbk') ?>
    <?= __('The plugin has been automatically deactivated for you to prevent that.', 'eddbk'); ?>
</p>
<p>
    <?=
    sprintf(
        _x(
            'Please %s, copy/paste the above error details into your message and explain what page you were visiting when this error occurred.',
            '%s = "contact EDD Bookings support"',
            'eddbk'
        ),
        sprintf(
            '<a href="http://eddbookings.com/contact" target="eddbk-support">%s</a>',
            __('contact EDD Bookings support', 'eddbk')
        )
    );
    ?>
</p>
<hr/>
<p>

</p>
<p>
    <a href="<?= admin_url() ?>"><?= __('Go to Dashboard', 'eddbk') ?> &raquo;</a>
</p>
<?php endif; ?>
