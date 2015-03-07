<?php 
/**
* Controller
*/

namespace app\controllers;

class Controller
{	
	/*
	* FatFreeFramework Instance
	*/
	public $f3;

	/*
	* The name of the default model
	*/
	public $model;

	/*
	* Infos about the user from the token decrypt
	*/
	public $userRequest;

	/*
	* Date to render
	*/
	protected $data = array();

	/*
	* Error to render
	*/
	protected $errorData = array();

	/*
	* Routes that don't need authentication
	*/
	protected $safe_routes = array(
							'/',
							'/users/signin', 
							'/users/signup'
						); 
	
	/*
	* Arguments available in URL GET
	*/
	private $arg_ok = array(
						'limit' => 'int',
						'order' => 'string',
						'by' => 'string',
						'offset' => 'int'
	 				);

		
	function __construct()
	{	
		// Insert F3
		$this->f3 = \Base::instance();		

		// Get name of the default model
		$this->model = substr(get_class($this), 0, -10);	

		// Load default model like nameModel.php from nameModelController.php
		$this->model = substr($this->model, 16);
		if(strlen($this->model) != 0 && class_exists('\\app\\models\\'. $this->model)) {
			$this->loadModel($this->model);
		}
	}


	/**
	* Load a Model Object 
	* @param $name name of the model
	* @return object 
	*/
	protected function loadModel($name)
	{	
		$class = '\\app\\models\\' . $name;
		$model = new $class();
		(!isset($this->$name)) ? $this->$name = $model : \app\helpers\API::error(404, 'No Model found');
		return $this;
	}


	/*
	* Method from the Base URL
	*/
	public function index()
	{	
		$this->setError(200, $this->f3->get('message_index'));
	}


	/******************************************************\
	*
	*   FAT FREE METHOD AFTER/BEFORE
	*
	\******************************************************/


	/**
	* Check if route needs to be authenticate
	* use Authentification class
	*/
	public function beforeroute()
	{	
		// Decoding Body of FatFree from the .post(data) in AngularJS
		$this->decodingBody();

		// Route call
		$route = $this->f3->get('PATTERN'); 
		// Is the route in the safe route array
		if(!in_array($route, $this->safe_routes)) {
			// check the token
			if(\app\helpers\Authentification::verifyToken()){
				// User infos in token body
				$this->UserRequest = \app\helpers\Authentification::$body;
			}else { 
				$this->setError(401, 'Token invalid');
			}
		}		
	}


	/**
	* Render data or error to the front
	*/
	public function afterroute()
	{
		// If there is an error
		if(isset($this->errorData['code'])){
			\app\helpers\API::error($this->errorData['code'], $this->errorData['message']);
		}else {
			\app\helpers\API::success($this->data);
		}
	}


	/******************************************************\
	*
	*   SET DATA & UNSET & ERROR METHOD 
	*
	\******************************************************/

	/**
	* Set data to the render
	* @param array $data 
	* @param integer $index from the data
	* @param string $key key for the new data
	*/
	protected function setData($data, $key = false, $index = 0)
	{	
		// if there is already data set
		if(!empty($this->data) && isset($this->data[0])) {
			// insert data in the Data lready set
			if (is_array($data)) {
				$this->data[$index]->$key = (array) $data;
			}else {
				$this->data[$index]->$key = $data;
			}
			
		}else {
			// If there is no data insert
			$this->data = (is_string($data) || is_bool($data)) ? array('message' => $data) : $data;	
		}
	}


	/**
	* Unset data before the render
	* Only on the first level of $this->data MAJ WIP
	* @param array or string $label label to unset 
	*/
	protected function unsetData($label)
	{	
		foreach ($this->data as $key => $value) {
			if(is_array($label)){
				foreach ($label as $lab) {
					unset($this->data[$key]->$lab);
				}
			}else {
				unset($this->data[$key]->$label);
			}
			
		}
	}


	/**
	* Create an error to render and cut the script before request
	* @param integer $code header http code
	* @param string $message message explain the error
	*/
	protected function setError($code, $message)
	{
		$this->errorData = array('code' => $code, 'message' => $message);
		$this->afterroute();
	}


	/**
	* Parse the Body hive varible and set the object return 
	* into $this->dataPost property
	* @return nothing 
	*/
	protected function decodingBody()
	{
		$this->dataPost = json_decode($this->f3->get('BODY'));
	}

	/**
	* Upload a media 
	* @param $where string where upload the media (spots, user, challenges)
	* @return array upload::return
	*/
	public function uploadImage($where = 'spots')
	{	
		$upload = new \app\helpers\Upload();
		$file = array();
		$file['photo'] = $this->f3->get('FILES')['file'];

		switch ($where) {
			case 'spots':
				$where = 'spots';
				break;
			case 'users':
				$where = 'users';
				break;
			case 'challenges':
				$where = 'challenges';
				break;
			default:
				$where = 'spots';
				break;
		}
		
		return $upload->save($file, $where);
	}


	/******************************************************\
	*
	*   (CRUD) | KEEP IT SIMPLE STUPID
	*
	\******************************************************/

	/**
	* Get a data
	*/
	public function get()
	{	
		$m = $this->model;
		$this->setData($this->$m->load(intval($this->f3->get('PARAMS.id'))));
	}


	/**
	* Post a data
	*/
	public function post()
	{	
		$m = $this->model;
		$this->setData($this->$m->save($this->dataPost));
	}


	/**
	* Update a data
	*/
	public function put()
	{	
		$m = $this->model;

		// id to update
		if($this->f3->get('PARAMS.id')) {
			// create conditions with the id
			$c = array('conditions' => array('id' => $this->f3->get('PARAMS.id')));			
			// send the request and set Data return
			$this->setData($this->$m->update($this->dataPost, $c));
		}else {
			$this->setError(404, 'Error with the id / No id pass');
		}
	}


	/**
	* Delete a data
	*/
	public function delete()
	{	
		$m = $this->model;
		$this->setData($this->$m->delete(intval($this->f3->get('PARAMS.id'))));
	}


	/**
	* Get all data
	*/
	public function getAll()
	{	
		$m = $this->model;		
		// Add condition from params in Url
		$c = $this->insertCondition();
		$this->setData($this->$m->findAll());
	}


	/******************************************************\
	*
	*   CONDITIONS AS PARAMETER
	*
	\******************************************************/


	/**
	* Add conitions from URL parameters
	* @return array
	*/
	private function insertCondition()
	{	
		// Base of the array
		$c = array();
		// All the conditions in URL
		$g = $this->f3->get('GET');

		if(!empty($g)) {
			foreach ($g as $k => $v) {
				// Check if the value is ok
				if($this->acceptedCond($k)) {
					// transform string in int in case of
					if($this->arg_ok[$k] == "int"){
						intval($v);
					}
					$c[$k] = $v;
				}
			}
		}
		return $c;
	}


	/**
	* Check if the condition is accept
	* @return boolean 
	*/
	private function acceptedCond($cond)
	{	
		$args = array_keys($this->arg_ok);
		return (in_array($cond, $args)) ? true : false;
	}

}
?>