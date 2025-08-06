from flask import Flask
from flask_cors import CORS
from .routes.predict import predict_bp

def create_app():
    app = Flask(__name__)
    CORS(app, origins=["*"])  # Use origins=["*"] for dev only, specify domain for production

    # Initialize Swagger UI
    try:
        from .config.swagger import init_swagger
        init_swagger(app)
    except ImportError as e:
        print(f"Warning: Could not load Swagger: {e}")

    # Import and register blueprint    
    app.register_blueprint(predict_bp)

    return app