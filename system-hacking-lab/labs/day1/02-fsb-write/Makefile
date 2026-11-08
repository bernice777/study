TARGET := 02
SOURCE := 02.c
ALIASES := fsb32_write fsb_write
CFLAGS := -m32 -O0 -g3 -Wall -Wextra -fno-omit-frame-pointer -fno-stack-protector -fno-pie -fcf-protection=none
LDFLAGS := -m32 -no-pie -Wl,-z,relro,-z,lazy -Wl,-z,noexecstack -Wl,--defsym,changeMe=0x0804c028
include ../../common.mk

fsb32_write fsb_write: 02
	ln -sf 02 $@
