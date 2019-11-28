<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class ImageUploadComponent extends Component{
	/**
* This function is used to upload image into server
*
* @access public
*
* @param array $imageArr
* @return array
*/

	public function uploadImage($image = null, $path= null, $thumbpath= null) {
		//echo '2'; die;
		$valid = true;
		$data = array();
		$fileName = $image['name'];
		$allowedFormats = array("jpeg", "jpg", "png");
		if ($image['size'] <= 0) {
			$valid = false; 
			$data['message'] = "Invalid file size, please upload file less than 5mb";
		}
		//check file size
		if ($image['size'] > 5000000) {
			$valid = false; 
			$data['message'] = "File size exceeded upload less than 5mb";
		}
		//check file extension
		$pathInfo = pathinfo($fileName);
			if (!in_array($pathInfo["extension"], $allowedFormats)) {
				$valid = false; 
				$data['message'] = "Extension not supported";
			}
			//file path
			if ($valid) {
				$file_name = time() . '_' . $fileName;
				$file_name = str_replace(' ', '', $file_name);
				$filePath = $path . $file_name;
				//thumbnail full path
				$thumbfilePath = $thumbpath . $file_name;
				if (!file_exists($path)) {
					mkdir($path, 0777);
				}
				if (move_uploaded_file($image['tmp_name'], $filePath)) {
					$this->resize(150, 150, $thumbfilePath, $filePath);
					$data['imageName'] = $file_name; 
				} else {
					$data['message'] = 'Image can not be uploaded. Please try again';
				}
			}
			$data['status'] = $valid;

		return $data;
	}

	public function resize($newHeight, $newWidth, $targetFile, $originalFile) {
		$info = getimagesize($originalFile);
		$mime = $info['mime'];
		switch ($mime) {
          case 'image/jpeg':
          $image_create_func = 'imagecreatefromjpeg';
          $image_save_func = 'imagejpeg';
          $new_image_ext = 'jpeg';
          break;

          case 'image/jpg':
          $image_create_func = 'imagecreatefromjpg';
          $image_save_func = 'imagejpg';
          $new_image_ext = 'jpg';
          break;

          case 'image/png':
          $image_create_func = 'imagecreatefrompng';
          $image_save_func = 'imagepng';
          $new_image_ext = 'png';
          break;

          case 'image/gif':
          $image_create_func = 'imagecreatefromgif';
          $image_save_func = 'imagegif';
          $new_image_ext = 'gif';
          break;

          default: 
          throw Exception('Unknown image type.');
        }
        $img = $image_create_func($originalFile);
        //$size = getimagesize($originalFile);
        list($orig_width, $orig_height) = getimagesize($originalFile);
        
        $width = $orig_width;
        $height = $orig_height;
        $max_height = $newHeight;
        $max_width = $newWidth;
        
        # taller
        if ($height > $max_height) {
          $width = ($max_height / $height) * $width;
        //echo '<br>';
          $height = $max_height;
        }

        # wider
        if ($width > $max_width) {
          $height = ($max_width / $width) * $height;
          $width = $max_width;
        }
        //$newHeight = ($height / $width) * $newWidth;
        $tmp = imagecreatetruecolor($width, $height);
        imagealphablending($tmp, false);
        imagesavealpha($tmp,true);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);

        if (file_exists($targetFile)) {
        	unlink($targetFile);
        }
        $image_save_func($tmp, "$targetFile");
      }
  }