<?php
/**
 * BLOCK: Profile
 *
 * Gutenberg Custom Profile Block assets.
 *
 * @since	1.0.0
 * @package OPB
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
function organic_profile_block() {
	// Scripts.
	wp_register_script(
		'organic-profile-block-script', // Handle.
		get_stylesheet_directory_uri() . '/inc/blocks/profile/block.js', // Block.js: We register the block here.
		array( 'wp-blocks', 'wp-element', 'wp-i18n' ) // Dependencies, defined above.
	);

	// Styles.
	wp_register_style(
		'organic-profile-block-editor-style', // Handle.
		get_stylesheet_directory_uri() . '/inc/blocks/profile/editor.css', // Block editor CSS.
		array( 'wp-edit-blocks' ) // Dependency to include the CSS after it.
	);
	wp_register_style(
		'organic-profile-block-frontend-style', // Handle.
		get_stylesheet_directory_uri() . '/inc/blocks/profile/style.css', // Block editor CSS.
		array( 'wp-edit-blocks' ) // Dependency to include the CSS after it.
	);
	wp_enqueue_style(
		'organic-profile-block-fontawesome', // Handle.
		get_stylesheet_directory_uri() . '/fonts/font-awesome.css' // Font Awesome for social media icons.
	);

	// Here we actually register the block with WP, again using our namespacing
	// We also specify the editor script to be used in the Gutenberg interface
	register_block_type( 'profile/block', array(
		'editor_script' => 'organic-profile-block-script',
		'editor_style' => 'organic-profile-block-editor-style',
		'style' => 'organic-profile-block-frontend-style',
	) );

} // End function organic_profile_block().

// Hook: Editor assets.
add_action( 'init', 'organic_profile_block' );
