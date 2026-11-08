CC ?= gcc
TARGETS := got_overwrite got_overwrite_full
CFLAGS := -O0 -g3 -Wall -Wextra -fno-omit-frame-pointer -fno-stack-protector -fno-pie -fcf-protection=none
BASE_LDFLAGS := -no-pie -Wl,-z,noexecstack

.PHONY: all clean test gdb-check

all: $(TARGETS)

got_overwrite: got_overwrite.c
	$(CC) $(CFLAGS) $< -o $@ $(BASE_LDFLAGS) -Wl,-z,relro,-z,lazy

got_overwrite_full: got_overwrite.c
	$(CC) $(CFLAGS) $< -o $@ $(BASE_LDFLAGS) -Wl,-z,relro,-z,now

test: all
	timeout 20s python3 ./solve_reference.py
	timeout 20s python3 ./verify_full_relro.py

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
	@echo "[PASS] Partial/Full RELRO GDB 실행 검증 완료"

clean:
	rm -f $(TARGETS) core core.*
