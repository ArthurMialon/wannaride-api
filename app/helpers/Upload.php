<?php 
/**
* Upload
*/

namespace app\helpers;

class Upload
{	
	function __construct()
	{	
		$this->f3= \Base::instance();
		$this->web=\Web::instance();
	}

	/**
	* Upload a file in the server
	* @param $file array the file
	* @param $path string the directory for the files
	* @return array code status / path
	*/
	public function save($file, $path)
	{


		// Config the path to put the file
		$this->url($path);
	
		// Get the type of the file
		$type=stristr($file['photo']['type'], '/', true);
		// Check if the file is a photo or a video
		if($type==='image' || $type==='video' ) {

			if ($type==='video' && $path='users') {
				return $this->response(404, "Les vidéos ne sont pas autorisées en photo de profile");
			}

			//Upload the file in the repository
			$files = $this->web->receive(function($file,$formFieldName){

				// Check if the file isn't too heavy
				return $this->checkSize($file['type'], $file['size'] );
				// return false;

	   		}, true, function($file, $formFieldName) {
		        
		        // Get the extension and rename the file with an uniq id
		        $tab = explode('.',$file);
		        $ext = $tab[count($tab)-1];
	   			return uniqid().'.'.$ext;

	   		});

			if(!$files[key($files)]) {
	   			// If the upload don't work, return error to the controller
				return $this->response(404, "Le fichier pèse plus de 2Mo");
	   		}

	   		// Check the path of the file
	   		// If users, we resize and crop the file to avoid the deformation 
			if($path == 'users') {
				
				$tab = explode('/',$file['photo']['type']);
		        $type = $tab[count($tab)-1];
				$this->resize(key($files), $type);

			}

			// If upload is Ok
			return $this->response(200, key($files));

		} else {

			// If upload return an error
			return $this->response(404, "Un problème est survenue durant l'upload");
		}
	}

	/**
	* Set in F3 the path
	* @param $path string the directory of the files
	*/
	private function url($path)
	{
		//Choose the folder according to the path
		$this->f3->set('UPLOADS', 'uploads/'.$path.'/');
	}

	/**
	* Resize image
	* @param $file a file 
	* @param $type integer the file's type
	* @return boolean
	*/
	private function resize($file, $type) {

		// Resize and crop profile picture to 200x200
		$img = new \Image($file, TRUE);
		$img->resize(200,200);
		$img->save();
		if(file_put_contents($file, $img->dump($type))){
			return true; 
		} else {
			return false;
		}
	}

	/**
	* Check if the size is ok (not to big)
	* @param $fileType string type of the file
	* @param $size integer size of the file
	* @return boolean
	*/
	private function checkSize ($fileType, $size) {

		$type=stristr($fileType, '/', true);

		// A video can't be over 60 mo and an image can't be over 2 mo
        if($type === 'video' && $size <= (120 * 1024 * 1024)) {
	        return true; 
        } else if($type === 'image' && $size <= (2 * 1024 * 1024)) {
        	return true;
        } else {
        	return false;
        }
	}

	/**
	* Response to save function
	* @param $code status code
	* @param $data mixed data to send
	* @return array message to send with status code
	*/
	private function response ($code, $data) {
		// construct the error to send to the controller
		$message['code'] = $code ;
		$message['data'] = $data;

		return $message;
	}


}
?>