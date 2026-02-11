# Fieldlab Zephyros – T-shore Maintenance Monitoring

## 📌 Project Overview  
This repository contains the work for a **university project** in collaboration with **World Class Maintenance (WCM)**, specifically focusing on the **T-shore initiative** within **Fieldlab Zephyros**.  

Fieldlab Zephyros is dedicated to developing an **unmanned digital control room** for offshore renewable energy sources such as **wind, wave, tide, and solar**.  
Our project contributes to this mission by researching and designing solutions for **predictive maintenance of offshore wind turbines**.  

---

## Objective  
The primary objective is to develop a **digital maintenance monitoring system** that:  
- 📊 Analyzes turbine data (real or synthetic).  
- 🔎 Detects early signals of component deterioration.  
- 🤖 Forecasts potential failures using predictive algorithms.  
- 🌬️ Provides insights into turbine efficiency factors (e.g., pitch, positioning).  
- ⚡ Helps reduce downtime and maintenance costs by optimizing repair scheduling.  

---

## Methodology  
We apply an **agile approach** combined with applied research methods, including:  
- Market research on control rooms  
- Expert and stakeholder interviews  
- Available product analysis & gap analysis  
- Prototyping and usability testing  

---

## Key Context  
- **Client**: World Class Maintenance (Fieldlab Zephyros)  
- **Focus Area**: T-shore project (training and maintenance for offshore wind)  
- **Duration**: Ongoing research within the project window (2022–2026)  
- **Location**: Breda (HQ) & Vlissingen (R&D, De KAAP living lab)  

---

## Related Initiatives at Fieldlab Zephyros  
- **AIRTuB** – Automated inspection and repair of turbine blades  
- **New Waves** – Climate resilience and innovation collaboration  
- **T-shore** – Training and maintenance for offshore wind *(our focus)*  
- **Offshore For Sure** – Integrating wind, tide, wave, and solar energy sources  

---

# 🚀 HZ FieldLab Project

> **University project with Vue.js frontend and Laravel backend**

## 📋 Prerequisites

Before starting, make sure you have these installed on your machine:

