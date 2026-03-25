<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

require 'vendor/autoload.php';

use Aws\S3\S3Client;

/* AWS CONFIG */

$bucket = "cloudvault-fileststorage";
$region = "ap-south-1";

$s3 = new S3Client([
    'version' => 'latest',
    'region' => $region
]);

/* RDS DATABASE CONFIG */

$host = "database-2.cjmyauyayakc.ap-south-1.rds.amazonaws.com";
$user = "root";
$password = "12345678";
$db = "cloudvaultdb";

$conn = new mysqli($host,$user,$password,$db);

if ($conn->connect_error) {
    die("Database connection failed");
}

/* FILE UPLOAD */

if(isset($_FILES['file'])){

    $uploaded_by = $_POST['uploaded_by'];

    $file_name = $_FILES['file']['name'];
    $file_type = $_FILES['file']['type'];
    $file_size = $_FILES['file']['size'];
    $tmp_name  = $_FILES['file']['tmp_name'];

    /* Generate unique filename */

    $unique_name = time() . "_" . $file_name;

    /* Temporary EC2 path */

    $ec2_path = "/usr/share/nginx/html/uploads/" . $unique_name;

    /* Move file to EC2 root volume */

    if(move_uploaded_file($tmp_name,$ec2_path)){

        try{

            /* Upload file to S3 */

            $result = $s3->putObject([
                'Bucket' => $bucket,
                'Key' => $unique_name,
                'SourceFile' => $ec2_path,
                'ACL' => 'public-read'
            ]);

            /* Get S3 URL */

            $s3_url = $result['ObjectURL'];

            /* Store URL in RDS */

            $stmt = $conn->prepare("INSERT INTO uploads (file_name,file_type,file_size,s3_url,uploaded_by) VALUES (?,?,?,?,?)");

            $stmt->bind_param("ssiss",$unique_name,$file_type,$file_size,$s3_url,$uploaded_by);

            $stmt->execute();

            /* Delete temp file from EC2 */

            unlink($ec2_path);

            echo "<h2>Upload Successful</h2>";
            echo "File URL: <a href='$s3_url'>$s3_url</a>";

        }

        catch(Exception $e){

            echo "S3 Upload Failed: ".$e->getMessage();

        }

    }

    else{
        echo "Failed to move uploaded file.";
    }

}

?>






















