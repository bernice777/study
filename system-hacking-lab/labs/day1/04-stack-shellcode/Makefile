CC ?= gcc
TARGETS := stack_shellcode_nx_off stack_shellcode_nx_on
CFLAGS := -O0 -g3 -Wall -Wextra -fno-omit-frame-pointer -fno-stack-protector -fno-pie -fcf-protection=none
BASE_LDFLAGS := -no-pie -Wl,-z,relro,-z,now

.PHONY: all clean test gdb-check

all: $(TARGETS)

stack_shellcode_nx_off: stack_shellcode.c
	$(CC) $(CFLAGS) $< -o $@ $(BASE_LDFLAGS) -Wl,-z,execstack

stack_shellcode_nx_on: stack_shellcode.c
	$(CC) $(CFLAGS) $< -o $@ $(BASE_LDFLAGS) -Wl,-z,noexecstack

test: all
	timeout 20s python3 ./solve_reference.py

gdb-check: all
	@output="$$(gdb -nx -q -batch \
		-ex 'set debuginfod enabled off' \
		-ex 'set disable-randomization off' \
		-ex 'break vuln' -ex 'run' \
		-ex 'printf "KSJ_GDB_BREAK_OK\\n"' \
		./stack_shellcode_nx_off 2>&1)"; \
	printf '%s\n' "$$output" | grep -q KSJ_GDB_BREAK_OK
	@echo "[PASS] stack shellcode GDB 실행 검증 완료"

clean:
	rm -f $(TARGETS) core core.*
