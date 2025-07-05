-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 04 Jul 2025 pada 20.50
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webdena_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int(50) NOT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `kelas` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `password_hint` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `nama_siswa`, `kelas`, `username`, `password`, `status`, `created_at`, `updated_at`, `password_hint`) VALUES
(2018081002, 'Dena Ardiena', '12A', 'dena26', '33a50291d1e33ff934c67cf390e344e2', 'aktif', '2025-07-03 14:42:46', '2025-07-03 15:02:53', 'dena12345');

-- --------------------------------------------------------

--
-- Struktur dari tabel `skor`
--

CREATE TABLE `skor` (
  `id` int(11) NOT NULL,
  `id_siswa` int(11) DEFAULT NULL,
  `nama_siswa` varchar(255) NOT NULL,
  `nilai` decimal(5,2) NOT NULL,
  `tipe_soal` enum('listening','reading','writting') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `skor`
--

INSERT INTO `skor` (`id`, `id_siswa`, `nama_siswa`, `nilai`, `tipe_soal`, `created_at`) VALUES
(11, NULL, 'dena', 60.00, 'reading', '2025-06-30 15:31:52'),
(33, NULL, 'Arif Kurniawan', 90.00, 'reading', '2025-07-04 17:44:08');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `skor`
--
ALTER TABLE `skor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_siswa` (`id_siswa`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2147483648;

--
-- AUTO_INCREMENT untuk tabel `skor`
--
ALTER TABLE `skor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `skor`
--
ALTER TABLE `skor`
  ADD CONSTRAINT `skor_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
