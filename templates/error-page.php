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

<h1><?= __('EDD Bookings Error', EDDBK_TEXT_DOMAIN) ?></h1>
<p><code><?= $exception->getMessage() ?></code></p>

<details class="exception-trace-details">
    <summary>
        <strong><?= __('Click to view error details:', EDDBK_TEXT_DOMAIN); ?></strong>
    </summary>
    <pre class="exception-trace"><?= new ExceptionStackTrace($exception); ?></pre>
</details>

<?php if (EDDBK_SAFE_EXCEPTION_HANDLING) : ?>
<p>
    <?= __('EDD Bookings experienced an error that could have broken your site.', EDDBK_TEXT_DOMAIN) ?>
    <?= __('The plugin has been automatically deactivated for you to prevent that.', EDDBK_TEXT_DOMAIN); ?>
</p>
<p>
    <?=
    sprintf(
        _x(
            'Please %s, copy/paste the above error details into your message and explain what page you were visiting when this error occurred.',
            '%s = "contact EDD Bookings support"',
            EDDBK_TEXT_DOMAIN
        ),
        sprintf(
            '<a href="%1$s" target="eddbk-support">%2$s</a>',
            EDDBK_CONTACT_PAGE_URL,
            __('contact EDD Bookings support', EDDBK_TEXT_DOMAIN)
        )
    );
    ?>
</p>
<hr/>
<p>

</p>
<p>
    <a href="<?= admin_url() ?>"><?= __('Go to Dashboard', EDDBK_TEXT_DOMAIN) ?> &raquo;</a>
</p>
<?php endif; ?>
