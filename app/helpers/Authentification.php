<?php 
/**
* Authentification
*/

namespace app\helpers;

class Authentification
{	
	/**
	* Private key for encoding token
	*/
	private static $key = "879E2CD42918A753BD8964D2F67FA";

	/**
	* Token decrypted
	*/
	public static $token_decrypt;

	/**
	* Token crypted
	*/
	public static $token_crypt;
	
	/**
	* Header of the token
	*/
	public static $header; 

	/**
	* Token body with information
	*/
	public static $body;

	/**
	* Token signature
	*/
	public static $signature;


	/**
	* Create a token for auth
	* @param array $a an array with information
	* @return string token 
	*/
	public static function createToken($a)
	{	
		return \JWT::encode($a, self::$key);
	}


	/**
	* Verify auth token
	* @return boolean/object token 
	*/
	static function verifyToken()
	{	
		$t = self::get();
		return ($t) ? self::decodeToken($t) : false;			
	}


	/**
	* Get the token from server
	* @return boolean/string token 
	*/
	private static function get()
	{
		$s = $_SERVER;

		// Token exemple || Must be send from the front APP
		// $s['HTTP_AUTHORIZATION'] = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MX0.BvVGpewtuIcJ7W2JZ5FU877jSX0MF_n_u6GbeVt9fSU";

		if(isset($s['HTTP_AUTHORIZATION'])) {
			if(strlen($s['HTTP_AUTHORIZATION']) > 0) {
				return self::$token_crypt = $s['HTTP_AUTHORIZATION'];
			}
		}	
		return false;	
	}


	/**
	* Decrypt token part
	* @param string a token to decrypt
	* @return string decode 
	*/
	private static function decoupe($token)
	{
		$decoded = str_pad($token,4 - (strlen($token) % 4),'=');
	    return base64_decode(strtr($decoded, '-_', '+/'));
	}


	/**
	* Decode token
	* @param string $token token
	* @return object token 
	*/
	private static function decodeToken($token)
	{	
		try {
			self::$token_decrypt = \JWT::decode($token, self::$key);
			self::multipart(self::$token_crypt);
			
			return self::$token_decrypt;	
	    } catch(Exception $ex) {
	    	return false;
	    }
	}


	/**
	* Get and decrypt all parts of the token
	* @param string $token the token
	*/
	private static function multipart($token)
	{
		$parts = explode('.', $token);

		self::$header = json_decode(self::decoupe($parts[0]));
		self::$body = json_decode(self::decoupe($parts[1]));
		self::$signature = self::decoupe($parts[2]);
	}
}
?>