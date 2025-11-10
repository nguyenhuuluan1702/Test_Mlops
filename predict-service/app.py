from flask import Flask, request, jsonify
import pandas as pd
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_squared_error, mean_absolute_error
import joblib
import os

app = Flask(__name__)

@app.route("/train", methods=["POST"])
def train_model():
    try:
        data = request.json
        dataset_path = data.get("dataset_path")

        if not dataset_path or not os.path.exists(dataset_path):
            return jsonify({"error": "Dataset not found"}), 400

        # Load data
        df = pd.read_csv(dataset_path)
        
        # Define feature columns explicitly (exclude both target and intermediate viability column)
        feature_columns = ['MXene (mg/mL)', 'Laminin peptide (ug/mL)', 'Electric stimulation (Hz)', 'Voltage (V)']
        target_column = 'Cell viability (%)'
        
        # Check if columns exist
        if not all(col in df.columns for col in feature_columns + [target_column]):
            return jsonify({"error": "Dataset missing required columns"}), 400
        
        X = df[feature_columns]
        y = df[target_column]

        # Train-test split
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

        # Train model
        model = RandomForestRegressor(n_estimators=100, random_state=42)
        model.fit(X_train, y_train)

        # Evaluate
        y_pred = model.predict(X_test)
        mse = mean_squared_error(y_test, y_pred)
        mae = mean_absolute_error(y_test, y_pred)

        # Save model
        model_dir = "trained_models"
        os.makedirs(model_dir, exist_ok=True)
        model_path = os.path.join(model_dir, "rf_model.pkl")
        joblib.dump(model, model_path)

        return jsonify({
            "message": "Training completed successfully",
            "mse": mse,
            "mae": mae,
            "model_path": os.path.abspath(model_path)
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)
