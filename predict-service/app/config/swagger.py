from flasgger import Swagger

def init_swagger(app):
    """Initialize Swagger UI for the Flask app"""
    
    # Swagger config - use api-docs endpoint only
    swagger_config = {
        "headers": [],
        "specs": [
            {
                "endpoint": 'apispec',
                "route": '/apispec.json',
                "rule_filter": lambda rule: True,
                "model_filter": lambda tag: True,
            }
        ],
        "static_url_path": "/flasgger_static",
        "swagger_ui": True,
        "specs_route": "/api-docs/",
        "swagger_ui_bundle_js": "//unpkg.com/swagger-ui-dist@3/swagger-ui-bundle.js",
        "swagger_ui_standalone_preset_js": "//unpkg.com/swagger-ui-dist@3/swagger-ui-standalone-preset.js",
        "jquery_js": "//unpkg.com/jquery@2.2.4/dist/jquery.min.js",
        "swagger_ui_css": "//unpkg.com/swagger-ui-dist@3/swagger-ui.css"
    }
    
    swagger_template = {
        "swagger": "2.0",
        "info": {
            "title": "Predict Service API",
            "description": "Machine Learning prediction service for Schwann Cell Viability Prediction System",
            "contact": {
                "name": "API Support",
                "email": "support@example.com"
            },
            "version": "1.0.0"
        },
        "host": "localhost:5000",
        "basePath": "/",
        "schemes": [
            "http"
        ],
        "consumes": [
            "application/json"
        ],
        "produces": [
            "application/json"
        ],
        "securityDefinitions": {
            "Bearer": {
                "type": "apiKey",
                "name": "Authorization", 
                "in": "header",
                "description": "JWT Authorization header using the Bearer scheme. Example: 'Bearer {token}'"
            }
        },
        "definitions": {
            "PredictRequest": {
                "type": "object",
                "required": ["pc_mxene_loading", "laminin_peptide_loading", "stimulation_frequency", "applied_voltage"],
                "properties": {
                    "pc_mxene_loading": {
                        "type": "number",
                        "description": "MXene loading in mg/mL",
                        "example": 1.5
                    },
                    "laminin_peptide_loading": {
                        "type": "number", 
                        "description": "Laminin peptide loading in ug/mL",
                        "example": 2.3
                    },
                    "stimulation_frequency": {
                        "type": "number",
                        "description": "Electric stimulation frequency in Hz", 
                        "example": 0.8
                    },
                    "applied_voltage": {
                        "type": "number",
                        "description": "Applied voltage in V",
                        "example": 4.1
                    }
                }
            },
            "PredictResponse": {
                "type": "object",
                "properties": {
                    "prediction": {
                        "type": "number",
                        "description": "Predicted Schwann cell viability",
                        "example": 85.6
                    },
                    "unit": {
                        "type": "string",
                        "description": "Unit of measurement",
                        "example": "%"
                    },
                    "user": {
                        "type": "string", 
                        "description": "Username of the requester",
                        "example": "testuser"
                    }
                }
            },
            "ErrorResponse": {
                "type": "object",
                "properties": {
                    "error": {
                        "type": "string",
                        "description": "Error message",
                        "example": "Missing field: pc_mxene_loading"
                    }
                }
            },
            "HealthResponse": {
                "type": "object",
                "properties": {
                    "status": {
                        "type": "string",
                        "description": "Service status",
                        "example": "healthy"
                    },
                    "message": {
                        "type": "string",
                        "description": "Status message",
                        "example": "Predict service is running"
                    }
                }
            }
        }
    }
    
    # Create Swagger instance
    swagger = Swagger(app, config=swagger_config, template=swagger_template)
    
    return swagger
