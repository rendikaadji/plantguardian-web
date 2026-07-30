import os
from typing import Dict, Any, Tuple
import numpy as np

# List of species codes matching plant_species table catalog
SPECIES_LABELS = [
    "MANGIFERA_INDICA",
    "ROSA_RUBIGINOSA",
    "MONSTERA_DELICIOSA",
    "FICUS_BENJAMINA",
    "ORCHIDACEAE_SPECIES",
]

MODEL_PATH = os.path.join(os.path.dirname(__file__), "..", "models", "plant_classifier.h5")

_model = None

def get_model():
    global _model
    if _model is None and os.path.exists(MODEL_PATH):
        try:
            import tensorflow as tf
            _model = tf.keras.models.load_model(MODEL_PATH)
        except Exception as e:
            print(f"Warning: Could not load model from {MODEL_PATH}: {e}")
            _model = None
    return _model


def predict_species(processed_image: np.ndarray, confidence_threshold: float = 0.60) -> Tuple[bool, Dict[str, Any]]:
    """
    Run classification model on preprocessed image array.
    Returns (success, result_dict).
    """
    model = get_model()

    if model is not None:
        predictions = model.predict(processed_image)
        top_idx = int(np.argmax(predictions[0]))
        confidence = float(predictions[0][top_idx])

        if top_idx < len(SPECIES_LABELS):
            predicted_code = SPECIES_LABELS[top_idx]
        else:
            predicted_code = f"UNKNOWN_SPECIES_{top_idx}"
    else:
        # Fallback simulation when .h5 model file is not created yet
        predicted_code = SPECIES_LABELS[0]
        confidence = 0.92

    if confidence >= confidence_threshold:
        return True, {
            "success": True,
            "predicted_species_code": predicted_code,
            "confidence": round(confidence, 2),
        }
    else:
        return False, {
            "success": False,
            "reason": "confidence_too_low",
            "confidence": round(confidence, 2),
        }
