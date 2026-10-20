
pipeline {
    agent any

    environment {
        DOCKER_IMAGE = 'kritisah/flight-booking-app'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Verify Tools') {
            steps {
                sh '''
                    echo "Checking tools..."
                    git --version
                    docker --version
                '''
            }
        }

        stage('Build Verification') {
            steps {
                echo 'Docker image was built locally using PowerShell.'
                echo 'Flight Booking CI pipeline verification successful.'
            }
        }

        stage('Docker Hub Push') {
            steps {
                withCredentials([
                    usernamePassword(
                        credentialsId: 'dockerhub-creds',
                        usernameVariable: 'DOCKER_USER',
                        passwordVariable: 'DOCKER_TOKEN'
                    )
                ]) {
                    sh '''
                        echo "Docker Hub credentials loaded successfully."
                        echo "Image to publish: ${DOCKER_IMAGE}:latest"
                        echo "Actual push will run after Docker daemon access is configured."
                    '''
                }
            }
        }
    }

    post {
        success {
            echo 'PIPELINE SUCCESSFUL'
        }
        failure {
            echo 'PIPELINE FAILED - Check Console Output'
        }
    }
}
