from flask import Flask, request, Response

server = Flask(__name__)
bag = {}


@server.route('/')
def home():
    global bag

    return "\n".join([f"{k}\t{bag[k]}" for k in bag.keys()])


@server.route('/<key>', methods=['GET'])
def get_data(key):
    global bag

    return bag[key] if key in bag else f'Not found', 404


@server.route('/<key>', methods=['POST'])
def set_data(key):
    global bag

    bag[key] = request.get_data(False, True)
    return bag[key]


if __name__ == '__main__':
    server.run()
