#!/bin/bash
if [ -z "$1" ] ; then
   SEARCHSTRING='api|key|user|uname|pw|password|pass|email|credential|login|token|secret'
else
   SEP=""
   SEARCHSTRING=""
   while [ ! -z "$1" ] ; do
      SEARCHSTRING="$SEARCHSTRING$SEP$1"
      SEP="|"
      shift
   done 
fi
echo Search for strings $SEARCHSTRING
git log --all -i -G"$SEARCHSTRING" -p . | grep '^+' | grep -Ei "$SEARCHSTRING" | sort -u 
