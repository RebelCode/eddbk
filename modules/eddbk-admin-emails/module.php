<?php

use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Emails\Module\EddBkAdminEmailsModule;

// Module info
define('RCMOD_EDDBK_ADMIN_EMAILS_MODULE_KEY', 'eddbk_admin_emails');
// Directories
define('RCMOD_EDDBK_ADMIN_EMAILS_MODULE_DIR', __DIR__);
define('RCMOD_EDDBK_ADMIN_EMAILS_CONFIG_DIR', RCMOD_EDDBK_ADMIN_EMAILS_MODULE_DIR);
define('RCMOD_EDDBK_ADMIN_EMAILS_SERVICES_DIR', RCMOD_EDDBK_ADMIN_EMAILS_MODULE_DIR);
define('RCMOD_EDDBK_ADMIN_EMAILS_TEMPLATES_DIR', RCMOD_EDDBK_ADMIN_EMAILS_MODULE_DIR . '/templates');
// Files
define('RCMOD_EDDBK_ADMIN_EMAILS_CONFIG_FILE', RCMOD_EDDBK_ADMIN_EMAILS_CONFIG_DIR . '/config.php');
define('RCMOD_EDDBK_ADMIN_EMAILS_SERVICES_FILE', RCMOD_EDDBK_ADMIN_EMAILS_SERVICES_DIR . '/services.php');

return function (ContainerInterface $c) {
    return new EddBkAdminEmailsModule(
        RCMOD_EDDBK_ADMIN_EMAILS_MODULE_KEY,
        ['eddbk_services', 'eddbk_cart'],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
