<?php
// GitHub Configuration
define('GITHUB_TOKEN', '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');
define('GITHUB_USERNAME', 'superkitty1549');
define('GITHUB_REPO', '2025-26-coding-assignments');
define('FILE_PATH', 'form_submissions.txt'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $contact_method = htmlspecialchars($_POST['contact_method']);
    $subscribe = isset($_POST['subscribe']) ? 'Yes' : 'No';
    $topic = htmlspecialchars($_POST['topic']);
    $message = htmlspecialchars($_POST['message']);
    
    $timestamp = date('Y-m-d H:i:s');
    
    $newSubmission = "=================================\n";
    $newSubmission .= "FORM SUBMISSION\n";
    $newSubmission .= "Date: $timestamp\n";
    $newSubmission .= "=================================\n";
    $newSubmission .= "Name: $name\n";
    $newSubmission .= "Email: $email\n";
    $newSubmission .= "Preferred Contact: $contact_method\n";
    $newSubmission .= "Newsletter Subscribe: $subscribe\n";
    $newSubmission .= "Topic: $topic\n";
    $newSubmission .= "Message: $message\n";
    $newSubmission .= "=================================\n\n";
    
    $apiUrl = "https://api.github.com/repos/" . GITHUB_USERNAME . "/" . GITHUB_REPO . "/contents/" . FILE_PATH;
    
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
    
    if ($httpCode == 200) {
        $fileData = json_decode($response, true);
        $sha = $fileData['sha'];
        $existingContent = base64_decode($fileData['content']);
    }
    
    $updatedContent = $existingContent . $newSubmission;
    
    $commitData = [
        'message' => 'New form submission - ' . $timestamp,
        'content' => base64_encode($updatedContent),
        'branch' => 'main' 
    ];
    
    if ($sha) {
        $commitData['sha'] = $sha;
    }
    
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
    
    if ($pushHttpCode == 200 || $pushHttpCode == 201) {
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
                <h2>Thank You!</h2>
                <p>Your form has been submitted and saved to GitHub successfully.</p>
                <a href='index.html'>Back to Form</a>
            </div>
        </body>
        </html>";
    } else {
        $errorData = json_decode($pushResponse, true);
        echo "Error: Unable to save to GitHub. ";
        echo "HTTP Code: " . $pushHttpCode . "<br>";
        echo "Response: " . htmlspecialchars($pushResponse);
    }
    
} else {
    header("Location: index.html");
    exit();
}
?>