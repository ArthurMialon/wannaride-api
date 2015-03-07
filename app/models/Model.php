<?php 
/**
* Model
*/

namespace app\models;

class Model
{	
	/*
	* PDO object
	*/
	protected $db; 			

	/*
	* All request to $db 	
	*/	
	private static $logger = array();
	
	/*
	* BDD by default	
	*/	
	public $BDD = 'default'; 	

	/*
	* Table use
	*/ 	
	public $table = false; 	

	/*
	* Default limit
	*/		
	public $limit = 10;		

	/*
	* Array with connections to databases sharing with others model classes	 
	*/
	static $connections = array();  	

	
	public function __construct()
	{	

		$this->f3 = \Base::instance();
		$b = $this->f3->get('databases');

		/**
		*	Connection to the database
		*/
		// If there is already a connection
		if (isset(self::$connections[$this->BDD])) {
			$this->db = Model::$connections[$this->BDD];
		}else {
			if(isset($b[$this->BDD])){
				// return PDO Object
				self::$connections[$this->BDD] = \app\helpers\Database::connexion($b[$this->BDD]);
				$this->db = self::$connections[$this->BDD];
			}else {
				die('Database --> <strong>' . $this->BDD . ' </strong> doesn\'t exist...');
			}
		}

		/**
		*	Table by default;
		*/
		$this->table = ($this->table === false) ? strtolower(get_class($this)) : $this->table;
	}

	/**
	* Insert a data
	* @param array/object $data  insert data
	* @param string $table table for the data
	* @return integer id insert
	*/
	public function save($data, $table = false)
	{	
		$table = (!$table) ? $this->table : $table;

		$data = (array) $data;
		$sql = "";
		$values = "";
		$keys = "";

		foreach ($data as $key => $value) {
			if(is_string($value)){
				$values .= ", '" . $this->secure($value) . "' ";
			}else {
				$values .= ", " . $this->secure($value);
			}			
			$keys .= ",".$key;
		}

		$sql = "INSERT INTO $table (id $keys) VALUES (NULL $values)";

		return $this->exec($sql);
	}

	/**
	* ²e a data
	* @param array $values  values
	* @param array $cond  conditions
	* @param string $table table for the data
	* @return boolean
	*/
	public function update($values, $cond, $table = false)
	{
		$table = (!$table) ? $this->table : $table;

		$req =  "UPDATE $table SET ";
		$colonnes = "";

		foreach ($values as $k => $v) {
			$v = $this->secure($v);
			$colonnes .= (is_string($v)) ? " $k='$v'," : " $k=$v,";
		}

		$colonnes = substr($colonnes, 0, -1);

		$req .= $colonnes;
		$req .= $this->addCondition($cond, $table);

		return $this->exec($req, false);
	}

	/** 
	* Delete a data
	* @param integer $id item id OR an array with condtions
	* @param string $table table for the data
	* @return boolean
	*/
	public function delete($id, $table = false)
	{	
		$table = (!$table) ? $this->table : $table;

		$req = "DELETE FROM $table ";

		if(is_array($id)){
			$req .= $this->addCondition($id, $table);
		}else {
			$id = intval($id);
			if($id !=0){
				$req .= "WHERE id=$id LIMIT 1";
			}else {
				return false;
			}			
		}
				
		return $this->exec($req, false);
	}

	/**
	* Find a data
	* @param array $cond  conditions
	* @param string $table table for the data
	* @return object data
	*/
	public function find($cond, $table = false)
	{	
		$table = (!$table) ? $this->table : $table;

		$req = "SELECT " . $this->addColumns($cond);
		$req .= " FROM " . $table;
		$req .= $this->addCondition($cond, $table);

		return $this->exec($req);
	}

	/**
	* Get just a data
	* @param integer $id  id
	* @param string $table table for the data
	* @return object data
	*/
	public function load($id, $table = false)
	{
		$table = (!$table) ? $this->table : $table;

		$cond = array(
			'conditions' => array(
				'id' => intval($id)
			),
			'limit' => 1
		);

		return $this->find($cond, $table);
	}

