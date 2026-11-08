TARGET := fake_rbp
CFLAGS := -O0 -g3 -Wall -Wextra -fno-omit-frame-pointer -fno-stack-protector -fno-pie -fcf-protection=none
LDFLAGS := -no-pie -Wl,-z,relro,-z,now -Wl,-z,noexecstack
include ../../common.mk
