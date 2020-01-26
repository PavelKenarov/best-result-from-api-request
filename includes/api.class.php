<?php
class api {

	const URL 		= 'http://hiring.rewardgateway.net/list';
	const USER 		= 'hard';
	const PASS 		= 'hard';
	
    /**
     * Get the response
     * @return array
     */
    public static function connect($attempts = 5){

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, self::USER.":".self::PASS);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		
		for ( $i = 0; $i < $attempts; $i ++ ) {
			
			$response  = curl_exec( $ch );
			$curl_info = curl_getinfo( $ch );

			// error, try again
			if ( curl_errno( $ch ) )
				continue; 

			// only accepting 200 as a status code.
			if ( $curl_info['http_code'] != 200 )
				continue; 

			if ( empty( $response ) )
				continue;

			return json_decode($response);
		}

		return null;

    }

}
?>