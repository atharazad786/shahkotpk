<?php
require_once __DIR__.'/app/bootstrap.php';

// Simple API endpoint for the bot
if (isset($_GET['action']) && $_GET['action'] === 'chat') {
    header('Content-Type: application/json');
    $msg = strtolower(trim($_POST['message'] ?? ''));
    $reply = "Mujhe samajh nahi aya. Kya aap 'deals', 'doctors', ya 'jobs' ke baray mein janna chahte hain?";
    
    if (strpos($msg, 'deal') !== false || strpos($msg, 'discount') !== false) {
        $reply = "Aaj Shahkot mein 'Fri Chicks' par 20% off chal raha hai. Aur bhi deals /deals.php par mojood hain!";
    } elseif (strpos($msg, 'doctor') !== false || strpos($msg, 'hospital') !== false) {
        $reply = "THQ Hospital Shahkot open hai. Private checkup ke liye Mohsin Surgical Clinic best hai.";
    } elseif (strpos($msg, 'hi') !== false || strpos($msg, 'hello') !== false || strpos($msg, 'salam') !== false) {
        $reply = "Walaikum Assalam! Main ShahkotPK ka AI Assistant hoon. Main aapki kya madad kar sakta hoon?";
    }
    
    echo json_encode(['reply' => $reply]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShahkotPK AI Assistant</title>
    <link rel="stylesheet" href="/assets/platform-v4.0.0.css?v=430">
    <style>
        .chat-container { max-width: 500px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); display: flex; flex-direction: column; height: 60vh; }
        .chat-header { background: #00d2ff; color: #fff; padding: 20px; text-align: center; font-weight: bold; border-radius: 12px 12px 0 0; }
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; background: #f9f9f9; }
        .msg { margin-bottom: 15px; padding: 10px 15px; border-radius: 20px; max-width: 80%; }
        .msg.bot { background: #e0e0e0; color: #333; align-self: flex-start; }
        .msg.user { background: #00d2ff; color: #fff; align-self: flex-end; margin-left: auto; }
        .chat-input { display: flex; padding: 15px; border-top: 1px solid #ddd; }
        .chat-input input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 20px; outline: none; }
        .chat-input button { background: #00d2ff; color: #fff; border: none; padding: 10px 20px; margin-left: 10px; border-radius: 20px; cursor: pointer; }
    </style>
</head>
<body style="background: #f0f2f5; font-family: system-ui, sans-serif; margin:0;">
    <div class="chat-container">
        <div class="chat-header">ShahkotPK AI Assistant</div>
        <div class="chat-messages" id="chatBox">
            <div class="msg bot">Salam! Shahkot mein aapko kya dhoondna hai? (e.g. Deals, Doctors)</div>
        </div>
        <div class="chat-input">
            <input type="text" id="chatInput" placeholder="Type a message..." onkeypress="if(event.key === 'Enter') sendMessage()">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
        function sendMessage() {
            const input = document.getElementById('chatInput');
            const box = document.getElementById('chatBox');
            const msg = input.value.trim();
            if(!msg) return;

            // Add user message
            box.innerHTML += `<div class="msg user">${msg}</div>`;
            input.value = '';
            box.scrollTop = box.scrollHeight;

            // Fetch bot response
            fetch('?action=chat', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'message=' + encodeURIComponent(msg)
            })
            .then(res => res.json())
            .then(data => {
                box.innerHTML += `<div class="msg bot">${data.reply}</div>`;
                box.scrollTop = box.scrollHeight;
            });
        }
    </script>
</body>
</html>
