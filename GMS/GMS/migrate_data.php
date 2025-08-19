<?php
require_once 'config.php';

// Connect to old database
$old_conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, 'project');
if ($old_conn->connect_error) {
    die("Connection to old database failed: " . $old_conn->connect_error);
}

// Connect to new database
$new_conn = getDBConnection();

try {
    // Start transaction
    $new_conn->begin_transaction();
    
    // Migrate users
    $result = $old_conn->query("SELECT * FROM userregistration");
    while ($row = $result->fetch_assoc()) {
        $stmt = $new_conn->prepare("INSERT INTO users (username, password_hash, email, first_name, last_name, phone, gender, role, created_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $role = ($row['username'] === 'admin') ? 'admin' : 'user';
        $created_at = date('Y-m-d H:i:s', strtotime($row['date']));
        
        $stmt->bind_param("sssssssss", 
            $row['username'],
            hashPassword($row['pass']), // Hash the password
            $row['email'],
            $row['fname'],
            $row['lname'],
            $row['phone'],
            $row['gender'],
            $role,
            $created_at
        );
        
        $stmt->execute();
    }
    
    // Migrate categories and subcategories
    $categories = [
        'Academic' => ['Faculty', 'Student', 'Course', 'Examination'],
        'Administrative' => ['Admission', 'Registration', 'Documentation'],
        'Infrastructure' => ['Facilities', 'Maintenance', 'Security'],
        'Student Services' => ['Library', 'Sports', 'Hostel', 'Transportation'],
        'Other' => ['General', 'Miscellaneous']
    ];
    
    foreach ($categories as $category => $subcategories) {
        // Insert category
        $stmt = $new_conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $description = $category . " related grievances";
        $stmt->bind_param("ss", $category, $description);
        $stmt->execute();
        $category_id = $new_conn->insert_id;
        
        // Insert subcategories
        foreach ($subcategories as $subcategory) {
            $stmt = $new_conn->prepare("INSERT INTO subcategories (category_id, name, description) VALUES (?, ?, ?)");
            $sub_desc = $category . " - " . $subcategory . " related grievances";
            $stmt->bind_param("iss", $category_id, $subcategory, $sub_desc);
            $stmt->execute();
        }
    }
    
    // Migrate complaints to grievances
    $result = $old_conn->query("SELECT * FROM complaints");
    while ($row = $result->fetch_assoc()) {
        // Get user_id
        $stmt = $new_conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $row['user']);
        $stmt->execute();
        $user_result = $stmt->get_result();
        $user_row = $user_result->fetch_assoc();
        $user_id = $user_row['id'];
        
        // Get category_id and subcategory_id
        $stmt = $new_conn->prepare("SELECT c.id as category_id, s.id as subcategory_id 
                                  FROM categories c 
                                  JOIN subcategories s ON c.id = s.category_id 
                                  WHERE c.name = ? AND s.name = ?");
        $stmt->bind_param("ss", $row['category'], $row['subcategory']);
        $stmt->execute();
        $cat_result = $stmt->get_result();
        $cat_row = $cat_result->fetch_assoc();
        
        if ($cat_row) {
            $category_id = $cat_row['category_id'];
            $subcategory_id = $cat_row['subcategory_id'];
        } else {
            // Use 'Other' category if not found
            $stmt = $new_conn->prepare("SELECT c.id as category_id, s.id as subcategory_id 
                                      FROM categories c 
                                      JOIN subcategories s ON c.id = s.category_id 
                                      WHERE c.name = 'Other' AND s.name = 'General'");
            $stmt->execute();
            $cat_result = $stmt->get_result();
            $cat_row = $cat_result->fetch_assoc();
            $category_id = $cat_row['category_id'];
            $subcategory_id = $cat_row['subcategory_id'];
        }
        
        // Determine status
        $status = 'pending';
        if ($row['pending'] == '0') {
            // Check if in completed or in-progress
            $stmt = $old_conn->prepare("SELECT * FROM completedcomp WHERE compnum = ?");
            $stmt->bind_param("s", $row['id']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $status = 'resolved';
            } else {
                $stmt = $old_conn->prepare("SELECT * FROM inprogresscomp WHERE compnum = ?");
                $stmt->bind_param("s", $row['id']);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    $status = 'in_progress';
                }
            }
        }
        
        // Insert grievance
        $stmt = $new_conn->prepare("INSERT INTO grievances (user_id, category_id, subcategory_id, title, description, status, created_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $title = $row['nature'];
        $created_at = date('Y-m-d H:i:s', strtotime($row['date']));
        
        $stmt->bind_param("iiissss", 
            $user_id,
            $category_id,
            $subcategory_id,
            $title,
            $row['comp'],
            $status,
            $created_at
        );
        
        $stmt->execute();
        $grievance_id = $new_conn->insert_id;
        
        // Handle file attachment if exists
        if (!empty($row['file']) && $row['file'] != '0') {
            $stmt = $new_conn->prepare("INSERT INTO attachments (grievance_id, file_name, file_path, file_type, file_size) 
                                      VALUES (?, ?, ?, ?, ?)");
            
            $file_name = basename($row['file']);
            $file_path = $row['file'];
            $file_type = mime_content_type($row['file']);
            $file_size = filesize($row['file']);
            
            $stmt->bind_param("isssi", 
                $grievance_id,
                $file_name,
                $file_path,
                $file_type,
                $file_size
            );
            
            $stmt->execute();
        }
    }
    
    // Migrate login history
    $result = $old_conn->query("SELECT * FROM userloginfo");
    while ($row = $result->fetch_assoc()) {
        $stmt = $new_conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $row['user']);
        $stmt->execute();
        $user_result = $stmt->get_result();
        $user_row = $user_result->fetch_assoc();
        
        if ($user_row) {
            $stmt = $new_conn->prepare("INSERT INTO login_history (user_id, login_time, ip_address, user_agent, status) 
                                      VALUES (?, ?, ?, ?, ?)");
            
            $login_time = date('Y-m-d H:i:s', strtotime($row['date']));
            $ip_address = '127.0.0.1'; // Default IP since not stored in old system
            $user_agent = 'Unknown'; // Default user agent since not stored in old system
            $status = 'success';
            
            $stmt->bind_param("issss", 
                $user_row['id'],
                $login_time,
                $ip_address,
                $user_agent,
                $status
            );
            
            $stmt->execute();
        }
    }
    
    // Commit transaction
    $new_conn->commit();
    echo "Data migration completed successfully!";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $new_conn->rollback();
    echo "Error during migration: " . $e->getMessage();
} finally {
    // Close connections
    $old_conn->close();
    $new_conn->close();
}
?> 