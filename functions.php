<?php
//Put Code In functions.php In Your Theme

function send_online_user_to_telegram() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // ReCheck Clint
            
            var interval = 5 * 60 * 1000; // 5 Min

            // Get Last Time In localStorage
            var lastVisitTime = localStorage.getItem('lastVisitTime');
            // Get Last PageURL localStorage
            var lastPageUrl = localStorage.getItem('lastPageUrl'); 

            // Time Now
            var currentTime = new Date().getTime();

            // Current Page URL Client
            var currentPageUrl = window.location.href;

            // Check Time And URL 
            if ((!lastVisitTime || (currentTime - lastVisitTime) > interval) || lastPageUrl !== currentPageUrl) {
                // ذخیره زمان و آدرس جدید در localStorage
                localStorage.setItem('lastVisitTime', currentTime);
                localStorage.setItem('lastPageUrl', currentPageUrl);

                // Get User Agent (سیستم عامل)
                var userAgent = navigator.userAgent;
                var os = "نامشخص";
                if (userAgent.indexOf("Windows NT 10.0") !== -1) os = "ویندوز 10";
                else if (userAgent.indexOf("Windows NT 6.1") !== -1) os = "ویندوز 7";
                else if (userAgent.indexOf("Mac") !== -1) os = "مک او اس";
                else if (userAgent.indexOf("X11") !== -1) os = "لینوکس";
                else if (userAgent.indexOf("Android") !== -1) os = "اندروید";
                else if (userAgent.indexOf("iPhone") !== -1) os = "آیفون";

                var page_url3 = window.location.href;

                // Encode UTF-8 URL (fa Character)
                var encoded_url = encodeURIComponent(page_url3); 

			
                // Send AJAX Request To Server
                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    method: "POST",
                    data: {
                        action: 'send_online_user_info_to_telegram',
                        page_url: encoded_url,  // Page URL
                        user_ip: '<?php echo $_SERVER['REMOTE_ADDR']; ?>', // User IP
                        os: os,  // User OS
                        time: new Date().toLocaleString(), // Now Time
                    },
                    success: function(response) {
                        // Success 
                    }
                });
            }
        });
    </script>
    <?php
}

add_action('wp_footer', 'send_online_user_to_telegram');



add_action('wp_footer', 'send_online_user_to_telegram');

// Sending on Telegram even on Iranian servers (without filters)
function send_online_user_info_to_telegram() {
    if (isset($_POST['page_url']) && isset($_POST['user_ip'])) {
        // Client Information
        $page_url = urldecode($_POST['page_url']);          // Page URL Decode
        $user_ip = sanitize_text_field($_POST['user_ip']);   // IP
        $os = sanitize_text_field($_POST['os']);             // OS
        $time = sanitize_text_field($_POST['time']);         // Visit Time
		

		// Fix Bug URL And Re-Decode
		$decoded_url = urldecode($page_url);
		
		// Get Location Via Ip
        $url = "https://ipinfo.io/{$user_ip}/json";
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            $location = "مکان نامشخص";
            $country = "نامشخص";
            $flag = "🌍";
        } else {
            $data = json_decode(wp_remote_retrieve_body($response));
            $location = isset($data->city) ? $data->city : "نامشخص";
            $country = isset($data->country) ? $data->country : "نامشخص";
            $flag = get_country_flag($country);  // Flag Emoji By Country
        }

		
        // BOT AND CHAT-ID
        $TokenBot = "<YOUR-BOT-TOKEN>";  // Enter Bot Token In "" -> Create In https://t.me/BotFather 
        $ChatId = "<YOUR-CHAT-ID>";  // Enter CHAT ID In "" -> Get in Start Bot https://t.me/chatIDrobot
        
        //
        //
        // ** Be sure to start the robot you are building and send it a message to confirm. **
        //
        // ******************** ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ ****************************

        // Create Message
        $message = "🖥 کاربر آنلاین در سایت:
        		
        🌍 آی‌پی کاربر: {$user_ip}
        🖥 سیستم عامل: {$os}
        ⏰ زمان بازدید: {$time}
        
        🌍 موقعیت مکانی: {$location}, {$country} {$flag}
		
		⌨️ Code By @Samansle
        ";
		
        // Create Button
		 $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'بازدید از صفحه 🌐', 'url' => $decoded_url],  // Button With URL Link
                ],
            ],
        ];
        $keyboard = json_encode($keyboard);


        // Telegram Api Register
        $TelegramApiUrl = "https://api.telegram.org/bot{$TokenBot}/sendMessage?chat_id={$ChatId}&text=" . urlencode($message). "&reply_markup=" . urlencode($keyboard);

		
		// Using HttpDebug To << Bypass The Filter >>
        $HttpDebug = "https://www.httpdebugger.com/Tools/ViewHttpHeaders.aspx";
        
        // Create Payload HttpDebug
        $Payloads = [
            "UrlBox"       => $TelegramApiUrl,
            "AgentList"    => "Mozilla Firefox",
            "VersionsList" => "HTTP/1.1",
            "MethodList"   => "POST"
        ];

        // Running Code Behind The Program Without Wasting Time ** :)
        $args = [
            'method'    => 'POST', 
            'blocking'  => false,  
            'timeout'   => 15,      // TimeOut
            'headers'   => [
                'Content-Type' => 'application/json',
            ],
            'body'      => json_encode($Payloads),
        ];

        // Send API Request  
        wp_remote_post($HttpDebug, $args);

    }

    // Close AjaxRequest
    wp_die();
}

