<?php
/**
 * Tests for admin AJAX handlers (Send, Groups, Meta Box).
 *
 * @package TailSignal
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__ ) . '/src/includes/class-tailsignal-db.php';
require_once dirname( __DIR__ ) . '/src/includes/class-tailsignal-expo.php';
require_once dirname( __DIR__ ) . '/src/includes/class-tailsignal-notification.php';
require_once dirname( __DIR__ ) . '/src/admin/class-tailsignal-admin-send.php';
require_once dirname( __DIR__ ) . '/src/admin/class-tailsignal-admin-groups.php';
require_once dirname( __DIR__ ) . '/src/admin/class-tailsignal-meta-box.php';

class Test_TailSignal_Admin_Handlers extends TailSignal_TestCase {

	protected function tearDown(): void {
		$_POST = array();
		$_GET  = array();
		parent::tearDown();
	}

	// ── Send Handler ──────────────────────────────────────────────

	/**
	 * Test handle_send checks nonce.
	 */
	public function test_handle_send_checks_nonce() {
		Functions\expect( 'check_ajax_referer' )
			->with( 'tailsignal_nonce', 'nonce' )
			->once();

		Functions\expect( 'current_user_can' )
			->with( 'tailsignal_manage' )
			->andReturn( false );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function() use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( 'wp_send_json_error called' );
		} );

		$send = new TailSignal_Admin_Send();
		try {
			$send->handle_send();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test handle_send requires title and body.
	 */
	public function test_handle_send_requires_fields() {
		$_POST = array(
			'title' => '',
			'body'  => '',
		);

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function( $data ) use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( $data['message'] );
		} );

		$send = new TailSignal_Admin_Send();
		try {
			$send->handle_send();
		} catch ( \RuntimeException $e ) {
			$this->assertStringContainsString( 'required', $e->getMessage() );
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test handle_cancel_scheduled checks nonce and permission.
	 */
	public function test_handle_cancel_scheduled_checks_permission() {
		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( false );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function() use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( 'denied' );
		} );

		$send = new TailSignal_Admin_Send();
		try {
			$send->handle_cancel_scheduled();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test handle_cancel_scheduled with invalid ID.
	 */
	public function test_handle_cancel_scheduled_invalid_id() {
		$_POST = array( 'notification_id' => '0' );

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function( $data ) use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( $data['message'] );
		} );

		$send = new TailSignal_Admin_Send();
		try {
			$send->handle_cancel_scheduled();
		} catch ( \RuntimeException $e ) {
			$this->assertStringContainsString( 'Invalid', $e->getMessage() );
		}
		$this->assertTrue( $exited );
	}

	// ── Groups Handler ────────────────────────────────────────────

	/**
	 * Test handle_save_group checks permission.
	 */
	public function test_handle_save_group_checks_permission() {
		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( false );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function() use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( 'denied' );
		} );

		$groups = new TailSignal_Admin_Groups();
		try {
			$groups->handle_save_group();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test handle_save_group requires name.
	 */
	public function test_handle_save_group_requires_name() {
		$_POST = array( 'name' => '' );

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function( $data ) use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( $data['message'] );
		} );

		$groups = new TailSignal_Admin_Groups();
		try {
			$groups->handle_save_group();
		} catch ( \RuntimeException $e ) {
			$this->assertStringContainsString( 'required', $e->getMessage() );
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test handle_save_group creates new group.
	 */
	public function test_handle_save_group_creates() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix    = 'wp_';
		$wpdb->insert_id = 3;

		$_POST = array(
			'name'        => 'New Group',
			'description' => 'A test group',
			'device_ids'  => array( '1', '2' ),
		);

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		// create_group.
		$wpdb->shouldReceive( 'insert' )->andReturn( 1 );

		// set_group_devices: delete then replace.
		$wpdb->shouldReceive( 'delete' )->andReturn( 1 );
		$wpdb->shouldReceive( 'replace' )->andReturn( 1 );

		$success_data = null;
		Functions\expect( 'wp_send_json_success' )->once()->andReturnUsing( function( $data ) use ( &$success_data ) {
			$success_data = $data;
			throw new \RuntimeException( 'success' );
		} );

		$groups = new TailSignal_Admin_Groups();
		try {
			$groups->handle_save_group();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertSame( 3, $success_data['group_id'] );
	}

	/**
	 * Test handle_save_group updates existing group.
	 */
	public function test_handle_save_group_updates() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$_POST = array(
			'group_id'    => '5',
			'name'        => 'Updated Group',
			'description' => 'Updated desc',
			'device_ids'  => array(),
		);

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		// update_group.
		$wpdb->shouldReceive( 'update' )->andReturn( 1 );

		// set_group_devices: only delete (no devices to add).
		$wpdb->shouldReceive( 'delete' )->andReturn( 1 );

		Functions\expect( 'wp_send_json_success' )->once()->andReturnUsing( function() {
			throw new \RuntimeException( 'success' );
		} );

		$groups = new TailSignal_Admin_Groups();
		try {
			$groups->handle_save_group();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertTrue( true );
	}

	/**
	 * Test handle_delete_group checks permission.
	 */
	public function test_handle_delete_group_checks_permission() {
		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( false );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function() use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( 'denied' );
		} );

		$groups = new TailSignal_Admin_Groups();
		try {
			$groups->handle_delete_group();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test handle_delete_group with invalid ID.
	 */
	public function test_handle_delete_group_invalid_id() {
		$_POST = array( 'group_id' => '0' );

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function() use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( 'invalid' );
		} );

		$groups = new TailSignal_Admin_Groups();
		try {
			$groups->handle_delete_group();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test handle_delete_group success.
	 */
	public function test_handle_delete_group_success() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$_POST = array( 'group_id' => '5' );

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		// delete_group: delete from pivot + groups.
		$wpdb->shouldReceive( 'delete' )->twice()->andReturn( 1 );

		Functions\expect( 'wp_send_json_success' )->once()->andReturnUsing( function() {
			throw new \RuntimeException( 'success' );
		} );

		$groups = new TailSignal_Admin_Groups();
		try {
			$groups->handle_delete_group();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertTrue( true );
	}

	/**
	 * Test handle_get_group_devices checks permission.
	 */
	public function test_handle_get_group_devices_checks_permission() {
		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( false );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function() use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( 'denied' );
		} );

		$groups = new TailSignal_Admin_Groups();
		try {
			$groups->handle_get_group_devices();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test handle_get_group_devices returns device IDs.
	 */
	public function test_handle_get_group_devices_success() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$_GET = array( 'group_id' => '5' );

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$wpdb->shouldReceive( 'get_col' )->andReturn( array( '1', '2', '3' ) );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		$success_data = null;
		Functions\expect( 'wp_send_json_success' )->once()->andReturnUsing( function( $data ) use ( &$success_data ) {
			$success_data = $data;
			throw new \RuntimeException( 'success' );
		} );

		$groups = new TailSignal_Admin_Groups();
		try {
			$groups->handle_get_group_devices();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertCount( 3, $success_data['device_ids'] );
	}

	// ── Meta Box Quick Send ───────────────────────────────────────

	/**
	 * Test handle_quick_send checks permission.
	 */
	public function test_handle_quick_send_checks_permission() {
		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( false );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function() use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( 'denied' );
		} );

		$meta = new TailSignal_Meta_Box();
		try {
			$meta->handle_quick_send();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test handle_quick_send requires post_id, title, body.
	 */
	public function test_handle_quick_send_requires_fields() {
		$_POST = array(
			'post_id' => '0',
			'title'   => '',
			'body'    => '',
		);

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function( $data ) use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( $data['message'] );
		} );

		$meta = new TailSignal_Meta_Box();
		try {
			$meta->handle_quick_send();
		} catch ( \RuntimeException $e ) {
			$this->assertStringContainsString( 'required', $e->getMessage() );
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test handle_quick_send returns error when post not found.
	 */
	public function test_handle_quick_send_post_not_found() {
		$_POST = array(
			'post_id' => '99',
			'title'   => 'Test',
			'body'    => 'Body',
		);

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );
		Functions\expect( 'get_post' )->with( 99 )->andReturn( null );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function( $data ) use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( $data['message'] );
		} );

		$meta = new TailSignal_Meta_Box();
		try {
			$meta->handle_quick_send();
		} catch ( \RuntimeException $e ) {
			$this->assertStringContainsString( 'not found', $e->getMessage() );
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test handle_quick_send returns error when no devices.
	 */
	public function test_handle_quick_send_no_devices() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$post = Mockery::mock( 'WP_Post' );
		$post->ID           = 10;
		$post->post_title   = 'Test Post';
		$post->post_content = 'Content';
		$post->post_author  = 1;

		$_POST = array(
			'post_id'     => '10',
			'title'       => 'Test',
			'body'        => 'Body',
			'target_type' => 'all',
		);

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );
		Functions\expect( 'get_post' )->with( 10 )->andReturn( $post );

		// parse_placeholders stubs.
		Functions\expect( 'get_bloginfo' )->andReturn( 'Blog' );
		Functions\expect( 'get_the_author_meta' )->andReturn( 'Author' );
		Functions\expect( 'wp_strip_all_tags' )->andReturnFirstArg();
		Functions\expect( 'get_the_category' )->andReturn( array() );
		Functions\expect( 'has_post_thumbnail' )->andReturn( false );
		Functions\expect( 'get_permalink' )->andReturn( 'http://example.com/test' );

		// get_tokens_by_target returns empty.
		Functions\expect( 'get_option' )
			->with( 'tailsignal_dev_mode', '0' )
			->andReturn( '0' );

		$wpdb->shouldReceive( 'get_col' )->andReturn( array() );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function( $data ) use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( $data['message'] );
		} );

		$meta = new TailSignal_Meta_Box();
		try {
			$meta->handle_quick_send();
		} catch ( \RuntimeException $e ) {
			$this->assertStringContainsString( 'No devices', $e->getMessage() );
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test add_meta_box registers for multiple post types.
	 */
	public function test_add_meta_box_multiple_types() {
		Functions\expect( 'apply_filters' )
			->with( 'tailsignal_post_types', array( 'post' ) )
			->andReturn( array( 'post', 'page' ) );

		Functions\expect( 'add_meta_box' )->twice();

		$meta = new TailSignal_Meta_Box();
		$meta->add_meta_box();
		$this->assertTrue( true );
	}
}
