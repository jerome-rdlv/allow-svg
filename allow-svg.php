<?php
/*
Plugin Name: Allow SVG
Plugin URI: https://gitlab.rue-de-la-vieille.fr/jerome/allow-svg
Description: Allow SVG upload
Author: Jérôme Mulsant
Version: 0.1
Author URI: https://rue-de-la-vieille.fr/
*/

add_filter('upload_mimes', function ($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});

add_action('admin_enqueue_scripts', function () {
    // Media Listing Fix
    wp_add_inline_style('wp-admin', ".media .media-icon img[src$='.svg'] { width: auto; height: auto; }");
    // Featured Image Fix
    wp_add_inline_style('wp-admin', "#postimagediv .inside img[src$='.svg'] { width: 100%; height: auto; }");
});

add_filter('wp_prepare_attachment_for_js', function ($response, $attachment, $meta) {
    if ($response['mime'] !== 'image/svg+xml' || !empty($response['sizes'])) {
        return $response;
    }
    $svg_file_path = get_attached_file($attachment->ID);

    if (!file_exists($svg_file_path)) {
        return $response;
    }

    /** @noinspection PhpComposerExtensionStubsInspection */
    $svg = simplexml_load_file($svg_file_path);
    $attributes = $svg->attributes();

    $response['sizes'] = array(
        'full' => array(
            'url'         => $response['url'],
            'width'       => (string)$attributes->width,
            'height'      => (string)$attributes->height,
            'orientation' => ((int)$attributes->width) > ((int)$attributes->height) ? 'landscape' : 'portrait',
        ),
    );

    return $response;
}, 10, 3);

add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
    $wp_filetype = wp_check_filetype($filename, $mimes);
    $ext = $wp_filetype['ext'];
    $type = $wp_filetype['type'];
    $proper_filename = $data['proper_filename'];
    return compact('ext', 'type', 'proper_filename');
}, 10, 4);