	/**
	* Find all datas
	* @param array $cond  conditions
	* @param string $table table for the data
	* @return object data
	*/
	public function findAll($cond = array(), $table=false)
	{
		$table = (!$table) ? $this->table : $table;

		$cond['limit'] = (isset($cond['limit'])) ? $cond['limit'] : false;

		return $this->find($cond, $table);
	}

	/**
	* Find only one data
	* @param array $cond  conditions
	* @param string $table table for the data
	* @return object data
	*/
	public function findOne($cond, $table = false)
	{
		$table = (!$table) ? $this->table : $table;

		$cond['limit'] = 1;

		return $this->find($cond, $table);
	}

	/**
	* Find first data inserted
	* @param array $cond  conditions
	* @param string $table table for the data
	* @return object data
	*/
	public function findFirst($cond, $table = false)
	{
		$table = (!$table) ? $this->table : $table;

		$cond['order'] = 'ASC';
		$cond['by'] = 'id';

		return $this->findOne($cond, $table);
	}

	/**
	* Find last data inserted
	* @param array $cond  conditions
	* @param string $table table for the data
	* @return object data
	*/
	public function findLast($cond, $table = false)
	{
		$table = (!$table) ? $this->table : $table;

		$cond['order'] = 'DESC';
		$cond['by'] = 'id';

		return $this->findOne($cond, $table);
	}

	/**
	* Count data
	* @param string $column  column
	* @param array $cond  conditions
	* @param string $table table for the data
	* @return integer number of data
	*/
	public function count($column = false, $cond, $table = false)
	{	
		$table = (!$table) ? $this->table : $table;
		$column = (!$column) ? '*' : $column;

		$req = "SELECT COUNT($column) AS nbr FROM $table ";
		$req .= $this->addCondition($cond, $table);

		return $this->exec($req);
	}

	/**
	* Increment a data
	* @param array $colonne  la colonne
	* @param integer $x incrementation
	* @param $cond conditions
	* @param string $table table for the data
	* @return boolean
	*/
	public function increment($column, $x, $cond, $table = false)
	{
		$table = (!$table) ? $this->table : $table;	
		$x = intval($x);
		$req = "UPDATE $table SET $column = $column + $x";
		$req .= $this->addCondition($cond, $table);

		return $this->exec($req, false);
	}

	/**
	* Decrement a data
	* @param array $colonne  la colonne
	* @param integer $x decrementation
	* @param $cond conditions
	* @param string $table table for the data
	* @return boolean
	*/
	public function decrement($column, $x, $cond, $table = false)
	{
		$table = (!$table) ? $this->table : $table;	
		$x = intval($x);
		$req = "UPDATE $table SET $column = $column - $x";
		$req .= $this->addCondition($cond, $table);

		return $this->exec($req);
	}


	/**
	* Search a data
	* @param string or array $column column to match
	* @param string $pattern pattern
	* @param array $cond conditions
	* @param boolean $like or not like
	* @param string $table table for the data
	* @return array datas
	*/
	public function search($columns = false, $pattern, $cond, $like=true, $table = false)
	{
		$table = (!$table) ? $this->table : $table;

		if(!$columns){
			return false;
		}else {
			$req = "SELECT * ";
			$req .= " FROM $table ";
			$req .= " WHERE $columns "; // SEPARER LES COLUMNS PAR DES OR
			$req .= ($like) ? "LIKE " : " NOT LIKE ";
			$req .= "'" . $this->secure($pattern) . "'";

			$req .= (isset($cond['conditions'])) ? " AND " . $this->addCondition($cond, $table) : $this->addCondition($cond, $table);
		}

		return $req;
		return $this->exec($req);
	}

	/**
	* Paginate datas
	* @param integer $page acive page
	* @param integer $perpage data per page
	* @param array $cond conditions
	* @param string $table table for the data
	* @return array datas
	*/	
	public function paginate($page, $perpage, $cond, $table = false)
	{	
		$table = (!$table) ? $this->table : $table;

		$cond['offset'] = ( $perpage * $page ) - $perpage;
		$cond['limit'] = $perpage;

		// AJouter le nombre de page max
		return $this->find($cond, $table);
	}

