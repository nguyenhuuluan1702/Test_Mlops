# Predict Service

Python Flask-based machine learning service for predicting Schwann cell viability using artificial neural networks (ANN). This service provides REST API endpoints for making predictions with pre-trained models.

## 🚀 Features

- **Flask-based REST API** with Blueprint architecture
- **Pre-trained ANN model** for Schwann cell viability prediction
- **JWT authentication middleware** for secure API access
- **CORS enabled** for cross-origin requests
- **Model and scaler lazy loading** for performance optimization
- **Health check endpoint** for service monitoring
- **Swagger API Documentation** with interactive testing

## 📚 API Documentation

### Access Swagger UI:
- **Direct Access**: http://localhost:5000/api-docs/
- **Features**: Interactive API testing, JWT authentication support, request/response schemas

### API Endpoints:
- **POST /predict/model** - ML prediction (requires JWT authentication)
  - Request: `{ pc_mxene_loading, laminin_peptide_loading, stimulation_frequency, applied_voltage, model_path }`
  - Response: `{ prediction, unit, user }`
- **GET /predict/health** - Health check
  - Response: `{ status, message }`

## 📋 Prerequisites

- Python 3.11+
- Pre-trained ML models (included in `ml_model/` directory)
- JWT secret key (shared with auth-service)
- pip or conda

## 🛠️ Local Development

### 1. Create Virtual Environment
```bash
# Create and activate virtual environment
python -m venv venv

# Windows
venv\Scripts\activate
# Linux/Mac  
source venv/bin/activate
```

### 2. Install Dependencies
```bash
pip install -r requirements.txt
```

### 3. Environment Variables
Create a `.env` file in the root directory or use the project's main `.env` file:
```env
JWT_SECRET=your_jwt_secret_here
MODEL_DIR=ml_model
```

### 4. Run the Service
```bash
# Development server
python run.py

The service will be available at `http://localhost:5000`

## 🐳 Docker Development

### Build and Run
```bash
# Build the image
docker build -t predict-service .

# Run the container
docker run -p 5000:5000 --env-file .env predict-service
```

### With Docker Compose (Recommended)
```bash
# From the root directory
docker-compose up predict-service
```

## 📚 API Endpoints

### Prediction

#### POST /predict/model
Make predictions using dynamic ML models (requires authentication)

**Headers:**
```
Authorization: Bearer <JWT_TOKEN>
Content-Type: application/json
```

**Request:**
```json
{
  "pc_mxene_loading": 0.01,
  "laminin_peptide_loading": 120,
  "stimulation_frequency": 1,
  "applied_voltage": 1,
  "model_path": "/path/to/model.keras"
}
```

**Response:**
```json
{
  "prediction": 85.6,
  "unit": "%",
  "user": "testuser"
}
```

**Error Responses:**
```json
// Missing authorization
{
  "error": "Token missing"
}

// Invalid token
{
  "error": "Token expired"
}

// Missing fields
{
  "error": "Missing field: pc_mxene_loading"
}
```

#### GET /predict/health
Health check endpoint

**Response:**
```json
{
  "status": "healthy",
  "message": "Predict service is running"
}
```

## 🧪 Testing

### Manual Testing
```bash
# Health check
curl http://localhost:5000/predict/health

# Get JWT token from auth service first
TOKEN=$(curl -X POST http://localhost:4000/login \
  -H "Content-Type: application/json" \
  -d '{"username": "testuser", "password": "Test@123"}' | jq -r '.token')

# Make prediction
curl -X POST http://localhost:5000/predict/model \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "pc_mxene_loading": 1.5,
    "laminin_peptide_loading": 2.3,
    "stimulation_frequency": 0.8,
    "applied_voltage": 4.1,
    "model_path": "/path/to/model.keras"
  }'
```

## 🏗️ Project Structure

```
predict-service/
├── app/
│   ├── __init__.py         # Flask app factory
│   ├── __pycache__/        # Python cache files
│   ├── config/
│   │   ├── env.py          # Environment configuration
│   │   ├── swagger.py      # Swagger configuration
│   │   └── __pycache__/    # Python cache files
│   ├── middlewares/
│   │   ├── auth.py         # JWT authentication middleware
│   │   └── __pycache__/    # Python cache files
│   ├── models/
│   │   ├── dynamic_loader.py # Dynamic model loader utility
│   │   └── __pycache__/    # Python cache files
│   ├── routes/
│   │   ├── predict.py      # Prediction routes
│   │   └── __pycache__/    # Python cache files
│   └── scalers/
│       ├── shared_scaler.py # Shared scaler utility
│       └── __pycache__/    # Python cache files
├── ml_model/
│   └── scaler.pkl          # Trained data scaler
├── dockerfile              # Docker configuration
├── README.md              # This file
├── requirements.txt       # Python dependencies
└── run.py                 # Application entry point
```

## 🔧 Configuration

### Environment Variables
- `JWT_SECRET`: Secret key for JWT token verification (should match auth service)
- `MODEL_DIR`: Directory containing ML models (default: ml_model)

### Key Dependencies
- **Flask**: Web framework
- **TensorFlow/Keras**: Machine learning framework
- **scikit-learn**: Data preprocessing and scaling
- **PyJWT**: JWT token handling
- **Flask-CORS**: Cross-origin resource sharing
- **Flasgger**: Swagger/OpenAPI documentation

## 🔍 Monitoring

### Health Check
The service provides a health check endpoint at `/predict/health` that returns the service status.

### Logging
The service logs important events including:
- Model loading
- Prediction requests
- Authentication attempts
- Errors and exceptions

## 🚨 Troubleshooting

### Common Issues

1. **Model Loading Errors**
   ```bash
   # Check if model files exist
   ls -la ml_model/
   
   # Verify model file integrity
   python -c "import tensorflow as tf; tf.keras.models.load_model('ml_model/ann_model.keras')"
   ```

2. **Memory Issues**
   ```bash
   # Monitor memory usage
   docker stats predict-service
   ```

3. **Authentication Errors**
   ```bash
   # Verify JWT_SECRET matches auth service
   echo $JWT_SECRET
   ```

4. **Dependency Issues**
   ```bash
   # Reinstall dependencies
   pip install -r requirements.txt --force-reinstall
   ```

