# Predicting Schwann Cell Viability - Laravel MVC System

A comprehensive web-based machine learning system for predicting Schwann cell viability using artificial neural networks. The system features a Laravel web application with admin panel, user management, and integration with Python Flask-based prediction services.

## 🏗️ System Architecture

This project consists of two main components working together:

### 🌐 **WebApp** (Laravel MVC)
- **Frontend & Backend**: Full-stack Laravel application
- **User Interface**: Responsive web interface with Bootstrap & AdminLTE
- **Authentication**: Role-based user management (Admin/User)
- **Admin Panel**: Model management, user administration, system monitoring
- **File Management**: ML model upload and storage
- **Database**: User data, model metadata, prediction history

### 🤖 **Predict-Service** (Python Flask)
- **ML Engine**: Python Flask API for neural network predictions
- **Model Loading**: Dynamic ML model loading and inference
- **Data Processing**: Feature scaling and preprocessing
- **API Endpoints**: RESTful services for prediction requests
- **Documentation**: Swagger/OpenAPI documentation

## 🚀 Quick Start

### Prerequisites
- **PHP 8.4+** with extensions
- **Python 3.11+** 
- **Node.js & npm**
- **Database** (MySQL/PostgreSQL/SQLite)

### Installation Commands

**1. Install PHP (choose your platform):**
```bash
# Windows
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.4'))

# Linux
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.4)"

# macOS
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.4)"
```

**2. Setup Laravel WebApp:**
```bash
cd WebApp
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate  # Select 'yes' if database doesn't exist
php artisan db:seed
npm run build
php artisan serve    # Runs on http://localhost:8000
```

**3. Setup Python Predict Service:**
```bash
cd predict-service
python -m venv venv

# Windows
venv\Scripts\activate
# Linux/Mac
source venv/bin/activate

pip install -r requirements.txt
python run.py        # Runs on http://localhost:5000
```

## 🌟 Key Features

### User Management
- ✅ **Role-based Authentication** (Admin/User roles)
- ✅ **User Registration & Login** with secure sessions
- ✅ **Admin Panel** for user management
- ✅ **Profile Management** and password reset

### Machine Learning Integration
- ✅ **Model Upload & Management** through web interface
- ✅ **Dynamic Model Loading** with metadata storage
- ✅ **Real-time Predictions** via Flask API
- ✅ **Prediction History** with detailed analytics
- ✅ **Model Validation** and error handling

### System Administration
- ✅ **Admin Dashboard** with system overview
- ✅ **User Management** (CRUD operations)
- ✅ **ML Model Management** with force delete options
- ✅ **System Monitoring** and health checks
- ✅ **Data Export** and reporting capabilities

### Technical Features
- ✅ **Responsive Design** (Bootstrap 5 + AdminLTE)
- ✅ **RESTful API Integration** between services
- ✅ **File Upload Security** with validation
- ✅ **Database Optimization** with proper indexing
- ✅ **Caching Systems** for performance
- ✅ **Error Handling** and logging

## 📁 Project Structure

```
PredictingSchwannCellViability-Laravel-MVC/
├── WebApp/                          # Laravel MVC Application
│   ├── app/
│   │   ├── Http/Controllers/        # Request controllers
│   │   ├── Models/                  # Eloquent models
│   │   └── Services/                # Business logic
│   ├── resources/views/             # Blade templates
│   ├── public/                      # Web assets & uploads
│   ├── database/                    # Migrations & seeders
│   └── README.md                    # Laravel setup guide
│
├── predict-service/                 # Python Flask API
│   ├── app/
│   │   ├── routes/                  # API endpoints
│   │   ├── models/                  # ML model utilities
│   │   ├── middlewares/             # Authentication
│   │   └── scalers/                 # Data preprocessing
│   ├── ml_model/                    # Trained models & scalers
│   └── README.md                    # Flask API setup guide
│
└── README.md                        # This file
```

## 🎯 User Workflows

### Regular Users
1. **Register/Login** → Access personal dashboard
2. **Make Predictions** → Input parameters, get ML results
3. **View History** → Track previous predictions
4. **Manage Profile** → Update account settings

### Administrators
1. **Admin Dashboard** → System overview and statistics
2. **User Management** → Create, edit, delete, reset passwords
3. **Model Management** → Upload, activate, delete ML models
4. **System Monitoring** → Health checks and logs

## 🔧 Development & Maintenance

### Laravel WebApp Development
```bash
cd WebApp

# Development commands
npm run build              # Build frontend assets
php artisan cache:clear    # Clear application cache
php artisan view:clear     # Clear compiled views
php artisan migrate        # Run new migrations
php artisan test          # Run test suite
```

### Python Service Development
```bash
cd predict-service

# Development commands
python run.py             # Start development server
pip install -r requirements.txt  # Install dependencies
# Access Swagger docs: http://localhost:5000/api-docs/
```

## 🔍 API Integration

The Laravel webapp communicates with the Python service via HTTP APIs:

- **Prediction Endpoint**: `POST /predict/model`
- **Health Check**: `GET /predict/health`
- **Authentication**: JWT token-based
- **Documentation**: Available at `/api-docs/`

## 📊 Default System Data

### Default Users
- **Admin**: Full system access, user/model management
- **User**: Basic prediction access, personal dashboard

### Default ML Model
- **ANN Model**: Pre-trained neural network for Schwann cell predictions
- **Protected**: Cannot be deleted to ensure system functionality
- **Scalers**: Included preprocessing pipelines

## 🛡️ Security Features

- **CSRF Protection**: Laravel built-in security
- **XSS Prevention**: Input sanitization and output escaping
- **SQL Injection Protection**: Eloquent ORM safeguards
- **File Upload Security**: Type validation and secure storage
- **JWT Authentication**: Secure service-to-service communication
- **Password Hashing**: Bcrypt encryption
- **Session Management**: Secure session handling

## 🚨 Troubleshooting

### Common Issues

**WebApp Issues:**
```bash
# Permission errors
chmod -R 755 storage/ bootstrap/cache/

# Cache issues
php artisan cache:clear
php artisan config:clear

# Database issues
php artisan migrate:refresh --seed
```

**Predict Service Issues:**
```bash
# Python environment
python -m venv venv
source venv/bin/activate  # or venv\Scripts\activate on Windows

# Dependencies
pip install -r requirements.txt --force-reinstall

# Model loading
ls -la ml_model/  # Check model files exist
```

## 📱 Responsive Design

The system works seamlessly across:
- **Desktop**: Full admin panel functionality
- **Tablet**: Optimized touch interface
- **Mobile**: Responsive prediction interface

## 📚 Documentation

- **WebApp/README.md**: Detailed Laravel setup and API reference
- **predict-service/README.md**: Python service configuration and API docs
- **Swagger UI**: Interactive API documentation at `/api-docs/`
