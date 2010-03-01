#!/bin/bash

SOFT_DIR='/home/afur/afur'

UPLOAD_DIR="$SOFT_DIR/ftp"
WORK_DIR='/tmp/afur'
ARCH=('i686' 'x86_64')
PKG_DIR="$SOFT_DIR/html/pkg"
PKGBUILD_DIR="$SOFT_DIR/html/pkgbuild"
REPO_NAME='archlinuxfr'

PARSE_CMD="$SOFT_DIR/scripts/parse_file"


[ -n "$1" ] && [ "$1" = "-q" ] && VERBOSE=0 || VERBOSE=1
[ -d "$WORK_DIR" ] || mkdir -p "$WORK_DIR" || exit 1
[ -e "$UPLOAD_DIR/exit" ] && rm "$UPLOAD_DIR/exit"

for arch in "${ARCH[@]}" 
do
	[ -d "$PKG_DIR/$arch" ] || mkdir -p "$PKG_DIR/$arch" || exit 1
done
[ -d "$PKG_DIR/any" ] || mkdir -p "$PKG_DIR/any" || exit 1
	
log ()
{
	[ $VERBOSE -eq 1 ] && echo "$@"
}

in_array ()
{
	local needle=$1
	shift
	for e in "$@"
	do
		[ "$e" = "$needle" ] && return 0
	done
	return 1
}

add_repo ()
{
	pushd "$PKG_DIR"/"$1"
	while [ -f "$REPO_NAME".db.tar.gz.lck ]; do sleep 1; done
	repo-add "$REPO_NAME".db.tar.gz "$2"
	popd
}




add_any_repo ()
{
	for arch in "${ARCH[@]}" 
	do
		ln -sf "$PKG_DIR"/any/"$1" "$PKG_DIR"/"$arch"
		add_repo "$arch" "$1"
	done
}

del_repo ()
{
	pushd "$PKG_DIR"/"$1"
	while [ -f "$REPO_NAME".db.tar.gz.lck ]; do sleep 1; done
	repo-remove "$REPO_NAME".db.tar.gz "$2"
	popd
}

del_any_repo ()
{
	for arch in "${ARCH[@]}" 
	do
		rm -v "$PKG_DIR/$arch/$1"
	done
}

pkg_archive ()
{
	local archive="$1"
	local user="$2"
	log "+ extraction .PKGINFO"
	bsdtar -xf "$archive" ".PKGINFO" || return 1
	log "+ ajout de l'archive dans afur"
	local arch=$($PARSE_CMD "$archive" .PKGINFO "$user")
	[ $? -ne 0 ] && return 1
	log "+ test $arch --"
	if [ "$arch" = "any" ]; then
		log "+ any"
		mv "$archive" "$PKG_DIR"/any
		add_any_repo "$archive"
	else
		log "+ $arch"
		in_array "$arch" "${ARCH[@]}" || return 1
		log "+ copie dans le dépôt"
		mv "$archive" "$PKG_DIR"/"$arch"
		add_repo "$arch" "$archive"
	fi
	return 0
}

src_archive ()
{
	local archive="$1"
	local pkg=${archive%-*-*.src.tar.*}
	log "+ copie PKGBUILD de '$pkg'"
	[ -d "$PKGBUILD_DIR"/"$pkg" ] && rm -rf "$PKGBUILD_DIR"/"$pkg"
	bsdtar -xf "$archive" -C "$PKGBUILD_DIR"
}

new_archive ()
{
	local archive="$1"
	local ret=0
	[ -z "$archive" ] && return 1
	log "+ récéption de '$archive'"
	local file=${archive##*/}
	local user=${archive%/*}
	local user=${user##*/}
	log "+ fichier '$file' de '$user'"
	
	if [ "$file" = "${file%pkg.tar.*}" -a "$file" = "${file%src.tar.*}" ]
	then
		log "- '$file' n'est pas un format connu -> suppression"
		rm "$archive" 
		return 1
	fi
	cd "$WORK_DIR"
	local tmp_dir=$(mktemp -d --tmpdir="$WORK_DIR")
	mv "$archive" "$tmp_dir"
	pushd "$tmp_dir"
	if [ "$file" != "${file%pkg.tar.*}" ]
	then
		pkg_archive "$file" "$user"
	else
		src_archive "$file"
	fi
	ret=$?
	rm -rf $tmp_dir
	return $ret
}

watch_upload ()
{
	tail --pid=$$ -f "$1" | while read archive
	do
		[ "$archive" = "$UPLOAD_DIR/exit" ] && break 
		new_archive "$archive"
	done
}

watch_upload_dir ()
{
	local p=
	inotifywait -r -q -e close_write --format "%w%f" -m "$UPLOAD_DIR" >> "$1" &
	p=$!
	while :
	do
		inotifywait -r -q -e create --format '%e' "$UPLOAD_DIR" | grep -q 'CREATE,ISDIR'
		if [ -e "$UPLOAD_DIR/exit" ]; then
			kill $p
			return 0
		fi
		if [ $? -eq 0 ]; then
			kill $p
			inotifywait -r -q -e close_write --format "%w%f" -m "$UPLOAD_DIR" >> "$1" &
			p=$!
		fi
	done
}

watch_pkg ()
{
	inotifywait -r -q -e delete --format "%w%f" -m "$PKG_DIR" | while read archive
	do
		[ -z "$archive" ] && continue
		local file=${archive##*/}
		local arch=${archive%/*}
		local arch=${arch##*/}
		log "++ suppression de '$file' de '$arch'"
		if [ "$file" = "${file%pkg.tar.*}" ]
		then
			log "- '$file' n'est pas un format connu"
			continue
		fi
		pkg=${file%-*-*-*.pkg.tar.*}
		if [ "$arch" = "any" ]; then
			del_any_repo "$file"
		else
			del_repo "$arch" "$pkg"
		fi
	done
}

tmp_upload=$(mktemp)

safe_quit ()
{
	touch "$UPLOAD_DIR/exit"
	rm "$tmp_upload"
}

watch_upload_dir "$tmp_upload" & 
watch_upload "$tmp_upload" & 
trap "safe_quit" 0
watch_pkg 

