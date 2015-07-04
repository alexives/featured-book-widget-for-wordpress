#! /bin/bash

if [[ "${1}" == "-t" ]]; then
	BUILD=false
elif [[ "${1}" == "-b" ]]; then
	TEST=false
fi

if [[ "${TEST}" != "false" ]]; then
	echo "Testing"
fi

if [[ "${BUILD}" != "false" ]]; then
	echo "Building plugin."
	temp=${TMPDIR}$(uuidgen)/featured-book-widget
	mkdir -p $temp
	cp assets/ReadMe.txt $temp
	cp -a src/main/ $temp
	rm -rf release/
	mkdir release/
	zip -r release/featured-book-widget.zip $temp
	rm -rf $temp
fi
