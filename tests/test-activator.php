<?php
/**
 * Tests for TailSignal_Activator and TailSignal_Deactivator.
 *
 * @package TailSignal
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__ ) . '/src/includes/class-tailsignal-db.php';
require_once dirname( __DIR__ ) . '/src/includes/class-tailsignal-activator.php';
require_once dirname( __DIR__ ) . '/src/includes/class-tailsignal-deactivator.php';

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
		Functions\expect( 'add_option' )->times( 11 );

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
		Functions\expect( 'add_option' )->times( 11 );

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

	/**
	 * Test activate sets all 11 default options.
	 */
	public function test_activate_sets_all_defaults() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );

		Functions\expect( 'dbDelta' )->andReturn( array() );

		$added_options = array();
		Functions\expect( 'get_option' )->andReturn( false );
		Functions\expect( 'add_option' )->andReturnUsing( function( $key, $value ) use ( &$added_options ) {
			$added_options[ $key ] = $value;
		} );

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->once();
		Functions\expect( 'get_role' )->andReturn( $role );

		TailSignal_Activator::activate();

		// Verify all 11 options are set.
		$expected_keys = array(
			'tailsignal_auto_notify',
			'tailsignal_expo_access_token',
			'tailsignal_default_title',
			'tailsignal_default_body',
			'tailsignal_use_featured_image',
			'tailsignal_dev_mode',
			'tailsignal_db_version',
			'tailsignal_portfolio_auto_notify',
			'tailsignal_portfolio_default_title',
			'tailsignal_portfolio_default_body',
			'tailsignal_portfolio_use_featured_image',
		);

		foreach ( $expected_keys as $key ) {
			$this->assertArrayHasKey( $key, $added_options, "Missing option: $key" );
		}
		$this->assertCount( 11, $added_options );
	}

	/**
	 * Test activate sets tailsignal_db_version to TAILSIGNAL_VERSION.
	 */
	public function test_activate_sets_db_version() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );

		Functions\expect( 'dbDelta' )->andReturn( array() );

		$added_options = array();
		Functions\expect( 'get_option' )->andReturn( false );
		Functions\expect( 'add_option' )->andReturnUsing( function( $key, $value ) use ( &$added_options ) {
			$added_options[ $key ] = $value;
		} );

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->once();
		Functions\expect( 'get_role' )->andReturn( $role );

		TailSignal_Activator::activate();

		$this->assertSame( TAILSIGNAL_VERSION, $added_options['tailsignal_db_version'] );
	}

	/**
	 * Test activate sets correct default values for portfolio options.
	 */
	public function test_activate_portfolio_defaults() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );

		Functions\expect( 'dbDelta' )->andReturn( array() );

		$added_options = array();
		Functions\expect( 'get_option' )->andReturn( false );
		Functions\expect( 'add_option' )->andReturnUsing( function( $key, $value ) use ( &$added_options ) {
			$added_options[ $key ] = $value;
		} );

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->once();
		Functions\expect( 'get_role' )->andReturn( $role );

		TailSignal_Activator::activate();

		$this->assertSame( '1', $added_options['tailsignal_portfolio_auto_notify'] );
		$this->assertSame( 'New Project: {post_title}', $added_options['tailsignal_portfolio_default_title'] );
		$this->assertSame( '{post_title} by {author_name}', $added_options['tailsignal_portfolio_default_body'] );
		$this->assertSame( '1', $added_options['tailsignal_portfolio_use_featured_image'] );
	}

	/**
	 * Test activate sets correct default values for base options.
	 */
	public function test_activate_base_defaults() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );

		Functions\expect( 'dbDelta' )->andReturn( array() );

		$added_options = array();
		Functions\expect( 'get_option' )->andReturn( false );
		Functions\expect( 'add_option' )->andReturnUsing( function( $key, $value ) use ( &$added_options ) {
			$added_options[ $key ] = $value;
		} );

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->once();
		Functions\expect( 'get_role' )->andReturn( $role );

		TailSignal_Activator::activate();

		$this->assertSame( '1', $added_options['tailsignal_auto_notify'] );
		$this->assertSame( '', $added_options['tailsignal_expo_access_token'] );
		$this->assertSame( 'New from {site_name}', $added_options['tailsignal_default_title'] );
		$this->assertSame( '{post_title}', $added_options['tailsignal_default_body'] );
		$this->assertSame( '1', $added_options['tailsignal_use_featured_image'] );
		$this->assertSame( '0', $added_options['tailsignal_dev_mode'] );
	}

	/**
	 * Test activate adds capability to administrator role.
	 */
	public function test_activate_adds_capability() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );

		Functions\expect( 'dbDelta' )->andReturn( array() );
		Functions\expect( 'get_option' )->andReturn( '1' ); // Options exist.

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )
			->with( 'tailsignal_manage' )
			->once();

		Functions\expect( 'get_role' )
			->with( 'administrator' )
			->andReturn( $role );

		TailSignal_Activator::activate();
		$this->assertTrue( true );
	}
}
