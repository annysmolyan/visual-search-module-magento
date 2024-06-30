FROM tensorflow/tensorflow:latest

RUN pip3 install --upgrade pip

# Install Keras and other dependencies
RUN pip3 install \
      blinker==1.4\
      gunicorn \
      flask \
      h5py \
      keras \
      pillow \
      pandas \
      jupyter

# Configure Keras to use TensorFlow for its backend.
env KERAS_BACKEND=tensorflow

WORKDIR /source

EXPOSE 8888
EXPOSE 5000

CMD ["bash"]
