CC ?= gcc
TARGETS := heap_layout uninitialized double_free type_confusion
CFLAGS := -O0 -g3 -Wall -Wextra -fno-omit-frame-pointer -fno-stack-protector -fno-pie -fcf-protection=none
LDFLAGS := -no-pie -Wl,-z,relro,-z,now -Wl,-z,noexecstack

.PHONY: all clean test gdb-check

all: $(TARGETS)

%: %.c
	$(CC) $(CFLAGS) $< -o $@ $(LDFLAGS)

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
	@echo "[PASS] heap basics GDB 실행 검증 완료"

clean:
	rm -f $(TARGETS) core core.*
