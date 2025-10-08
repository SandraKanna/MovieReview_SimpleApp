# PHP Intermediate — Practice Application

This project is the **next level** after 

---

## Overview

sssss
It includes:
- A simple **router** (`index.php`) thsdasdasd
---

## Tech Stack

| Layer | Description |
|-------|--------------|
| **PHP** | Version 7.4 (kept intentionally for Laravel 8–9 compatibility). |
| **PostgreSQL** | Database used instead of SQLite (runs as a separate container). |
| **Apache** | Bundled with the PHP container for easy local hosting. |
| **Docker Compose** | Manages the PHP and PostgreSQL containers. |
| **CSS** | Custom stylesheet for improved UI and dark/light mode. |
| **.env file** | Stores environment variables such as DB credentials and host info. |
---

## Project Structure
```
simple-movie-reviews/
├── Dockerfile
├── docker-compose.yml
├── Makef
```
---

## ⚙️ Setup Instructions

### 1. Clone the repository
```bash
git clone https://github.com/<your-username>/php-intermediate.git
cd php-intermediate
```
---
### 2. Configure environment
Edit config/.env if needed:
```bash
DB_HOST=db
DB_PORT=5432
DB_NAME=appdb
DB_USER=app
DB_PASS=secret
```
---
## 3. Usage
Make sure you have **Docker** and **Docker Compose** installed.  
Then use the provided `Makefile`:

```bash
# build images (if they dont exist yet) and start the containers
make start

# view logs
make logs

# stop all
make stop

# cleanup (remove containers, volumes, local images)
make clean

# Destroy EVERYTHING (all containers/images/volumes, careful!)
make destroy
```

## 4. Database
Data is now stored in PostgreSQL (instead of SQLite).
The database container is defined in docker-compose.yml, and connection credentials are loaded automatically from the environment variables (DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT).

You can inspect the data directly from the Postgres container:

```bash
# Open a shell inside the Postgres container
docker compose exec db bash

# Connect to the database
psql -U app -d appdb

# Once inside the psql prompt:
\dt                       -- list all tables (you should see "form1")
SELECT * FROM form1;      -- view all submitted form entries

#to exit postgres:
\q
```
---

## 5. Open in browser
```
http://localhost:8080

```