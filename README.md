# Project Setup

To run this project, you need to use Docker. Follow the steps below to get started:

## Requirements

- Docker
- Docker Compose

## Getting Started

1. **Clone the repository**:

    ```sh
    git clone git@github.com:DanielPalmSie/social-network.git
    cd <repository-directory>
    ```

2. **Run the project using Docker Compose**:

    ```sh
    docker-compose up
    ```

   This command will build and start the application along with all its dependencies.


3. **Access the application**:

   The application will be available at [http://application.local](http://application.local).

   **Note**: To access the application via `application.local`, you need to add the following entry to your `/etc/hosts` file (on Linux/Mac) or `C:\Windows\System32\drivers\etc\hosts` file (on Windows):

    ```plaintext
    127.0.0.1 application.local
    ```

## API Documentation

A Postman collection is included in the project. You can find the `social-network.postman_collection.json` file in the root directory. Import this file into Postman to get all the API endpoints and examples.
