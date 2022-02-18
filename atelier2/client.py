from socket import socket, AF_INET, SOCK_STREAM
from struct import pack, unpack

PORT = 0x2BAD
SERVER = "127.0.0.1"


if __name__ == '__main__':
    with socket(AF_INET, SOCK_STREAM) as sock:
        sock.connect((SERVER, PORT))
        num = unpack('!i', sock.recv(4))[0]
        print(f"You're player {num}")
        print("Waiting for an opponent...")
        unpack('?', sock.recv(1))[0]
        print("The match will begin")
        while True:
            content = input("0:Pierre, 1:papier, 2:Ciseau or Q:Quit :").lower()
            if content == "q":
                break
            elif content in ['0', '1', '2']:
                sock.send(pack('i', int(content)))
                enemy_choice = unpack('i', sock.recv(4))[0]
                score_num = unpack('!i', sock.recv(4))[0]
                if enemy_choice == 0:
                    print("Your enemy took rock, here are the scores :")
                elif enemy_choice == 1:
                    print("Your enemy took paper, here are the scores :")
                elif enemy_choice == 2:
                    print("Your enemy took scissors, here are the scores :")
                for i in range(1, score_num+1):
                    score = unpack('!i', sock.recv(4))[0]
                    print(f"- Player {i}{' (you)' if i == num else ''} : {score if score>=0 else '-'}")
        sock.close()