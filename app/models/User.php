<?php 
/**
* USER
*/

namespace app\models;

class User extends Model
{
	public $table = "users";

	/**
	* Log the user
	* @param string $mail user's mail
	* @param string $pass user's password
	* @return object users informations 
	*/
	public function login($mail , $pass)
	{
		return $this->findOne(array('conditions' => array('mail' => $mail, 'password' => $pass )));
	}


	/**
	* Check user password is right
	* @param integer $id user's id
	* @param string $pass user's password
	* @return object users informations 
	*/
	public function checkPassword($id, $pass)
	{
		$c = array( 
			'columns' => 'password',
			'conditions' => array(
				'id' => $id,
				'password' => $pass
				) 
			);

		return $this->findOne($c);
	}


	/**
	* Update user password
	* @param integer $id user's id
	* @param string $pass user's password
	* @return bool  
	*/
	public function changePassword($id, $pass)
	{
		$v =array('password' => $pass);
		$c = array('conditions' => array('id' => $id));

		return $this->update($v, $c);
	}

	/**
	* Check if the mail is already using by one other user
	* @param string $mail user mail
	* @return bool existing or not
	*/
	public function existingMail($mail)
	{	
		$c = array('conditions'=> array('mail' => $mail));

		$nb = $this->count('mail', $c);

		return ($nb[0]->nbr > 0) ? true : false;
	}


	/**
	* Get user's spots
	* @param integer $id user's id
	* @return object user's spots informations 
	*/
	public function getSpotsByUserID($id)
	{
		return $this->findAll(array('conditions' => array('users_id' => $id)), 'spots');
	}


	/**
	* Add sport to user
	* @param integer $user_id user's id
	* @param string $sport_id sport's id
	* @return Object 
	*/
	public function addSport($user_id, $sport_id)
	{	
		return $this->save(array('users_id' => $user_id, 'sports_id' => $sport_id), 'users_has_sports');
	}


	/**
	* Remove sport to user
	* @param integer $user_id user's id
	* @param string $sport_id sport's id
	* @return bool 
	*/
	public function removeSport($user_id, $sport_id)
	{
		$c = array('conditions' => array('sports_id' => $sport_id, 'users_id' => $user_id));
		return $this->delete($c, 'users_has_sports');
	}


	/**
	* Get all sport to user
	* @param integer $user_id user's id
	* @return object user's sport information 
	*/
	public function getAllSport($user_id)
	{	
		$data = array();

		$c = array('conditions' => array('users_id' => $user_id));
		$sports_id = $this->findAll($c, 'users_has_sports');

		foreach ($sports_id as $sport) {
			$data[] = $this->load($sport->sports_id, 'sports')[0];
		}

		return $data;
	}


	/**
	* Get followers or follows of a user
	* @param $id user id
	* @param $him know if we should get his follower or his follow
	* @return array all users
	*/
	public function getFollows($id, $him = true)
	{	
		// His followers or his follow
		$him = (!$him) ? 'users_id' : 'user_follow';

		// Data return
		$data = array();

		// Get followers id or follows id
		$followers = $this->findAll(array('conditions' => array( $him => $id )), 'follow');

		// Load each user form his id
		foreach ($followers as $f) {
			// His followers or his follow
			$id = ($him == 'user_follow') ? $f->users_id : $f->user_follow;	

			$u = $this->load($id)[0];

			unset($u->password);
			unset($u->token_signup);

			$data[] = $u;
		}
		
		return $data;	
	}


	/**
	* Searching user from his name
	* @param $name fullname of the user
	* @return array all users found
	*/
	public function searchByName($name)
	{	
		// Secure the data before MySQL request
		$this->secure($name);
		return $this->exec("SELECT * FROM users WHERE CONCAT(firstname, ' ', lastname) LIKE '%$name%'");
	}
	

	/**
	* Update the photo of the user
	* @param $img_path string the image path
	* @param $user_id the user id
	* @return boolean
	*/
	public function updatePhoto($img_path, $user_id)
	{
		$c = array('conditions' => array('id' => $user_id));
		return $this->update(array('profil_photo'=> $img_path), $c);
	}

}
?>