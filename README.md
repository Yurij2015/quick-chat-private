# Real-Time Laravel Chat Application

This project is a real-time chat application built with Laravel 11/12, utilizing WebSockets (Laravel Reverb) for instant messaging, Spatie Laravel Permission for user roles (admin/user), and styled with Tailwind CSS + Flowbite. It features an impersonation system for admins and an interactive dashboard.

## Requirements
- Docker Desktop
- Laravel Sail (included)
- Node.js & NPM (for frontend compilation if overriding Sail)

## Setup & Running the Project

1. **Install Dependencies**
   If you haven't opened the project inside Docker yet, you can use a small temporary PHP container to install composer dependencies:
   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php84-composer:latest \
       composer install --ignore-platform-reqs
   ```

2. **Copy `.env` Environment File**
   ```bash
   cp .env.example .env
   ```

3. **Start Docker Containers via Sail**
   ```bash
   ./vendor/bin/sail up -d
   ```
   *(This starts `laravel.test` app server, `mysql`, `redis`, `reverb`, and `worker` services).*

4. **Generate Application Key**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

5. **Run Migrations & Seeders**
   This command will migrate the database scheme and populate it with 10 test users and default roles (admin & user).
   ```bash
   ./vendor/bin/sail artisan migrate --seed
   ```

6. **Install NPM Packages & Build Frontend**
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run build
   ```

## Usage
- Access the site at: `http://localhost`
- **Admin Account**: You can find an admin account created by the `UserSeeder` with the email `admin@example.com` and password `password`. All other users have a standard `user` role.
- **WebSocket Server**: Running on port `8080`.
- **Database**: Port `3306`, Credentials: User `sail`, Database `chat_project`, Password `password`.

## Key Features
- **impersonation**: Logged in as `admin`, click the "Login as" button next to any user in the dashboard.
- **Real-Time Char**: Open two separate incognito windows logged in as different users to see real-time interaction without reloading!
