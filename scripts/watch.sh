#!/bin/bash

SOFT_DIR="$HOME/afur"

UPLOAD_DIR="$SOFT_DIR/ftp"
WORK_DIR='$HOME/tmp/afur'
ARCH=('i686' 'x86_64' 'arm')
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
	[ $VERBOSE -eq 1 ] && echo $(date +"%Y%m%d %H:%M:%S") "$@"
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
	pushd "$PKG_DIR"/"$1" &> /dev/null
	log "+ en attente du verrou repo-add..."
	while [ -f "$REPO_NAME".db.tar.gz.lck ]; do sleep 1; done
	repo-add "$REPO_NAME".db.tar.gz "$2" &> /dev/null && log "+ ajout de $2" || return 1
	popd &> /dev/null
}




link_any_repo ()
{
	for arch in "${ARCH[@]}"
	do
		# Un ln -sf active un évenement "delete", donc ln -s &> /dev/null
		# TODO penser à nettoyer les liens obsolètes
		ln -s ../any/"$1" "$PKG_DIR"/"$arch" &> /dev/null
		(($2)) && add_repo "$arch" "$1"
	done
}

add_any_repo ()
{
	link_any_repo "$1" 1
	add_repo "any" "$1"
}

del_repo ()
{
	pushd "$PKG_DIR"/"$1" &> /dev/null
	log "+ en attente du verrou repo-remove..."
	while [ -f "$REPO_NAME".db.tar.gz.lck ]; do sleep 1; done
	repo-remove "$REPO_NAME".db.tar.gz "$2" &> /dev/null && log "+ suppression de $2" || return 1
	popd &> /dev/null
}

del_any_repo ()
{
	for arch in "${ARCH[@]}"
	do
		rm "$PKG_DIR/$arch/$1"
	done
	del_repo "any" "$1"
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
		mv "$archive" "$PKG_DIR"/any &> /dev/null
		add_any_repo "$archive"
	else
		log "+ $arch"
		in_array "$arch" "${ARCH[@]}" || return 1
		log "+ copie dans le dépôt"
		mv "$archive" "$PKG_DIR"/"$arch" &> /dev/null
		add_repo "$arch" "$archive"
	fi
	return 0
}

sig_archive ()
{
	local archive=$1
	log "+ ajout de la signature"
	arch=${archive%%.pkg.tar.*}
	arch=${arch##*-}
	if [ "$arch" = "any" ]; then
		log "+ any"
		mv "$archive" "$PKG_DIR"/any &> /dev/null
		link_any_repo "$1" 0
	else
		log "+ $arch"
		in_array "$arch" "${ARCH[@]}" || return 1
		log "+ copie dans le dépôt"
		mv "$archive" "$PKG_DIR"/"$arch" &> /dev/null
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
	# Teste si le fichier n'a pas été effacé entre temps
	[ ! -e "$archive" ] && return 1
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
	mv "$archive" "$tmp_dir" &> /dev/null
	pushd "$tmp_dir" &> /dev/null
	if [[ "$file" != "${file%.sig}" ]]; then
		sig_archive "$file"
	elif [ "$file" != "${file%pkg.tar.*}" ]; then
		pkg_archive "$file" "$user"
	else
		src_archive "$file"
	fi
	ret=$?
	popd &> /dev/null
	rm -rf $tmp_dir
	return $ret
}

watch_upload ()
{
	while read -u 3 archive
	do
		[ "$archive" = "$UPLOAD_DIR/exit" ] && break
		new_archive "$archive"
	done 3< <(tail --pid=$SELF_PID -f "$1")
}

watch_pkg ()
{
	while read -u 3 archive
	do
		[ -z "$archive" ] && continue
		# Teste si le fichier n'a pas été effacé entre temps
		[ ! -e "$archive" ] || continue
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
	done 3< <(tail --pid=$SELF_PID -f "$1")
}

tmp_upload=$(mktemp)
tmp_pkg=$(mktemp)

safe_quit ()
{
	touch "$UPLOAD_DIR/exit"
	kill $pit1 $pit2
	wait $pit1 $pit2 2> /dev/null
	rm "$tmp_upload" "$tmp_pkg"
}
trap "safe_quit" 0
SELF_PID=$$
$HOME/bin/inotify-tree "$UPLOAD_DIR" >> "$tmp_upload" &
pit1=$!
inotifywait --exclude="$REPO_NAME.db.tar.gz" \
            --exclude="$REPO_NAME.db" \
            -r -q -e delete --format "%w%f" -m "$PKG_DIR" >> "$tmp_pkg" &
pit2=$!
watch_upload "$tmp_upload" &
watch_pkg "$tmp_pkg"

