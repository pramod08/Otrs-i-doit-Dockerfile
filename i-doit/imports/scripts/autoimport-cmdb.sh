#!/bin/bash

# i-doit auto import -------
# 
# Description:
# Automatically imports all xml files in IMPORT_DIR, Can be used to overwrite
# existing (NO SYNC!!) or for an initial mass import.
#
# Can be started periodically via crontab or manually.
#
# Crontab example:
# 0 2 * * * /var/www/idoit/imports/scripts/autoimport.sh > /var/log/idoit-import
#
# author: dennis stuecken (ds) <dstuecken@i-doit.org>
#

# Path to your running i-doit installation
PWD=`pwd`				# Current directory
IDOIT_DIR=`dirname $PWD`		# Path to i-doit
# ----------------------------------------

# DEFAULTS -----------------
USER="admin"				# Login Username
PASS="admin"				# It's Password
MID="3" 				# Mandator ID; Retrieve with /idoit-dir/mandator ls
IMPORT_DIR="$IDOIT_DIR/imports/"	# Directory of your inventory files
IMPORT_TYPE="cmdb"			# Type of the import; cmdb = cmdb-import, inventory = h-inventory import
# --------------- CONFIG END

cd $IMPORT_DIR
chmod +x import controller

for file in `ls | grep xml`; do
  
  echo "Importing $file.."
  
  # Script example:
  ${IDOIT_DIR}/import -u $USER -p $PASS -i $MID ${IMPORT_DIR}${file}

done
