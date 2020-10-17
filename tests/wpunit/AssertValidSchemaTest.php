<?php

class AssertValidSchemaTest extends \Codeception\TestCase\WPTestCase {

	public function setUp(): void {
		$settings = get_option( 'graphql_general_settings' );
		$settings['public_introspection_enabled'] = 'on';
		update_option( 'graphql_general_settings', $settings );
		WPGraphQL::clear_schema();
		parent::setUp(); // TODO: Change the autogenerated stub

	}

	public function tearDown(): void {
		// your tear down methods here

		// then
		parent::tearDown();
		WPGraphQL::clear_schema();
	}

	public function testValidSchema() {
		$this->assertTrue( true );
	}

	// Validate schema.
	public function testSchema() {
		try {
			$request = new \WPGraphQL\Request();
			$request->schema->assertValid();

			// Assert true upon success.
			$this->assertTrue( true );
		} catch (\GraphQL\Error\InvariantViolation $e) {
			// use --debug flag to view.
			codecept_debug( $e->getMessage() );

			// Fail upon throwing
			$this->assertTrue( false );
		}
	}

	public function testIntrospectionQueriesDisabledForPublicRequests() {

		$settings = get_option( 'graphql_general_settings' );
		$settings['public_introspection_enabled'] = 'off';
		update_option( 'graphql_general_settings', $settings );

		$actual = graphql([
			'query' => '
			{
			  __type(name: "RootQuery") {
			    name
			  }
			  __schema {
			    queryType {
			      name
			    }
			  }
			}
			'
		]);

		codecept_debug( $actual );

		$this->assertArrayHasKey( 'errors', $actual );
		$this->assertSame( 'GraphQL introspection is not allowed, but the query contained __schema or __type', $actual['errors'][0]['message'] );

	}

	public function testIntrospectionQueriesByAdminWhenPublicIntrospectionIsDisabled() {

		$settings = get_option( 'graphql_general_settings' );
		$settings['public_introspection_enabled'] = 'off';
		update_option( 'graphql_general_settings', $settings );

		$admin = $this->factory()->user->create( [
			'role' => 'administrator'
		] );

		wp_set_current_user( $admin );

		$actual = graphql([
			'query' => '
			{
			  __type(name: "RootQuery") {
			    name
			  }
			  __schema {
			    queryType {
			      name
			    }
			  }
			}
			'
		]);

		codecept_debug( $actual );

		$this->assertArrayNotHasKey( 'errors', $actual );

	}

	public function testIntrospectionQueriesEnabledForPublicUsers() {

		$settings = get_option( 'graphql_general_settings' );
		$settings['public_introspection_enabled'] = 'on';
		update_option( 'graphql_general_settings', $settings );

		$actual = graphql([
			'query' => '
			{
			  __type(name: "RootQuery") {
			    name
			  }
			  __schema {
			    queryType {
			      name
			    }
			  }
			}
			'
		]);

		codecept_debug( $actual );

		$this->assertArrayNotHasKey( 'errors', $actual );

	}

}