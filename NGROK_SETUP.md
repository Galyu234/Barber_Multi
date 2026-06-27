# Panduan Setup Ngrok untuk BarberQ

BarberQ menggunakan pendekatan **client-server**, di mana satu komputer (misalnya laptop di meja kasir) bertindak sebagai server utama, dan perangkat lain (HP pelanggan, HP admin) bertindak sebagai client yang mengakses server tersebut melalui browser.

Agar perangkat HP dapat mengakses server lokal di laptop Anda tanpa harus berada di jaringan WiFi yang sama, kita menggunakan **Ngrok**.

---

## 1. Instalasi Ngrok

1. Daftar akun gratis di [ngrok.com](https://ngrok.com)
2. Download ngrok untuk Windows.
3. Ekstrak file zip, Anda akan mendapatkan file `ngrok.exe`.
4. Buka Terminal (Command Prompt / PowerShell) di folder tempat Anda mengekstrak `ngrok.exe`.
5. Hubungkan akun Anda dengan menjalankan perintah auth token (token bisa dilihat di dashboard ngrok Anda):
   ```bash
   ngrok config add-authtoken <TOKEN_ANDA>
   ```

---

## 2. Menjalankan Server (Laragon)

Pastikan web server Anda (Apache/Nginx di Laragon) atau PHP Artisan Serve sudah berjalan.

**Opsi A (Menggunakan Laragon - Disarankan):**
- Start All di Laragon.
- Pastikan aplikasi bisa diakses di browser lokal, misalnya `http://barber-multi.test` atau `http://localhost`.

**Opsi B (Menggunakan PHP Artisan Serve):**
- Buka terminal di folder project BarberQ.
- Jalankan perintah:
  ```bash
  php artisan serve --host=0.0.0.0 --port=8000
  ```

---

## 3. Menjalankan Ngrok

Buka terminal baru dan jalankan ngrok untuk mem-forward port server Anda.

**Jika menggunakan Laragon (Apache port 80):**
```bash
ngrok http 80
```

**Jika menggunakan PHP Artisan Serve (port 8000):**
```bash
ngrok http 8000
```

Setelah dijalankan, terminal ngrok akan menampilkan output seperti ini:
```text
Session Status                online
Account                       Nama Anda (Plan: Free)
Version                       3.x.x
Region                        Asia Pacific (ap)
Forwarding                    https://1234-abcd.ngrok-free.app -> http://localhost:80
```

Catat URL pada bagian **Forwarding** (contoh: `https://1234-abcd.ngrok-free.app`). Ini adalah URL Publik Anda.

---

## 4. Konfigurasi BarberQ

Agar QR code dan semua link di dalam BarberQ bekerja dengan benar saat diakses via ngrok, Anda **WAJIB** mengupdate file `.env`.

1. Buka file `.env` di folder project BarberQ.
2. Cari baris `APP_URL`.
3. Ubah nilainya menjadi URL ngrok Anda.
   ```env
   APP_URL=https://1234-abcd.ngrok-free.app
   ```
4. Simpan file `.env`.
5. Clear config cache (penting!):
   ```bash
   php artisan config:clear
   ```

---

## 5. Testing & Operasional

1. Buka URL ngrok di browser HP Anda.
2. Coba scan QR Code cabang.
3. Coba fitur Join Antrian (pastikan QR tiket muncul).
4. Login sebagai Admin di Laptop, buka menu **Scan QR Pelanggan**.
5. Izinkan akses kamera, lalu scan layar HP pelanggan yang menampilkan QR Tiket.
6. Antrian otomatis ditandai selesai!

---

> **⚠️ PERHATIAN PENTING!**
> 
> * **URL Ngrok Berubah**: Jika Anda menggunakan Ngrok versi gratis, **URL akan berubah setiap kali Anda me-restart ngrok**. 
> * Jika URL berubah, Anda harus:
>   1. Update `APP_URL` di `.env` dengan URL ngrok yang baru.
>   2. Jalankan `php artisan config:clear`.
>   3. Cetak ulang QR Code cabang (karena URL di dalam QR sudah berubah).
> * Untuk barbershop produksi, sangat disarankan menggunakan **Ngrok berbayar (Static Domain)** atau me-hosting aplikasi di VPS (DigitalOcean, Niagahoster, dll).
> * **Kamera Scanner**: Fitur Scan QR di panel Admin mewajibkan koneksi **HTTPS**. Pastikan Anda mengakses panel Admin menggunakan awalan `https://` dari ngrok, bukan `http://`.
