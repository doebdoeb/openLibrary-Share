<?php
    include_once "../utils/session.php";
    include_once "../utils/database.php";

    $uploadDir = "../uploads/";
    $uploadOk = 1;
    $uploadMessage = "";
    $previewFile = null;
    $previewTags = [];

    // Handle file preview
    if(isset($_FILES["pdf"]) && $_FILES["pdf"]["error"] == UPLOAD_ERR_OK) {
        $filename = uniqid("pdf_") . ".pdf";
        $tmpName = $_FILES["pdf"]["tmp_name"];
        $originalName = $_FILES["pdf"]["name"];

        $_SESSION['temp_file'] = [
            'filename' => $filename,
            'original_name' => $originalName,
            'size' => $_FILES["pdf"]["size"]
        ];
        
        $fileType = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if($fileType != "pdf") {
            $uploadMessage = "Error: Only PDF files allowed!";
        }

        if(move_uploaded_file($tmpName, $uploadDir . $filename) == false) {
            $uploadMessage = "Error: Could not move file!";
        } else {
            $previewFile = $originalName;
        }
    }

    // Handle Tag upload
    if (isset($_POST["btnConfirmUpload"]) && isset($_SESSION['temp_file'])) {
        $filename = $_SESSION['temp_file']['filename'];
        // upload file to database
        uploadPdf($filename);

        $userFiles = loadUploadsByUser($_SESSION["uid"]);
        $lastFile = end($userFiles);

        if (isset($_POST['tags']) && !empty($_POST['tags'])) {
            $tags = explode(',', $_POST['tags']);
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                    addTagToFile($lastFile->id, htmlspecialchars($tag));
                }
            }
        }
        // Clear temporary session data
        unset($_SESSION['temp_file']);

        $uploadMessage = "File uploaded successfully!";
        $previewFile = null;
    }

    // Handle cancel
    if(isset($_POST["btnCancel"])) {
        if(isset($_SESSION['temp_file'])) {
            // Delete the file if user cancels
            $uploadDir = "../uploads/";
            $filePath = $uploadDir . $_SESSION['temp_file']['filename'];
            if(file_exists($filePath)) {
                unlink($filePath);
            }
            //deleteFileByName($_SESSION['temp_file']['filename']);
        }
        unset($_SESSION['temp_file']);
        $previewFile = null;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="intermediate">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/upload.css">
    <title>Upload</title>
</head>
<body>
    <?php 
    include_once "../components/navbar.php";
    ?>

    <div class="container upload-container">
        <div class="row">
            <div class="col">
                <h1>Upload PDF Document</h1>
            </div>
        </div>

        <?php if($uploadMessage): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($uploadMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if(!$previewFile): ?>
            <!-- File Upload Form -->
            <div class="upload-form-section">
                <div class="form-card">
                    <h2>Step 1: Select File</h2>
                    <form enctype="multipart/form-data" method="post" action="upload.php" id="uploadForm">
                        <div class="form-group">
                            <label for="pdfFile" class="form-label">Choose PDF File</label>
                            <input type="file" class="form-control form-control-lg" id="pdfFile" name="pdf" accept=".pdf" required>
                            <small class="form-text text-muted">Only PDF files are allowed. Maximum size: 50MB</small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">Preview & Add Tags</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Preview Section -->
            <div class="preview-section">
                <div class="form-card">
                    <h2>Step 2: Review & Add Tags</h2>
                    
                    <form method="post" action="upload.php" id="previewForm">
                        <div class="row">
                            <!-- File Info & Tags -->
                            <div class="col-lg-8">
                                <div class="file-info-wrapper">
                                    <h3>File Information</h3>
                                    <div class="info-item">
                                        <label>Filename:</label>
                                        <p><?php echo htmlspecialchars($_SESSION['temp_file']['original_name']); ?></p>
                                    </div>
                                    <div class="info-item">
                                        <label>File Size:</label>
                                        <p><?php echo number_format($_SESSION['temp_file']['size'] / 1024, 2) . " KB"; ?></p>
                                    </div>

                                    <!-- Tags Management -->
                                    <div class="tags-input-wrapper">
                                        <h4>Add Tags</h4>
                                        <p class="text-muted small">Enter tags separated by commas</p>
                                        <textarea class="form-control" id="tagsInput" name="tags" rows="6" placeholder="e.g., author, genre, topic"></textarea>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="action-buttons">
                                        <button type="submit" name="btnConfirmUpload" class="btn btn-primary btn-block mb-2">
                                            <i class="bi bi-check-circle"></i> Confirm & Upload
                                        </button>
                                        <button type="submit" name="btnCancel" class="btn btn-secondary btn-block">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recent Uploads -->
        <div class="recent-uploads-section">
            <div class="form-card">
                <h2>Your Recent Uploads</h2>
                <div class="files-grid">
                    <?php 
                        $dir = "../uploads/";
                        $files = loadUploadsByUser($_SESSION["uid"]);
                        
                        if(empty($files)) {
                            echo "<p class='no-uploads'>No files uploaded yet.</p>";
                        } else {
                            foreach($files as $f) {
                                $tags = loadTagsByFileWithId($f->id);
                                echo "<div class='file-preview-card'>";
                                echo "<iframe src='" . $dir . $f->filename . "' width='100%' height='180px' style='border:none; border-radius: 6px;' title='PDF Preview'></iframe>";
                                echo "<div class='file-details'>";
                                echo "<p class='file-name'>" . htmlspecialchars($f->filename) . "</p>";
                                if(!empty($tags)) {
                                    $tagValues = array_map(function($tag) { return htmlspecialchars($tag['value']); }, $tags);
                                    echo "<div class='tags-preview'>" . implode(", ", $tagValues) . "</div>";
                                }
                                echo "</div>";
                                echo "</div>";
                            }
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcVqwpEV7tZrpomMgA" crossorigin="anonymous"></script>
</body>
</html>