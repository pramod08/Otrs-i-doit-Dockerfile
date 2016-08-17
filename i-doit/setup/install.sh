#!/bin/bash
# author dennis stücken <dstuecken@i-doit.org>

host="localhost"
port="3306"
sed=`which sed`
pwd=`pwd`
base=`dirname ${pwd}/ | sed -e 's/\//\\\\\//g'`
admincenterpw=""
pass=""

usage()
{
	cat << EOF
i-doit command line installer

Usage: $0 options
Example: $0 -m idoit_data_install -s idoit_system_install -n "install-test"

OPTIONS:
   -d  db_suffix
   -n  mandator name
   -s  system database name
   -m  mandator database name
   -h  mysql host (default: localhost)
   -p  mysql root password
   -P  mysql port (default: 3306)
   -a  set admin center password (default: empty)
   -r  revert (uninstall)
   -h  help
   -q  quiet mode
EOF
}

# initial config
REVERT="0"
QUIET="0"

while getopts ":d:m:s:n:p:P:a:hrq" opt; do
	case $opt in
	    d)
			db_suffix=$OPTARG
			;;
		m)
			idoit_data=$OPTARG
			;;
		s)
			idoit_system=$OPTARG
			;;
		n)
			mandator_name=$OPTARG
			;;
		a)
			admincenterpw=$OPTARG
			;;
		h)
			host=$OPTARG
			;;
		p)
			pass=$OPTARG
			;;
		P)
			port=$OPTARG
			;;
		q)
			QUIET="1"
			;;
		r)
			REVERT="1"
			;;
		h)
			usage
			exit 0
			;;
		:)
			echo
			echo
			echo "Missing option argument for -$OPTARG" >&2
			echo
			echo
			usage
			exit 1
			;;
		\?)
			echo "Invalid option: -$OPTARG" >&2
			;;
    esac
done

if [ "$REVERT" = "1" ]; then
	# REVERT:
	mysql -uroot -p"$pass" -e "DROP DATABASE ${idoit_data};" 2>/dev/null
	mysql -uroot -p"$pass" -e "DROP DATABASE ${idoit_system};" 2>/dev/null
	echo "Databases ${idoit_data} and ${idoit_system} dropped."

	exit 0;
fi

if [ "$QUIET" = "0" ]; then
	echo "Welcome to the i-doit command line setup."
	echo "Specify your configuration:"
fi

if [ "`which mysql 2>/dev/null`" ]; then
	mysql="`which mysql`"
else
	echo "Could not autodetect mysql client location."
	echo "Enter your path to the mysql binary manually."
	until [ -n "$mysql" ]; do
		echo -n "Path: [/usr/bin/mysql]: "
		read mysql
		if [ -z "$mysql" ]; then
			mysql="/usr/bin/mysql"
			if [ ! -e "$mysql" ]; then
				echo "mysql does not exist in $mysql."
				unset mysql
			fi
			continue
		fi
	done
	
	if [ ! -e "$mysql" ]; then
		 echo "The mysql client does not exist in $mysql."
		 exit 0
	fi
fi

if [ "$QUIET" = "0" ]; then
	echo ""
fi

until [ -n "$idoit_data" ]; do
	echo -n "Mandator Database: [idoit_data${db_suffix}]: "
	read idoit_data
	if [ -z "$idoit_data" ]; then
		idoit_data="idoit_data${db_suffix}"
		continue
	fi
done

if [ "$QUIET" = "0" ]; then
	echo ""
fi

until [ -n "$idoit_system" ]; do
	echo -n "System Database: [idoit_system${db_suffix}]: "
	read idoit_system
	if [ -z "$idoit_system" ]; then
		idoit_system="idoit_system${db_suffix}"
		continue
	fi
done

if [ "$QUIET" = "0" ]; then
	echo ""
fi

until [ -n "$mandator_name" ]; do
	echo -n "Mandator name: [synetics gmbh]: "
	read mandator_name
	if [ -z "$mandator_name" ]; then
		mandator_name="synetics gmbh"
		continue
	fi
done

if [ "$QUIET" = "0" ]; then
	until [ -n "$pass" ]; do
		echo -n "MySQL root password: []: "

		stty -echo
		read pass
		stty echo

		if [ -z "$pass" ]; then
			pass="^^"
			continue
		fi
	done
fi

if [ "$pass" = "^^" ]; then
	unset pass
fi

if [ "$QUIET" = "0" ]; then
	echo ""
	echo "i-doit will be installed using the following databases: "
	echo "Mandator: ${idoit_data}, System: ${idoit_system}"
	echo "MySQL host: ${host}:${port}"
	echo ""

	until [ -n "$go" ]; do
	  echo -n "Continue? [Y]es [N]o: "
	  read go
	  case $go in
	    [Nn])
	      exit 0
	      continue
	      ;;
	    [Yy])
	      echo " "
	      go=n
	      ;;
	    *)
	     unset go
	     continue
	     ;;
	  esac
	done
	unset go

	echo "Installing..."
	echo ""
fi

if [ "$pass" = "" ]; then
	PASSARGUMENT=" "
else
	PASSARGUMENT="--password=$pass "
fi

SQLEXEC="$mysql -uroot ${PASSARGUMENT}-h $host -P $port -N -e"
SQL="$mysql --default-character-set=utf8 -uroot ${PASSARGUMENT}-h $host -P $port -D"

$SQLEXEC "CREATE DATABASE $idoit_data DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;" 2>/dev/null
$SQL $idoit_data < sql/idoit_data.sql 2>/dev/null

$SQLEXEC "CREATE DATABASE $idoit_system DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;" 2>/dev/null
$SQL $idoit_system < sql/idoit_system.sql 2>/dev/null

if [ "$QUIET" = "0" ]; then
	echo "Databases installed. Creating mandator entries.."
fi

$SQL ${idoit_system} -e "REPLACE INTO isys_mandator VALUES (1, '${mandator_name}',  '${mandator_name}',  'cache_${mandator_name}',  'default',  'localhost',  '${port}',  '${idoit_data}',  'root',  '${pass}', '', '1',  '1');"

if [ "$QUIET" = "0" ]; then
	if [ -f ../src/config.inc.php ]; then
		echo "Old configuration file found. Moving to ../src/config.inc.php.old"
		mv -f ../src/config.inc.php ../src/config.inc.php.old
	fi
	echo "Creating config.."
fi

$sed 	-e 's/%config.adminauth.username%/admin/g' \
		-e "s/%config.adminauth.password%/${admincenterpw}/g" \
		-e "s/%config.db.host%/${host}/g" \
		-e "s/%config.db.port%/${port}/g" \
		-e 's/%config.db.username%/root/g' \
		-e "s/%config.db.password%/${pass}/g" \
		-e "s/%config.db.name%/${idoit_system}/g" \
		config_template.inc.php > ../src/config.inc.php

if [ "$QUIET" = "0" ]; then
	echo "Finished. If you have no errors above, the setup is complete."
	echo "You may want to take a look at the auto-generated config.inc.php in ../src/ to check if everything is correct"
	echo ""
	echo "Then you can login to i-doit using the default logins: "
	echo "admin/admin, reader/reader, editor/editor, author/author, archivar/archivar"
fi
exit 0
