# بازدید لحظه ای کاربران آنلاین در تلگرام
# Instant view of online users on Telegram
**زبان‌ها / Languages:**  
🇮🇷 [فارسی (پیش‌فرض)](#فارسی) | 🇬🇧 [English](#english)

---

## فارسی

### 📡 معرفی

این کد یک افزونه‌ی سبک وردپرس است که اطلاعات بازدید کاربران را به‌صورت لحظه‌ای به تلگرام مدیر سایت ارسال می‌کند.  
این افزونه می‌تواند موقعیت آی‌پی، سیستم عامل، مرورگر، ساعت محلی و لینک صفحه‌ای که کاربر دیده را ثبت کند.

---

### ✨ ویژگی‌ها

- اطلاع‌رسانی بلادرنگ در تلگرام  
- ساختار سبک و بدون وابستگی اضافی  
- پشتیبانی از مناطق دارای محدودیت تلگرام با *پراکسی داخلی*  
- مکان‌یابی دقیق بر پایه‌ی `ipinfo.io` با fallback به `ip-api.com`  
- پشتیبانی از **فارسی** و **انگلیسی**  
- قابل‌تنظیم از بالای فایل (بدون نیاز به ویرایش داخلی کد)  
- ارسال ایمن با فیلتر و ضد SQL/XSS  

---

### ⚙️ نصب و فعال‌سازی

1. فایل `function.php` را در مسیر زیر قرار دهید:
function.php در theme فعال سایت شما


2.فایل را **ذخیره کنید**.
3. در بالای فایل، این بخش را ویرایش نمایید:
```php
   define('TEL_BOT_TOKEN', 'توکن ربات');
   define('TEL_CHAT_ID', 'چت آیدی');
   define('TEL_PROXY_URL', 'https://www.httpdebugger.com/Tools/ViewHttpHeaders.aspx');
   define('TEL_LANGUAGE', 'fa'); // پیش‌فرض فارسی
   ```
4. صفحه سایت را باز کنید تا اولین پیام در ربات تلگرام شما ظاهر شود.


# English

### 📡 Overview

This code is a lightweight WordPress plugin that sends user visit information to the site administrator's Telegram in real time.
Each notification includes visitor IP, OS, browser, location, and current page link.

---

### ✨ Features

- Real-time visitor notifications  
- Proxy support for Telegram-restricted regions  
- Fallback IP lookup (ipinfo → ip-api)  
- English and Persian message support  
- All messages and titles easily customizable  
- Fully sanitized and WordPress-compliant AJAX  

---

### ⚙️ Installation

1. Copy `function.php` into:
function.php in Your Theme
2. **Save** The File.
3. Edit the configuration section at the top of the file:

```php
   define('TEL_BOT_TOKEN', 'YOUR_BOT_TOKEN');
   define('TEL_CHAT_ID', 'YOUR_CHAT_ID');
   define('TEL_PROXY_URL', 'https://www.httpdebugger.com/Tools/ViewHttpHeaders.aspx');
   define('TEL_LANGUAGE', 'fa'); // default is Persian
```
4. Visit your site, and the first notification will appear in your Telegram chat.

