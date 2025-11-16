<?php
/**
 * Plugin Name: Site Activity Notifier
 * Description: Send real-time visitor information to Telegram (custom lightweight implementation).
 * Version: 1.0
 * Author: Mr-Saman
 */

// =======================[ CONFIGURATION ]=======================

// Telegram Bot token and chat ID
define('TEL_BOT_TOKEN', 'YOUR_BOT_TOKEN');
define('TEL_CHAT_ID', 'YOUR_CHAT_ID');

// Proxy URL (for environments where Telegram is restricted)
define('TEL_PROXY_URL', 'https://www.httpdebugger.com/Tools/ViewHttpHeaders.aspx');

// Message language: 'fa' for Persian or 'en' for English
define('TEL_LANGUAGE', 'en');

// All display texts can be easily modified here
$tel_texts = [
    'fa' => [
        'title'  => 'کاربر جدید در سایت',
        'ip'     => 'آی‌پی',
        'os'     => 'سیستم عامل',
        'agent'  => 'مرورگر',
        'time'   => 'زمان',
        'place'  => 'موقعیت',
        'button' => 'مشاهده صفحه'
    ],
    'en' => [
        'title'  => 'New visitor detected',
        'ip'     => 'IP Address',
        'os'     => 'Operating System',
        'agent'  => 'Browser',
        'time'   => 'Local Time',
        'place'  => 'Location',
        'button' => 'View Page'
    ]
];

// ===============================================================

// Enqueue jQuery and output script in the footer
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('jquery');
    add_action('wp_footer', 'notify_insert_script', 100);
});

function notify_insert_script() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        const INTERVAL = 5 * 60 * 1000; // 5 minutes
        const lastVisit = localStorage.getItem('lastVisitTime');
        const lastUrl = localStorage.getItem('lastPageUrl');
        const now = Date.now();
        const url = window.location.href;

        if (!lastVisit || (now - lastVisit > INTERVAL) || lastUrl !== url) {
            localStorage.setItem('lastVisitTime', now);
            localStorage.setItem('lastPageUrl', url);

            const agent = navigator.userAgent;
            let os = "Unknown";
            if (/Windows NT 10/.test(agent)) os = "Windows 10";
            else if (/Linux/.test(agent)) os = "Linux";
            else if (/Android/.test(agent)) os = "Android";
            else if (/iPhone|iPad|iPod/.test(agent)) os = "iOS";
            else if (/Macintosh/.test(agent)) os = "MacOS";

            $.post('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                action: 'send_user_data_to_telegram_custom',
                page_url: url,
                os: os,
                agent: agent,
                local_time: new Date().toLocaleString()
            });
        }
    });
    </script>
    <?php
}

// Handle AJAX requests (both visitors and logged-in users)
add_action('wp_ajax_send_user_data_to_telegram_custom', 'send_user_data_to_telegram_custom');
add_action('wp_ajax_nopriv_send_user_data_to_telegram_custom', 'send_user_data_to_telegram_custom');

function send_user_data_to_telegram_custom() {
    global $tel_texts;

    $lang = TEL_LANGUAGE;
    $texts = $tel_texts[$lang];

    $page_url  = esc_url_raw($_POST['page_url'] ?? '');
    $os        = sanitize_text_field($_POST['os'] ?? 'Unknown');
    $agent     = sanitize_text_field($_POST['agent'] ?? 'N/A');
    $localtime = sanitize_text_field($_POST['local_time'] ?? current_time('mysql'));
    $ip        = sanitize_text_field($_SERVER['REMOTE_ADDR']);

    // Get location via IP (primary: ipinfo, fallback: ip-api)
    $geo = get_geo_from_ipinfo($ip);
    if ($geo['country'] === '??') {
        $geo = get_geo_from_ipapi($ip);
    }

    $message = "{$texts['title']}\n\n"
             . "{$texts['ip']}: {$ip}\n"
             . "{$texts['os']}: {$os}\n"
             . "{$texts['agent']}: {$agent}\n"
             . "{$texts['time']}: {$localtime}\n"
             . "{$texts['place']}: {$geo['location']}, {$geo['country']}";

    $keyboard = json_encode([
        'inline_keyboard' => [
            [['text' => $texts['button'], 'url' => esc_url_raw($page_url)]]
        ]
    ]);

    $telegram_url = sprintf(
        "https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s&reply_markup=%s",
        TEL_BOT_TOKEN,
        TEL_CHAT_ID,
        urlencode($message),
        urlencode($keyboard)
    );

    // Send via proxy (for restricted regions)
    wp_remote_post(TEL_PROXY_URL, [
        'method'  => 'POST',
        'blocking' => false,
        'timeout' => 15,
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => wp_json_encode([
            "UrlBox" => $telegram_url,
            "AgentList" => "Mozilla Firefox",
            "VersionsList" => "HTTP/1.1",
            "MethodList" => "POST"
        ])
    ]);

    wp_die();
}

// -----------------[ Helper Functions ]-----------------

function get_geo_from_ipinfo($ip) {
    $res = wp_remote_get("https://ipinfo.io/{$ip}/json");
    if (is_wp_error($res)) return ['location' => 'Unknown', 'country' => '??'];
    $data = json_decode(wp_remote_retrieve_body($res), true);
    return [
        'location' => sanitize_text_field($data['city'] ?? 'Unknown'),
        'country'  => sanitize_text_field($data['country'] ?? '??')
    ];
}

function get_geo_from_ipapi($ip) {
    $res = wp_remote_get("http://ip-api.com/json/{$ip}?fields=status,country,city");
    if (is_wp_error($res)) return ['location' => 'Unknown', 'country' => '??'];
    $data = json_decode(wp_remote_retrieve_body($res), true);
    if (($data['status'] ?? '') !== 'success') return ['location' => 'Unknown', 'country' => '??'];
    return [
        'location' => sanitize_text_field($data['city'] ?? 'Unknown'),
        'country'  => sanitize_text_field($data['country'] ?? '??')
    ];
}
