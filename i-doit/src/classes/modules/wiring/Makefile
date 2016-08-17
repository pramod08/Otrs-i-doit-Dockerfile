SHELL = /bin/sh
ROOT_DIR := $(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
#REVISION = $(shell cat REVISION)
VERSION = $(shell cat VERSION)
MODULE = $(shell basename $(PWD))
ZIP_FILE="idoit-${MODULE}-${VERSION}.zip"

source:
	cd $(ROOT_DIR)
	if [ -f ${ZIP_FILE} ]; then rm ${ZIP_FILE}; fi
	mkdir -p /tmp/package/src/classes/modules/${MODULE}/
	cp -R * /tmp/package/src/classes/modules/${MODULE}/
	mv /tmp/package/src/classes/modules/${MODULE}/package.json /tmp/package/
	cd /tmp/package/ && zip -x Makefile -rqy ${ZIP_FILE} * && cp -f /tmp/package/${ZIP_FILE} $(ROOT_DIR)
	rm -R /tmp/package

clean:
	rm -f $(ROOT_DIR)/${ZIP_FILE}  2&> /dev/null