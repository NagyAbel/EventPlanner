# EventPlanner

A full-stack event planning application built with Laravel (backend) and Vue 3 (frontend), containerized with Docker.

## Prerequisites

- Docker and Docker Compose installed

## Quick Start

### 1. Setup Environment
```bash
cp backend/.env.example backend/.env
```

### 2. Start Services
```bash
docker compose up -d
```

### 3. Initialize Database
```bash
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate
```

### 4. Access the Application
- **Frontend**: http://localhost
- **Backend API**: http://localhost/api
