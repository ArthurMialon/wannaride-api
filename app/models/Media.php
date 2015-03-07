<?php 
/**
* MEDIA
*/

namespace app\models;

class Media extends Model
{
	public $table = "medias";

	/**
	* Insert an image
	* @param $imgPath string path to the media
	* @param $id integer id of the data where there is a media
	* @param $where string where the image should be register
	* @return Object the new entry
	*/
	public function addImage($imgPath, $id, $where)
	{
		$c = array( $where . '_id' => $id, 'src' => $imgPath );
		return $this->save($c);
	}

}
?>