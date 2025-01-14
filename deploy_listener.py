from flask import Flask, request
import os

app = Flask(__name__)

@app.route('/deploy', methods=['POST'])
def deploy():
    with open('/home/user/htdocs/srv633900.hstgr.cloud/deploy_listener.log', 'a') as log_file:
        log_file.write(f"Webhook triggered at {request.json}\n")
    os.system('/home/user/htdocs/srv633900.hstgr.cloud/deploy.sh >> /home/user/htdocs/srv633900.hstgr.cloud/deploy.log 2>&1')
    return "Deployment initiated", 200

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000)

