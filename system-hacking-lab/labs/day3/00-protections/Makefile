CC ?= gcc
TARGETS := aslr_demo pie_off pie_on lazy_binding
COMMON_CFLAGS := -O0 -g3 -Wall -Wextra -fno-omit-frame-pointer -fno-stack-protector -fcf-protection=none
COMMON_LDFLAGS := -Wl,-z,noexecstack

.PHONY: all clean test gdb-check

all: $(TARGETS)

aslr_demo: aslr_demo.c
	$(CC) $(COMMON_CFLAGS) -fno-pie $< -o $@ -no-pie $(COMMON_LDFLAGS) -Wl,-z,relro,-z,now -ldl

pie_off: pie_demo.c
	$(CC) $(COMMON_CFLAGS) -fno-pie $< -o $@ -no-pie $(COMMON_LDFLAGS) -Wl,-z,relro,-z,now

pie_on: pie_demo.c
	$(CC) $(COMMON_CFLAGS) -fPIE $< -o $@ -pie $(COMMON_LDFLAGS) -Wl,-z,relro,-z,now

lazy_binding: lazy_binding.c
	$(CC) $(COMMON_CFLAGS) -fno-pie $< -o $@ -no-pie $(COMMON_LDFLAGS) -Wl,-z,relro,-z,lazy

test: all
	timeout 20s python3 ./verify_reference.py

gdb-check: all
	@set -eu; \
	for binary in $(TARGETS); do \
		output="$$(gdb -nx -q -batch \
			-ex 'set debuginfod enabled off' \
			-ex 'set disable-randomization off' \
			-ex 'break main' -ex 'run' \
			-ex 'printf "KSJ_GDB_BREAK_OK\\n"' \
			./$$binary 2>&1)"; \
		printf '%s\n' "$$output" | grep -q KSJ_GDB_BREAK_OK; \
	done
	@echo "[PASS] protection demos GDB 실행 검증 완료"

clean:
	rm -f $(TARGETS) core core.*
