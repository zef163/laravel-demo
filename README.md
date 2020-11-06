# Laravel 8 (demo)

Requirements:
 - Docker v19.03+
 - docker-compose v1.27+
 - git
 - make

## How to start

1. Copy .env file
```bash
$ cp .env.example .env
```

2. Set database password to `DB_PASSWORD`

3. Run docker containers
```bash
$ make install
```

4. Seed database (optional)
```bash
$ make seed
```

## API

### [GET] /users
Get all users

**Query params**
- `limit` - Items per page
- `page` - Current page
- `query` - Query string

### [POST] /users
Create new user

**Post data**
- `name` - User name
- `email` - User email
- `password` - User password

### [GET] /users/:id
Get user info by user identification

### [PUT/PATCH] /users/:id
Update user info by user identification

**Post data**
- `name` - User name
- `email` - User email
- `password` - User password

### [DELETE] /users/:id
Delete user

### [GET] /tasks
Get all tasks by filters

**Query params**
- `limit` - Items per page
- `page` - Current page

### [POST] /tasks
Create new task

**Post data**
- `owner_id` - Assignee user identification
- `reporter_id` - Reporter user identification
- `title` - Task title
- `description` - Task description

### [GET] /tasks/:id
Get task info by task identification

### [PUT/PATCH] /tasks/:id
Update task info by task identification

**Post data**
- `owner_id` - Assignee user identification
- `reporter_id` - Reporter user identification
- `title` - Task title
- `description` - Task description

### [DELETE] /tasks/:id
Delete task
