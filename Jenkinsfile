
pipeline {
    agent any

    environment {
        AWS_REGION = 'ap-south-1'
        ECR_REPO = '799093034353.dkr.ecr.ap-south-1.amazonaws.com/flight-booking-management-system'
        IMAGE_TAG = "${BUILD_NUMBER}"
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build Docker Image') {
            steps {
                bat 'docker build -t flight-booking-app:%BUILD_NUMBER% .'
            }
        }

        stage('Tag and Push to ECR') {
            steps {
                withCredentials([[$class: 'AmazonWebServicesCredentialsBinding',
                    credentialsId: 'aws-credentials']]) {
                    bat '''
                    aws ecr get-login-password --region %AWS_REGION% | docker login --username AWS --password-stdin %ECR_REPO%
                    docker tag flight-booking-app:%BUILD_NUMBER% %ECR_REPO%:%BUILD_NUMBER%
                    docker push %ECR_REPO%:%BUILD_NUMBER%
                    '''
                }
            }
        }

        stage('Deploy to Kubernetes') {
            steps {
                bat '''
                kubectl set image deployment/flight-booking flight-booking=%ECR_REPO%:%BUILD_NUMBER%
                kubectl rollout status deployment/flight-booking --timeout=120s
                kubectl get pods
                '''
            }
        }
    }
}
