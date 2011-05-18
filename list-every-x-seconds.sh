#! /bin/sh

interval=5;
terminate=600;
destination='';

if [ ! -z $1 ]
then	interval=$1;
fi;

if [ ! -z $2 ]
then	terminate=$2;
fi;

if [ ! -z $3 ]
then	destination=$3;
fi;

now=$(date +%s);
soon=$(( $now + $interval ));
end=$(( $now + $terminate ));


ls -lh $destination > vid.log;
tail -n1 vid.log;

while [ $now -lt $end ]
do	if [ $now -eq $soon ]
	then	ls -hl $destination >> vid.log;
		tail -n1 vid.log;
		soon=$(( $now + $interval ));
	fi;
	now=$(date +%s);
done;
halt;
exit;

