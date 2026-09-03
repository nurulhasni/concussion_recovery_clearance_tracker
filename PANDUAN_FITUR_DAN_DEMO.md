# 🧠 PANDUAN LENGKAP FITUR & SKRIP DEMO APLIKASI
## Concussion Recovery & Clearance Tracker
> **Standar Klinis:** CDC HEADS UP Protocol & PedsConcussion Living Guidelines  
> **Dokumen ini dibuat khusus untuk anggota tim dan persiapan demo/pitching.**

---

## 📌 1. Elevator Pitch (Masalah & Solusi)
* **Masalah di Dunia Nyata:**
  Gegar otak (*concussion*) pada atlet muda/siswa sekolah sering kali dianggap remeh. Jika seorang siswa kembali berolahraga atau beraktivitas berat terlalu cepat sebelum otaknya pulih, ada risiko fatal bernama **Second Impact Syndrome** (kerusakan otak permanen). Masalah terbesarnya adalah **komunikasi yang terputus (*siloed*)** antara Orang Tua, Dokter, dan Pihak Sekolah.
* **Solusi Aplikasi Ini:**
  Platform terpusat berbasis web yang memadukan **AI klinis pendeteksi tanda bahaya**, **protokol pemulihan bertahap**, dan **sistem persetujuan multi-pihak (*multi-party consensus*)** yang memastikan seorang anak baru boleh berolahraga jika **Dokter, Sekolah, dan Orang Tua** sudah memberi izin resmi secara bersamaan.

---

## 🗺️ 2. Perjalanan Pemulihan (4 Milestone Recovery)
Aplikasi membagi pemulihan menjadi 4 tonggak pencapaian resmi:

| Milestone | Nama Tahap | Deskripsi & Batasan Aktivitas | Siapa yang Harus Menyetujui? |
|---|---|---|---|
| **Milestone 1** | **Rest & Cognitive Pause** | Istirahat total. Batas layar maksimal 20-30 menit, tidak ada PR/tugas sekolah berat, tidak ada olahraga. | Dokter |
| **Milestone 2** | **Return to Learn** | Boleh kembali masuk kelas dengan akomodasi belajar (waktu istirahat tambahan, bebas ujian berat), tapi belum boleh olahraga. | Dokter + Pihak Sekolah |
| **Milestone 3** | **Active Rehab & Return to Play** | Mengikuti protokol latihan fisik 6 langkah (*CDC 6-Step Return to Play*), mulai dari jalan santai hingga latihan spesifik tanpa kontak. | Dokter + Pihak Sekolah + Orang Tua |
| **Milestone 4** | **Full Sport & Life Clearance** | Pemulihan tuntas 100%. Diizinkan kembali bertanding dalam olahraga kontak penuh (*full contact practice*). | **Selesai (Status: Cleared)** |

---

## 🛠️ 3. Rincian Fitur Utama & Fungsinya

### Fitur A: Paspor Pemulihan Digital (*Recovery Passport*)
* **Tampilan:** Dashboard utama dengan UI bertema *dark health-tech* yang tenang (mengurangi ketegangan mata pasien).
* **Fungsi:**
  * Menampilkan identitas pasien, jenis cedera, dan durasi hari pemulihan.
  * Menampilkan grafik tren gejala 7 hari terakhir (*7-Day Symptom Trajectory*) dengan pill gauge warna hijau (*Mild*), kuning (*Moderate*), dan merah (*Severe*).
  * Menampilkan **Batasan Aktivitas Aktif** (*Active Restrictions*) yang sedang berlaku.

### Fitur B: Pencatatan Gejala Harian & Analisis AI (*Symptom Logger + AI Engine*)
* **Tampilan:** Modal *"Catat Gejala Harian"* dengan kotak teks bebas dan tombol saran cepat (*Quick Suggestions*).
* **Cara Kerja:**
  * Pengguna tidak dipaksa mengisi kuisioner rumit. Cukup ketik bahasa sehari-hari: *"Hari ini pusing ringan setelah membaca 20 menit, tapi hilang setelah istirahat."*
  * AI mengekstrak keparahan gejala (*mild, moderate, severe*) dan menyusun **Ringkasan Bahasa Awam** (*Layperson Summary*) agar orang tua paham tanpa membaca istilah medis rumit.
  * **Negation-Aware**: AI cukup pintar mengenali negasi (misal: *"zero headache"* tetap diklasifikasikan sebagai *mild*, bukan salah deteksi karena ada kata "headache").

