<?php
/**
 * Base test case for TailSignal tests.
 *
 * @package TailSignal
 */

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TailSignal_TestCase extends PHPUnitTestCase {

	/**
	 * Set up test environment.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Mock common WordPress functions.
		Functions\stubs( array(
			'sanitize_text_field'     => function( $str ) { return trim( strip_tags( $str ) ); },
			'sanitize_textarea_field' => function( $str ) { return trim( strip_tags( $str ) ); },
			'esc_url_raw'             => function( $url ) { return $url; },
			'esc_html'                => function( $str ) { return htmlspecialchars( $str, ENT_QUOTES, 'UTF-8' ); },
			'esc_attr'                => function( $str ) { return htmlspecialchars( $str, ENT_QUOTES, 'UTF-8' ); },
			'wp_parse_args'           => function( $args, $defaults ) { return array_merge( $defaults, $args ); },
			'wp_json_encode'          => function( $data ) { return json_encode( $data ); },
			'wp_unslash'              => function( $value ) { return $value; },
			'current_time'            => function() { return '2025-02-12 12:00:00'; },
			'__'                      => function( $text ) { return $text; },
			'esc_html__'              => function( $text ) { return $text; },
		) );
	}

	/**
	 * Tear down test environment.
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}
