<?php

$cs = new CatSpawn();
class CatSpawn{

	function __construct(){
		//error_reporting(0);

		if($_GET && is_array($_GET) && !empty($_GET)){
			$params = array_keys($_GET);
			$params = preg_split('/[x_]/', $params[0]);

			if(!empty($params[2]) && !is_numeric($params[2]))
				$type = $params[2];
			else
				$type = null;

			if($params[0] && intval($params[0]) > 0)
				$width = $params[0];

			if($params[1] && intval($params[1]) > 0)
				$height = $params[1];

			if($width && $height){
				if($width <= 250 && $height <= 250)
					$catSize = 'small';
				else if($width <= 500 && $height <= 500)
					$catSize = 'medium';
				else
					$catSize = 'full';

				$catAPI = 'http://thecatapi.com/api/images/get?api_key=NjkwNg&' . 
					'format=xml&results_per_page=1&size=' . $catSize . '&category=space';

				if($type == 'jpg' || $type == 'jpeg' || $type == 'png')
					$catAPI .= '&type=' . $type;
				else
					$catAPI .= '&type=png';

				$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
				$xml = file_get_contents($catAPI, false, $context);
				$xml = simplexml_load_string($xml);

				if($xml && is_object($xml)){
					foreach($xml as $data)
						foreach($data as $images)
							foreach($images as $image)
								$kitty = json_decode(json_encode($image), 1);

					$ext = explode('.', strtolower($kitty['url']));
					$ext = $ext[count($ext) - 1];
					$origImg = $this->saveImage($kitty['url'], $ext);

					$img = imagecreatetruecolor($width, $height);
					imagecopyresized($img, $origImg, 0, 0, 0, 0, $width, $height, imagesx($origImg), imagesy($origImg));
					
					$this->watermarkImage($img, $kitty['source_url']);
					header('Content-Type: image/' . $ext);
					imagejpeg($img);
					imagedestroy($img);
				}

			}
		}
	}

	private function saveImage($filename, $ext){
	    switch($ext){
        	case 'jpeg':
	        case 'jpg':
	            return imagecreatefromjpeg($filename);
	        break;

	        case 'png':
	            return imagecreatefrompng($filename);
	        break;

	    }
    }

    private function watermarkImage($filename, $words){
		putenv('GDFONTPATH=' . realpath('.'));
		$black = imagecolorallocate($filename, 255, 255, 255);
    	$font = 'Arial Black';
		$font_size = 10;

		imagettftext($filename, $font_size, 0, 20, imagesy($filename) - 20, $black, $font, $words);
    }
}


?>