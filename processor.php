<!DOCTYPE html>
<html>
	<head>
		<title>SSD PHP Lab 8 Sample</title>
		<link rel="stylesheet" href="http://bcitcomp.ca/ssd/css/style.css" />

	</head>
	<body>
		<h1>Lab08 - Open Source PHP</h1>

<?php

const COMPRESSED_FILES_DIRECTORY = "compressed_files/";
const UNCOMPRESSED_FILES_DIRECTORY = "uncompressed_files/";
const IMAGES_DESTINATION_DIRECTORY = "images_destination/";
//const UPLOAD_DIRECTORY = "compressed_files/";
$zipFilename = "";


//ensure the file upload form was used
if( !isset( $_FILES['filename'] ) ){
	die("<p>Ack! no upload exists</p>");
}

//filter to ensure the upload type is appropriate
if($_FILES['filename']['type'] != "application/x-zip-compressed"){
	die("<p>Sorry, '".$_FILES['filename']['name']."' does not appear to be a .zip file</p><p><a href='Lab08.html'>Upload a .zip file</a></p>");
} else {
    
    //determine where the temp file is
    $temp_file = $_FILES['filename']['tmp_name'];

    //determine where you want to save the uploaded file
    $file_location = COMPRESSED_FILES_DIRECTORY . $_FILES['filename']['name'];

    //move the uploaded file
    if (move_uploaded_file($temp_file, $file_location)){
        $zipFilename = $_FILES['filename']['name'];
        unset($_FILES['filename']);
        if( !is_file(COMPRESSED_FILES_DIRECTORY . $zipFilename) ){
            echo "<p>'$zipFilename' is not available for unzipping.</p>";
        } else {
            
            echo "<p>Received file: $zipFilename</p>";
            
            //instantiate a new ZipArchive object
            $zip = new ZipArchive();

            //try to open the .zip file
            if( $zip->open( COMPRESSED_FILES_DIRECTORY . $zipFilename )) {
                //extract the .zip file to the specified directory
                $zip->extractTo(UNCOMPRESSED_FILES_DIRECTORY);
                echo "<p>Zip file info:</p>";
                echo "<ul>";
                echo "<li>Filename: " . $zip->filename .  "</li>";
                echo "<li>Number of files in archive: " . $zip->numFiles . "</li>";
                echo "</ul>";
                //close the zip file archive
                $zip->close();
                //let the user know what happend
                echo "<p>Unzip of ".$zipFilename." was successful</p>";
                //delete the .zip file now that we have the files it contains
                unlink( COMPRESSED_FILES_DIRECTORY . $zipFilename  );

                $arrayOfFiles = scandir(UNCOMPRESSED_FILES_DIRECTORY);
                require_once("wideimage/lib/WideImage.php");
                $watermark = WideImage::load("watermark.gif");		//load the watermark
                echo "<fieldset><legend>Your new images:</legend>";
                foreach ($arrayOfFiles as $file){
                    if(is_file(UNCOMPRESSED_FILES_DIRECTORY.$file)){
                        //just .jpg OR .png OR .gif OR .jpeg extenstions
                        if(preg_match("/(\.jpg$)|(\.png$)|(\.gif$)|(\.jpeg$)/i",$file) != 0){

                            $img = WideImage::load(UNCOMPRESSED_FILES_DIRECTORY.$file);	//load the image
                            $watermarkedImage = $img->merge($watermark, "center", "center", 30);
                            $watermarkedImage->saveToFile(IMAGES_DESTINATION_DIRECTORY.$file);
                        } else {
                            echo "<p style='color:red;'>ERROR: ".$file." is not an image!</p>";
                        }
                        unlink(UNCOMPRESSED_FILES_DIRECTORY.$file);
                    }
                    
                }
                $toZip = new ZipArchive();
                if( $toZip->open(COMPRESSED_FILES_DIRECTORY.$zipFilename,  ZipArchive::CREATE) ){
                    //obtain an array of files to be zipped
                    $files = scandir(IMAGES_DESTINATION_DIRECTORY);
                    //loop through each file
                    foreach ($files as $file) {
                        //ignore any directories such as . and ..
                        if(is_file(IMAGES_DESTINATION_DIRECTORY.$file)){
                            //add the file to the archive
                            $toZip->addFile(IMAGES_DESTINATION_DIRECTORY.$file, $file);
                        }
                    }
                    //close the zip file, saving it to disk
                    $toZip->close();
                    //display a link to the .zip file
                    echo "<fieldset><legend>Download</legend><p>Download your newly watermarked images as a .zip file: <a href='".COMPRESSED_FILES_DIRECTORY.$zipFilename."'>".$zipFilename."</a></p></fieldset>";
                }
                echo "</fieldset>";
            }else{
                //if .zip file could not be opened (maybe it was corrupt?)
                echo("<p class='error'>Uh oh! Could not unzip $zipFilename</p>");
            }
        }

    }else{
        echo "error";
    }
}
echo "<p><a href='Lab08.html'>Back to the form</a></p>";
?>


    </body>
</html>