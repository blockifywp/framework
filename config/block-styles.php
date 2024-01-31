<?php

declare( strict_types=1 );

return ( static function (): array {
    $global_settings = wp_get_global_settings();

    $register = [
        'core/archive-title'       => [ 'sub-heading' ],
        'core/buttons'             => [ 'surface' ],
        'core/button'              => [ 'ghost' ],
        'core/code'                => [ 'surface' ],
        'core/columns'             => [ 'surface' ],
        'core/column'              => [ 'surface' ],
        'core/comment-author-name' => [ 'heading' ],
        'core/details'             => [
            [ 'summary-heading' => __( 'Heading', 'blockify' ) ],
        ],
        'core/group'               => [ 'surface' ],
        'core/list'                => [
            'checklist',
            'check-outline',
            'check-circle',
            'square',
            'list-heading',
            'dash',
            'none',
        ],
        'core/list-item'           => [ 'surface' ],
        'core/navigation'          => [ 'heading' ],
        'core/page-list'           => [ 'none' ],
        'core/paragraph'           => [ 'sub-heading', 'notice', 'heading' ],
        'core/post-author-name'    => [ 'heading' ],
        'core/post-terms'          => [ 'list', 'sub-heading', 'badges' ],
        'core/post-title'          => [ 'sub-heading' ],
        'core/query-pagination'    => [ 'badges' ],
        'core/read-more'           => [ 'button' ],
        'core/site-title'          => [ 'heading' ],
        'core/spacer'              => [ 'angle', 'curve', 'round', 'wave', 'fade' ],
        'core/tag-cloud'           => [ 'badges' ],
        'core/quote'               => [ 'surface' ],
    ];

    $dark_mode  = $global_settings['custom']['darkMode'] ?? null;
    $light_mode = $global_settings['custom']['lightMode'] ?? null;

    if ( $dark_mode || $light_mode ) {
        $register['core/code'][]    = 'light';
        $register['core/code'][]    = 'dark';
        $register['core/column'][]  = 'light';
        $register['core/column'][]  = 'dark';
        $register['core/columns'][] = 'light';
        $register['core/columns'][] = 'dark';
        $register['core/group'][]   = 'light';
        $register['core/group'][]   = 'dark';
    }

    // Values must be arrays.
    $unregister = [
        'core/image'     => [ 'rounded', 'default' ],
        'core/site-logo' => [ 'default', 'rounded' ],
        'core/separator' => [ 'wide', 'dots' ],
    ];

    return [
        'register'   => $register,
        'unregister' => $unregister,
    ];
} )();
