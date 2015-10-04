# /bin/bash

rtusrvl=eth12.$2
rtegm=eth11.$1
rtusrbr=usr$2
if ! ifconfig $rtusrbr ; then
	brctl addbr $rtusrbr
fi
ifconfig $rtusrbr up
ip addr flush dev $rtusrbr
if ! ifconfig $rtusrvl ; then
	vconfig add eth12 $2
fi
testbrif=`brctl show $rtusrbr | grep eth12`
if [[ "" == $testbrif ]]; then
	brctl addif $rtusrbr $rtusrvl
fi
ifconfig $rtusrvl up
ip addr flush dev $rtusrvl
if ifconfig $rtegm ; then
	ifconfig $rtegm down
	tstbr=`brctl show | grep usr | cut -c 1-7`
	for u in $tstbr
	do
		tstval=`brctl show $u | grep eth11 | sed -n 's/.*\(eth11.[0-9][0-9][0-9]*\).*/\1/p'`
		for e in $tstval
		do
			if [ $e == $rtegm ] ; then
				brctl delif $u $rtegm
			fi
		done
	done
	else
		vconfig add eth11 $1
fi

ifconfig $rtegm up
ip addr flush dev $rtegm
brctl addif $rtusrbr $rtegm
