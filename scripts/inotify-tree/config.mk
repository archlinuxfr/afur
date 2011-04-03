PREFIX = /usr/local

CFLAGS = -std=c99 -pedantic -Wall `pkg-config glib-2.0 --cflags`
LDFLAGS = `pkg-config glib-2.0 --libs`

CC = gcc

