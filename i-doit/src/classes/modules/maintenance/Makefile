SHELL = /bin/sh
ROOT_DIR := $(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
#REVISION = $(shell cat REVISION)
VERSION = $(shell cat VERSION)
ZIP_FILE="idoit-maintenance-${VERSION}.zip"

source:
	cd $(ROOT_DIR)
	if [ -f ${ZIP_FILE} ]; then rm ${ZIP_FILE}; fi
	mkdir -p /tmp/package/src/classes/modules/maintenance/
	cp -R * /tmp/package/src/classes/modules/maintenance/
	mv /tmp/package/src/classes/modules/maintenance/package.json /tmp/package/
	cd /tmp/package/ && zip -x Makefile -rqy ${ZIP_FILE} * && cp -f /tmp/package/${ZIP_FILE} $(ROOT_DIR)
	rm -R /tmp/package
