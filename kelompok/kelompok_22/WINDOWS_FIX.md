# Fix untuk Error di Windows

## Masalah
Aplikasi berjalan normal di Linux tapi error di Windows dengan pesan seperti:
1. `SyntaxError: Unexpected token '<', "<br /><b>"... is not valid JSON`
2. `Deprecated: htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated`

## Penyebab
Di Windows, PHP secara default menampilkan error/warning sebagai HTML output, yang merusak JSON response. Di Linux, konfigurasi PHP biasanya sudah menyembunyikan error atau mengarahkannya ke log file.

## Solusi yang Diterapkan

### 1. ✅ Disable Display Errors di API Endpoint
File: `src/backend/controllers/api.php`

Ditambahkan di awal file:
```php
// CRITICAL: Disable HTML error output for API to prevent JSON corruption on Windows
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Clear any output buffer that might contain errors
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();
```

### 2. ✅ Perbaiki JSON Response Function
File: `src/backend/utils/config.php`

Fungsi `jsonResponse()` sekarang membersihkan output buffer sebelum mengirim JSON:
```php
function jsonResponse($success, $data = null, $message = '', $code = 200) {
    // Clear any accidental output (errors, warnings, whitespace)
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code($code);
    header('Content-Type: application/json');
    // ... rest of code
}
```

### 3. ✅ Helper Function untuk htmlspecialchars
File: `src/backend/utils/config.php`

Ditambahkan helper function `h()` yang aman dari nilai `null`:
```php
/**
 * Helper function untuk htmlspecialchars yang aman dari null (PHP 8.1+)
 * @param mixed $string - String yang akan di-escape, bisa null
 * @param string $default - Default value jika $string null
 * @return string
 */
function h($string, $default = '') {
    return htmlspecialchars($string ?? $default, ENT_QUOTES, 'UTF-8');
}
```

**Penggunaan:**
```php
// Sebelum (bisa error di Windows jika null)
<?= htmlspecialchars($user['phone']) ?>

// Sesudah (aman dari null)
<?= h($user['phone'], '-') ?>
```

### 4. ✅ .htaccess untuk Backend
File: `src/backend/.htaccess`

Ditambahkan konfigurasi Apache:
```apache
# Disable display_errors untuk semua file backend PHP
php_flag display_errors Off
php_flag display_startup_errors Off
php_flag log_errors On

# Prevent directory listing
Options -Indexes
```

### 5. ✅ Improved Error Handling di JavaScript
File: `src/backend/controllers/admin.php`

Fungsi `fetchPetugas()` sekarang menangani response yang corrupt:
```javascript
async function fetchPetugas() {
    try {
        const response = await fetch(`${API_URL}?action=getPetugas`);
        if (!response.ok) {
             throw new Error(`Gagal mengambil data petugas. Status: ${response.status}`);
        }
        
        // Get response text first to check if it's valid JSON
        const responseText = await response.text();
        
        // Try to parse JSON, with error handling for corrupted responses
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Invalid JSON response:', responseText.substring(0, 200));
            throw new Error('Server returned invalid JSON. Check PHP error logs.');
        }
        
        if (result.success) {
            petugasList = result.data;
            populatePetugasDropdown();
        }
    } catch (error) {
        console.error('Error fetching petugas:', error);
    }
}
```

## Testing di Windows

### Checklist Testing:
- [ ] Dashboard Admin → Data Laporan Warga → Buka browser console, tidak ada error JSON
- [ ] Dashboard Admin → Assign petugas ke laporan → Berhasil tanpa error
- [ ] Super Admin → Activity Logs → Tidak ada deprecation warning
- [ ] Semua halaman → Tidak ada `<br />` atau `<b>` di console errors

### Jika Masih Error:

1. **Check php.ini di Windows:**
   ```ini
   display_errors = Off
   display_startup_errors = Off
   log_errors = On
   error_log = "C:/xampp/logs/php_error.log"  ; Sesuaikan path
   ```

2. **Restart Apache/Web Server** setelah mengubah konfigurasi

3. **Check Error Log** bukan output HTML:
   - XAMPP: `C:/xampp/apache/logs/error.log`
   - WAMP: `C:/wamp64/logs/php_error.log`

4. **Pastikan .htaccess aktif:**
   - Apache harus enable mod_rewrite
   - AllowOverride All di httpd.conf

## Catatan untuk Developer

- **Gunakan fungsi `h()` untuk semua output HTML** yang bisa null
- **Jangan gunakan `htmlspecialchars()` langsung** pada data dari database
- **Untuk API endpoint**, pastikan tidak ada output apapun sebelum `jsonResponse()`
- **Test di Windows** sebelum commit jika memungkinkan

## Status File yang Sudah Diperbaiki

✅ `src/backend/controllers/api.php` - Added error suppression
✅ `src/backend/utils/config.php` - Added h() helper, improved jsonResponse()
✅ `src/backend/controllers/super_admin.php` - Using h() for null-safe output
✅ `src/backend/controllers/admin.php` - Improved fetchPetugas error handling
✅ `src/backend/.htaccess` - Apache configuration

## Referensi
- [PHP htmlspecialchars null deprecation](https://www.php.net/manual/en/function.htmlspecialchars.php)
- [JSON parse errors](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Errors/JSON_bad_parse)
