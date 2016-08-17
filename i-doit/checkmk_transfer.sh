#!/bin/bash

## This file is a component of the i-doit framework
## Website: http://www.i-doit.org/
## Licence: http://www.i-doit.com/license
## Copyright: synetics GmbH

#Edit these variables to fit your environment

idoit_login="admin"                     #i-doit login user
idoit_pass="admin"                      #i-doit password
idoit_structure=3                       #Export i-doit locations to Check_MK? 0=No 1=Physical 2=Logical 3=ObjectType
checkmk_export_path="checkmk_export"    #Check_MK Export path as defined in the i-doit system settings, relative to the idoit_base path
omd_site="idoit"                        #Name of the target OMD site
omd_host="1.2.3.4"                      #IP-Address or DNS of the Check_MK host

#System variables, only change when necessary

tmp=/tmp
idoit_base=$(dirname $0)                #Webserver base path for i-doit without trailing slash
scp=$(which scp)
ssh=$(which ssh)
md5sum=$(which md5sum)
php=$(which php)
find=$(which find)
cat=$(which cat)
grep=$(which grep)
xargs=$(which xargs)
awk=$(which awk)

md5_check=1

if [ $# -eq 0 ]; then
	md5_check=1
else
	if [ $1 == "--force" ]; then
		echo "Forcing the transfer"
		md5_check=0
	fi
fi

#Export the config
${php} ${idoit_base}/controller.php -v -u ${idoit_login} -p ${idoit_pass} -i 1 -m check_mk_export -x ${idoit_structure} > temp/check-mk-transfer.log

#Initialize result
md5_result=1

if [ ${md5_check} == 1 ]; then

	if [ -f "${tmp}/${checkmk_export_path}.md5" ]; then

		md5_old=`${cat} ${tmp}/${checkmk_export_path}.md5 |${awk} '{print $1}'`

		if [ -d "${idoit_base}/${checkmk_export_path}/" ]; then

			md5_act=`${find} ${idoit_base}/${checkmk_export_path}/ -type f|${grep} -v \.zip |${grep} -v \.tar\.gz |${grep} -v \.tar |${xargs} cat |${md5sum} |${awk} '{print $1}'`

			if [ "${md5_act}" != "" ]; then

				echo ${md5_act} > ${tmp}/${checkmk_export_path}.md5

				if [ "${md5_act}" == "${md5_old}" ]; then
					md5_result=0
				else
					md5_result=1
				fi

			else

				echo "Error: Could not create MD5 Checksum of Check-MK exports. You need to export the Check-MK configuration first."
				exit 0;

			fi

		else

			echo "Error: Check-MK export path ${idoit_base}/${checkmk_export_path}/ does not exist. Please create it and start the Check-MK config file export."

			exit 0;

		fi

	fi

fi

if [ ${md5_result} == 1 ]; then
	if [ -f "${idoit_base}/${checkmk_export_path}/${checkmk_export_path}.zip" ]; then

		${scp} ${idoit_base}/${checkmk_export_path}/${checkmk_export_path}.zip ${omd_site}@${omd_host}:/omd/sites/${omd_site}/etc/check_mk/conf.d/wato
		${ssh} ${omd_site}@${omd_host} "cd etc/check_mk/conf.d/wato/ ;rm -rf ${checkmk_export_path}/* >/dev/null 2>&1;mkdir ${checkmk_export_path} >/dev/null 2>&1; unzip -d ${checkmk_export_path} ${checkmk_export_path}.zip && rm -f ${checkmk_export_path}.zip && mv ${checkmk_export_path}/idoit_hosttags.mk ~/etc/check_mk/multisite.d/ && echo $(date +%s) - i-doit i-doit i-doit Export Konfiguration aktualisiert >> ~/var/check_mk/wato/log/pending.log"

		echo "No errors during transfer!"

	else

		echo "Error: Check-MK export file ${checkmk_export_path}.zip does not exist"

	fi
else
	echo Nothing changed.
fi
