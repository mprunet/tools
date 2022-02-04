#!/bin/bash
CONTINUER=1
ALL=0
SORT=0
while [ $CONTINUER == 1 ] ; do
   case $1 in
      --sort)
         SORT=1
      ;;
      --all)
         ALL=1
      ;;
      --)
         CONTINUER=0
      ;;
      *)
         CONTINUER=0
         break
      ;;
   esac
   shift
done
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
echo ALL $ALL
echo SORT $SORT
echo Search for strings $SEARCHSTRING
if [ $ALL == 0 ] ; then
   if [ $SORT == 1 ] ; then
      git log --all -i -G"$SEARCHSTRING" -p . | grep '^+' | grep -Ei "$SEARCHSTRING" | sort -u 
   else
      git log --all -i -G"$SEARCHSTRING" -p . | grep '^+' | grep --color -Ei "$SEARCHSTRING"
   fi
else
   if [ $SORT == 1 ] ; then
      git log --all -p . | grep -Ei "$SEARCHSTRING" | sort -u 
   else
      git log --all -p . | grep --color -Ei "$SEARCHSTRING"
   fi
fi