### Fitur C: Guardrail Keamanan & Auto-Downgrade (*Clinical Safety Net*)
* **Fungsi Krusial:** Jika pasien memasukkan gejala bahaya medis (*Red Flag Danger Signs* seperti *muntah berulang, pingsan, kejang, bicara pelo*):
  1. Sistem otomatis mendeteksi bahaya klinis (baik melalui AI maupun *keyword safety guardrail* bawaan CDC).
  2. **Auto-Downgrade Otomatis**: Jika pasien sudah di Tahap 2 atau 3, sistem akan **otomatis menurunkan tahapannya kembali ke Tahap 1** demi keselamatan nyawa pasien!
  3. Sistem membatalkan izin lama dan membuat tautan peninjauan baru bagi dokter.

### Fitur D: Portal Persetujuan Tanpa Login (*Frictionless Approver Portal*)
* **Masalah Umum:** Dokter dan staf sekolah sangat sibuk dan malas mendaftar akun/login password baru.
* **Solusi Kami:**
  * Dokter/Sekolah menerima **tautan persetujuan unik 64-karakter sekali pakai (*single-use token*)** dengan masa berlaku 7 hari.
  * Mereka cukup membuka tautan tersebut &rarr; melihat riwayat ringkas pasien &rarr; memilih *Approve* atau *Reject* &rarr; memasukkan catatan klinis &rarr; selesai.

### Fitur E: Konsensus Multi-Pihak (*Consensus Engine*)
* Tahapan pemulihan **tidak akan pernah maju** hanya dengan izin sepihak.
* Menuju Milestone 3 butuh izin **Dokter + Sekolah**. Menuju Milestone 4 butuh izin **Dokter + Sekolah + Orang Tua**.
* Jika salah satu pihak menolak (*Reject*), tahapan tertahan dan tim medis harus meninjau ulang.

### Fitur F: Dukungan Bilingual Penuh (ID & EN)
* Terdapat *Language Switcher* instan di pojok kanan atas navbar untuk berganti antara Bahasa Indonesia dan Bahasa Inggris secara penuh.

---

## ⏱️ 4. Skrip Demo 3 Menit (Cara Presentasi Cepat Tanpa Capek Klik)

> **Tips Presenter:** Anda tidak perlu membuat data dari nol atau mengklik puluhan form. Buka URL `http://localhost:8000` dan gunakan **3 Akun Demo Bawaan** berikut secara berurutan:

---

### Menit 1: Alex Rivera (Mendemonstrasikan Cedera Baru & Kecerdasan AI)
1. Buka akun **Alex Rivera** (Milestone 1 - *Rest & Cognitive Pause*).
2. **Ucapkan:**  
   > *"Pertama, ini adalah Alex Rivera. Dia baru saja cedera gegar otak 3 hari lalu dan berada di Milestone 1. Mari kita coba fitur AI-nya."*
3. Klik tombol **Log Daily Symptoms** (Catat Gejala).
4. Klik chip template **"Mild / Improving"** &rarr; klik **Analyze & Submit Report**.
5. **Ucapkan:**  
   > *"Perhatikan tombol loading spinner yang mencegah klik ganda. AI mengekstrak gejala bahasa bebas ini dan langsung merangkumnya ke dalam kartu AI Assistant di dashboard."*

---

### Menit 2: Jordan Taylor (Mendemonstrasikan Akomodasi Sekolah & Konsensus Multi-Pihak)
1. Buka akun **Jordan Taylor** (Milestone 2 - *Return to Learn*).
2. **Ucapkan:**  
   > *"Ketika kondisi pasien membaik, pasien masuk ke Milestone 2 seperti Jordan Taylor. Di sini pasien sudah boleh masuk kelas dengan akomodasi belajar (waktu istirahat tambahan)."*
