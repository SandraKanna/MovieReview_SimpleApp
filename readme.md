# PHP web app — MovieReview

A minimal full-stack CRUD web app built with **PHP + PostgreSQL**, packaged with **Docker Compose**.
Users can add, edit, delete and browse movies they’ve watched — including short reviews, ratings, and optional poster uploads.

---

## Overview

**Core features:**
- **Create a review:** title, genre, rating (1-5), add review text, date and upload an optional image.
- **Read/List reviews:** table view with columns and a search bar to search by title and filter by genre and sort by date or rating.
- **Update review:** edit any field. Previous image remains if no new upload is selected.
- **Delete review:** confirmation prompt before deletion handled via a simple JS script included in the header.
- **Feedback messages:** Flash success/error messages stored in PHP session.
- **Persistent data:** Database and uploads stored in Docker named volumes for durability.

---

## Tech Stack
- **PHP** version 7.4 (kept intentionally for Laravel 8–9 compatibility).
- **PostgreSQL 16-alpine** running as a separate container.
- **Apache** bundled with the PHP container for easy local hosting.
- **Docker Compose** for isolated, reproducible setup  
- **Vanilla JS + CSS** (no frameworks)  
---

## Project Structure
```
MovieReview_app/
├── Dockerfile
├── docker-compose.yml
├── Makefile
├── config/
│   └── php-upload.ini            # PHP upload limits
├── src/
│   ├── assets/
│   │   ├── app.js                # Delete confirmation logic
│   │   └── style.css             # App styling/design
│   ├── helpers.php               # Shared PHP functions (DB, flash, uploads…)
│   ├── layout/
│   │   ├── header.php            # Common header + navbar
│   │   └── footer.php            # Common footer
│   ├── pages/
│   │   ├── actions/
│   │   │   ├── delete.php        # Delete review (POST)
│   │   │   └── save.php          # Create or update review
│   │   ├── render/
│   │   │   ├── home.php          # Welcome page
│   │   │   ├── movie-list.php    # Table view + filters
│   │   │   ├── movie-new.php     # New review form
│   │   │   └── movie-edit.php    # Edit existing review
│   └── index.php                 # Simple router
└── readme.md
```
---

## Database
Data is now stored in PostgreSQL.
The database container is defined in docker-compose.yml, and connection credentials are loaded automatically from the environment variables (DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT).

You can inspect the data directly from the Postgres container:

```bash
# Open an interactive shell inside the Postgres container
docker compose exec db bash

# Connect to the database
psql -U app -d appdb

# Once inside the psql prompt:
\dt                       -- list all tables (you should see "movie_reviews")
SELECT * FROM movie_reviews;      -- view all submitted form entries

#to exit postgres:
\q
```
---

## Uploaded files
Uploaded posters are stored in the named Docker volume `uploads-data`.

- List volume: `docker volume ls`
- Inspect volume: `docker volume inspect moviereview_app_uploads-data`
- List the content of the upload folder: `docker compose exec php ls -lah /var/www/html/.uploads`

💡 Uploaded files are persistent thanks to the named volume and will survive.
They are deleted only with `make destroy`.

---

## ⚙️ Setup Instructions

### 1. Clone the repository
```bash
git clone https://git@github.com:SandraKanna/MovieReview_SimpleApp.git
cd MovieReview_app
```
---
### 2. Configure environment
Edit .env or create it if missing. These are the default values:
```bash
DB_HOST=db
DB_PORT=5432
DB_NAME=appdb
DB_USER=app
DB_PASS=secret
```
Adjust values at your convenience.

---

### 3. Create the images and start the containers
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
---

### 4. Open in browser
```
http://localhost:8080

```