<?php
// Configuration
$target_dir = "uploads/"; // Make sure this directory exists and is writable by the web server.

// --- Handle File Upload ---
$message = ''; // Initialize a message variable for feedback

if (isset($_POST["submit"])) {
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        $message .= "<p style='color: orange;'>Maaf, berkas **" . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . "** sudah ada.</p>";
        $uploadOk = 0;
    }

    // Check file size (500KB limit)
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        $message .= "<p style='color: orange;'>Maaf, berkas Anda terlalu besar. (Maksimal 500KB)</p>";
        $uploadOk = 0;
    }

    // Allow certain file formats (images only as per your HTML 'accept' attribute)
    if ($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg" && $fileType != "gif") {
        $message .= "<p style='color: orange;'>Maaf, hanya berkas JPG, JPEG, PNG & GIF yang diperbolehkan.</p>";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $message .= "<p style='color: red;'>Maaf, berkas Anda tidak dapat diunggah.</p>";
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $message .= "<p style='color: green;'>Berkas **" . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . "** telah diunggah.</p>";
        } else {
            $message .= "<p style='color: red;'>Maaf, terjadi kesalahan saat mengunggah berkas Anda.</p>";
        }
    }
}

// --- Handle File Deletion ---
if (isset($_GET['delete_file'])) {
    $file_to_delete = $target_dir . basename($_GET['delete_file']); // Use basename for security
    if (file_exists($file_to_delete) && !is_dir($file_to_delete)) {
        if (unlink($file_to_delete)) {
            $message .= "<p style='color: green;'>Berkas **" . htmlspecialchars(basename($file_to_delete)) . "** berhasil dihapus.</p>";
        } else {
            $message .= "<p style='color: red;'>Maaf, gagal menghapus berkas **" . htmlspecialchars(basename($file_to_delete)) . "**.</p>";
        }
    } else {
        $message .= "<p style='color: red;'>Berkas tidak ditemukan atau tidak valid.</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Gambar Sederhana</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            display: flex;
            flex-direction: column; /* Changed to column to stack containers */
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* Use min-height to allow content to grow */
            margin: 0;
            padding: 20px; /* Add padding for overall spacing */
            box-sizing: border-box; /* Include padding in element's total width and height */
        }

        .upload-container, .file-management-container, .image-viewer-container {
            background-color: white;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 320px;
            margin-bottom: 30px; /* Space between containers */
        }
        
        .file-management-container, .image-viewer-container {
            width: 80%; /* Wider for file listing and image viewer */
            max-width: 800px; /* Max width for larger screens */
            text-align: left; /* Align text within list */
        }

        .image-viewer-container {
            display: none; /* Hidden by default */
            position: relative; /* For the close button positioning */
            padding-top: 20px; /* Space for image */
            padding-bottom: 20px;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        input[type="file"] {
            margin-bottom: 15px;
        }

        input[type="submit"] {
            background-color: #4a90e2;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #357ab8;
        }

        #preview {
            margin-top: 20px;
            max-width: 100%;
            height: auto; /* Maintain aspect ratio */
            border-radius: 10px;
            display: none;
            object-fit: contain; /* Ensure image fits well */
        }

        .messages {
            margin-top: 20px;
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
        }
        .messages p {
            margin: 5px 0; /* Adjust margin for paragraphs within messages */
        }

        p[style*='green'] {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        p[style*='orange'] {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
        }
        p[style*='red'] {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .file-list {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }

        .file-list li {
            background-color: #f0f4f8;
            border: 1px solid #e0e6ed;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .file-list li:hover {
            background-color: #e6edf5;
        }

        .file-list li span {
            flex-grow: 1; /* Allow filename to take available space */
            word-break: break-all; /* Break long words */
            margin-right: 15px;
        }

        .file-list li .actions {
            display: flex;
            gap: 10px;
        }

        .file-list li .actions a {
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 0.9em;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .file-list li .actions .download-btn {
            background-color: #28a745;
            color: white;
        }

        .file-list li .actions .download-btn:hover {
            background-color: #218838;
        }

        .file-list li .actions .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .file-list li .actions .delete-btn:hover {
            background-color: #c82333;
        }
        
        .file-list li .actions .view-btn {
            background-color: #6c757d; /* Grey color for view button */
            color: white;
        }

        .file-list li .actions .view-btn:hover {
            background-color: #5a6268;
        }

        #revealedImage {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            display: block; /* Ensure it's block to center */
            margin: 0 auto; /* Center the image */
            margin-top: 20px;
            border: 1px solid #ddd;
        }
        
        #closeViewerBtn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ffc107; /* Orange color for close button */
            color: #333;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        #closeViewerBtn:hover {
            background-color: #e0a800;
        }


        @media (max-width: 600px) {
            .upload-container, .file-management-container, .image-viewer-container {
                width: 95%;
                padding: 20px;
            }
            .file-list li {
                flex-direction: column;
                align-items: flex-start;
            }
            .file-list li span {
                 margin-bottom: 10px;
            }
            .file-list li .actions {
                margin-top: 10px;
                width: 100%; /* Make actions take full width */
                justify-content: space-around; /* Distribute buttons */
            }
            .file-list li .actions a {
                flex: 1; /* Make buttons grow to fill space */
                text-align: center;
            }
            #closeViewerBtn {
                top: 5px;
                right: 5px;
                padding: 5px 10px;
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>

    <div class="upload-container">
        <h2>Unggah File</h2>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <input type="file" name="fileToUpload" id="fileToUpload" accept="image/*" required><br>
            <input type="submit" value="Unggah File" name="submit">
            <img id="preview" alt="Preview Gambar">
        </form>
    </div>

    <!-- Display messages from PHP -->
    <?php if (!empty($message)): ?>
        <div class="messages">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="file-management-container">
        <h2>Berkas yang Sudah Diunggah</h2>
        <?php
        // Get list of files in the upload directory
        $files = scandir($target_dir);
        $uploaded_files = array_diff($files, array('.', '..')); // Remove . and ..

        if (empty($uploaded_files)) {
            echo "<p>Belum ada berkas gambar yang diunggah.</p>";
        } else {
            echo "<ul class='file-list'>";
            foreach ($uploaded_files as $file) {
                $file_path = $target_dir . $file;
                // Check if it's an actual file and an image for viewing/downloading
                $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif']);

                if (is_file($file_path)) {
                    echo "<li>";
                    echo "<span>" . htmlspecialchars($file) . "</span>";
                    echo "<div class='actions'>";
                    
                    // Show View button only for images
                    if ($is_image) {
                        echo "<a href='#' class='view-btn' data-filepath='" . htmlspecialchars($file_path) . "'>Lihat</a>";
                    }

                    // Download link (for all files)
                    echo "<a href='" . htmlspecialchars($file_path) . "' download class='download-btn'>Unduh</a>";
                    
                    // Delete link - important: link back to this same file (upload.php) with a GET parameter
                    echo "<a href='?delete_file=" . urlencode($file) . "' class='delete-btn' onclick='return confirm(\"Apakah Anda yakin ingin menghapus berkas ini?\\n" . addslashes(htmlspecialchars($file)) . "\");'>Hapus</a>";
                    echo "</div>";
                    echo "</li>";
                }
            }
            echo "</ul>";
        }
        ?>
    </div>

    <div class="image-viewer-container" id="imageViewerContainer">
        <h2>Pratinjau Gambar</h2>
        <img id="revealedImage" src="" alt="Gambar Pratinjau">
        <button id="closeViewerBtn">Tutup</button>
    </div>

    <script>
        const fileInput = document.getElementById('fileToUpload');
        const previewImg = document.getElementById('preview');
        const imageViewerContainer = document.getElementById('imageViewerContainer');
        const revealedImage = document.getElementById('revealedImage');
        const closeViewerBtn = document.getElementById('closeViewerBtn');

        // Client-side preview for the upload form
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                previewImg.style.display = 'none';
                previewImg.src = ''; // Clear preview if non-image file is selected
            }
        });

        // Hide messages after a few seconds (optional)
        const messagesDiv = document.querySelector('.messages');
        if (messagesDiv) {
            setTimeout(() => {
                messagesDiv.style.opacity = '0';
                messagesDiv.style.transition = 'opacity 1s ease-out';
                setTimeout(() => messagesDiv.remove(), 1000); // Remove after transition
            }, 5000); // Hide after 5 seconds
        }

        // Add event listeners for "Lihat" buttons (for uploaded files)
        document.querySelectorAll('.view-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default link behavior (e.g., scrolling to top)
                const filePath = this.dataset.filepath; // Get the file path from data-filepath attribute
                revealedImage.src = filePath; // Set the image source
                imageViewerContainer.style.display = 'block'; // Show the viewer container
                imageViewerContainer.scrollIntoView({ behavior: 'smooth' }); // Scroll to the viewer
            });
        });

        // Close the image viewer
        closeViewerBtn.addEventListener('click', function() {
            imageViewerContainer.style.display = 'none'; // Hide the viewer container
            revealedImage.src = ''; // Clear the image source
        });

    </script>

</body>
</html>