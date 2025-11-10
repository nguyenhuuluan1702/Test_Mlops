from flask import Blueprint, request, jsonify
from app.middlewares.auth import token_required
import pandas as pd
import os
import joblib
from datetime import datetime
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import r2_score, mean_squared_error, mean_absolute_error
from sklearn.preprocessing import StandardScaler
import traceback

train_bp = Blueprint('train', __name__, url_prefix='/train')

# Try to import swagger decorator
try:
    from flasgger import swag_from
    HAS_SWAGGER = True
except ImportError:
    def swag_from(spec):
        def decorator(f):
            return f
        return decorator
    HAS_SWAGGER = False


@train_bp.route('/model', methods=['POST'])
# @token_required  # Temporarily disabled for development
@swag_from({
    'tags': ['Training'],
    'summary': 'Train a new ML model',
    'description': 'Train a Random Forest model with provided dataset',
    'security': [{'Bearer': []}],
    'parameters': [
        {
            'in': 'body',
            'name': 'body',
            'description': 'Training parameters',
            'required': True,
            'schema': {
                'type': 'object',
                'required': ['dataset_path'],
                'properties': {
                    'dataset_path': {
                        'type': 'string',
                        'description': 'Absolute path to the dataset CSV file'
                    },
                    'model_name': {
                        'type': 'string',
                        'description': 'Custom name for the trained model (optional)'
                    },
                    'n_estimators': {
                        'type': 'integer',
                        'default': 100,
                        'description': 'Number of trees in the forest'
                    },
                    'max_depth': {
                        'type': 'integer',
                        'default': None,
                        'description': 'Maximum depth of trees'
                    },
                    'test_size': {
                        'type': 'number',
                        'default': 0.2,
                        'minimum': 0.1,
                        'maximum': 0.5,
                        'description': 'Proportion of dataset for testing'
                    },
                    'random_state': {
                        'type': 'integer',
                        'default': 42,
                        'description': 'Random state for reproducibility'
                    }
                }
            }
        }
    ],
    'responses': {
        '200': {
            'description': 'Training completed successfully',
            'schema': {
                'type': 'object',
                'properties': {
                    'success': {'type': 'boolean'},
                    'message': {'type': 'string'},
                    'model_path': {'type': 'string'},
                    'metrics': {
                        'type': 'object',
                        'properties': {
                            'r2_score': {'type': 'number'},
                            'rmse': {'type': 'number'},
                            'mae': {'type': 'number'}
                        }
                    },
                    'training_info': {
                        'type': 'object',
                        'properties': {
                            'train_samples': {'type': 'integer'},
                            'test_samples': {'type': 'integer'},
                            'n_features': {'type': 'integer'},
                            'trained_by': {'type': 'string'},
                            'trained_at': {'type': 'string'}
                        }
                    }
                }
            }
        },
        '400': {'description': 'Bad request - invalid parameters'},
        '401': {'description': 'Unauthorized'},
        '404': {'description': 'Dataset file not found'},
        '500': {'description': 'Training failed'}
    }
})
def train_model():
    """
    Train a new Random Forest model with provided dataset
    """
    try:
        data = request.get_json()
        
        # Get user info (if available from token)
        trained_by = 'unknown'
        if hasattr(request, 'user'):
            trained_by = request.user.get('username', 'unknown')
        
        # Validate required fields
        if 'dataset_path' not in data:
            return jsonify({'error': 'Missing required field: dataset_path'}), 400
        
        dataset_path = data['dataset_path']
        
        # Check if dataset file exists
        if not os.path.exists(dataset_path):
            return jsonify({'error': f'Dataset file not found: {dataset_path}'}), 404
        
        # Get training parameters with defaults and ensure correct types
        n_estimators = int(data.get('n_estimators', 100))
        
        # Handle max_depth - can be None or integer
        max_depth_value = data.get('max_depth', None)
        if max_depth_value is not None and max_depth_value != '':
            max_depth = int(max_depth_value)
        else:
            max_depth = None
        
        test_size = float(data.get('test_size', 0.2))
        random_state = int(data.get('random_state', 42))
        model_name = data.get('model_name', f'RF_Model_{datetime.now().strftime("%Y%m%d_%H%M%S")}')
        
        # Validate parameters
        if not (10 <= n_estimators <= 1000):
            return jsonify({'error': 'n_estimators must be between 10 and 1000'}), 400
        
        if max_depth is not None and not (1 <= max_depth <= 50):
            return jsonify({'error': 'max_depth must be between 1 and 50 or None'}), 400
        
        # Validate test_size
        if not (0.1 <= test_size <= 0.5):
            return jsonify({'error': 'test_size must be between 0.1 and 0.5'}), 400
        
        # Read dataset
        print(f"📂 Reading dataset from: {dataset_path}")
        df = pd.read_csv(dataset_path)
        print(f"✅ Dataset loaded: {len(df)} rows, {len(df.columns)} columns")
        
        # Prepare features and target
        # Define feature columns explicitly (exclude both target and intermediate viability column)
        feature_columns = ['MXene (mg/mL)', 'Laminin peptide (ug/mL)', 'Electric stimulation (Hz)', 'Voltage (V)']
        target_column = 'Cell viability (%)'
        
        # Check if columns exist
        missing_cols = [col for col in feature_columns + [target_column] if col not in df.columns]
        if missing_cols:
            return jsonify({'error': f'Missing required columns: {missing_cols}'}), 400
        
        X = df[feature_columns]
        y = df[target_column]
        
        print(f"🔢 Features: {X.shape[1]} columns")
        print(f"🎯 Target: {y.name if hasattr(y, 'name') else 'Last column'}")
        
        # Split train/test
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=test_size, random_state=random_state
        )
        print(f"✂️ Train set: {len(X_train)} samples, Test set: {len(X_test)} samples")
        
        # Scale features
        scaler = StandardScaler()
        X_train_scaled = scaler.fit_transform(X_train)
        X_test_scaled = scaler.transform(X_test)
        
        # Train model
        print("🚀 Training Random Forest model...")
        model = RandomForestRegressor(
            n_estimators=n_estimators,
            max_depth=max_depth,
            random_state=random_state,
            n_jobs=-1
        )
        model.fit(X_train_scaled, y_train)
        print("✅ Training completed!")
        
        # Evaluate model
        y_pred = model.predict(X_test_scaled)
        r2 = r2_score(y_test, y_pred)
        
        # Calculate RMSE (compatible with all sklearn versions)
        mse = mean_squared_error(y_test, y_pred)
        rmse = mse ** 0.5  # Square root of MSE
        
        mae = mean_absolute_error(y_test, y_pred)
        
        print(f"\n📈 Model Performance:")
        print(f"   R² Score: {r2:.4f}")
        print(f"   RMSE: {rmse:.4f}")
        print(f"   MAE: {mae:.4f}")
        
        # Save model
        model_dir = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'ml_model')
        os.makedirs(model_dir, exist_ok=True)
        
        model_filename = f"{model_name}.pkl"
        model_path = os.path.join(model_dir, model_filename)
        
        joblib.dump(model, model_path)
        print(f"💾 Model saved: {model_path}")
        
        # Save scaler with model
        scaler_path = os.path.join(model_dir, f"{model_name}_scaler.pkl")
        joblib.dump(scaler, scaler_path)
        print(f"💾 Scaler saved: {scaler_path}")
        
        # Also save as latest
        latest_model_path = os.path.join(model_dir, 'latest_model.pkl')
        latest_scaler_path = os.path.join(model_dir, 'latest_scaler.pkl')
        joblib.dump(model, latest_model_path)
        joblib.dump(scaler, latest_scaler_path)
        
        # IMPORTANT: Save as shared scaler for prediction endpoint
        shared_scaler_path = os.path.join(model_dir, 'scaler.pkl')
        joblib.dump(scaler, shared_scaler_path)
        print(f"💾 Shared scaler saved: {shared_scaler_path}")
        
        # Return success response
        return jsonify({
            'success': True,
            'message': 'Model trained successfully',
            'model_path': model_path,
            'scaler_path': scaler_path,
            'model_name': model_name,
            'metrics': {
                'r2_score': round(r2, 4),
                'rmse': round(rmse, 4),
                'mae': round(mae, 4)
            },
            'training_info': {
                'train_samples': len(X_train),
                'test_samples': len(X_test),
                'n_features': X.shape[1],
                'n_estimators': n_estimators,
                'max_depth': max_depth,
                'trained_by': trained_by,
                'trained_at': datetime.now().isoformat()
            }
        }), 200
        
    except Exception as e:
        print(f"\n❌ Training Error: {str(e)}")
        traceback.print_exc()
        return jsonify({
            'success': False,
            'error': f'Training failed: {str(e)}'
        }), 500


