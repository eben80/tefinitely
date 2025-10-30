<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$uploadDir = 'uploads/';
$processedDir = 'processed/';
$outputFile = '';
$error = '';

// Check for directories and permissions
if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
    $error = "Upload directory is missing or not writable.";
}
if (!is_dir($processedDir) || !is_writable($processedDir)) {
    $error = "Processed directory is missing or not writable.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && empty($error)) {
    $canvasWidth = isset($_POST['canvas_width']) ? (int)$_POST['canvas_width'] : 100;
    $canvasDepth = isset($_POST['canvas_depth']) ? (int)$_POST['canvas_depth'] : 100;
    $canvasHeight = isset($_POST['canvas_height']) ? (int)$_POST['canvas_height'] : 2;
    $extrudeHeight = isset($_POST['extrude_height']) ? (int)$_POST['extrude_height'] : 2;

    $image = $_FILES['image'];
    $imagePath = $uploadDir . basename($image['name']);
    $imageFileType = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($image['tmp_name']);
    if($check === false) {
        $error = "File is not an image.";
    }

    // Check file size
    if ($image['size'] > 5000000) {
        $error = "Sorry, your file is too large.";
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        $error = "Sorry, only JPG, JPEG, & PNG files are allowed.";
    }

    if (empty($error) && move_uploaded_file($image['tmp_name'], $imagePath)) {
        $baseName = pathinfo($imagePath, PATHINFO_FILENAME);
        $bmpPath = $processedDir . $baseName . '.bmp';
        $svgPath = $processedDir . $baseName . '.svg';

        // Convert image to a line drawing using ImageMagick
        $command = "convert " . escapeshellarg($imagePath) . " -colorspace gray -negate -edge 1 -normalize " . escapeshellarg($bmpPath);
        shell_exec($command);
        if (!file_exists($bmpPath)) {
            $error = "Failed to convert image to bitmap. Make sure ImageMagick is installed.";
        }

        // Vectorize the bitmap to SVG using potrace
        if (empty($error)) {
            $command = "potrace " . escapeshellarg($bmpPath) . " -s -o " . escapeshellarg($svgPath);
            shell_exec($command);
            if (!file_exists($svgPath)) {
                $error = "Failed to vectorize bitmap. Make sure potrace is installed.";
            }
        }

        $scadPath = $processedDir . $baseName . '.scad';
        $stlPath = $processedDir . $baseName . '.stl';

        // Generate OpenSCAD script
        $scadScript = "
union() {
    // The canvas, centered
    cube([" . $canvasWidth . ", " . $canvasDepth . ", " . $canvasHeight . "], center = true);

    // The extruded drawing, translated to sit on top of the canvas
    translate([0, 0, " . $canvasHeight / 2 . "]) {
        linear_extrude(height = " . $extrudeHeight . ") {
             resize([" . $canvasWidth . ", " . $canvasDepth . ", 0])
             import(\"" . $svgPath . "\", center = true);
        }
    }
}
        ";
        file_put_contents($scadPath, $scadScript);

        // Execute OpenSCAD to generate STL
        if (empty($error)) {
            $command = "openscad -o " . escapeshellarg($stlPath) . " " . escapeshellarg($scadPath);
            shell_exec($command);
            if (!file_exists($stlPath)) {
                $error = "Failed to generate STL file. Make sure OpenSCAD is installed.";
            }
        }

        $outputFile = $stlPath;
    } else if (empty($error)) {
        $error = "Sorry, there was an error uploading your file.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Image to STL Converter</title>
</head>
<body>

    <h1>Image to STL Converter</h1>

    <form action="image_converter.php" method="post" enctype="multipart/form-data">
        <h2>Configuration</h2>
        <label for="canvas_width">Canvas Width:</label>
        <input type="number" name="canvas_width" id="canvas_width" value="100">
        <br>
        <label for="canvas_depth">Canvas Depth:</label>
        <input type="number" name="canvas_depth" id="canvas_depth" value="100">
        <br>
        <label for="canvas_height">Canvas Height:</label>
        <input type="number" name="canvas_height" id="canvas_height" value="2">
        <br>
        <label for="extrude_height">Extrude Height:</label>
        <input type="number" name="extrude_height" id="extrude_height" value="2">
        <br><br>
        Select image to upload:
        <input type="file" name="image" id="image">
        <input type="submit" value="Upload Image" name="submit">
    </form>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if (!empty($outputFile) && file_exists($outputFile)): ?>
        <h2>Generated STL:</h2>
        <a href="<?php echo $outputFile; ?>" download>Download STL</a>
    <?php elseif (!empty($_POST)): ?>
        <p style="color:red;">Could not generate the STL file.</p>
    <?php endif; ?>

</body>
</html>
