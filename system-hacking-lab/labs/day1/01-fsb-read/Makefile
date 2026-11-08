TARGET := 01
SOURCE := 01.c
ALIASES := fsb32_read
CFLAGS := -m32 -O0 -g3 -Wall -Wextra -fno-omit-frame-pointer -fno-stack-protector -fno-pie -fcf-protection=none
LDFLAGS := -m32 -no-pie -Wl,-z,relro,-z,lazy -Wl,-z,noexecstack
include ../../common.mk

fsb32_read: 01
	ln -sf 01 $@
