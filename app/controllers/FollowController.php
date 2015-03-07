<?php 
/**
* Followcontroller
*/

namespace app\controllers;

class FollowController extends Controller
{	

	/**
	* Unfollow 
	*/
	public function unFollow()
	{	
		// add users id
		$users = array($this->UserRequest->id, $this->dataPost->user_follow);
		$this->setData($this->Follow->unFollow($users));
	}

	/**
	* Add a folloWing 
	*/
	public function newFollow()
	{	
		// add users id
		$users = array($this->UserRequest->id, $this->dataPost->user_follow);
		$this->setData($this->Follow->newfollow($users));
	}

}	
?>
