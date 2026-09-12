# Step 1: Use an official PHP runtime with Apache web server built-in
FROM php:8.2-apache

# Step 2: Install and enable the MySQLi extension needed for your database connections
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Step 3: Copy all 13 of your project files from GitHub into the server's web directory
COPY . /var/www/html/

# Step 4: Expose port 80 to allow incoming web traffic to access your ATM Shield application
EXPOSE 80
