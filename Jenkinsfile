
pipeline {
    agent any

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Verify Tools') {
            steps {
                sh '''
                    echo "Checking available tools..."
                    git --version
                    docker --version
                    kubectl version --client
                '''
            }
        }

        stage('Build Docker Image') {
            steps {
                echo "Docker image was built locally using PowerShell."
                sh '''
                    echo "Jenkins Docker CLI is installed."
                    docker --version
                '''
            }
        }

        stage('Pipeline Complete') {
            steps {
                echo 'Source checkout and tool verification completed successfully.'
            }
        }
    }
}
