#!/bin/bash


clean_first=0
from_dir=''
get_pkgbuild=''
MAKEPKG_ARGS='-sr'

# TODO: avoir un makepkg.conf spécifique
[ -r /etc/makepkg.conf ] && source /etc/makepkg.conf
[ -r /etc/afur-makepkg.conf ] && source /etc/afur-makepkg.conf
[ -r $HOME/.afur-makepkg.conf ] && source $HOME/.afur-makepkg.conf


usage ()
{
	echo 'Usage: '$(basename "$0")' [options] -- [makepkg args]'
	echo "Ce script permet d'envoyer un paquet sur [archlinuxfr]."
	echo 'Sans options, par défaut il le construit.'
	echo
	echo 'Options:'
	echo '	-h	Aide'
	echo '	-c	Supprime les répertoires src/ et pkg/ avant de commencer'
	echo '	-d	Répertoire contenant un paquet pré-construit'
	echo '	-G	Utilisation de yaourt pour récupérer un PKGBUILD'
	exit 1
}

while getopts 'cd:hG:' arg; do
	case "${arg}" in
		c) clean_first=1 ;;
		d) from_dir="$OPTARG" ;;
		h) usage ;;
		G) get_pkgbuild="$OPTARG" ;;
		*) MAKEPKG_ARGS="$MAKEPKG_ARGS -$arg $OPTARG" ;;
	esac
done

MAKEPKG_ARGS="$MAKEPKG_ARGS ${*:$OPTIND}"


clean_dir ()
{
	echo 'Suppression des répertoire src/ et pkg/ dans 5 sec...'
	echo 'Ctrl-C pour arrêter.'
	sleep 5
	rm -r src pkg
}


build_filelist ()
{
	local folder="$1"
	[ -d "$folder" ] || return 1
	file_list=()
	OLD_IFS="$IFS"
	for f in $(find "$folder" -type f -name "*$PKGEXT")
	do
		file_list=("${file_list[@]}" "$f")
	done
	for f in $(find "$folder" -type f -name "*$SRCEXT")
	do
		file_list=("${file_list[@]}" "$f")
	done
	IFS="$OLD_IFS"
}


build ()
{
	makepkg $MAKEPKG_ARGS || return 1
	for pkg in "$PKGDEST"/*; do
		LC_ALL=C namcap $pkg | grep -q '^Err' && return 1
	done
	makepkg --source || return 1
}


send ()
{
	{
		echo 'user "'$USER'" "'$PASSWD'"'
		for f in "${file_list[@]}"
		do
			echo 'put "'$f'"'
		done
		echo 'quit'
	} |	lftp "$SRV_URI" 
	return 1
}

file_list=()
PKGDEST=''
if [ -z "$from_dir" ]; then
	[ $clean_first -eq 1 ] && clean_first
	export PKGDEST=$(mktemp -d) 
	build || exit 1
	build_filelist "$PKGDEST"
else
	build_filelist "$from_dir"
fi

if ! send; then
	echo
	echo "- Erreur lors de l'envoi des fichiers."
	[ -n "$PKGDEST" ] && echo "- Répertoire temporaire non supprimé: $PKGDEST"
	exit 1
fi

[ -n "$PKGDEST" ] && rm -r "$PKGDEST"
echo
echo 'Envoi effectué"