| Tool | Version | Download Link |
|------|---------|---------------|
| **PHP** | 8.1+ | [Download](https://www.php.net/downloads) |
| **Composer** | Latest | [Download](https://getcomposer.org/download/) |
| **Node.js** | 16+ | [Download](https://nodejs.org/) |
| **Git** | Latest | [Download](https://git-scm.com/) |
| **Database** | MySQL/PostgreSQL/SQLite | Optional for development |
| **Docker Desktop** | v28.5.1+ | [Download](https://www.docker.com/products/docker-desktop/) |

### ✅ Verify Installation
```bash
php --version
composer --version
node --version
npm --version
git --version
```

## 🚀 Quick Start

### 1️⃣ Clone Repository
```bash
git clone https://github.com/netas369/HZ-FieldLab-Project.git
cd HZ-FieldLab-Project
```

### 2️⃣ Backend Setup (Laravel)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```
🌐 **Backend running at:** `http://localhost:8000`

### 3️⃣ Frontend Setup (Vue.js)
> **Open a new terminal window**
```bash
cd frontend
npm install
npm run dev
```
🌐 **Frontend running at:** `http://localhost:5173`

### 4️⃣ Test Everything
- **API Test:** Open `http://localhost:8000/api/test` in browser
- **Frontend:** Open `http://localhost:5173` in browser

---

## ⚙️ Detailed Setup

<details>
<summary><strong>🗄️ Database Configuration</strong></summary>

Edit the `.env` file in the `backend/` folder:

**Option 1: SQLite (Easiest for development)**
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/your/project/backend/database/database.sqlite
```
Create the database file:
```bash
touch database/database.sqlite
```

**Option 2: MySQL**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```
</details>

<details>
<summary><strong>🔧 Development Workflow</strong></summary>

You need **both servers running** during development:

**Terminal 1 - Backend:**
```bash
cd backend
php artisan serve
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
```
</details>

<details>
<summary><strong>📁 Project Structure</strong></summary>

```
HZ-FieldLab-Project/
├── 🖥️ backend/          # Laravel API
│   ├── app/             # Application logic
│   ├── routes/          # API routes (api.php)
│   ├── database/        # Migrations, models
│   └── .env             # Environment settings
├── 🎨 frontend/         # Vue.js application
│   ├── src/             # Source code
│   ├── public/          # Static files
│   └── package.json     # Dependencies
└── 📖 README.md
```
</details>

<details>
  <summary><strong>How to import Training Data</strong></summary>
Insert csv to storage/app/imports/wind_farm_training_data.csv Step 2 is to php artisan migrate
Step 3 php artisan import:wind-farm-data storage/app/imports/wind_farm_training_data.csv
</details>

## 🚨 Troubleshooting

<details>
<summary><strong>❌ "composer: command not found"</strong></summary>

**Solution:** Install Composer from https://getcomposer.org/download/
</details>

<details>
<summary><strong>❌ "npm: command not found"</strong></summary>

**Solution:** Install Node.js from https://nodejs.org/
</details>

<details>
<summary><strong>❌ Laravel returns 500 error</strong></summary>

```bash
cd backend
php artisan config:clear
php artisan cache:clear
php artisan key:generate
```
</details>

<details>
<summary><strong>❌ Database connection error</strong></summary>

- Check your `.env` file database settings
- Make sure your database server is running  
- For SQLite, ensure the database file exists
</details>

<details>
<summary><strong>❌ Port already in use</strong></summary>

```bash
# Laravel (use different port)
php artisan serve --port=8001

# Vue.js will automatically suggest a different port
npm run dev
```
</details>

<details>
<summary><strong>❌ CORS errors</strong></summary>

This should be configured already, but if you get CORS errors, contact the team.
</details>

## 🔄 Git Workflow

### 📥 Before making changes:
```bash
git pull origin main
```

### 📤 After making changes:
```bash
git add .
git commit -m "Description of your changes"
git push origin main
```

### 🌿 Working with branches (recommended):
```bash
# Create new branch
git checkout -b feature/your-feature-name

# Make changes, then commit
git add .
git commit -m "Add your feature"
git push origin feature/your-feature-name

# Create pull request on GitHub
```

---

## 🛠️ Useful Commands

<details>
<summary><strong>⚡ Laravel Commands</strong> (run from <code>backend/</code>)</summary>

```bash
php artisan route:list          # Show all API routes
php artisan migrate:fresh       # Reset database
php artisan tinker              # Laravel console
php artisan config:clear        # Clear config cache
php artisan make:controller     # Create new controller
php artisan make:model          # Create new model
```
</details>

<details>
<summary><strong>⚡ Vue.js Commands</strong> (run from <code>frontend/</code>)</summary>

```bash
npm run build                   # Build for production
npm run preview                 # Preview production build
npm install package-name        # Add new package
npm run lint                    # Check code style
```
</details>

---

## 🧪 API Testing

Test the API connection:

| Method | URL | Expected Response |
|--------|-----|-------------------|
| **Browser** | `http://localhost:8000/api/test` | `{"message": "API is working!"}` |
| **Postman** | GET `http://localhost:8000/api/test` | JSON response |
| **Frontend** | Automatic via Axios | Data displayed in Vue app |

---

## 🟩 Local SonarQube Setup with Docker

```bash
# First run this command in the root of the project
docker compose -f docker-compose.sonar up -d

# Wait 30–60 seconds until it’s fully started.

# Open:
http://localhost:9000

# Login:
admin / admin   # change password when asked.

# Create a new project using the "manual" option:
Project key = HZ-FieldLab

# Save the created project token as you need to use it later.

# Run the Sonar Scanner:
docker run --rm `
  -v "${PWD}:/usr/src" `
  sonarsource/sonar-scanner-cli `
  "-Dsonar.host.url=http://host.docker.internal:9000" `
  "-Dsonar.token=YOUR_TOKEN_HERE"

# Open SonarQube and check results in the measures tab:
http://localhost:9000
```
---

## 🆘 Need Help?

1. 📖 Check this README first
2. 💬 Ask in our project group chat  
3. 📚 Check documentation:
   - [Laravel Docs](https://laravel.com/docs)
   - [Vue.js Guide](https://vuejs.org/guide/)
4. 🐛 Create an issue on GitHub

---

<div align="center">

### 🎉 Ready to code!

**Backend:** `http://localhost:8000` | **Frontend:** `http://localhost:5173`

Made with ❤️ by the HZ FieldLab Team

</div>
