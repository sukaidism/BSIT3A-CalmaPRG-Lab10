# BSIT3A Calma PRG Lab 10

A simple **Vanilla PHP** dashboard and calendar app using the **Northwind** database.

## Requirements

Make sure you have:

- PHP 8+
- Composer
- XAMPP (Apache and MySQL)
- phpMyAdmin

---

## How to Run the Project

### 1. Put the project inside `htdocs`

Example path:

```
..\BSIT3A_CalmaPRG_Lab10
```

### 2. Install dependencies

Open a terminal in the project folder and run:

```bash
composer install
```

### 3. Import the database

- Open **phpMyAdmin**
- Create a database named `northwind`
- Import the file `northwind-mysql.sql`

### 4. Create a `.env` file

In the project root, create a file named `.env` and paste:

```env
APP_NAME=Northwind Reports
APP_DEBUG=true
APP_TIMEZONE=Asia/Manila

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=northwind
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Start XAMPP

Open **XAMPP Control Panel** and start:

- Apache
- MySQL

### 6. Run the project

In the terminal, run:

```bash
composer run serve
```

### 7. Open in browser

Go to:

```
http://localhost:8000
```

---

## Notes
- If Redis is installed and enabled, the calendar page uses caching for faster repeated loading.