import base64
import cv2
import numpy as np


def preprocess_image(image_base64: str, target_size=(224, 224)) -> np.ndarray:
    """
    Decode base64 string to OpenCV image, resize, and normalize to 0.0 - 1.0.
    """
    if "," in image_base64:
        image_base64 = image_base64.split(",")[1]

    image_bytes = base64.b64decode(image_base64)
    np_arr = np.frombuffer(image_bytes, np.uint8)
    image = cv2.imdecode(np_arr, cv2.IMREAD_COLOR)

    if image is None:
        raise ValueError("Gagal membaca data gambar dari base64.")

    # Convert BGR to RGB
    image_rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    
    # Resize to target size
    resized = cv2.resize(image_rgb, target_size)

    # Normalize pixel values
    normalized = resized.astype(np.float32) / 255.0

    # Expand dims for batch dimension: shape (1, height, width, 3)
    return np.expand_dims(normalized, axis=0)