	/**
	* Get all columns from a table
	* @param string $table table
	* @return object
	*/
	public function getSchema($table = false)
	{
		$table = (!$table) ? $this->table : $table;
		return $this->exec("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='$table'");
	}

	/**
	* Get all tables from the database
	* @return object
	*/
	public function getTables()
	{
	}

	/**
	* Exectute a SQL request
	* @param string $req SQL syntax
	* @param boolean $fetch return datas or boolean
	* @return boolean or object
	*/
	public function exec($req, $fetch = true)
	{	
		// Debugging
		$this->setLog($req);

		$sql = $this->db->prepare($req);
		if(!$sql) { return $this->db->errorInfo(); }

		$sql->execute();

		if($this->is_insert_into($sql->queryString)) {
			// Return the new insert data
			$new_id = $this->db->lastInsertId();
			return $this->exec("SELECT * FROM $this->table WHERE id=$new_id");
		}else {
			$data = ($fetch) ? $sql->fetchAll(\PDO::FETCH_OBJ) : array('status' => true); 
			return $data;	
		}
		
	}

	/**
	* Chack if it is a INSERT INTO query
	* @param string $sql SQL syntax
	* @return boolean
	*/
	public function is_insert_into($sql)
	{	
		$req = explode(' ', $sql);

		if($req[0] == "INSERT" AND $req[1] == "INTO") {
			return true;
		}
		return false;
	}

	/**
	* Secure Datas before executing
	* $value
	* @return secure value
	*/
	public function secure($value)
	{	
		if(is_string($value)) { return htmlentities($value); }
		else { return intval($value); }
	}

	/**
	* Add column to request
	* @param array $cond $conditions
	*/
	private function addColumns($cond)
	{
		return (isset($cond['columns'])) ? $cond['columns'] : "*";
	}

	/**
	* Add condition to request
	* @param array $cond  $conditions
	* @return string the conditions
	*/
	private function addCondition($cond, $table)
	{	
		$requete = "";

		/**
		* WHERE - TODO [ OR conditions system & < > system ]
		*/
		if (isset($cond['conditions'])) {
			$requete .= ' WHERE '; 
			$t = array();
			foreach ($cond['conditions'] as $k => $v) {
				$v = $this->secure($v);
				$t[] = (is_string($v)) ? "$k='$v'" : "$k=$v";
			}
			$requete .= implode(' AND ', $t);
		}

		/**
		* ORDERBY
		*/
		if (isset($cond['order'])) {
			switch ($cond['order']) {
				case 'ASC':
					$order = $cond['order'];
					break;
				case 'DESC':
					$order = $cond['order'];
					break;
				case 'RAND':
					$order = $cond['order'];
					break;				
				default:
					$order = 'DESC';
					break;
			}

			$thing = (isset($cond['by'])) ? $cond['by'] : 'id';

			$requete .= ' ORDER BY '.$thing.' '.$order;
		}

		/**
		* INNER JOIN
		*/
		if(isset($cond['inner'])) {
			$inner = $cond['inner'];
			$requete .= " INNER JOIN " . $inner['table'];
			$requete .= " ON ". $table . ".". $col1 ."=";
			$requete .= $inner['table'] . "." . $inner['col2'];
		}
		// i.e INNER JOIN spots ON user.id = spots.user_id

		/**
		* LIMIT
		*/
		if(isset($cond['limit']) AND !$cond['limit']){
			$requete .= "";
		}else if(isset($cond['limit'])) {
			$requete .= ' LIMIT '. intval($cond['limit']);
		}else {
			$requete .= ' LIMIT '. $this->limit;
		}

		/**
		* OFFSET
		*/
		if(isset($cond['offset'])){
			$requete .= ' OFFSET ' . intval($cond['offset']);
		}

		return $requete;
	}

	/**
	* Show request
	* @return array all SQL request done
	*/
	static function log()
	{
		return json_encode(self::$logger);
	}

	private function setLog($req)
	{	
		return self::$logger[] = $req;
	}
}
?>