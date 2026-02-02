# Aplikasi CRUD PHP-MySQL yang menyimpan file di **AWS S3** 
---

## menggunakan **EC2 Ubuntu 24.04** di lingkungan **AWS Academy**.
---

### Project ini mendokumentasikan langkah-langkah mendeploy aplikasi PHP yang menyimpan file (asset) di **AWS S3** menggunakan **EC2 Ubuntu 24.04** di lingkungan **AWS Academy**.
---

## I. Persiapan Infrastruktur AWS

Buat dulu SG yang sesuai, ijinkan inbound rule port 22 dan 80 dari anywhere-IPv4 (0.0.0.0/0)

### A. Buat RDS

### A. Membuat Instance EC2

1. Login ke AWS Academy Learner Lab.
2. Launch Instance dengan spesifikasi:
   - **Nama:** `PHP-S3-Server`
   - **AMI:** Ubuntu 24.04 LTS.
   - **Instance Type:** t2.micro (Free Tier).
   - **Key Pair:** Pilih atau buat baru.
   - **Security Group:** Izinkan **HTTP (80)** dan **SSH (22)**.
   - Pastikan ubah IAM Role menjadi LabInstanceProfile dari menu Action > Security > Modify IAM Role
3. Hubungkan ke instance via SSH.

### B. Membuat dan Konfigurasi S3 Bucket
S3 Bucket dapat dibuat dengan Web GUO Management Console seperti biasa, atau jalankan perintah ini melalui terminal (setelah konfigurasi AWS CLI), atau AWS Clodshell untuk membuat bucket bernama `nugwebphps3`:


#### Membuka Public Access Block
```bash
aws s3api put-public-access-block --bucket nugwebphps3 --public-access-block-configuration "BlockPublicAcls=false,IgnorePublicAcls=false,BlockPublicPolicy=false,RestrictPublicBuckets=false"
```

#### Mengatur Policy agar file bisa diakses publik (Read Only)
```bash
aws s3api put-bucket-policy --bucket nugwebphps3 --policy '{
    "Version":"2012-10-17",
    "Statement":[{"Sid":"PublicReadGetObject","Effect":"Allow","Principal":"*","Action":"s3:GetObject","Resource":"arn:aws:s3:::nugwebphps3/*"}]
}'
```


## II. Deploy App ke EC2

## Langkah 1: Persiapan dan Instalasi Server
Jalankan perintah berikut pada terminal EC2 Ubuntu 24.04 untuk menginstal Apache, PHP, dan dependensi lainnya:

```bash
# Update sistem
sudo apt update

# Install Apache, PHP, dan ekstensi yang diperlukan
sudo apt install -y apache2 php php-cli php-curl php-xml php-mbstring libapache2-mod-php unzip


# Install Composer secara global
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install AWS SDK for PHP di direktori project
cd /var/www/html
sudo composer require aws/aws-sdk-php

# Atur izin folder agar web server bisa menulis file
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 777 /var/www/html

# Hapus index.html
sudo rm /var/www/html/index.html
```


## Langkah 2: Deploy Aplikasi
Di dalam direktori /var/www/html buat file config.php dan index.php, sesuaikan dengan nama bucket S3