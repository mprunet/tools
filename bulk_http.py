#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import sys
import socket, ssl
def connect(domain, port=443, ssl_enable=True):
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.settimeout(socket_timeout)

    connection = ssl.wrap_socket(s, ssl_version=ssl.PROTOCOL_TLSv1_2, cert_reqs=ssl.CERT_NONE) if ssl_enable else s

    try:
        connection.connect((domain, port))
    except socket.error as e:
        print('[!] Abort. {}'.format(e))
        exit()

    return connection

def test_request(domain, port, ssl_enable, request):
    with connect(domain, port, ssl_enable) as connection:
        connection.sendall(request)
        print("===> REQUEST\n")
        print(request.decode(errors='ignore'))
        print("===> RESPONSE\n")
        response = ''

        while True:
            try:
                data = connection.recv(1024)
            except socket.error as e:
                try:
                    connection.shutdown(socket.SHUT_WR)
                    data = connection.recv(1024)
                except:
                    if verbose > 1:
                        print('[!] {}'.format(e))
                    break
                response += data.decode(errors='ignore')
                if verbose > 1:
                    print('[!] {}'.format(e))
                break
            if not data:
                break
            print(data.decode(errors='ignore'))

verbose = 2
socket_timeout = 20
probe_rounds = 1

params=sys.argv
def usage():
    print('[i] Usage: python3 {} http|https request_file [hostname]'.format(sys.argv[0]))
    print('This tools send bulk http/s request based on a file')
    exit(0)

if len(params) < 3:
    usage()

if params[1] == 'https':
    ssl_enable = True
    port = 443
else:
    ssl_enable = False
    port = 80

with open(params[2], 'rb') as file:
    content = file.read()
    if len(params) == 4:
        host=params[3]
    else:
        idx = content.find(b'\nHost: ')
        if idx == -1:
            print("hostname must be specified")
            usage()
        idxEnd = content.find(b'\n', idx+1)
        if idxEnd == -1:
            idxEnd = len(content)
        host=content[idx+7:idxEnd-1].decode('UTF-8')
    print("Connecting to {}\n".format(host))
    test_request(host, port, ssl_enable, content)

