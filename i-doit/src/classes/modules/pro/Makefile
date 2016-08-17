SHELL = /bin/sh
ROOT_DIR := $(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
VERSION = $(shell php --run 'include("../../../version.inc.php"); echo($$g_product_info["version"]);')
ZIP_FILE="idoit-pro-module-${VERSION}.zip"

source:
	cd $(ROOT_DIR)
	if [ -f ${ZIP_FILE} ]; then rm ${ZIP_FILE}; fi
	mkdir -p /tmp/package/src/classes/modules/pro/
	cp -R * /tmp/package/src/classes/modules/pro/

	mkdir -p /tmp/package/src/classes/modules/check_mk/lang
	cp ../check_mk/lang/* /tmp/package/src/classes/modules/check_mk/lang

	mkdir -p /tmp/package/src/classes/modules/monitoring/lang
	cp ../monitoring/lang/* /tmp/package/src/classes/modules/monitoring/lang

	mkdir -p /tmp/package/src/classes/modules/dashboard
	cp -R ../dashboard/* /tmp/package/src/classes/modules/dashboard

	mkdir -p /tmp/package/src/classes/modules/jdisc
	cp -R ../jdisc/* /tmp/package/src/classes/modules/jdisc

	mkdir -p /tmp/package/src/classes/modules/qrcode/lang
	cp -R ../qrcode/lang/* /tmp/package/src/classes/modules/qrcode/lang

	mkdir -p /tmp/package/src/lang
	cp -R ../../../lang/de* /tmp/package/src/lang/

	echo " ${VERSION}" >> /tmp/package/src/classes/modules/pro/README
	mv /tmp/package/src/classes/modules/pro/package.json /tmp/package/
	cd /tmp/package/ && zip -x Makefile -rqy ${ZIP_FILE} * && cp -f /tmp/package/${ZIP_FILE} $(ROOT_DIR)
	rm -R /tmp/package