3. Tunjukkan bagian **Clearance Approvals** di bawah:
   > *"Untuk maju ke Milestone 3 (Latihan Fisik), Jordan butuh persetujuan dari Dokter dan Sekolah. Kita bisa melihat status masing-masing pihak secara transparan."*
4. Di panel demo bagian bawah, klik tombol **Open Review Portal**:
   > *"Ini adalah tampilan yang dilihat oleh Dokter atau Koordinator Sekolah melalui tautan aman tanpa perlu login. Mereka cukup memilih Approve/Reject dan memberikan catatan klinis."*

---

### Menit 3: Maya Chen (Mendemonstrasikan Latihan Fisik CDC & Fitur Pengaman 403)
1. Buka akun **Maya Chen** (Milestone 3 - *Active Rehab*, Step 3/6).
2. **Ucapkan:**  
   > *"Berikutnya adalah Maya Chen yang sedang menjalani Milestone 3 (Latihan Fisik Bertahap). Perhatikan bagian CDC 6-Step Return to Play Progression."*
3. Tunjukkan sub-langkah fisik:
   > *"Maya saat ini berada di Step 3. Sekarang mari kita tunjukkan fitur kepatuhan medis kami: jika dokter atau sekolah mencoba membuka link persetujuan Final Clearance (Milestone 4) sekarang, apa yang terjadi?"*
4. Klik tombol **Test Safety Gate (403)** di panel demo bawah:
   > *"Sistem secara ketat memblokir dengan kode 403 Access Restricted! CDC melarang keras pemberian izin bertanding penuh sebelum atlet menyelesaikan seluruh 6 langkah latihan fisik."*
5. Kembali ke paspor Maya Chen & klik **Log Daily Symptoms** (masukkan gejala ringan):
   > *"Begitu Maya menyelesaikan langkah-langkah latihannya hingga mencapai Step 6, gerbang persetujuan Final Clearance otomatis terbuka!"*

---

## ❓ 5. Tanya Jawab Cepat (FAQ untuk Tim / Antisipasi Juri)

| Pertanyaan | Jawaban Singkat & Tepat |
|---|---|
| **Kenapa saat klik approval Maya Chen muncul error 403?** | **Ini adalah fitur pengaman medis resmi CDC.** Maya Chen berada di Milestone 3 Step 3. Protokol CDC HEADS UP melarang pemberian izin bertanding (*Final Clearance*) jika belum menyelesaikan Step 6. Sistem dengan sengaja memblokir akses tersebut hingga Step 6 tercapai. |
| **Apakah AI di sini menggantikan dokter?** | **Sama sekali tidak.** AI hanya bersifat asisten administratif (*assistive tool*) untuk mengekstrak gejala dari bahasa bebas dan merangkum tren. Keputusan izin medis tetap 100% berada di tangan Dokter berlisensi. |
| **Bagaimana jika pasien memasukkan gejala bahaya?** | Sistem memiliki *safety guardrail* lapis ganda. Jika ada kata kunci bahaya (*seizure, vomiting, blackout*), sistem otomatis memicu status bahaya klinis dan menurunkan tahapan pasien (*auto-downgrade*) ke Tahap 1. |
| **Kenapa dokter dan pihak sekolah tidak perlu registrasi akun?** | Untuk menghilangkan friksi. Tenaga medis dan guru sangat sibuk. Kami menggunakan *secure 64-character token* sekali pakai dengan masa kedaluwarsa 7 hari yang dikirim langsung ke mereka. |
| **Kenapa UI-nya bertema gelap (Dark Mode)?** | Ini adalah keputusan desain berbasis klinis. Pasien gegar otak sangat rentan terhadap *photophobia* (sensitivitas cahaya dan kelelahan mata). Desain dark slate ini meminimalkan ketegangan mata pasien. |

---

> *File ini tersimpan di root project sebagai `PANDUAN_FITUR_DAN_DEMO.md` dan dapat langsung Anda salin ke Notion, Google Docs, atau dibagikan ke tim.*
