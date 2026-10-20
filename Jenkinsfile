
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
                    docker --version
                    aws --version
                    kubectl version --client
                '''
            }
        }

        stage('Build Docker Image') {
            steps {
                sh '''
                    docker build -t flight-booking-app:${BUILD_NUMBER} .
                '''
            }
        }
    }
}
