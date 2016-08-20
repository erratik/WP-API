<?php

abstract class WP_Test_rest_TestCase extends WP_UnitTestCase {
	protected function assertErrorResponse( $code, $response, $status = null ) {

		if ( is_a( $response, 'CUTV_REST_Response' ) ) {
			$response = $response->as_error();
		}

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( $code, $response->get_error_code() );

		if ( null !== $status ) {
			$data = $response->get_error_data();
			$this->assertArrayHasKey( 'status', $data );
			$this->assertEquals( $status, $data['status'] );
		}
	}
}
