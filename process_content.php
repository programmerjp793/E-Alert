<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'update_about') {
        // Update About Us content
        $contentId = $_POST['content_id'];
        $title = $_POST['title'];
        $body = $_POST['body'];
        
        $stmt = $conn->prepare("INSERT INTO content (content_id, title, body, created_at) 
                              VALUES (?, ?, ?, NOW())
                              ON DUPLICATE KEY UPDATE 
                              title = VALUES(title), 
                              body = VALUES(body), 
                              created_at = NOW()");
        $stmt->bind_param("sss", $contentId, $title, $body);
        $stmt->execute();
        
        $_SESSION['message'] = "About Us content updated successfully!";
    } 
    elseif ($_POST['action'] === 'update_carousel') {
        // Update Carousel images
        $contentId = $_POST['content_id'];
        $updateData = ['content_id' => $contentId];
        
        // Handle file uploads and deletions
        for ($i = 1; $i <= 7; $i++) {
            $fieldName = "image_$i";
            $deleteField = "delete_image_$i";
            
            // Check if delete checkbox is checked
            if (isset($_POST[$deleteField])) {
                $updateData[$fieldName] = null;
                continue;
            }
            
            // Handle file upload
            if (!empty($_FILES[$fieldName]['name'])) {
                $targetDir = "uploads/carousel/";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                
                $fileName = basename($_FILES[$fieldName]["name"]);
                $targetFile = $targetDir . uniqid() . "_" . $fileName;
                
                if (move_uploaded_file($_FILES[$fieldName]["tmp_name"], $targetFile)) {
                    $updateData[$fieldName] = $targetFile;
                }
            }
        }
        
        // Build SQL query
        $columns = [];
        $values = [];
        $updateParts = [];
        $types = '';
        $params = [];
        
        foreach ($updateData as $column => $value) {
            $columns[] = $column;
            $values[] = $value;
            $updateParts[] = "$column = ?";
            $types .= $value === null ? 's' : 's'; // s for string (including NULL)
        }
        
        $columnsStr = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $updateStr = implode(', ', $updateParts);
        
        $sql = "INSERT INTO content ($columnsStr, created_at) 
               VALUES ($placeholders, NOW())
               ON DUPLICATE KEY UPDATE 
               $updateStr, created_at = NOW()";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('s', count($values)), ...$values);
        $stmt->execute();
        
        $_SESSION['message'] = "Carousel images updated successfully!";
    }
    
    header("Location: edit_content.php");
    exit();
}
?>