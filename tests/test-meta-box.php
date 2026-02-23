<?php
/**
 * Tests for TailSignal_Meta_Box.
 *
 * @package TailSignal
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__ ) . '/src/includes/class-tailsignal-db.php';
require_once dirname( __DIR__ ) . '/src/includes/class-tailsignal-expo.php';
require_once dirname( __DIR__ ) . '/src/includes/class-tailsignal-notification.php';
require_once dirname( __DIR__ ) . '/src/admin/class-tailsignal-meta-box.php';

class Test_TailSignal_Meta_Box extends TailSignal_TestCase {

	/**
	 * @var TailSignal_Meta_Box
	 */
	private $meta_box;

	protected function setUp(): void {
		parent::setUp();
		$this->meta_box = new TailSignal_Meta_Box();
	}

	/**
	 * Test save_meta_box requires nonce.
	 */
	public function test_save_meta_box_requires_nonce() {
		$post = Mockery::mock( 'WP_Post' );

		// No nonce in $_POST - should return early.
		$_POST = array();

		// wp_verify_nonce should NOT be called since nonce key is missing.
		$this->meta_box->save_meta_box( 1, $post );

		// If we got here without errors, the early return works.
		$this->assertTrue( true );
	}

	/**
	 * Test save_meta_box rejects invalid nonce.
	 */
	public function test_save_meta_box_rejects_invalid_nonce() {
		$post = Mockery::mock( 'WP_Post' );

		$_POST = array(
			'tailsignal_meta_box_nonce' => 'bad_nonce',
		);

		Functions\expect( 'wp_verify_nonce' )
			->with( 'bad_nonce', 'tailsignal_meta_box' )
			->andReturn( false );

		// update_post_meta should NOT be called.
		$this->meta_box->save_meta_box( 1, $post );
		$this->assertTrue( true );
	}

	/**
	 * Test save_meta_box saves meta when valid.
	 */
	public function test_save_meta_box_saves_meta() {
		$post = Mockery::mock( 'WP_Post' );

		$_POST = array(
			'tailsignal_meta_box_nonce' => 'good_nonce',
			'tailsignal_notify'         => '1',
			'tailsignal_include_image'  => '1',
			'tailsignal_custom_title'   => 'Custom Title',
			'tailsignal_custom_body'    => 'Custom Body',
		);

		Functions\expect( 'wp_verify_nonce' )
			->with( 'good_nonce', 'tailsignal_meta_box' )
			->andReturn( true );

		Functions\expect( 'current_user_can' )
			->with( 'edit_post', 1 )
			->andReturn( true );

		Functions\expect( 'update_post_meta' )->times( 4 );

		$this->meta_box->save_meta_box( 1, $post );
		$this->assertTrue( true );
	}

	/**
	 * Test add_meta_box registers meta box.
	 */
	public function test_add_meta_box() {
		Functions\expect( 'apply_filters' )
			->with( 'tailsignal_post_types', array( 'post' ) )
			->andReturn( array( 'post' ) );

		Functions\expect( 'add_meta_box' )->once();

		$this->meta_box->add_meta_box();
		$this->assertTrue( true );
	}

	/**
	 * Test save_meta_box checks permissions.
	 */
	public function test_save_meta_box_checks_permissions() {
		$post = Mockery::mock( 'WP_Post' );

		$_POST = array(
			'tailsignal_meta_box_nonce' => 'good_nonce',
		);

		Functions\expect( 'wp_verify_nonce' )
			->with( 'good_nonce', 'tailsignal_meta_box' )
			->andReturn( true );

		Functions\expect( 'current_user_can' )
			->with( 'edit_post', 1 )
			->andReturn( false );

		// update_post_meta should NOT be called.
		$this->meta_box->save_meta_box( 1, $post );
		$this->assertTrue( true );
	}
}
