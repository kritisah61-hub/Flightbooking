
pipeline {
    agent any

    environment {
        DOCKER_IMAGE = 'kritisah/flight-booking-app'
        KUBECONFIG = '/var/jenkins_home/.kube/config'
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
                    set -e
                    git --version
                    docker --version
                    kubectl version --client
                    kubectl get nodes
                '''
            }
        }

        stage('Build Docker Image') {
            steps {
                sh '''
                    set -e
                    docker build -t ${DOCKER_IMAGE}:${BUILD_NUMBER} .
                    docker tag ${DOCKER_IMAGE}:${BUILD_NUMBER} ${DOCKER_IMAGE}:latest
                '''
            }
        }

        stage('Push to Docker Hub') {
            steps {
                withCredentials([
                    usernamePassword(
                        credentialsId: 'dockerhub-creds',
                        usernameVariable: 'DOCKER_USER',
                        passwordVariable: 'DOCKER_TOKEN'
                    )
                ]) {
                    sh '''
                        set -e
                        echo "$DOCKER_TOKEN" | docker login \
                            -u "$DOCKER_USER" --password-stdin
                        docker push ${DOCKER_IMAGE}:${BUILD_NUMBER}
                        docker push ${DOCKER_IMAGE}:latest
                        docker logout
                    '''
                }
            }
        }

        stage('Deploy to Kubernetes') {
            steps {
                sh '''
                    set -e
                    kubectl set image deployment/flight-booking \
                        flight-booking=${DOCKER_IMAGE}:${BUILD_NUMBER}

                    kubectl rollout status deployment/flight-booking \
                        --timeout=180s

                    kubectl get deployment flight-booking -o wide
                    kubectl get pods
                    kubectl get service flight-booking-service
                '''
            }
        }
    }

    post {
        success {
            echo 'SUCCESS: Docker image pushed and Kubernetes deployment verified.'
        }
        failure {
            echo 'Pipeline failed. Check Console Output for the failing stage.'
        }
    }
}