// Ajax action
add_action('wp_ajax_send_online_user_info_to_telegram', 'send_online_user_info_to_telegram'); 
add_action('wp_ajax_nopriv_send_online_user_info_to_telegram', 'send_online_user_info_to_telegram'); 

// Flags
function get_country_flag($country_code) {
    $flags = [
        'US' => '🇺🇸', // آمریکا
        'GB' => '🇬🇧', // بریتانیا
        'CA' => '🇨🇦', // کانادا
        'DE' => '🇩🇪', // آلمان
        'FR' => '🇫🇷', // فرانسه
        'IT' => '🇮🇹', // ایتالیا
        'IN' => '🇮🇳', // هند
        'RU' => '🇷🇺', // روسیه
        'BR' => '🇧🇷', // برزیل
        'AU' => '🇦🇺', // استرالیا
        'CN' => '🇨🇳', // چین
        'JP' => '🇯🇵', // ژاپن
        'KR' => '🇰🇷', // کره جنوبی
        'ZA' => '🇿🇦', // آفریقای جنوبی
        'MX' => '🇲🇽', // مکزیک
        'ES' => '🇪🇸', // اسپانیا
        'PT' => '🇵🇹', // پرتغال
        'NG' => '🇳🇬', // نیجریه
        'EG' => '🇪🇬', // مصر
        'TR' => '🇹🇷', // ترکیه
        'SA' => '🇸🇦', // عربستان سعودی
        'KR' => '🇰🇷', // کره جنوبی
        'SE' => '🇸🇪', // سوئد
        'PL' => '🇵🇱', // لهستان
        'FI' => '🇫🇮', // فنلاند
        'NO' => '🇳🇴', // نروژ
        'BE' => '🇧🇪', // بلژیک
        'AT' => '🇦🇹', // اتریش
        'CH' => '🇨🇭', // سوئیس
        'DK' => '🇩🇰', // دانمارک
        'IR' => '🇮🇷', // ایران
        'AE' => '🇦🇪', // امارات متحده عربی
        'KW' => '🇰🇼', // کویت
        'OM' => '🇴🇲', // عمان
        'QA' => '🇶🇦', // قطر
        'BH' => '🇧🇭', // بحرین
        'LY' => '🇱🇾', // لیبی
        'JO' => '🇯🇴', // اردن
        'LB' => '🇱🇧', // لبنان
        'SY' => '🇸🇾', // سوریه
        'YE' => '🇾🇪', // یمن
        'IQ' => '🇮🇶', // عراق
        'AF' => '🇦🇫', // افغانستان
        'PK' => '🇵🇰', // پاکستان
        'BD' => '🇧🇩', // بنگلادش
        'MM' => '🇲🇲', // میانمار
        'VN' => '🇻🇳', // ویتنام
        'TH' => '🇹🇭', // تایلند
        'PH' => '🇵🇭', // فیلیپین
        'ID' => '🇮🇩', // اندونزی
        'KH' => '🇰🇭', // کامبوج
        'LA' => '🇱🇦', // لائوس
        'SG' => '🇸🇬', // سنگاپور
        'MY' => '🇲🇾', // مالزی
        'TW' => '🇹🇼', // تایوان
        'HK' => '🇭🇰', // هنگ کنگ
        'MO' => '🇲🇴', // ماکائو
        'MZ' => '🇲🇿', // موزامبیک
        'KE' => '🇰🇪', // کنیا
        'UG' => '🇺🇬', // اوگاندا
        'TZ' => '🇹🇿', // تانزانیا
        'ZW' => '🇿🇼', // زیمبابوه
        'ET' => '🇪🇹', // اتیوپی
        'KE' => '🇰🇪', // کنیا
        'GH' => '🇬🇭', // غنا
        'ZW' => '🇿🇼', // زیمبابوه
        'SN' => '🇸🇳', // سنگال
        'KE' => '🇰🇪', // کنیا
        'UG' => '🇺🇬', // اوگاندا
        'TZ' => '🇹🇿', // تانزانیا
        'NG' => '🇳🇬', // نیجریه
        'MW' => '🇲🇼', // مالاوی
        'BJ' => '🇧🇯', // بنین
        'ZM' => '🇿🇲', // زامبیا
        'RW' => '🇷🇼', // رواندا
        'LR' => '🇱🇷', // لیبریا
        'MW' => '🇲🇼', // مالاوی
    ];

    // Return Flags
    return isset($flags[$country_code]) ? $flags[$country_code] : '🌍';
}




?>
