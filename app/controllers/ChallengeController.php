<?php 
/**
* Challengecontroller
*/

namespace app\controllers;

class ChallengeController extends Controller
{	

	/**
	* Get all challenges of a user
	*/
	public function getChallenge() 
	{
		$this->setData($this->Challenge->getAllChallenge($this->f3->get('PARAMS.id')));
	}

	/**
	* Get the last challenge of user's followers
	*/
	public function getChallengeFollowers() 
	{
		$this->setData($this->Challenge->getChallengeFollowers($this->UserRequest->id));
	}

	/**
	* Get all users of a challenge
	*/	
	public function getUsers()
	{
		$this->setData($this->Challenge->getUsers($this->f3->get('PARAMS.id')));
	}

	/**
	* Get all invitation of one user
	*/	
	public function getInvitation()
	{
		$user_id = $this->UserRequest->id;
		$this->setData($this->Challenge->getInvitation($user_id));
	}

	/**
	* Add challenge and his creator
	*/	
	public function addChallenge()
	{
		$data = $this->dataPost;

		// add challenge and get all his information
		$save = $this->Challenge->save($data);
		
		// get challenge's id and user's id 
		$challenge_id = $save[0]->id;
		$user_id = $this->UserRequest->id;

		// add the challenge's creator to users_has_challenges
		$this->Challenge->addUser($user_id,$challenge_id,1,1);

		$this->setData($save);
	}

	/**
	* Add challenge's participant
	*/	
	public function addUser() 
	{	
		$challenge_id = $this->f3->get('PARAMS.id');

		$users = $this->dataPost->users;

		$master_id = $this->Challenge->getMasterId($challenge_id);

		//add challenge's participants one by one
		foreach ($users as $user) {
			$user_id = $user->id;
			$this->Challenge->addUser($user_id,$challenge_id,0,0,$master_id);
		}

		$this->setData('Users add');
	}

	/*
	* Update the invitation's response of one user
	*/
	public function putInvitation() 
	{

		$challenge_id = $this->f3->get('PARAMS.id');

		$user_id = $this->UserRequest->id;

		$this->setData($this->updateInvitation($challenge_id, $user_id));
	}

	/**
	* Delete challenge and all challenge's participant
	*/	
	public function delete() 
	{
		parent::delete();

		//delete challenge's participants in users_has_challenges too
		$s = $this->Challenge->deleteAllUsers($this->f3->get('PARAMS.id'));
		$this->setData('Challenge delete');

	}

	/**
	* Delete invitation of one user
	*/	
	public function deleteInvitation() 
	{
		$challenge_id = $this->f3->get('PARAMS.id');

		$user_id = $this->UserRequest->id;

		$c = array(
			'conditions' => array(
				'challenges_id' => $challenge_id,
				'users_id' => $user_id
			)
		);
		//delete invitation of one user
		$s = $this->Challenge->delete($c, 'users_has_challenges');

		$this->setData('Invitation delete');

	}

}	
?>
