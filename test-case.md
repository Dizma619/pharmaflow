# TEST CASE POSITIVE

## 10 Test Case Positive

| ID Test Case | Kategori | Skenario Pengujian | Test Data | Expected Result | Actual Result | Status | Bukti (Screenshot) |
|-------------|----------|-------------------|------------|----------------|--------------|--------|-------------------|
| TC-P-01 | Positive | Login dengan data valid | Username: staff1@pharmaflow.local <br> Password: password123 | Staff berhasil masuk ke dashboard | Masuk ke halaman dashboard | PASS | ![Login Valid](docs/screenshoots/login_Staff1_valid.png) |
| TC-P-02 | Positive | Daftar akun baru | Username: asepganteng123@gmail.com <br> Password: password123 | User berhasil daftar dan diarahkan ke halaman login | Registrasi berhasil dan masuk ke halaman login | PASS | ![Register Valid](docs/screenshoots/register_vlid.png) |
| TC-P-03 | Positive | Menambahkan obat | Nama: XimogXilin <br> Harga: Rp10.000 | Data berhasil tersimpan dan muncul di daftar menu | Data muncul pada menu input pesanan | PASS | ![Tambah Obat Valid](docs/screenshoots/tambahobat_valid.png)|
| TC-P-04 | Positive | Menambahkan kategori obat | Nama Kategori: Obat Ganteng | Kategori berhasil disimpan | Kategori berhasil tampil | PASS | ![Kategori Valid](docs/screenshoots/kategori_obat_valid.png) |
| TC-P-05 | Positive | Menambahkan Stock Obat | M: Stock Paracetamol | Stock Obat berhasil ditambahkan | Stock Obat tampil di daftar stock | PASS | ![Stock Valid](docs/screenshoots/Stock_Valid.png) |


---

# TEST CASE NEGATIVE

## 10 Test Case Negative

| ID Test Case | Kategori | Skenario Pengujian | Test Data | Expected Result | Actual Result | Status | Bukti (Screenshot) |
|-------------|----------|-------------------|------------|----------------|--------------|--------|-------------------|
| TC-N-01 | Negative | Harga bernilai negatif | Harga: -10000 | Sistem menolak input | Pesan validasi muncul | PASS | ![Minimal 0](docs/screenshoots/harga_negatif.png) |
| TC-N-02 | Negative | Login dengan field kosong | Username: - <br> Password: - | Validasi gagal dan pesan error muncul | Sistem meminta mengisi semua field | PASS |![Isi Field](docs/screenshoots/login_kosong.png) |
| TC-N-03 | Negative | Input harga menggunakan huruf | Harga: "rafli mahal" | Sistem menolak input | Field harga tidak ada | PASS | ![Masukkan Field](docs/screenshoots/harga_huruf.png).png |
| TC-N-04 | Negative | Username sudah digunakan | Username: owner | Registrasi ditolak | Pesan username sudah digunakan muncul | PASS | ! [Email has ready taken](docs/screenshoots/duplikat.png) |
| TC-N-05 | Negative | Password kurang dari 6 karakter | Password: 1 | Sistem menolak input | Validasi password gagal | PASS | ![Password Field Must be 6 line](docs/screenshoots/password_pendek.png) |


---

# TEST CASE EDGE

## 10 Test Case Edge

| ID Test Case | Kategori | Skenario Pengujian | Test Data | Expected Result | Actual Result | Status | Bukti (Screenshot) |
|-------------|----------|-------------------|------------|----------------|--------------|--------|-------------------|
| TC-E-01 | Edge | Input nama dengan spasi berlebih | Nama: "Owner        @pharmaflow.local" | Sistem menormalkan spasi atau menolak input | Sistem menerima tetapi tidak menormalkan spasi | FAIL | ![](docs/screenshoots/spasi.png) |
| TC-E-02 | Edge | Input karakter Unicode | Nama: Dmog😎 | Sistem menangani karakter khusus | Data tersimpan | PASS | ![](docs/screenshoots/kode_unik.png) |
| TC-E-03 | Edge | Password tepat batas minimum | 123456 | Sistem menerima input | Registrasi berhasil | PASS | ![Berhasil](docs/screenshoots/password_minimal.png) |
| TC-E-04 | Edge | Harga = 0 | Harga: 0 | Sistem tidak menolak atau memberi peringatan | Validasi berjalan | PASS | ![Berhasil Input Harga](docs/screenshoots/harga_nol.png) |
| TC-E-05 | Edge | Input harga maksimum integer | Harga: 214748364787381283678624 | Sistem menerima jika masih dalam batas | Data Tidak tersimpan | PASS |![Eror](docs/screenshoots/max_harga.png) |
