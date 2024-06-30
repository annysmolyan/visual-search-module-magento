import tensorflow as tf
from tensorflow.keras.applications import VGG16
from tensorflow.keras.applications.vgg16 import preprocess_input
from tensorflow.keras.preprocessing import image
import numpy as np

# Load the pre-trained VGG16 model
model = VGG16(weights='imagenet', include_top=False)

# Load and preprocess an image
img_path = '/source/home-main.jpg'
img = image.load_img(img_path, target_size=(224, 224))
img_array = image.img_to_array(img)
img_array = np.expand_dims(img_array, axis=0)
img_array = preprocess_input(img_array)

# Get the image vector (feature vector)
features = model.predict(img_array)
features_flatten = features.flatten()

print("Image vector shape:", features_flatten.shape)
