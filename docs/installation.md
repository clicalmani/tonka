# Installation

## Create a Tonka Project with Composer

To create a new Tonka project using Composer, follow these steps:

1. **Install Composer**: If you haven't already, download and install Composer from [getcomposer.org](https://getcomposer.org/).

2. **Create a New Project**: Open your terminal and run the following command to create a new Tonka project:

    ```sh
    composer create-project clicalmani/tonka my-tonka-app
    ```

    Replace `my-tonka-app` with the desired name for your project.

3. **Navigate to Project Directory**: Change into the newly created project directory:

    ```sh
    cd my-tonka-app
    ```

4. **Run the Application**: Start the application using the built-in PHP server:

    ```sh
    php tonka dev
    ```

    You may specify the port number by using the `--port` option:

    ```sh
    php tonka dev --port=8080
    ```

    Open your browser and navigate to `http://localhost:8000` to see your new Tonka project in action.

5. **Configuration**: Customize your project by editing the configuration files located in the `config` directory.