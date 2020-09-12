#!/usr/bin/python3
import sys
import base64

token=sys.argv[1].split('.')
token[2]=base64.urlsafe_b64decode(token[2]+"===").hex()
print("{}.{}#{}".format(token[0],token[1],token[2]))
