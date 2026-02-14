<?php
/**
 * Tests for TailSignal_Activator and TailSignal_Deactivator.
 *
 * @package TailSignal
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__ ) . '/includes/class-tailsignal-db.php';
require_once dirname( __DIR__ ) . '/includes/class-tailsignal-activator.php';
require_once dirname( __DIR__ ) . '/includes/class-tailsignal-deactivator.php';

class Test_TailSignal_Activator extends TailSignal_TestCase {

	/**
	 * Test activate creates tables and sets defaults.
	 */
	public function test_activate() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );

		// create_tables calls dbDelta.
		Functions\expect( 'dbDelta' )->andReturn( array() );

		// get_option returns false for new options.
		Functions\expect( 'get_option' )
			->andReturn( false );

		// add_option called for each default.
		Functions\expect( 'add_option' )->times( 7 );

		// get_role + add_cap.
		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->with( 'tailsignal_manage' )->once();

		Functions\expect( 'get_role' )
			->with( 'administrator' )
			->andReturn( $role );

		TailSignal_Activator::activate();
		$this->assertTrue( true );
	}

	/**
	 * Test activate skips existing options.
	 */
	public function test_activate_skips_existing_options() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );

		Functions\expect( 'dbDelta' )->andReturn( array() );

		// Options already exist (get_option returns non-false).
		Functions\expect( 'get_option' )->andReturn( '1' );

		// add_option should NOT be called.
		// (Brain Monkey will verify no unexpected calls.)

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->once();

		Functions\expect( 'get_role' )
			->with( 'administrator' )
			->andReturn( $role );

		TailSignal_Activator::activate();
		$this->assertTrue( true );
	}

	/**
	 * Test activate handles null administrator role.
	 */
	public function test_activate_no_admin_role() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );

		Functions\expect( 'dbDelta' )->andReturn( array() );
		Functions\expect( 'get_option' )->andReturn( false );
		Functions\expect( 'add_option' )->times( 7 );

		Functions\expect( 'get_role' )
			->with( 'administrator' )
			->andReturn( null );

		// Should not throw even without admin role.
		TailSignal_Activator::activate();
		$this->assertTrue( true );
	}

	/**
	 * Test deactivate clears cron events.
	 */
	public function test_deactivate_clears_cron() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		Functions\expect( 'wp_clear_scheduled_hook' )->twice();

		// No scheduled notifications.
		$wpdb->shouldReceive( 'get_results' )->andReturn( array() );

		TailSignal_Deactivator::deactivate();
		$this->assertTrue( true );
	}

	/**
	 * Test deactivate unschedules individual events.
	 */
	public function test_deactivate_unschedules_individual() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		Functions\expect( 'wp_clear_scheduled_hook' )->twice();

		$notification     = new stdClass();
		$notification->id = 5;

		$wpdb->shouldReceive( 'get_results' )->andReturn( array( $notification ) );

		Functions\expect( 'wp_next_scheduled' )
			->with( 'tailsignal_send_scheduled', array( 5 ) )
			->andReturn( 1234567890 );

		Functions\expect( 'wp_unschedule_event' )
			->with( 1234567890, 'tailsignal_send_scheduled', array( 5 ) )
			->once();

		TailSignal_Deactivator::deactivate();
		$this->assertTrue( true );
	}

	/**
	 * Test deactivate skips notifications without scheduled cron.
	 */
	public function test_deactivate_skips_unscheduled() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		Functions\expect( 'wp_clear_scheduled_hook' )->twice();

		$notification     = new stdClass();
		$notification->id = 5;

		$wpdb->shouldReceive( 'get_results' )->andReturn( array( $notification ) );

		Functions\expect( 'wp_next_scheduled' )
			->with( 'tailsignal_send_scheduled', array( 5 ) )
			->andReturn( false );

		// wp_unschedule_event should NOT be called.
		TailSignal_Deactivator::deactivate();
		$this->assertTrue( true );
	}
}
