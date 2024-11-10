from flask import Flask, request
import os

app = Flask(__name__)

@app.route('/deploy', methods=['POST'])
def deploy():
    # Execute the deployment script
    os.system('/home/user/htdocs/srv633900.hstgr.cloud/deploy.sh')
    return "Deployment completed", 200

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000)
