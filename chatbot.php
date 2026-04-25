<?php
session_start();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'] ?? '';
    
    if(empty($message)) {
        echo json_encode(['reply' => 'Please type a message.']);
        exit();
    }

    $api_key = 'AIzaSyAfYeOthQ7BZOrtSRSU-IcDJLMQUd-qSRo';
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $api_key;

    $system_prompt = "You are a helpful assistant for the Cainta Scholarship Program of the Municipality of Cainta, Rizal, Philippines. 
    
    Here is what you know about the program:
    - The Cainta Scholarship Program provides financial assistance to deserving students who are residents of Cainta, Rizal.
    - There are 7 barangays in Cainta: San Andres, San Isidro, San Juan, San Roque, Santa Rosa, Santo Domingo, Santo Niño.
    - Requirements to apply: Grade Slip or Transcript, School Enrollment Receipt, Enrollment Form.
    - Students need to register an account, fill out the application form, upload documents, and submit.
    - After submitting, the scholarship office will review the application.
    - Application statuses: Pending, For Review, Approved, Rejected, Incomplete.
    - If approved, scholars will receive allowance disbursements per semester.
    - Students can track their application status by logging in to the portal.
    - The system has 3 staff roles: Admin, Scholarship Officer, and Cashier.
    - For inquiries, contact the Cainta Scholarship Office.
    
    Answer questions clearly, politely, and in a helpful manner. 
    If asked in Filipino or Tagalog, respond in Filipino.
    Keep answers short and easy to understand.
    If you don't know something specific, suggest contacting the scholarship office directly.";

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $system_prompt . "\n\nStudent question: " . $message]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    $reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not process your request. Please try again.';

    echo json_encode(['reply' => $reply]);
    exit();
}
?>