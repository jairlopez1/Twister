<?php

// This code is combined from a number of locations and modified
// by Frank McCown, Harding University, 2009.


// Returns an empty string if uploaded image is successfully saved as
// $image_filename or an error message.
// $image_filename should be saved in a directory that the web
// server can write to.
function UploadSingleImage($image_filename)
{
	// This function is greatly modified code from
	// http://www.webdeveloper.com/forum/showthread.php?t=101466
	
	
	// possible PHP upload errors
	$errors = array(1 => 'php.ini max file size exceeded (' . ini_get('upload_max_filesize') . ' limit)',
                2 => 'html form max file size exceeded',
                3 => 'file upload was only partial',
                4 => 'no file was attached');
    
	/*
print "<pre>";
print_r($_FILES);
print "</pre>\n";
*/

	// check if any files were uploaded and if
	// so store the active $_FILES array keys
	$active_keys = array();
	foreach($_FILES as $key => $file)
	{
		if (!empty($file['name']))
		{
			$active_keys[] = $key;
		}
	}

	// check at least one file was uploaded
	if (count($active_keys) == 0)
		return 'No files were uploaded';
        
	// check for standard uploading errors
	foreach ($active_keys as $key)
	{
		if ($_FILES[$key]['error'] > 0)
			return $_FILES[$key]['tmp_name'] . ': ' . $errors[$_FILES[$key]['error']];
	}
    
	// check that the file we are working on really was an HTTP upload
	foreach ($active_keys as $key)
	{
		if (!is_uploaded_file($_FILES[$key]['tmp_name']))
			return $_FILES[$key]['tmp_name'] . ' not an HTTP upload';
	}
    
	// validation... since this is an image upload script we
	// should run a check to make sure the upload is an image
	foreach ($active_keys as $key)
	{
		if (!getimagesize($_FILES[$key]['tmp_name']))
			return $_FILES[$key]['tmp_name'].' is not an image';
	}
    	

	// Save every uploaded file to the same filename (normally we'd want to
	// save each file with its own unique name, but we are assuming there
	// is only one file).
	foreach ($active_keys as $key)
	{
		if (!move_uploaded_file($_FILES[$key]['tmp_name'], $image_filename))
			return 'receiving directory (' . $image_filename . ') has insuffiecient permission';
	}
    
	// If you got this far, everything has worked and the file has been successfully saved.

	return '';
}  


// Returns true if at least one image was uploaded, false otherwise.
function ImageUploaded()
{
	$image_uploaded = false;
	
	$active_keys = array();
	foreach($_FILES as $key => $file)
	{
		if (!empty($file['name']))
			$image_uploaded = true;
	}

	return $image_uploaded;
}

// Code from https://davidwalsh.name/create-image-thumbnail-php
function CreateThumbnailImage($src_filename, $dest_filename, $desired_width)
{
	$source_image = imagecreatefromjpeg($src_filename);
	$width = imagesx($source_image);
	$height = imagesy($source_image);
	
	// create height based on desired width, keeping the same aspect ratio
	$desired_height = floor($height * ($desired_width / $width));
	
	// create a new, "virtual" image
	$virtual_image = imagecreatetruecolor($desired_width, $desired_height);
	
	// copy source image at a resized size 
	imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, 
		$desired_height, $width, $height);
	
	// create the physical thumbnail image to its destination 
	imagejpeg($virtual_image, $dest_filename);
}

// This function uses Unix utilities to create a thumbnail image.  
// $scr_filename = The pull path to where the image file resizes.
// $dest_filename = The pull path of the thumbnail image to be created.
// $thumb_max_size = The longest width or height the image should have.
// Normally resizing is done with imagecreatefromjpeg, but this is not
// working on Taz (JPEG support is only available if PHP was compiled against
// GD-1.8 or later).
function CreateThumbnailImage_OLD($src_filename, $dest_filename, $thumb_max_size)
{
	$max_width = $thumb_max_size;
	$max_height = $thumb_max_size;

	list($width, $height) = getimagesize($src_filename);
	
	if ($width < $thumb_max_size && $height < $thumb_max_size)
	{
		// No need to resize since image is smaller than thumb, so
		// just make copy
		copy($src_filename, $dest_filename);
		return;
	}

	$x_ratio = $max_width / $width;
	$y_ratio = $max_height / $height;

	if (($width <= $max_width) && ($height <= $max_height))
	{
		$tn_width = $width;
		$tn_height = $height;
	}
	elseif (($x_ratio * $height) < $max_height)
	{
		$tn_height = ceil($x_ratio * $height);
		$tn_width = $max_width;
	}
	else
	{
		$tn_width = ceil($y_ratio * $width);
		$tn_height = $max_height;
	}

	// Where to store temp img file 
	$tmpimg = tempnam("/tmp", "MKPH");

	// Extract file extension 
	$i = strrpos($src_filename, ".");
    if (!$i) 
	{
		echo "Unable to find filename extension.";
		return;
	}

	$len = strlen($src_filename) - $i;
	$ext = strtolower(substr($src_filename, $i+1, $len));
	
	// Make sure this is a jpg image
	if ($ext != "jpg") 
	{ 
		echo("Extension is not .jpg."); 
		return;		
	}
	
	system("djpeg $src_filename >$tmpimg"); 

	// Scale image using pnmscale and output using cjpeg
	system("pnmscale -xy $tn_height $tn_width $tmpimg | cjpeg -smoo 10 -qual 50 >$dest_filename");
}
?> 