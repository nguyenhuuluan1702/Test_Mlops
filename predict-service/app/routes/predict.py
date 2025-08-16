from flask import Blueprint, request, jsonify
from app.middlewares.auth import token_required
import pandas as pd
import os

from app.models.dynamic_loader import ModelLoader, ModelPredictor
from app.scalers.shared_scaler import get_scaler

predict_bp = Blueprint('predict', __name__, url_prefix='/predict')

# Try to import swagger decorator, but continue without it if not available
try:
    from flasgger import swag_from
    HAS_SWAGGER = True
except ImportError:
    def swag_from(spec):
        def decorator(f):
            return f
        return decorator
    HAS_SWAGGER = False

@predict_bp.route('/model', methods=['POST'])
@token_required  
@swag_from({
    'tags': ['Prediction'],
    'summary': 'Predict using dynamic model loading',
    'description': 'Make predictions using any supported ML model type (Keras, PyTorch, XGBoost, Sklearn, etc.)',
    'security': [{'Bearer': []}],
    'parameters': [
        {
            'in': 'body',
            'name': 'body',
            'description': 'Prediction parameters with model information',
            'required': True,
            'schema': {
                'type': 'object',
                'required': ['pc_mxene_loading', 'laminin_peptide_loading', 'stimulation_frequency', 'applied_voltage', 'model_path', 'model_type'],
                'properties': {
                    'pc_mxene_loading': {
                        'type': 'number',
                        'minimum': 0,
                        'maximum': 0.3,
                        'description': 'PC MXene loading value'
                    },
                    'laminin_peptide_loading': {
                        'type': 'number', 
                        'minimum': 0,
                        'maximum': 150,
                        'description': 'Laminin peptide loading value'
                    },
                    'stimulation_frequency': {
                        'type': 'number',
                        'minimum': 0,
                        'maximum': 3,
                        'description': 'Stimulation frequency value'
                    },
                    'applied_voltage': {
                        'type': 'number',
                        'minimum': 0,
                        'maximum': 3,
                        'description': 'Applied voltage value'
                    },
                    'model_path': {
                        'type': 'string',
                        'description': 'Absolute path to the model file'
                    },
                    'model_type': {
                        'type': 'string',
                        'enum': ['keras', 'pytorch', 'sklearn', 'xgboost', 'pickle', 'joblib'],
                        'description': 'Type of machine learning model'
                    }
                }
            }
        }
    ],
    'responses': {
        '200': {
            'description': 'Successful prediction',
            'schema': {
                'type': 'object',
                'properties': {
                    'prediction': {
                        'type': 'number',
                        'description': 'Predicted viability percentage'
                    },
                    'model_used': {
                        'type': 'string',
                        'description': 'Name/path of the model used'
                    },
                    'model_type': {
                        'type': 'string', 
                        'description': 'Type of the model used'
                    },
                    'input_parameters': {
                        'type': 'object',
                        'description': 'Input parameters used for prediction'
                    }
                }
            }
        },
        '400': {
            'description': 'Bad request - invalid input parameters'
        },
        '401': {
            'description': 'Unauthorized - invalid or missing token'
        },
        '404': {
            'description': 'Model file not found'
        },
        '500': {
            'description': 'Internal server error - model loading or prediction failed'
        }
    }
})
def predict_with_dynamic_model():
    """
    Universal prediction endpoint supporting multiple ML model types
    """
    try:
        data = request.get_json()
        
        # Validate required fields
        required_fields = ['pc_mxene_loading', 'laminin_peptide_loading', 'stimulation_frequency', 'applied_voltage', 'model_path', 'model_type']
        for field in required_fields:
            if field not in data:
                return jsonify({'error': f'Missing required field: {field}'}), 400
        
        # Extract and validate parameters
        pc_mxene_loading = float(data['pc_mxene_loading'])
        laminin_peptide_loading = float(data['laminin_peptide_loading'])
        stimulation_frequency = float(data['stimulation_frequency'])
        applied_voltage = float(data['applied_voltage'])
        model_path = data['model_path']
        model_type = data['model_type'].lower()
        
        # Validate parameter ranges
        if not (0 <= pc_mxene_loading <= 0.3):
            return jsonify({'error': 'pc_mxene_loading must be between 0 and 0.3'}), 400
        if not (0 <= laminin_peptide_loading <= 150):
            return jsonify({'error': 'laminin_peptide_loading must be between 0 and 150'}), 400
        if not (0 <= stimulation_frequency <= 3):
            return jsonify({'error': 'stimulation_frequency must be between 0 and 3'}), 400
        if not (0 <= applied_voltage <= 3):
            return jsonify({'error': 'applied_voltage must be between 0 and 3'}), 400
        
        # Validate model type
        supported_types = ['keras', 'pytorch', 'sklearn', 'xgboost', 'pickle', 'joblib']
        if model_type not in supported_types:
            return jsonify({'error': f'Unsupported model_type: {model_type}. Supported types: {supported_types}'}), 400
        
        # Check if model file exists
        if not os.path.exists(model_path):
            return jsonify({'error': f'Model file not found: {model_path}'}), 404
        
        # Load model using dynamic loader (static method)
        model = ModelLoader.load_model(model_path, model_type)
        
        if model is None:
            return jsonify({'error': f'Failed to load model from {model_path}'}), 500
        
        # Prepare input data with correct column names (same as used for fitting scaler)
        input_data = pd.DataFrame({
            'MXene (mg/mL)': [pc_mxene_loading],
            'Laminin peptide (ug/mL)': [laminin_peptide_loading], 
            'Electric stimulation (Hz)': [stimulation_frequency],
            'Voltage (V)': [applied_voltage]
        })
        
        # Get shared scaler (used consistently across all model types)
        scaler = get_scaler()
        if scaler is None:
            return jsonify({'error': 'Failed to load scaler'}), 500
        
        # Scale the input data first
        scaled_data = scaler.transform(input_data)
        
        # Make prediction using dynamic predictor (static method)
        prediction = ModelPredictor.predict(model, scaled_data, model_type)
        
        if prediction is None:
            return jsonify({'error': 'Prediction failed'}), 500
        
        # Return response
        return jsonify({
            'prediction': prediction,
            'unit': '%',
            'user': request.user["username"],
            'model_used': os.path.basename(model_path),
            'model_type': model_type,
            'input_parameters': {
                'pc_mxene_loading': pc_mxene_loading,
                'laminin_peptide_loading': laminin_peptide_loading,
                'stimulation_frequency': stimulation_frequency,
                'applied_voltage': applied_voltage
            }
        })
        
    except ValueError as ve:
        return jsonify({'error': f'Invalid parameter value: {str(ve)}'}), 400
    except Exception as e:
        return jsonify({'error': f'Prediction error: {str(e)}'}), 500

@predict_bp.route('/health', methods=['GET'])
@swag_from({
    'tags': ['Health'],
    'summary': 'Health check endpoint',
    'description': 'Check if the predict service is running and healthy',
    'responses': {
        200: {
            'description': 'Service is healthy',
            'schema': {
                'type': 'object',
                'properties': {
                    'status': {'type': 'string', 'description': 'Health status'},
                    'message': {'type': 'string', 'description': 'Status message'}
                }
            }
        }
    }
})
def health_check():
    """Health check endpoint for the predict service"""
    return jsonify({
        "status": "healthy",
        "message": "Predict service is running"
    }), 200

# In the future, you can add routes for other models like /cnn, /xgboost ...