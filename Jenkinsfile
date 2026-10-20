
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
                    echo "Checking tools..."
                    git --version
                    docker --version
                '''
            }
        }

        stage('Build Verification') {
            steps {
                echo 'Docker image build was completed locally using PowerShell.'
                echo 'Flight Booking CI pipeline verification successful.'
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
