/*
 *  inotify-tree.c
 *
 *  Copyright (c) 2010-2011 Tuxce <tuxce.net@gmail.com>
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
#define _GNU_SOURCE
#include <stdlib.h>
#include <unistd.h>
#include <stdio.h>
#include <sys/inotify.h>
#include <sys/select.h>
#include <dirent.h>
#include <string.h>
#include <glib.h>

#include <errno.h>

#define EVENT_SIZE	(sizeof (struct inotify_event))
#define BUF_LEN		(1024 * (EVENT_SIZE + 16))


extern int errno;

GHashTable *wds = NULL;
GHashTable *folders = NULL;

void no_watch_dir  (int fd, const char *dir, int self)
{
	int *wd;

	GList *to_remove = NULL;
	GList *folders_old;
	GList *l;

	folders_old = g_hash_table_get_keys (folders);
	for (l=g_list_first (folders_old); l; l=g_list_next (l))
	{
		if (l->data == strstr (l->data, dir))
		{
			wd = g_hash_table_lookup (folders, l->data);
			if (wd != NULL)
			{
				if (strcmp (l->data, dir) != 0 || !self)
					inotify_rm_watch (fd, *wd);
				to_remove = g_list_append (to_remove, l->data);
			}
		}
	}
	g_list_free (folders_old);
	for (l=g_list_first (to_remove); l; l=g_list_next (l))
	{
		wd = g_hash_table_lookup (folders, l->data);
		if (wd != NULL)
		{
			g_hash_table_remove (folders, l->data);
			g_hash_table_remove (wds, wd);
		}
	}
	g_list_free (to_remove);
}


int watch_dir (int fd, const char *dir)
{
	int *wd;
	char *dir_dup;
	DIR *fdir;
	struct dirent *dir_entry;
	char new_dir[PATH_MAX];
	no_watch_dir (fd, dir, 0);
	wd = (int *) malloc (sizeof (int));
	*wd = inotify_add_watch (fd, dir,
		IN_MODIFY | IN_CREATE | IN_DELETE | IN_MOVED_FROM |
		IN_MOVED_TO | IN_CLOSE_WRITE);
	if (*wd < 0)
	{
		perror ("inotify_add_watch");
		exit (2);
	}
	else
	{
		dir_dup = strdup (dir);
		g_hash_table_insert (wds, wd, (gpointer) dir_dup);
		g_hash_table_insert (folders, (gpointer) dir_dup, wd);
	}
	if ((fdir = opendir (dir)) != NULL)
	{
		while ((dir_entry = readdir (fdir)) != NULL)
		{
			if (dir_entry->d_type == DT_DIR &&
				strcmp (dir_entry->d_name, ".") &&
				strcmp (dir_entry->d_name, ".."))
			{
				new_dir[0] = '\0';
				strcat (new_dir, dir);
				strcat (new_dir, "/");
				strcat (new_dir, dir_entry->d_name);
				watch_dir (fd, new_dir);
			}
		}
		closedir (fdir);
	}
	return *wd;
}


void affiche_str (gpointer key, gpointer data, gpointer user_data)
{
	char *s = key;
	int *d = data;
	fprintf (stderr, "+ %s : %d\n", s, *d);
}

void affiche (gpointer key, gpointer data, gpointer user_data)
{
	int *k = key;
	char *s = data;
	fprintf (stderr, "+ %d : %s\n", *k, s);
}

void close_wd (gpointer key, gpointer data, gpointer user_data)
{
	int *wd = key, *fd = user_data;
	fprintf (stderr, "- %d : %d\n", *wd, inotify_rm_watch (*fd, *wd));
}


int main (int argc, char **argv)
{
	int done=0;
	int fd;
	char dir[PATH_MAX];
	char *dir_watch;

	if (argc <= 1)
	{
		fprintf (stderr, "%s <folder>\n", basename (argv[0]));
		exit (1);
	}

	dir_watch = strdup (argv[1]);
	wds = g_hash_table_new_full (g_int_hash, g_int_equal, free, free);
	folders = g_hash_table_new (g_str_hash, g_str_equal);

	fd = inotify_init();
	if (fd < 0)
	{
        perror ("inotify_init");
		exit (1);
	}
	watch_dir (fd, dir_watch);

	struct timeval time;
	fd_set rfds, irfds;
	int ret;


	/* zero-out the fd_set */
	FD_ZERO (&rfds);

	/* add the inotify fd to the fd_set */
	FD_SET (fd, &rfds);
	irfds = rfds;
	while (!done)
	{
		/* timeout after five seconds */
		time.tv_sec = 5;
		time.tv_usec = 0;
		rfds = irfds;
		ret = select (fd + 1, &rfds, NULL, NULL, &time);
		if (ret < 0)
		{
			perror ("select");
			exit (1);
		}
		else if (!ret)
			continue;
		else if (FD_ISSET (fd, &rfds))
		{
			/* inotify events are available! */
			char buf[BUF_LEN];
			int len, i = 0, retry = 1;
			while (retry)
			{
				retry = 0;
				len = read (fd, buf, BUF_LEN);
				i=0;
				if (len < 0)
				{
					if (errno == EINTR)
					{
						retry = 1;
						continue;
					}
					else
					{
						perror ("read");
						exit (1);
					}
				}
				else if (!len)
					fprintf (stderr, "BUF_LEN too small?\n");
				else while (i < len)
				{
					struct inotify_event *event;

					event = (struct inotify_event *) &buf[i];
					if (event->mask & IN_ISDIR)
					{
						strcpy (dir, g_hash_table_lookup (wds, &(event->wd)));
						if (event->len)
						{
							strcat (dir, "/");
							strcat (dir, event->name);
						}
						if (event->mask & IN_CREATE || event->mask & IN_MOVED_TO)
							watch_dir (fd, dir);
						if (event->mask & IN_DELETE || event->mask & IN_MOVED_FROM)
							no_watch_dir (fd, dir, 1);
					}
					else
					{
						if (event->mask & IN_CLOSE_WRITE)
						{
							/* new file */
							strcpy (dir, g_hash_table_lookup (wds, &(event->wd)));
							if (event->len)
							{
								strcat (dir, "/");
								strcat (dir, event->name);
							}
							printf ("%s\n", dir);
							fflush (stdout);
						}
						else if (event->mask & IN_IGNORED)
						{
							const char *dir_ignored = g_hash_table_lookup (wds, &(event->wd));
							if (dir_ignored)
							{
								if (strcmp (dir_ignored, dir_watch) == 0)
									done = 1;
							}
						}

					}
					i += EVENT_SIZE + event->len;
				}
			}
		}
	}

	g_hash_table_destroy (folders);
	g_hash_table_foreach (wds, close_wd, &fd);
	g_hash_table_destroy (wds);


	close (fd);
	free (dir_watch);
	return 0;
}

