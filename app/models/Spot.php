<?php 
/**
* SPOT
*/

namespace app\models;

class Spot extends Model
{	
	public $table = 'spots';


	public function setFilesOnPost($files, $spot_id)
	{	
		for ($i=0; $i < count($files); $i++) { 
			$file_ext = explode('.', $files[$i]);

			if($file_ext[1] == "jpg" || $file_ext[1] == "png" || $file_ext[1] == "gif" || $file_ext[1] == "jpeg") {
				$type = 'image';
			}else {
				$type = 'video';
			}

			$file = array(
				'src' => $files[$i],
				'type' => 'image',
				'spots_id' => $spot_id
			);

			$this->save($file, 'medias');			
		}
	}

	/**
	* Get the number of media on a spot
	* @param $spot_id integer spot's id
	* @param $type string type of media
	* @return object with the number
	*/
	public function getNbMedia($spot_id, $type)
	{	
		$c = array('conditions' => array('spots_id' => $spot_id, 'type' => $type ));
		return $this->count('*', $c, 'medias')[0]->nbr;
	}

}
?>
