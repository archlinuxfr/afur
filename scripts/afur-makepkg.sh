#!/bin/bash

[ -f /etc/afur-makepkg.conf ] && source /etc/afur-makepkg.conf
[ -f $HOME/.afur-makepkg.conf ] && source $HOME/.afur-makepkg.conf

# TODO une config à faire
build ()
{
	makepkg -s || return 1
	for pkg in "$PKGDEST"/*; do
		LC_ALL=C namcap $pkg | grep -q '^Err' && return 1
	done
	makepkg --source || return 1
}


send ()
{
	lftp "$SRV_URI" << EOF
user "$USER" "$PASSWD"
mput "$PKGDEST/*"
quit
EOF
}

export PKGDEST=$(mktemp -d) 
if build; then
	if send; then
		echo "GOOD"
	fi
fi

rm -rf "$PKGDEST"


