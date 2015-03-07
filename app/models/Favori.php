<?php 
/**
* FAVORI
*/

namespace app\models;

class Favori extends Model
{
	public $table = "favoris";	

	/** 
	* Add a spot to the user's favorites
	* @param $id integer user's id
	* @param $spot integer spot's id
	* @return Object a new favorites data
	*/
	public function addFavorites($id, $spot)
	{	
		return $this->save(array('users_id' => $id, 'spots_id' => $spot));
	}


	/** 
	* Remove a spot from the user's favorites
	* @param $id integer user's id
	* @param $spot integer spot's id
	* @return Boolean
	*/
	public function removeFavorites($id, $spot)
	{
		return $this->delete(array('conditions'=> array('users_id' => $id, 'spots_id' => $spot)));
	}


	/** 
	* Get all user's favorites spots
	* @param $user_id integer user's id
	* @return Array all favorites spots
	*/
	public function getAllFavorites($user_id)
	{	
		// get all favorites id
		$favoris = $this->findAll(array('conditions'=> array('users_id' => $user_id)));
		$data = array();

		// get all the spot from their id
		foreach ($favoris as $favoris) {
			$favoris = $this->load($favoris->spots_id, 'spots');
		
			if(isset($favoris[0])) {
				$data[] =  $favoris[0];
			}
			
		}

		return (object) $data;
	}

}
?>