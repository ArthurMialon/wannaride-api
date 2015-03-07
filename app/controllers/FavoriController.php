<?php 
/**
* Favoricontroller
*/

namespace app\controllers;

class FavoriController extends Controller
{	
	/**
	* Add a spots into the users favorites
	*/
	public function addFavorites()
	{	
		$id_spot = $this->dataPost->spot_id;
		$this->setData($this->Favori->addFavorites($this->UserRequest->id, $id_spot));
	}

	/**
	* Remove a spots from the users favorites
	*/
	public function removeFavorites()
	{	
		$id_spot = $this->f3->get('PARAMS.spot_id');
		$this->setData($this->Favori->removeFavorites($this->UserRequest->id, $id_spot));
	}


	public function getFavorites()
	{	
		$id = $this->f3->get('PARAMS.id');
		$this->setData($this->Favori->getAllFavorites($id));
	}

}	
?>
