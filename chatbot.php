<?php
session_start();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'] ?? '';

    if(empty($message)) {
        echo json_encode(['reply' => 'Please type a message.']);
        exit();
    }

    // Paste your Groq API key here — only in this file, never in chat
    $api_key = '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';
    $url     = 'https://api.groq.com/openai/v1/chat/completions';

    // ✅ PHP detects if message is Tagalog or English
    // Common Tagalog words to detect Filipino messages
    $tagalog_words = [
        'ano', 'paano', 'saan', 'sino', 'bakit', 'kailan', 'magkano',
        'ako', 'ikaw', 'siya', 'kami', 'kayo', 'sila', 'namin', 'nila',
        'ang', 'ng', 'sa', 'na', 'at', 'ay', 'mga', 'po', 'opo', 'ho',
        'yung', 'yun', 'ito', 'iyan', 'iyon', 'dito', 'diyan', 'doon',
        'hindi', 'oo', 'paki', 'pwede', 'gusto', 'kailangan', 'mayroon',
        'wala', 'meron', 'para', 'kung', 'kapag', 'dahil', 'kasi',
        'salamat', 'kumusta', 'maganda', 'naman', 'talaga', 'lang',
        'din', 'rin', 'nga', 'ba', 'daw', 'raw', 'sana', 'siguro',
        'mag', 'nag', 'pag', 'mag-', 'nag-', 'makuha', 'makita',
        'requirements', // sometimes Tagalog users say this
    ];

    $message_lower = strtolower($message);
    $words_in_message = preg_split('/\s+/', $message_lower);
    
    $tagalog_count = 0;
    foreach ($words_in_message as $word) {
        $word_clean = preg_replace('/[^a-z]/', '', $word);
        if (in_array($word_clean, $tagalog_words)) {
            $tagalog_count++;
        }
    }

    // If more than 0 Tagalog words found, treat as Tagalog
    $is_tagalog = $tagalog_count > 0;
    
    if ($is_tagalog) {
        $language_instruction = "IMPORTANT: The user is writing in Filipino/Tagalog. You MUST reply in Tagalog/Filipino only. Do not use English in your reply.";
    } else {
        $language_instruction = "IMPORTANT: The user is writing in English. You MUST reply in English only. Do not use Tagalog or Filipino in your reply.";
    }

    $system_prompt = "You are an intelligent assistant for the Web-Based Scholarship Management System of the Cainta Scholarship Program, Municipality of Cainta, Rizal, Philippines.

$language_instruction

Here is everything you know about the system:

=== ABOUT THE PROGRAM ===
- The Cainta Scholarship Program provides financial assistance to deserving students who are residents of Cainta, Rizal.
- Scholarship allowance is 2,500 pesos per semester (standard) or 5,000 pesos (special allowance).
- The program covers students from 7 barangays: San Andres, San Isidro, San Juan, San Roque, Santa Rosa, Santo Domingo, Santo Nino.

=== HOW TO APPLY ===
1. Go to the student portal and click Register to create an account.
2. Fill in your personal information: full name, birthdate, barangay, gender, contact number, and address.
3. After registering, log in using your email and password.
4. Click My Application or Apply Now on your dashboard.
5. Fill out the application form with personal, family, and academic information.
6. Upload the required documents: Grade Slip, School Enrollment Receipt, and Enrollment Form.
7. Click Submit Application.
8. Wait for the scholarship office to review your application.

=== REQUIRED DOCUMENTS ===
- Latest Grade Slip or Transcript
- School Enrollment Receipt
- Enrollment Form

=== APPLICATION STATUS ===
- Pending: Your application has been received and is waiting for review.
- For Review: The scholarship office is currently reviewing your application.
- Approved: Your application has been approved.
- Rejected: Your application was not approved.
- Incomplete: Some requirements are missing.

=== HOW TO TRACK YOUR APPLICATION ===
- Log in to the student portal.
- Click Status in the navigation menu.
- You will see a timeline showing your application progress.

=== DISBURSEMENTS ===
- After approval, your allowance will be released by the cashier.
- Standard allowance is 2,500 pesos per semester.
- View disbursement history by clicking Disbursements in the student portal.

=== RULES FOR ANSWERING ===
- Always answer in a friendly and helpful tone.
- Keep answers clear and easy to understand.
- Only answer questions related to the Cainta Scholarship Program.
- If asked something unrelated, politely say you can only help with scholarship-related questions.";

    $data = [
        'model'    => 'llama-3.1-8b-instant',
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user',   'content' => $message]
        ],
        'temperature' => 0.4,
        'max_tokens'  => 1024,
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response   = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curl_error) {
        echo json_encode(['reply' => 'Connection error: ' . $curl_error]);
        exit();
    }

    if ($http_code === 429) {
        echo json_encode(['reply' => 'The assistant is currently busy. Please try again in a few seconds.']);
        exit();
    }

    if ($http_code !== 200) {
        $err = json_decode($response, true);
        echo json_encode(['reply' => 'Error ' . $http_code . ': ' . ($err['error']['message'] ?? $response)]);
        exit();
    }

    $result = json_decode($response, true);

    if (isset($result['choices'][0]['message']['content'])) {
        $reply = $result['choices'][0]['message']['content'];
    } else {
        $reply = 'Sorry, I could not process your request. Please try again.';
    }

    echo json_encode(['reply' => $reply]);
    exit();
}
?>
