# Plugin Jadwal Sholat

Plugin Jadwal Sholat adalah solusi praktis dan personal untuk menampilkan jadwal sholat di situs WordPress Anda. Plugin ini dirancang untuk memberikan kemudahan akses informasi waktu ibadah dengan fitur kustomisasi yang luas agar dapat disesuaikan dengan estetika situs Anda.

## Fitur Utama

### 1. Kustomisasi Tampilan Penuh
Pengguna memiliki kontrol penuh terhadap estetika tampilan, termasuk:
- Penyesuaian warna latar belakang (form, input, dan hasil jadwal).
- Pengaturan warna teks dan ukuran font secara spesifik untuk nama jadwal dan waktu.
- Integrasi gambar kota yang dapat diunggah langsung melalui media uploader WordPress.

### 2. Penentuan Lokasi Fleksibel
- Pengaturan kota default melalui panel administrasi.
- Dropdown dinamis pada halaman publik yang memungkinkan pengunjung mencari dan memilih lokasi lain secara real-time.

### 3. Informasi Real-Time
- Jam Digital: Menampilkan waktu saat ini yang diperbarui setiap detik.
- Hitung Mundur (Countdown): Memberikan estimasi sisa waktu menuju waktu sholat berikutnya.

### 4. Doa Harian Acak
Menampilkan doa harian yang diambil secara acak, lengkap dengan teks Arab, transliterasi (opsional sesuai API), dan terjemahan Bahasa Indonesia.

### 5. Desain Responsif
Dioptimalkan untuk tampil sempurna di berbagai perangkat, mulai dari desktop hingga telepon seluler.

## Panduan Penggunaan

### Instalasi
1. Unggah folder plugin ke direktori `/wp-content/plugins/`.
2. Aktifkan plugin melalui menu 'Plugins' di WordPress.

### Konfigurasi Admin
Buka menu **Jadwal Sholat** di dashboard WordPress Anda untuk mengatur:
- Kota Default.
- Gambar Header.
- Tema warna dan ukuran tipografi.
- Pratinjau langsung akan tersedia di halaman pengaturan tersebut.

### Penggunaan Shortcode
Anda dapat menyisipkan fitur ini ke dalam halaman, postingan, atau widget menggunakan shortcode berikut:

- **[jadwal_sholat]**: Menampilkan paket lengkap (Pencarian, Gambar, Jam, Jadwal, dan Doa).
- **[jadwal_sholat_only]**: Hanya menampilkan tabel jadwal sholat untuk kota yang dipilih.
- **[doa_harian]**: Hanya menampilkan bagian doa harian acak.

## Integrasi API
Plugin ini ditenagai oleh **API Muslim v2 - MyQuran**. API ini menyediakan data waktu sholat yang akurat dan terpercaya untuk berbagai wilayah di Indonesia.

### Lisensi dan Kredit
- Data Source: [MyQuran API](https://api.myquran.com/)
- Developer: bungrahman

---
Dapatkan pembaruan dan informasi lebih lanjut melalui repositori resmi.