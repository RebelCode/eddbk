<?php

use RebelCode\EddBookings\Core\PluginKnowledgeBaseLinkHandler;

return [
    'eddbk_plugin_knowledge_base_link_handler' => function () {
        return new PluginKnowledgeBaseLinkHandler();
    },
];
