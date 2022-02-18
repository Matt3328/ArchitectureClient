import random
from socket import socket, AF_INET, SOCK_STREAM
from struct import pack, unpack
from threading import Thread, Event

PORT = 0x2BAD
players = []
ready_event = Event()
change_index = 1
all_choice = []


class Player(Thread):
    def __init__(self, num, sock):
        Thread.__init__(self)
        self._id = num
        self._score = 0
        self._choice = None
        self._sock = sock

    def is_ready(self):
        return self._choice is not None

    def get_score(self):
        return self._score

    def run(self):
        global ready_event, change_index

        self._sock.send(pack('!i', self._id))
        while len(players) != 2:
            pass
        self._sock.send(pack('?', True))
        while True:
            data = self._sock.recv(4)
            if len(data) == 0:
                players[self._id - 1] = None
                print(f"- Player {self._id} left")
                return
            self._choice = unpack("i", data)[0]
            all_choice.append(self._choice)
            index_choice = change_index
            change_index = 0
            if all_players_ready():
                ready_event.set()
                ready_event.clear()
            else:
                ready_event.wait()
            if who_win(str(self._choice), str(all_choice[index_choice])) == "Win":
                self._score += 1
            self._choice = None
            self._sock.send(pack('i', all_choice[index_choice]))
            self._sock.send(pack('!i', len(players)))
            for player in players:
                self._sock.send(pack('!i', -1 if player is None else player.get_score()))
            all_choice.clear()
            change_index = 1


def all_players_ready():
    global players

    for player in players:
        if player is not None and not player.is_ready():
            return False
    return True


def who_win(choice, enemy_choice):
    if choice == "0":
        if enemy_choice == "0":
            return "Equality"
        if enemy_choice == "1":
            return "Lost"
        if enemy_choice == "2":
            return "Win"
    if choice == "1":
        if enemy_choice == "0":
            return "Win"
        if enemy_choice == "1":
            return "Equality"
        if enemy_choice == "2":
            return "Lost"
    if choice == "2":
        if enemy_choice == "0":
            return "Lost"
        if enemy_choice == "1":
            return "Win"
        if enemy_choice == "2":
            return "Equality"


def find_player_id():
    global players

    for i in range(len(players)):
        if players[i] is None:
            return i + 1
    players.append(None)
    return len(players)


if __name__ == '__main__':
    with socket(AF_INET, SOCK_STREAM) as sock_listen:
        sock_listen.bind(('', PORT))
        sock_listen.listen(5)
        print(f"Listening on port {PORT}")
        while True:
            sock_service, client_addr = sock_listen.accept()
            index = find_player_id()
            print(f"- Player {index} arrived")
            players[index - 1] = Player(index, sock_service)
            players[index - 1].start()
