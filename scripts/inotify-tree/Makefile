include config.mk

SRC = inotify-tree.c
OBJ = ${SRC:.c=.o}

all: inotify-tree

.c.o:
	${CC} -c ${CFLAGS} $<

inotify-tree: ${OBJ}
	${CC} -o $@ ${OBJ} ${LDFLAGS}

install: inotify-tree
	install -Dm0755 inotify-tree "${DESTDIR}${PREFIX}/bin"

uninstall:
	rm -f "${DESTDIR}${PREFIX}/bin/inotify-tree"

clean:
	rm -f inotify-tree ${OBJ}

.PHONY: all install uninstall clean

