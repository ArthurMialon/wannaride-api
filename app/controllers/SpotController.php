<?php
/**
* Spotcontroller
*/

namespace app\controllers;

class SpotController extends Controller
{	
	/**
	* Surcharge on CRUD get
	* Add new data about author, category, and nb media
	*/
	public function get()
	{
		parent::get();

		
		if(isset($this->data[0])){
			/*** If there is 1 spots set and add his author data */
			$author = $this->Spot->load($this->data[0]->users_id, 'users');
			unset($author->password);

			$this->setData($author, 'author');
			$this->unsetData('users_id');
			/**********************************/

			/*** Get the category of a spot */
			$category = $this->Spot->load($this->data[0]->categories_id, 'categories');
			$this->setData($category[0]->name, 'category');
			/**********************************/

			/*** If there is 1 spots set and add his challenge data */
			$s = $this->Spot->findAll(array('conditions' => array('spots_id' => $this->data[0]->id)), 'challenges');
			$this->setData($s, 'challenges');
			/**********************************/

			/*** If there is 1 spots set and add his number of media */
			$nb_videos = $this->Spot->getNbMedia($this->data[0]->id, 'videos');
			$nb_photos = $this->Spot->getNbMedia($this->data[0]->id, 'image');
			$this->setData($nb_videos, 'nb_videos');
			$this->setData($nb_photos, 'nb_photos');
			/**********************************/
		}
	}


	/**
	* Surcharge on CRUD getAll
	* Add new data about author, category, and nb media 
	*/
	public function getAll()
	{
		parent::getAll();

		// Add user data for each spot
		foreach ($this->data as $key => $value) {
			/*** Get the category of a spot */
			$category = $this->Spot->load($this->data[$key]->categories_id, 'categories');
			if(isset($category[0])) {
				$this->setData($category[0]->name, 'category', $key);
			}
			/**********************************/

			/*** add his author data */
			$s = $this->Spot->load($this->data[$key]->users_id, 'users');
			unset($s->password);				
			$this->setData($s, 'author', $key);
			/**********************************/

			/*** If there is 1 spots set and add his number of media */
			$nb_videos = $this->Spot->getNbMedia($this->data[$key]->id, 'videos');
			$nb_photos = $this->Spot->getNbMedia($this->data[$key]->id, 'image');
			$this->setData($nb_videos, 'nb_videos', $key);
			$this->setData($nb_photos, 'nb_photos', $key);
			/**********************************/			
			
		}
	}
	

	/**
	* Surcharge on CRUD post to upload Media (Tricky system...)
	*/
	public function post()
	{	
		
		$spot = $this->Spot->save($this->dataPost->spot);
		
		if(isset($spot[0])) {
			// Enregister les fichiers dans la tables Medias
			// Array avec nom des fichiers à ajouter en média
			$this->Spot->setFilesOnPost($this->dataPost->files, $spot[0]->id);
		}
		
		$this->setData($spot);
	}
	

	/**
	* Get Challenges of a spot
	*/
	public function getChallenges()
	{	
		$cond = array('conditions' => array('spots_id' => $this->f3->get('PARAMS.id')));
		$challenges = $this->Spot->findAll($cond, 'challenges');
		$this->setData($challenges);
	}

	/**
	* Tricky system because of angularJs
	*/
	public function uploadImageTest()
	{
		$this->setData(parent::uploadImage('spots'));
	}
}
?>
