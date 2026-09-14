# Task Manager

Web untuk membantu pengguna mengelola tugas, deadline, prioritas, dan kategori secara terstruktur.

## ✨ Fitur

- 🔐 Autentikasi dan pengelolaan profil
- 📝 CRUD tugas
- 🎯 Prioritas Low, Medium, dan High
- 📅 Deadline dan pemantauan tugas
- 🗂️ Kategori tugas
- 🔎 Pencarian, filter, dan sorting
- 📊 Dashboard produktivitas
- 🔔 Sistem notifikasi deadline
- 👤 Isolasi data berdasarkan pengguna
- 📱 Responsive untuk desktop dan mobile

## 🛠️ Teknologi

- Laravel 13
- PHP 8.5+
- PostgreSQL
- Blade
- Tailwind CSS
- Vite
- Laravel Breeze
- PHPUnit
- Laravel Pint

## 🚀 Instalasi

Clone repository:

```bash
git clone https://github.com/RezaFahresi/task-manager.git
cd task-manager

#install dependency
composer install
npm install

#buat file environtment
cp .env.example .env
php artisan key:generate

#Sesuaikan konfigurasi PostgreSQL pada .env, kemudian jalankan:
php artisan migrate
npm run build
php artisan serve

#Task Manager memiliki sistem notifikasi untuk Deadline hari ini, Task yang terlambat, Deadline yang akan datang Pengecekan deadline dapat dijalankan dengan:
php artisan tasks:check-deadlines

#Laravel Scheduler:
php artisan schedule:list
php artisan schedule:work

