<?php 
/**
* Mediacontroller
*/

namespace app\controllers;

class MediaController extends Controller
{
	/**
	* Add Media on a spot
	*/
	public function addMediaOnSpot() 
	{	
		$spot_id = $this->f3->get('PARAMS.id');
		$this->upload('spots', $spot_id);
	}

	/**
	* Add Media on a challenge
	*/
	public function addMediaOnchallenge()
	{	
		$chal_id = $this->f3->get('PARAMS.id');
		$this->upload('challenges', $chal_id);
		
	}

	/**
	* Upload the media of challenge or spot
	* @param $where string spots or challenges
	* @param $id id of the data
	*/
	private function upload($where, $id)
	{
		$upload = $this->uploadImage($where);

		if($upload['code'] == 200) {
			// Upload réussi // PATH \\
			$imgPath = $upload['data'];
			$this->setData($this->Media->addImage($imgPath, $id, $where));
		}else {
			$this->setError($upload['code'], $upload['data']);
		}
	}

	/**
	* Get all media from a spot
	*/
	public function getSpotMedia()
	{	
		$c = array(
			'conditions' => array(
				'spots_id' => $this->f3->get('PARAMS.id')
			)
		);

		$this->setData($this->Media->findAll($c));
	}

	/**
	* Get all media from a challenge
	*/
	public function getChallengeMedia()
	{	
		$c = array(
			'conditions' => array(
				'challenges_id' => $this->f3->get('PARAMS.id')
			)
		);

		$this->setData($this->Media->findAll($c));
	}
}
?>
