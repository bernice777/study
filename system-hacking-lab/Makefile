CC := gcc
CFLAGS := -O0 -g3 -Wall -Wextra -fno-omit-frame-pointer -fno-inline -fPIE -fcf-protection=full
LDFLAGS := -pie

.PHONY: all clean check verify

all: argument

argument: argument.c
	$(CC) $(CFLAGS) $< -o $@ $(LDFLAGS)

check: argument verify
	./argument
	file ./argument
	readelf -h ./argument | grep -E 'Class:|Machine:|Type:'
	objdump -d -M intel ./argument | sed -n '/<main>:/,/^$$/p'

verify: argument
	./scripts/verify.sh

clean:
	rm -f argument
