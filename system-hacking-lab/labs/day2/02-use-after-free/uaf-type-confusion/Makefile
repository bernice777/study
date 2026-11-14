CC := gcc
CFLAGS := -O0 -g -Wall -Wextra -Werror -std=c11 -fno-pie
LDFLAGS := -no-pie

.PHONY: all clean dist test gdb-check

all: challenge

challenge: src/challenge.c
	$(CC) $(CFLAGS) $< -o $@ $(LDFLAGS)

test: challenge
	python3 exploit.py QUIET | grep -F 'DH{basic_uaf_type_confusion}'

gdb-check: challenge
	gdb -q -batch ./challenge -x debug.gdb | grep -F 'same=1'
	gdb -q -batch ./challenge -x debug.gdb | grep -F 'condition true; print_flag will be called'

dist: challenge
	rm -rf dist
	mkdir -p dist/src
	cp challenge README.md docker-compose.yml dist/
	cp Dockerfile.dist dist/Dockerfile
	cp src/challenge.c dist/src/

clean:
	rm -f challenge
	rm -rf dist
