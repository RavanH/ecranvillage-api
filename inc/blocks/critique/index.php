<?php
/**
 * BLOCK: Critique
 *
 * Gutenberg Custom Critique Block assets.
 *
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) ||	exit;

/**
 * Enqueue the block's assets for the editor.
 *
 * `wp-blocks`: Includes block type registration and related functions.
 * `wp-element`: Includes the WordPress Element abstraction for describing the structure of your blocks.
 * `wp-i18n`: To internationalize the block's text.
 *
 * @since 1.0.0
 */
function ev_critique_block() {
	// Scripts.
	wp_register_script(
		'ev-critique-block-script', // Handle.
		get_stylesheet_directory_uri() . '/inc/blocks/critique/block.js', // Block.js: We register the block here.
		array( 'wp-blocks', 'wp-element', 'wp-i18n' ) // Dependencies, defined above.
	);

	// Styles.
	wp_register_style(
		'ev-critique-block-editor-style', // Handle.
		get_stylesheet_directory_uri() . '/inc/blocks/critique/editor.css', // Block editor CSS.
		array( 'wp-edit-blocks' ) // Dependency to include the CSS after it.
	);

	// Block.
	register_block_type( 'critique/block', array(
		'editor_script' => 'ev-critique-block-script',
		'editor_style' => 'ev-critique-block-editor-style'
	) );

}

// Hook: Editor assets.
add_action( 'init', 'ev_critique_block' );
