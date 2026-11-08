TARGET := canary_leak
CFLAGS := -O0 -g3 -Wall -Wextra -fno-omit-frame-pointer -fstack-protector-strong -fno-pie -fcf-protection=none
LDFLAGS := -no-pie -Wl,-z,relro,-z,now -Wl,-z,noexecstack
include ../../common.mk
