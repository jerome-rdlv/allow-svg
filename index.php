<?php

add_filter('upload_mimes', function ($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});
add_action('admin_enqueue_scripts', function () {
    // Media Listing Fix
    wp_add_inline_style( 'wp-admin', ".media .media-icon img[src$='.svg'] { width: auto; height: auto; }" );
    // Featured Image Fix
    wp_add_inline_style( 'wp-admin', "#postimagediv .inside img[src$='.svg'] { width: 100%; height: auto; }" );
});
add_filter('wp_prepare_attachment_for_js', function ($response, $attachment, $meta) {
    if( $response['mime'] == 'image/svg+xml' && empty( $response['sizes'] ) ) {
        $svg_file_path = get_attached_file( $attachment->ID );

        $svg = simplexml_load_file( $svg_file_path );
        $attributes = $svg->attributes();

        $response[ 'sizes' ] = array(
            'full' => array(
                'url' => $response[ 'url' ],
                'width' => $attributes->width,
                'height' => $attributes->height,
                'orientation' => $attributes->width > $attributes->height ? 'landscape' : 'portrait'
            )
        );
    }

    return $response;
}, 10, 3);
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
    if (get_bloginfo('version') < '4.7.3') {
        $wp_filetype = wp_check_filetype($filename, $mimes);
        $ext = $wp_filetype['ext'];
        $type = $wp_filetype['type'];
        $proper_filename = $data['proper_filename'];
        return compact('ext', 'type', 'proper_filename');
    }
    return $data;

}, 10, 4);