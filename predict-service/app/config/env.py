import os

class Config_env:
    SECRET_KEY = os.environ.get("JWT_SECRET", "jwt_secret")
    MODEL_DIR = os.environ.get("MODEL_DIR", "ml_model")
    PORT = int(os.environ.get("PREDICT_PORT", 5000))

config = Config_env()