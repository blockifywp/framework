<?php

declare( strict_types=1 );

return [
	'responsive' => [
		'position'            => [
			'property' => 'position',
			'label'    => __( 'Position', 'blockify' ),
			'options'  => [
				[
					'label' => '',
					'value' => '',
				],
				[
					'label' => __( 'Relative', 'blockify' ),
					'value' => 'relative',
				],
				[
					'label' => __( 'Absolute', 'blockify' ),
					'value' => 'absolute',
				],
				[
					'label' => __( 'Sticky', 'blockify' ),
					'value' => 'sticky',
				],
				[
					'label' => __( 'Fixed', 'blockify' ),
					'value' => 'fixed',
				],
				[
					'label' => __( 'Static', 'blockify' ),
					'value' => 'static',
				],
			],
		],
		'top'                 => [
			'property' => 'top',
			'label'    => __( 'Top', 'blockify' ),
		],
		'right'               => [
			'property' => 'right',
			'label'    => __( 'Right', 'blockify' ),
		],
		'bottom'              => [
			'property' => 'bottom',
			'label'    => __( 'Bottom', 'blockify' ),
		],
		'left'                => [
			'property' => 'left',
			'label'    => __( 'Left', 'blockify' ),
		],
		'zIndex'              => [
			'property' => 'z-index',
			'label'    => __( 'Z-Index', 'blockify' ),
		],
		'display'             => [
			'property' => 'display',
			'label'    => __( 'Display', 'blockify' ),
			'options'  => [
				[
					'label' => '',
					'value' => '',
				],
				[
					'label' => __( 'None', 'blockify' ),
					'value' => 'none',
				],
				[
					'label' => __( 'Flex', 'blockify' ),
					'value' => 'flex',
				],
				[
					'label' => __( 'Inline Flex', 'blockify' ),
					'value' => 'inline-flex',
				],
				[
					'label' => __( 'Block', 'blockify' ),
					'value' => 'block',
				],
				[
					'label' => __( 'Inline Block', 'blockify' ),
					'value' => 'inline-block',
				],
				[
					'label' => __( 'Inline', 'blockify' ),
					'value' => 'inline',
				],
				[
					'label' => __( 'Grid', 'blockify' ),
					'value' => 'grid',
				],
				[
					'label' => __( 'Inline Grid', 'blockify' ),
					'value' => 'inline-grid',
				],
				[
					'label' => __( 'Contents', 'blockify' ),
					'value' => 'contents',
				],
			],
		],
		'order'               => [
			'property' => 'order',
			'label'    => __( 'Order', 'blockify' ),
		],
		'gridTemplateColumns' => [
			'property' => 'grid-template-columns',
			'label'    => __( 'Columns', 'blockify' ),
		],
		'gridTemplateRows'    => [
			'property' => 'grid-template-rows',
			'label'    => __( 'Rows', 'blockify' ),
		],
		'gridColumnStart'     => [
			'property' => 'grid-column-start',
			'label'    => __( 'Column Start', 'blockify' ),
		],
		'gridColumnEnd'       => [
			'property' => 'grid-column-end',
			'label'    => __( 'Column End', 'blockify' ),
		],
		'gridRowStart'        => [
			'property' => 'grid-row-start',
			'label'    => __( 'Row Start', 'blockify' ),
		],
		'gridRowEnd'          => [
			'property' => 'grid-row-end',
			'label'    => __( 'Row End', 'blockify' ),
		],
		'overflow'            => [
			'property' => 'overflow',
			'label'    => __( 'Overflow', 'blockify' ),
			'options'  => [
				[
					'label' => '',
					'value' => '',
				],
				[
					'label' => __( 'Hidden', 'blockify' ),
					'value' => 'hidden',
				],
				[
					'label' => __( 'Visible', 'blockify' ),
					'value' => 'visible',
				],
			],
		],
		'pointerEvents'       => [
			'property' => 'pointer-events',
			'label'    => __( 'Pointer Events', 'blockify' ),
			'options'  => [
				[
					'label' => '',
					'value' => '',
				],
				[
					'label' => __( 'None', 'blockify' ),
					'value' => 'none',
				],
				[
					'label' => __( 'All', 'blockify' ),
					'value' => 'all',
				],
			],
		],
		'width'               => [
			'property' => 'width',
			'label'    => __( 'Width', 'blockify' ),
		],
		'minWidth'            => [
			'property' => 'min-width',
			'label'    => __( 'Min Width', 'blockify' ),
		],
		'maxWidth'            => [
			'property' => 'max-width',
			'label'    => __( 'Max Width', 'blockify' ),
		],
	],
	'filter'     => [
		'blur'       => [
			'unit' => 'px',
			'min'  => 0,
			'max'  => 500,
		],
		'brightness' => [
			'unit' => '%',
			'min'  => 0,
			'max'  => 360,
		],
		'contrast'   => [
			'unit' => '%',
			'min'  => 0,
			'max'  => 200,
		],
		'grayscale'  => [
			'unit' => '%',
			'min'  => 0,
			'max'  => 100,
		],
		'hueRotate'  => [
			'unit' => 'deg',
			'min'  => -360,
			'max'  => 360,
		],
		'invert'     => [
			'unit' => '%',
			'min'  => 0,
			'max'  => 100,
		],
		'opacity'    => [
			'unit' => '%',
			'min'  => 0,
			'max'  => 100,
		],
		'saturate'   => [
			'unit' => '',
			'min'  => 0,
			'max'  => 100,
			'step' => 0.1,
		],
		'sepia'      => [
			'unit' => '%',
			'min'  => 0,
			'max'  => 100,
		],
	],
	'image'      => [
		'aspectRatio'    => [
			'property' => 'aspect-ratio',
			'label'    => __( 'Aspect Ratio', 'blockify' ),
			'options'  => [
				[
					'label' => '',
					'value' => '',
				],
				[
					'label' => '1/1',
					'value' => '1/1',
				],
				[
					'label' => '1/2',
					'value' => '1/2',
				],
				[
					'label' => '1/3',
					'value' => '1/3',
				],
				[
					'label' => '2/1',
					'value' => '2/1',
				],
				[
					'label' => '2/3',
					'value' => '2/3',
				],
				[
					'label' => '3/1',
					'value' => '3/1',
				],
				[
					'label' => '3/2',
					'value' => '3/2',
				],
				[
					'label' => '3/4',
					'value' => '3/4',
				],
				[
					'label' => '4/3',
					'value' => '4/3',
				],
				[
					'label' => '4/5',
					'value' => '4/5',
				],
				[
					'label' => '5/2',
					'value' => '5/2',
				],
				[
					'label' => '5/4',
					'value' => '5/4',
				],
				[
					'label' => '9/16',
					'value' => '9/16',
				],
				[
					'label' => '16/9',
					'value' => '16/9',
				],
			],
		],
		'height'         => [
			'property' => 'height',
			'label'    => __( 'Height', 'blockify' ),
		],
		'objectFit'      => [
			'property' => 'object-fit',
			'label'    => __( 'Object Fit', 'blockify' ),
			'options'  => [
				[
					'label' => '',
					'value' => '',
				],
				[
					'label' => __( 'Fill', 'blockify' ),
					'value' => 'fill',
				],
				[
					'label' => __( 'Contain', 'blockify' ),
					'value' => 'contain',
				],
				[
					'label' => __( 'Cover', 'blockify' ),
					'value' => 'cover',
				],
				[
					'label' => __( 'None', 'blockify' ),
					'value' => 'none',
				],
				[
					'label' => __( 'Scale Down', 'blockify' ),
					'value' => 'scale-down',
				],
			],
		],
		'objectPosition' => [
			'property' => 'object-position',
			'label'    => __( 'Object Position', 'blockify' ),
			'options'  => [
				[
					'label' => '',
					'value' => '',
				],
				[
					'label' => __( 'Top', 'blockify' ),
					'value' => 'top',
				],
				[
					'label' => __( 'Top Right', 'blockify' ),
					'value' => 'top right',
				],
				[
					'label' => __( 'Right', 'blockify' ),
					'value' => 'right',
				],
				[
					'label' => __( 'Bottom Right', 'blockify' ),
					'value' => 'bottom right',
				],
				[
					'label' => __( 'Bottom', 'blockify' ),
					'value' => 'bottom',
				],
				[
					'label' => __( 'Bottom Left', 'blockify' ),
					'value' => 'bottom left',
				],
				[
					'label' => __( 'Left', 'blockify' ),
					'value' => 'left',
				],
				[
					'label' => __( 'Top Left', 'blockify' ),
					'value' => 'top left',
				],
				[
					'label' => __( 'Center', 'blockify' ),
					'value' => 'center',
				],
				[
					'label' => __( 'None', 'blockify' ),
					'value' => 'none',
				],
			],
		],
	],
];