@train_bp.route('/status', methods=['GET'])
@swag_from({
    'tags': ['Training'],
    'summary': 'Get training service status',
    'description': 'Check if training service is available',
    'responses': {
        '200': {
            'description': 'Service status',
            'schema': {
                'type': 'object',
                'properties': {
                    'status': {'type': 'string'},
                    'available_models_dir': {'type': 'string'}
                }
            }
        }
    }
})
def training_status():
    """Check training service status"""
    model_dir = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'ml_model')
    return jsonify({
        'status': 'available',
        'models_directory': model_dir,
        'models_count': len([f for f in os.listdir(model_dir) if f.endswith('.pkl')]) if os.path.exists(model_dir) else 0
    }), 200


@train_bp.route('/models', methods=['GET'])
@token_required
@swag_from({
    'tags': ['Training'],
    'summary': 'List all trained models',
    'description': 'Get list of all trained models in ml_model directory',
    'security': [{'Bearer': []}],
    'responses': {
        '200': {
            'description': 'List of models',
            'schema': {
                'type': 'object',
                'properties': {
                    'models': {
                        'type': 'array',
                        'items': {
                            'type': 'object',
                            'properties': {
                                'name': {'type': 'string'},
                                'path': {'type': 'string'},
                                'size': {'type': 'integer'},
                                'created_at': {'type': 'string'}
                            }
                        }
                    }
                }
            }
        }
    }
})
def list_models():
    """List all trained models"""
    try:
        model_dir = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'ml_model')
        
        if not os.path.exists(model_dir):
            return jsonify({'models': []}), 200
        
        models = []
        for filename in os.listdir(model_dir):
            if filename.endswith('.pkl') and not filename.endswith('_scaler.pkl'):
                filepath = os.path.join(model_dir, filename)
                stat = os.stat(filepath)
                models.append({
                    'name': filename,
                    'path': filepath,
                    'size': stat.st_size,
                    'created_at': datetime.fromtimestamp(stat.st_mtime).isoformat()
                })
        
        return jsonify({'models': models}), 200
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500
