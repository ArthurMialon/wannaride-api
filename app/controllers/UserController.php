<?php 
/**
* Usercontroller
*/

namespace app\controllers;

class UserController extends Controller
{	

	/**
	* Surcharge on the CRUD 
	* Add data about sport too
	*/
	public function get()
	{
		parent::get();
		// Unset sensible data from user
		$this->unsetData(array('token_signup', 'password'));
		// If there is 1 user set and add his sports data
		if(isset($this->data[0])){
			$s = $this->User->getAllSport($this->data[0]->id);
			$this->setData($s, 'sports');
		}		
	}


	/**
	* Surcharge on the CRUD getAll to get sports too
	* Add data about sport too
	*/
	public function getAll()
	{
		parent::getAll();
		// Unset sensible data from user
		$this->unsetData(array('token_signup', 'password'));		
		// Add sports data for each user
		foreach ($this->data as $key => $value) {
			$s = $this->User->getAllSport($this->data[$key]->id);
			$this->setData($s, 'sports', $key);
		}		
	}

	/**
	* Surcharge on the CRUD put to security
	* Update only connected user 
	*/
	public function put()
	{
		if($this->f3->get('PARAMS.id') == $this->UserRequest->id) {
			parent::put();
		}
	}


	/**
	* Search users from their names
	*/
	public function searchByName()
	{	
		$name = $this->f3->get('PARAMS.name');
		$this->setData($this->User->searchByName($name));
	}


	/**
	* Sign In user
	*/
	public function signIn()
	{	
		$data = $this->dataPost;
		
		// Login the user
		$user = $this->User->login($data->mail, $this->hashPassword($data->password));

		// // If user find
		if($user) {
			// Add token to user data
			$user[0]->token = $this->initToken($user);
			// Render User infos
			$this->setData($user);
			$this->unsetData(array('token_signup', 'password'));
		}else {
			// Render Error
			$this->setError(404, 'Mauvais mot de passe ou mauvaise adresse mail');
		}		
	}


	/**
	*  Sign up user
	*  Check if the mail already exist too
	*/
	public function signUp()
	{	
		$user = $this->dataPost;

		if($user) {
			if(!$this->User->existingMail($user->mail)){
				// Check if passwords are same
				if($user->confirmepass == $user->password) {
					unset($user->confirmepass);
					// Hash password
					$user->password = $this->hashPassword($user->password);

					// Create user
					$user = $this->User->save($user);

					// Add token to the user
					$user[0]->token = $this->initToken($user);

					// Set the user in render data 
					$this->setData($user);
				}else {
					$this->setError(404, "Les mots de passes ne sont pas identiques");
				}	
			}else {
				$this->setError(404, "Cette adresse mail existe déjà");
			}		
		}else {
			$this->setError(404, "No user send");
		}
	}


	/**
	*  Update & Upload the user photo 
	*/
	public function updatePhoto()
	{	
		$upload = $this->uploadImage('users');

		// If it's ok
		if($upload['code'] == 200) {
			// Upload réussi // PATH \\
			$imgPath = $upload['data'];

			// Update the photo
			$this->setData($this->User->updatePhoto($imgPath, $this->UserRequest->id));
		}else {
			$this->setError($upload['code'], $upload['data']);
		}
	}


	/**
	* Get all user Sport
	*/
	public function getSports()
	{
		$this->setData($this->User->getAllSport($this->f3->get('PARAMS.id')));
	}


	/**
	* Add a sport to the user
	*/
	public function addSport() 
	{	
		$this->User->addSport($this->UserRequest->id, $this->dataPost->sport_id);

		$this->setData('Sports add');
	}


	/**
	* Remove a sport from the user
	*/
	public function removeSport()
	{
		$sport_id = $this->f3->get('PARAMS.sport_id');
		$user_id = $this->UserRequest->id;

		$this->setData($this->User->removeSport($user_id, $sport_id));
	}


	/**
	* Find Spots From A user
	*/
	public function getSpots()
	{	
		$this->setData($this->User->getSpotsByUserID($this->f3->get('PARAMS.id')));
	}


	/**
	* Get spots from the user connect
	*/
	public function getUserSpots()
	{
		$this->setData($this->User->getSpotsByUserID($this->UserRequest->id));
	}


	/**
	* Get all users following the actual user
	*/
	public function getFollowers()
	{
		$id = $this->f3->get('PARAMS.id');
		$this->setData($this->User->getFollows($id));
	}


	/**
	* Get all users that the actual follows
	*/
	public function getFollows()
	{
		$id = $this->f3->get('PARAMS.id');
		$this->setData($this->User->getFollows($id, false));
	}

	/**
	* Get all user's media
	*/
	public function getUserMedia()
	{	
		if($this->f3->get('PARAMS.id') == 0) {
			$c = array(
				'conditions' => array(
					'users_id' => $this->f3->get('PARAMS.id')
				)
			);

			$this->setData($this->findAll($c, 'medias'));	
		}else {
			$this->setError(404, 'Identifiant invalide');
		}		
	}


	/**
	* Reset the user password
	*/
	public function resetPassword()
	{	
		$id 	  = $this->UserRequest->id;
		$pass_old = $this->hashPassword($this->dataPost->pass_old);
		$pass_new = $this->hashPassword($this->dataPost->pass_new);

		if($this->User->checkPassword($id, $pass_old)) {
			$this->setData($this->User->changePassword($id, $pass_new));
		}else {
			$this->setError(404,'Mauvais mot de passe');
		}
	}
	

	/**
	* Hash password with BCrypt
	* @return string hash password
	*/
	private function hashPassword($pass)
	{
	    return \Bcrypt::instance()->hash($pass, 'WHQnWEydq9iOBSvqfrpjrr', 04);
	}


	/**
	* Create the token for the user
	* @param $user array User
	* @return string token signed
	*/
	private function initToken($user) 
	{
		// Data need in the token
		$for_token = array(
			'id'		     => $user[0]->id,
			'mail'		     => $user[0]->mail,
			'firstname'      => $user[0]->firstname, 
			'lastname'       => $user[0]->lastname,
			'firstconnect'   => $user[0]->firstconnect,
		);

		// Return the token
		return \app\helpers\Authentification::createToken($for_token);
	}

}	
?>
