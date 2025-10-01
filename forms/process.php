<?php
// GitHub Configuration
define('GITHUB_TOKEN', '[token]'); 
define('GITHUB_USERNAME', 'superkitty1549');
define('GITHUB_REPO', '2025-26-coding-assignments');
define('FILE_PATH', 'forms/form_submissions.txt');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get form data and sanitize it
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $grade = htmlspecialchars($_POST['grade']);
    
    // Get all selected colors
    $colors = isset($_POST['colors']) ? implode(', ', array_map('htmlspecialchars', $_POST['colors'])) : 'None';
    
    $flavor = htmlspecialchars($_POST['flavor']);
    $comments = isset($_POST['comments']) ? htmlspecialchars($_POST['comments']) : 'No comments';
    
    // Get current timestamp
    $timestamp = date('Y-m-d H:i:s');
    
    // Format the new submission
    $newSubmission = "=================================\n";
    $newSubmission .= "FORM SUBMISSION\n";
    $newSubmission .= "Date: $timestamp\n";
    $newSubmission .= "=================================\n";
    $newSubmission .= "Name: $name\n";
    $newSubmission .= "Email: $email\n";
    $newSubmission .= "Grade Level: $grade\n";
    $newSubmission .= "Favorite Colours: $colors\n";
    $newSubmission .= "Favorite Flavour: $flavor\n";
    $newSubmission .= "Comments: $comments\n";
    $newSubmission .= "=================================\n\n";
    
    // GitHub API URL to get the file
    $apiUrl = "https://api.github.com/repos/" . GITHUB_USERNAME . "/" . GITHUB_REPO . "/contents/" . FILE_PATH;
    
    // Initialize cURL to get existing file
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . GITHUB_TOKEN,
        'User-Agent: PHP-Form-Script',
        'Accept: application/vnd.github.v3+json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $existingContent = '';
    $sha = null;
    
    // If file exists, get its content and SHA
    if ($httpCode == 200) {
        $fileData = json_decode($response, true);
        $sha = $fileData['sha'];
        $existingContent = base64_decode($fileData['content']);
    }
    
    // Append new submission to existing content
    $updatedContent = $existingContent . $newSubmission;
    
    // Prepare data for GitHub API
    $commitData = [
        'message' => 'New form submission - ' . $timestamp,
        'content' => base64_encode($updatedContent),
        'branch' => 'main'
    ];
    
    // Add SHA if file exists (required for updates)
    if ($sha) {
        $commitData['sha'] = $sha;
    }
    
    // Push to GitHub
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($commitData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . GITHUB_TOKEN,
        'User-Agent: PHP-Form-Script',
        'Accept: application/vnd.github.v3+json',
        'Content-Type: application/json'
    ]);
    
    $pushResponse = curl_exec($ch);
    $pushHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Check if successful
    if ($pushHttpCode == 200 || $pushHttpCode == 201) {
        // Success
        echo "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Success</title>
            <style>
                body {
                    font-family: 'Courier New', monospace;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .message {
                    background: white;
                    padding: 40px;
                    border-radius: 15px;
                    text-align: center;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                }
                h2 { color: #764ba2; margin-bottom: 20px; }
                a {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background: #667eea;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <div class='message'>
                <h2>thank u!!</h2>
                <p>ur form has been submitted successfully :)</p>
                <a href='index.html'>back to form <3 </a>
            </div>
        </body>
        </html>";
    } else {
        // Error
        $errorData = json_decode($pushResponse, true);
        echo "Error: Unable to save to GitHub. ";
        echo "HTTP Code: " . $pushHttpCode . "<br>";
        echo "Response: " . htmlspecialchars($pushResponse);
    }
    
} else {
    // If someone tries to access this page directly
    header("Location: index.html");
    exit();
}
?>