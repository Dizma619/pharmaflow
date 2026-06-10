# TEST CASE POSITIVE

## 10 Test Case Positive

| ID Test Case | Kategori | Skenario Pengujian | Test Data | Expected Result | Actual Result | Status | Bukti (Screenshot) |
|-------------|----------|-------------------|------------|----------------|--------------|--------|-------------------|
| TC-P-01 | Positive | Login dengan data valid | Username: staff1@pharmaflow.local <br> Password: password123 | Staff berhasil masuk ke dashboard | Masuk ke halaman dashboard | PASS | ![Login Valid](docs/screenshoots/login_Staff1_valid.png) |
| TC-P-02 | Positive | Daftar akun baru | Username: asepganteng123@gmail.com <br> Password: password123 | User berhasil daftar dan diarahkan ke halaman login | Registrasi berhasil dan masuk ke halaman login | PASS | ![Register Valid] (docs/screenshoots/register_vlid.png) |
| TC-P-03 | Positive | Menambahkan obat | Nama: XimogXilin <br> Harga: Rp10.000 | Data berhasil tersimpan dan muncul di daftar menu | Data muncul pada menu input pesanan | PASS |docs/screenshoots/tambahobat_valid.png|
| TC-P-04 | Positive | Menambahkan kategori obat | Nama Kategori: Obat Ganteng | Kategori berhasil disimpan | Kategori berhasil tampil | PASS | (docs/screenshoots/kategori_obat_valid.png) |
| TC-P-05 | Positive | Menambahkan Stock | M: Ayam Sambal Bakar | Pesanan berhasil ditambahkan | Pesanan tampil di keranjang | PASS | pesanan.png |


---

# TEST CASE NEGATIVE

## 10 Test Case Negative

| ID Test Case | Kategori | Skenario Pengujian | Test Data | Expected Result | Actual Result | Status | Bukti (Screenshot) |
|-------------|----------|-------------------|------------|----------------|--------------|--------|-------------------|
| TC-N-01 | Negative | Login dengan username dan password salah | Username: rafi <br> Password: rafi1234 | Login gagal dan pesan error muncul | Login gagal dan pesan "Username tidak ditemukan" muncul | PASS | login-salah.png |
| TC-N-02 | Negative | Login dengan field kosong | Username: - <br> Password: - | Validasi gagal dan pesan error muncul | Sistem meminta mengisi semua field | PASS | login-kosong.png |
| TC-N-03 | Negative | Input harga menggunakan huruf | Harga: "rafli mahal" | Sistem menolak input | Field harga tidak dapat diisi huruf | PASS | harga-huruf.png |
| TC-N-04 | Negative | Username sudah digunakan | Username: admin | Registrasi ditolak | Pesan username sudah digunakan muncul | PASS | duplicate-user.png |
| TC-N-05 | Negative | Password kurang dari 6 karakter | Password: 123 | Sistem menolak input | Validasi password gagal | PASS | password-short.png |


---

# TEST CASE EDGE

## 10 Test Case Edge

| ID Test Case | Kategori | Skenario Pengujian | Test Data | Expected Result | Actual Result | Status | Bukti (Screenshot) |
|-------------|----------|-------------------|------------|----------------|--------------|--------|-------------------|
| TC-E-01 | Edge | Input nama dengan spasi berlebih | Nama: "Herlina     Putri" | Sistem menormalkan spasi atau menolak input | Sistem menerima tetapi tidak menormalkan spasi | FAIL | edge-spasi.png |
| TC-E-02 | Edge | Username maksimum karakter | Username 50 karakter | Sistem menerima jika sesuai batas | Data berhasil disimpan | PASS | username-max.png |
| TC-E-03 | Edge | Password tepat batas minimum | Password 6 karakter | Sistem menerima input | Registrasi berhasil | PASS | password-min.png |
| TC-E-04 | Edge | Harga = 0 | Harga: 0 | Sistem menolak atau memberi peringatan | Validasi berjalan | PASS | harga-zero.png |
| TC-E-05 | Edge | Nama menu 100 karakter | Nama sangat panjang | Sistem tetap menyimpan data | Data tersimpan | PASS | nama-panjang.png |
