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

<h1><?= __('EDD Bookings Unhandled Exception') ?></h1>
<p><code><?= $exception->getMessage() ?></code></p>

<details class="exception-trace-details">
    <summary>
        <strong><?= __('Exception stack trace:', 'eddbk'); ?></strong>
    </summary>
    <pre class="exception-trace"><?= new ExceptionStackTrace($exception); ?></pre>
</details>

<p>
    <?php if (EDDBK_SAFE_EXCEPTION_HANDLING) {
        echo __('EDD Bookings has been deactivated.', 'eddbk');
        if (current_user_can('edit_plugins')) {
            ?>
            <a href="<?= admin_url('plugins.php') ?>">
                <?= __('Go to plugins page', 'eddbk') ?> &raquo;
            </a>
            <?php
        }
    }
    ?>
</p>
