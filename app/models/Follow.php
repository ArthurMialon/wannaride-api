<?php 
/**
* FOLLOW
*/

namespace app\models;

class Follow extends Model
{
	public $table = "follow";

	/**
	* Unfollow a user
	* @param $users_id integer user id
	* @return boolean
	*/
	public function unFollow($users_id) 
	{	
		/* FIRST DECREMENT */
		$c = array('conditions' => array('id' => $users_id[1]));
		$this->decrement('nb_followers', 1, $c, 'users');

		/* SECOND DELETE */
		$c = array(
			'conditions' => array(
				'user_follow' => $users_id[1],
				'users_id' => $users_id[0]
			),
			'limit' => 1
		);

		$this->delete($c);

		return true;	
	}


	/**
	* Follow a user
	* @param $users_id integer user id
	* @return boolean
	*/
	public function newfollow($users_id)
	{	
		/* FIRST SAVE */
		$c = array('user_follow' => $users_id[1], 'users_id' => $users_id[0]);
		$this->save($c);

		/* SECOND INCREMENT */
		$c = array('conditions' => array('id' => $users_id[1]));
		$this->increment('nb_followers', 1, $c, 'users');

		return true;	
	}
}
?>