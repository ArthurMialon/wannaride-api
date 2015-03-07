<?php 
/**
* Databases
*/

namespace app\helpers;

class Database
{

	static function connexion($db)
	{	
		
		$user   = $db['login'];
		$pass   = $db['password'];
		$host   = $db['host'];
		$dbName = $db['dbname'];

		try {
			$pdo = new \PDO(
				'mysql:host='.$host.';dbname='.$dbName.';charset=utf8',
				$user,
				$pass
			);
		} catch (PDOException $e) {
			API::error(404, 'Cannot connect to database');
			return false;
		}
		
		return $pdo;
	}	
}
?>