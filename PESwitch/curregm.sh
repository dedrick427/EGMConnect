#!/bin/bash
tregm=egm$1
trvlan=`brctl show $tregm | grep 'eth2.'`
echo $trvlan | sed -r 's/^.{5}//'
