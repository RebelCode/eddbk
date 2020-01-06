<?php

namespace RebelCode\EddBookings\Services\Storage;

/**
 * Provides awareness of the services post key map.
 *
 * @since [*next-version*]
 */
trait ServicesPostKeyMapAwareTrait
{
    /**
     * Retrieves the map of services keys to their WP Post key counterparts.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function _getServicesPostKeyMap()
    {
        return [
            // Core service fields
            'id'               => 'ID',
            'name'             => 'post_title',
            'description'      => 'post_excerpt',
            'status'           => 'post_status',
            // Other post fields
            'guid'             => 'guid',
            'author'           => 'post_author',
            'type'             => 'post_type',
            'date'             => 'post_date',
            'date_gmt'         => 'post_date_gmt',
            'content'          => 'post_content',
            'content_filtered' => 'post_content_filtered',
            'category'         => 'post_category',
            'password'         => 'post_password',
            'parent'           => 'post_parent',
            'modified'         => 'post_modified',
            'modified_gmt'     => 'post_modified_gmt',
            'mime_type'        => 'post_mime_type',
            'tags'             => 'tags_input',
            'taxonomies'       => 'tax_input',
            'pinged'           => 'pinged',
            'ping_status'      => 'ping_status',
            'comment_count'    => 'comment_count',
            'comment_status'   => 'comment_status',
            'menu_order'       => 'menu_order',
        ];
    }
}
