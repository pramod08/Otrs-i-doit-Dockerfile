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
IDOIT_DIR="/var/www/idoit"
# ----------------------------------------

# DEFAULTS -----------------
IMPORT_DIR="$IDOIT_DIR/imports/"    # Directory of your inventory files
OBJECT_TYPE="10"					# 10 = Client
IMPORT_TYPE="inventory"				# inventory = h-inventory xml files
OVERWRITE="--force"                 # "--force" = Overwrite, "" = Skip existing
# --------------- CONFIG END

cd $IMPORT_DIR
chmod +x import controller

for file in `ls | grep xml`; do
  
  echo "Importing $file.."
  
  # Script example:
  # ./import inventory-export.xml import-type [object-type-id] [--force] [object-id]
  ${IDOIT_DIR}/import ${IMPORT_DIR}${file} $IMPORT_TYPE $OBJECT_TYPE $OVERWRITE

done