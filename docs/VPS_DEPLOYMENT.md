# Panduan Deploy ke VPS Ubuntu

Panduan lengkap untuk deploy aplikasi Apotek POS ke VPS dengan Ubuntu Server.

---

## Daftar Isi

1. [Persyaratan Server](#persyaratan-server)
2. [Persiapan VPS](#persiapan-vps)
3. [Instalasi Software](#instalasi-software)
4. [Setup Database](#setup-database)
5. [Deploy Aplikasi Laravel](#deploy-aplikasi-laravel)
6. [Konfigurasi Nginx](#konfigurasi-nginx)
7. [Setup SSL/HTTPS](#setup-sslhttps)
8. [Optimasi Production](#optimasi-production)
9. [Maintenance & Backup](#maintenance--backup)
10. [Troubleshooting](#troubleshooting)

---

## Persyaratan Server

### Minimum Specifications
- **OS**: Ubuntu 22.04 LTS atau 24.04 LTS
- **RAM**: 2 GB (4 GB recommended)
- **CPU**: 2 vCPU
- **Storage**: 20 GB SSD
- **Bandwidth**: Unlimited atau minimal 1 TB/bulan

### Provider VPS Recommended
- Biznet GioCloud
- DigitalOcean
- Vultr
- Linode
- AWS Lightsail
- Google Cloud Platform

---

## Persiapan VPS

### Langkah 1: Login ke VPS

```bash
ssh username@ip_public_vps
```

Contoh:
```bash
ssh root@103.123.456.789
```

### Langkah 2: Update System

```bash
sudo apt update && sudo apt upgrade -y
```

### Langkah 3: Setup Timezone

```bash
sudo timedatectl set-timezone Asia/Jakarta
```

Verifikasi:
```bash
date
```

### Langkah 4: Buat User Non-Root (Recommended)

```bash
# Buat user baru
sudo adduser deployer

# Tambahkan ke sudo group
sudo usermod -aG sudo deployer

# Switch ke user baru
su - deployer
```

---

## Instalasi Software

### 1. Install Nginx

```bash
sudo apt install nginx -y

# Start dan enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Cek status
sudo systemctl status nginx
```

Verifikasi: Buka `http://ip_vps_anda` di browser, akan muncul halaman default Nginx.

### 2. Install MariaDB

```bash
# Install MariaDB Server dan Client
sudo apt install mariadb-server mariadb-client -y

# Start dan enable MariaDB
sudo systemctl start mariadb
sudo systemctl enable mariadb

# Secure installation
sudo mysql_secure_installation
```

Saat menjalankan `mysql_secure_installation`:
1. Enter current password for root: (tekan Enter jika kosong)
2. Switch to unix_socket authentication: **N**
3. Change the root password: **Y** (masukkan password baru)
4. Remove anonymous users: **Y**
5. Disallow root login remotely: **Y**
6. Remove test database: **Y**
7. Reload privilege tables: **Y**

### 3. Install PHP 8.3

```bash
# Install dependencies
sudo apt install software-properties-common -y

# Tambah repository PHP
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.3 dengan extensions yang dibutuhkan
sudo apt install php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-zip php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml php8.3-bcmath php8.3-intl php8.3-readline php8.3-redis -y

# Verifikasi
php -v
```

### 4. Install Composer

```bash
# Download installer
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php

# Install secara global
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Hapus installer
rm composer-setup.php

# Verifikasi
composer -V
```

### 5. Install Node.js dan NPM

```bash
# Install Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y

# Verifikasi
node -v
npm -v
```

### 6. Install Git

```bash
sudo apt install git -y
git --version
```

---

## Setup Database

### Langkah 1: Buat Database dan User

```bash
# Login ke MariaDB
sudo mysql -u root -p
```

```sql
-- Buat database
CREATE DATABASE apotek_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Buat user database
CREATE USER 'apotek_user'@'localhost' IDENTIFIED BY 'password_kuat_anda';

-- Berikan privileges
GRANT ALL PRIVILEGES ON apotek_pos.* TO 'apotek_user'@'localhost';

-- Apply privileges
FLUSH PRIVILEGES;

-- Keluar
EXIT;
```

### Langkah 2: Test Koneksi

```bash
mysql -u apotek_user -p apotek_pos
```

---

## Deploy Aplikasi Laravel

### Langkah 1: Buat Direktori dan Clone Repository

```bash
# Pindah ke direktori web
cd /var/www

# Clone repository
sudo git clone https://github.com/[username]/laravel-apotik-be.git apotek

# Pindah ke direktori project
cd apotek

# Set ownership
sudo chown -R $USER:www-data .
```

### Langkah 2: Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install

# Build assets
npm run build
```

### Langkah 3: Setup Environment

```bash
# Copy file environment
cp .env.example .env

# Edit file .env
nano .env
```

Konfigurasi `.env` untuk production:

```env
APP_NAME="Apotek POS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://apotek.domain-anda.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apotek_pos
DB_USERNAME=apotek_user
DB_PASSWORD=password_kuat_anda

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

# Xendit (jika digunakan)
XENDIT_SECRET_KEY=xnd_development_xxx
XENDIT_PUBLIC_KEY=xnd_public_development_xxx
XENDIT_WEBHOOK_TOKEN=xxx
```

Simpan dengan `Ctrl + X`, lalu `Y`, lalu `Enter`.

### Langkah 4: Generate Key dan Migrasi

```bash
# Generate application key
php artisan key:generate

# Jalankan migrasi
php artisan migrate --force

# Jalankan seeder (opsional, untuk data awal)
php artisan db:seed --force

# Buat storage link
php artisan storage:link
```

### Langkah 5: Set Permissions

```bash
# Set permission direktori
sudo chmod -R 775 storage bootstrap/cache

# Set ownership
sudo chown -R $USER:www-data storage bootstrap/cache
```

### Langkah 6: Optimasi Laravel

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## Konfigurasi Nginx

### Langkah 1: Buat Konfigurasi Virtual Host

```bash
sudo nano /etc/nginx/sites-available/apotek
```

Isi dengan konfigurasi berikut:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name apotek.domain-anda.com;
    root /var/www/apotek/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Increase max upload size
    client_max_body_size 50M;
}
```

### Langkah 2: Enable Site

```bash
# Buat symbolic link
sudo ln -s /etc/nginx/sites-available/apotek /etc/nginx/sites-enabled/

# Hapus default site (opsional)
sudo rm /etc/nginx/sites-enabled/default

# Test konfigurasi
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

### Langkah 3: Konfigurasi PHP-FPM

Edit konfigurasi PHP-FPM:

```bash
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

Pastikan pengaturan berikut:

```ini
user = www-data
group = www-data
listen = /var/run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.3-fpm
```

---

## Setup SSL/HTTPS

### Menggunakan Certbot (Let's Encrypt)

### Langkah 1: Install Certbot

```bash
sudo apt install certbot python3-certbot-nginx -y
```

### Langkah 2: Dapatkan SSL Certificate

```bash
sudo certbot --nginx -d apotek.domain-anda.com
```

Ikuti instruksi:
1. Masukkan email address
2. Agree to terms of service: **Y**
3. Share email with EFF: **N** (opsional)
4. Redirect HTTP to HTTPS: **2** (recommended)

### Langkah 3: Verifikasi Auto-Renewal

```bash
# Test renewal
sudo certbot renew --dry-run

# Cek timer
sudo systemctl list-timers | grep certbot
```

### Langkah 4: Update .env

```bash
nano /var/www/apotek/.env
```

Ubah:
```env
APP_URL=https://apotek.domain-anda.com
```

Clear cache:
```bash
cd /var/www/apotek
php artisan config:cache
```

---

## Optimasi Production

### 1. Setup Queue Worker dengan Supervisor

```bash
# Install supervisor
sudo apt install supervisor -y

# Buat konfigurasi
sudo nano /etc/supervisor/conf.d/apotek-worker.conf
```

Isi:

```ini
[program:apotek-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/apotek/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/apotek/storage/logs/worker.log
stopwaitsecs=3600
```

Aktifkan:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start apotek-worker:*
```

### 2. Setup Cron untuk Scheduler

```bash
# Edit crontab
crontab -e
```

Tambahkan:

```
* * * * * cd /var/www/apotek && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Konfigurasi Firewall (UFW)

```bash
# Enable firewall
sudo ufw enable

# Allow SSH
sudo ufw allow OpenSSH

# Allow HTTP dan HTTPS
sudo ufw allow 'Nginx Full'

# Cek status
sudo ufw status
```

### 4. Optimasi PHP-FPM

```bash
sudo nano /etc/php/8.3/fpm/php.ini
```

Sesuaikan:

```ini
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 60
```

Restart:

```bash
sudo systemctl restart php8.3-fpm
```

---

## Maintenance & Backup

### Script Backup Otomatis

Buat script backup:

```bash
sudo nano /var/www/backup-apotek.sh
```

Isi:

```bash
#!/bin/bash

# Konfigurasi
BACKUP_DIR="/var/www/backups"
APP_DIR="/var/www/apotek"
DB_NAME="apotek_pos"
DB_USER="apotek_user"
DB_PASS="password_anda"
DATE=$(date +%Y%m%d_%H%M%S)

# Buat direktori backup jika belum ada
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_$DATE.sql
gzip $BACKUP_DIR/db_$DATE.sql

# Backup storage files
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz -C $APP_DIR storage

# Hapus backup lebih dari 7 hari
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
```

Set permissions dan jadwalkan:

```bash
sudo chmod +x /var/www/backup-apotek.sh

# Tambah ke crontab (backup setiap hari jam 2 pagi)
crontab -e
```

Tambahkan:

```
0 2 * * * /var/www/backup-apotek.sh >> /var/log/backup-apotek.log 2>&1
```

### Script Update Aplikasi

Buat script deploy:

```bash
sudo nano /var/www/deploy-apotek.sh
```

Isi:

```bash
#!/bin/bash

cd /var/www/apotek

# Maintenance mode
php artisan down

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev

# Build assets
npm install
npm run build

# Migrasi database
php artisan migrate --force

# Clear dan cache ulang
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue worker
sudo supervisorctl restart apotek-worker:*

# Keluar dari maintenance mode
php artisan up

echo "Deployment completed!"
```

Set permissions:

```bash
sudo chmod +x /var/www/deploy-apotek.sh
```

---

## Troubleshooting

### Error 502 Bad Gateway

**Penyebab**: PHP-FPM tidak berjalan

**Solusi**:
```bash
sudo systemctl status php8.3-fpm
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
```

### Error 500 Internal Server Error

**Penyebab**: Permission atau konfigurasi

**Solusi**:
```bash
# Cek log
tail -f /var/www/apotek/storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# Fix permissions
sudo chmod -R 775 /var/www/apotek/storage
sudo chown -R www-data:www-data /var/www/apotek/storage
```

### Error "SQLSTATE[HY000] [2002] Connection refused"

**Penyebab**: MariaDB tidak berjalan atau konfigurasi salah

**Solusi**:
```bash
# Cek status MariaDB
sudo systemctl status mariadb

# Restart MariaDB
sudo systemctl restart mariadb

# Verifikasi konfigurasi .env
nano /var/www/apotek/.env
```

### Error "Permission denied" pada Storage

**Solusi**:
```bash
sudo chown -R www-data:www-data /var/www/apotek/storage
sudo chmod -R 775 /var/www/apotek/storage
sudo chmod -R 775 /var/www/apotek/bootstrap/cache
```

### SSL Certificate Tidak Renew

**Solusi**:
```bash
# Test renewal manual
sudo certbot renew --dry-run

# Force renewal
sudo certbot renew --force-renewal

# Cek log
sudo cat /var/log/letsencrypt/letsencrypt.log
```

### Queue Worker Tidak Berjalan

**Solusi**:
```bash
# Cek status supervisor
sudo supervisorctl status

# Restart worker
sudo supervisorctl restart apotek-worker:*

# Cek log
tail -f /var/www/apotek/storage/logs/worker.log
```

---

## Monitoring

### Cek Resource Usage

```bash
# Memory usage
free -h

# Disk usage
df -h

# CPU usage
top
```

### Cek Log Files

```bash
# Laravel log
tail -f /var/www/apotek/storage/logs/laravel.log

# Nginx access log
tail -f /var/log/nginx/access.log

# Nginx error log
tail -f /var/log/nginx/error.log

# PHP-FPM log
tail -f /var/log/php8.3-fpm.log
```

---

## Checklist Deployment

- [ ] VPS sudah disiapkan dengan Ubuntu 22.04/24.04
- [ ] Nginx terinstall dan berjalan
- [ ] MariaDB terinstall dan dikonfigurasi
- [ ] PHP 8.3 dengan semua extensions terinstall
- [ ] Composer terinstall
- [ ] Node.js dan NPM terinstall
- [ ] Git terinstall
- [ ] Database sudah dibuat
- [ ] Repository sudah di-clone
- [ ] Dependencies terinstall (composer install, npm install)
- [ ] File .env sudah dikonfigurasi
- [ ] Application key sudah di-generate
- [ ] Migrasi database sudah dijalankan
- [ ] Assets sudah di-build
- [ ] Storage link sudah dibuat
- [ ] Permissions sudah diset dengan benar
- [ ] Virtual host Nginx sudah dikonfigurasi
- [ ] SSL certificate sudah terpasang
- [ ] Firewall sudah dikonfigurasi
- [ ] Queue worker sudah disetup dengan Supervisor
- [ ] Cron job untuk scheduler sudah disetup
- [ ] Script backup sudah disiapkan

---

## Quick Commands Reference

```bash
# Start/Stop/Restart Services
sudo systemctl start nginx
sudo systemctl restart php8.3-fpm
sudo systemctl restart mariadb

# Laravel Artisan
php artisan down                  # Maintenance mode ON
php artisan up                    # Maintenance mode OFF
php artisan migrate --force       # Run migrations
php artisan config:cache          # Cache config
php artisan route:cache           # Cache routes
php artisan view:cache            # Cache views
php artisan optimize:clear        # Clear all cache

# Supervisor
sudo supervisorctl status
sudo supervisorctl restart all

# Logs
tail -f /var/www/apotek/storage/logs/laravel.log
```
