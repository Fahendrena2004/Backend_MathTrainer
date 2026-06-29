# MathTrainer Laravel API

Backend API Laravel ho an'ny application Flutter MathTrainer.

## Configuration PostgreSQL

Ovay ao amin'ny `.env` ireto raha tsy mitovy amin'ny PgAdmin anao:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mathtrainer_db
DB_USERNAME=postgres
DB_PASSWORD=apetraho_eto_ny_password_postgres
```

Rehefa avy manova `.env`:

```powershell
php artisan config:clear
php artisan serve --host=127.0.0.1 --port=8000
```

## Endpoints

```text
GET    /api/health
GET    /api/levels
POST   /api/levels
PUT    /api/levels/{level}
DELETE /api/levels/{level}
GET    /api/topics
POST   /api/topics
PUT    /api/topics/{topic}
DELETE /api/topics/{topic}
GET    /api/badges
POST   /api/badges
PUT    /api/badges/{badge}
DELETE /api/badges/{badge}
GET    /api/exercises?topic_id=1&school_level_id=1
GET    /api/exercises/{exercise}
POST   /api/exercises
PUT    /api/exercises/{exercise}
DELETE /api/exercises/{exercise}
POST   /api/auth/register
POST   /api/auth/admin-login
POST   /api/attempts
GET    /api/users/{user}/progress
```

## Exemples JSON

Register:

```json
{
  "name": "Rakoto",
  "email": "rakoto2@example.com",
  "password": "secret123",
  "school_level_id": 1
}
```

Attempt:

```json
{
  "user_id": 2,
  "exercise_id": 1,
  "answer": "45"
}
```

Admin login demo:

```json
{
  "email": "admin@mathtrainer.app"
}
```
