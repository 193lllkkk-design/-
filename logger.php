<?php
header('Content-Type: application/json');
$log = json_decode(file_get_contents('php://input'), true) ?: $_POST;

// Полный IP (учитывая прокси)
$real_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Гео по IP
$geo = json_decode(file_get_contents("https://ipapi.co/{$real_ip}/json/"), true);

// Логируем
$log_data = date('Y-m-d H:i:s') . " | IP: $real_ip | Гео: {$geo['city']} ({$geo['latitude']},{$geo['longitude']}) | UA: " . 
            ($log['ua'] ?? $_SERVER['HTTP_USER_AGENT']) . " | Referrer: " . ($_SERVER['HTTP_REFERER'] ?? '-') . "\n";

file_put_contents('ip_logs.txt', $log_data, FILE_APPEND | LOCK_EX);

// Telegram уведомление (твой бот)
$bot_token = '7944593201:AAETdO_4MsYrQqdKyr2UGBkEcztnmvQfNMw';  // Создай бота @BotFather
$chat_id = '6538163691';      // Твой TG ID
$message = "🕵️ **Новый скаммер!**\nIP: `$real_ip`\nГео: {$geo['city']}, {$geo['country_name']}\nUA: " . substr($log['ua'] ?? '', 0, 100);

file_get_contents("https://api.telegram.org/bot{$bot_token}/sendMessage?chat_id={$chat_id}&text=" . urlencode($message) . "&parse_mode=Markdown");

echo json_encode(['status'=>'logged', 'ip'=>$real_ip]);
?>