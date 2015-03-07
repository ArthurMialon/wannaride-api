<?php 
/**
* CHALLENGES
*/

namespace app\models;

class Challenge extends Model
{
	public $table = "challenges";

	/**
	* Get challenge's master id
	* @param string $challenges_id challenge's id
	* @return string
	*/
	public function getMasterId($challenges_id)
	{
		$c = array(
				'conditions' => array(
					'challenges_id' => $challenges_id,
					'master' => 1
				)
			);

		$masters = $this->findOne($c, 'users_has_challenges');

		return $masters[0]->users_id;
	}		

	/**
	* Add users
	* @param string $users_id user's id
	* @param string $challenges_id challenge's id
	* @param int $master user's title
	* @param int $accept user's accept
	* @param int $sender sender's id
	* @return bool
	*/
	public function addUser($users_id, $challenges_id, $master, $accept, $sender=NULL)
	{
		return $this->save(array('users_id' => $users_id, 'challenges_id' => $challenges_id, 'accept' => $accept , 'master' => $master, 'sender' => $sender), 'users_has_challenges');
	}	

	/**
	* Get all challenge of one user
	* @param string $user_id user's id
	* @return array user's challenge information
	*/
	public function getAllChallenge($user_id)
	{
		$data = array();

		$c = array(
			'conditions' => array(
				'users_id' => $user_id,
			)
		);

		//get all user's challenges 
		$challenges = $this->findAll($c, 'users_has_challenges');

		foreach ($challenges as $challenge) {
			//get challenge one by one and add them in $data
			$data[] =  $this->load($challenge->challenges_id)[0];
		}

		return $data;	
	}

	/**
	* Get last challenge of follower's user
	* @param string $user_id user's id
	* @return array follower's challenge information of user
	*/
	public function getChallengeFollowers($user_id)
	{
		$data = array();

		$c = array(
			'conditions' => array(
				'users_id' => $user_id,
			)
		);

		//Get all user's followers 
		$followers = $this->findAll($c, 'follow');

		// Get the last challenge each followers 
		// Add them in $data 
		foreach ($followers as $follow) {
			$cfollow = array(
				'conditions' => array(
					'users_id' => $follow->user_follow,
					'accept' => 1
				)
			);

			$challenges = $this->findLast($cfollow, 'users_has_challenges');

			foreach ($challenges as $challenge) {
				$data[] =  $this->load($challenge->challenges_id)[0];
			}
		}

		return $data;
	}

	/**
	* Get all users of a challenge
	* @param string $challenge_id challenge's id
	* @return array challenge's users information
	*/
	public function getUsers($challenge_id) {
		$data = array();

		$c = array(
			'conditions' => array(
				'challenges_id' => $challenge_id,
			)
		);

		//Get all challenge's participant
		$users = $this->findAll($c, 'users_has_challenges');

		foreach ($users as $user) {
			 $u =  $this->load($user->users_id, 'users')[0];
			 unset($u->password);
			 unset($u->token_signup);
			 $data[];
		}

		return $data;	
	}

	/**
	* Get invitation with not response
	* @param string $user_id user's id
	* @return array challenge and user information of invitation
	*/
	public function getInvitation($user_id) {
		$data = array();

		$c = array(
			'conditions' => array(
				'users_id' => $user_id,
			)
		);

		//Get all challenge's participant
		$challenges = $this->findAll($c, 'users_has_challenges');

		$i = 0;
		foreach ($challenges as $challenge) {
			$data[] =  $this->load($challenge->challenges_id)[0];
			$data[$i]['sender'] +=  $this->load($challenge->sender, 'users')[0];
			$i++;
		}

		return $data;
	}

	/**
	* Update invitation response
	* @param string $challenge_id challenge's id
	* @param string $user_id user's id
	* @return object invitation information
	*/
	public function updateInvitation($challenge_id, $user_id) {
		
		// Response yes to the invitation, if response no, the data in delete from the tables
		$update['accept'] = 1;

		$c = array(
			'conditions' => array(
				'challenges_id' => $challenge_id,
				'users_id' => $user_id
			)
		);

		return $this->update($update, $c, 'users_has_challenges');
	}

	/**
	* Delete all invitations of one challenge
	* @param string $challenge_id challenge's id
	* @return object invitation information
	*/
	public function deleteAllUsers($challenge_id) {
		
		$c = array(
			'conditions' => array(
				'challenges_id' => $challenge_id,
			)
		);

		$this->delete($c, 'users_has_challenges');
	}

	
}
?>