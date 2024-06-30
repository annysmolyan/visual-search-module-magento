import tensorflow as tf
from tensorflow.keras.applications import InceptionV3
from tensorflow.keras.applications.inception_v3 import preprocess_input, decode_predictions
from tensorflow.keras.preprocessing import image
import numpy as np

# Load the pre-trained InceptionV3 model
# Documentation: https://www.tensorflow.org/api_docs/python/tf/keras/applications/inception_v3/InceptionV3

# Default vector dimension = 1000. Use this value for elasticsearch dimension param in magento
# see BelSmol\VisualSearch\Model\Manager\AiManager::getCnnModelVectorDimension method
class InceptionV3FeatureExtractor:
    # Define a class-level constant
    MODEL_CODE = 'InceptionV3'

    def __init__(self):
        self.model = InceptionV3(weights='imagenet', classes=1000)

    # Return image feature
    def getImageFeature(self, image_path):
        img = image.load_img(image_path, target_size=(299, 299))
        img_array = image.img_to_array(img)
        img_array = np.expand_dims(img_array, axis=0)
        img_array = preprocess_input(img_array)

        features = self.model.predict(img_array)
        features_flatten = features.flatten()

        return features_flatten  # features array is here: features_flatten.shape
