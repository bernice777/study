CC ?= gcc
TARGET ?= challenge
SOURCE ?= $(TARGET).c
ALIASES ?=
CFLAGS ?= -O0 -g3 -Wall -Wextra -fno-omit-frame-pointer
LDFLAGS ?=

.PHONY: all clean test gdb-check

all: $(TARGET) $(ALIASES)

$(TARGET): $(SOURCE)
	$(CC) $(CFLAGS) $< -o $@ $(LDFLAGS)

test: $(TARGET)
	timeout 30s python3 ./solve_reference.py

gdb-check: $(TARGET)
	@output="$$(gdb -nx -q -batch \
		-ex 'set debuginfod enabled off' \
		-ex 'set disable-randomization off' \
		-ex 'break main' \
		-ex 'run' \
		-ex 'printf "KSJ_GDB_BREAK_OK\\n"' \
		./$(TARGET) 2>&1)"; \
	printf '%s\n' "$$output"; \
	printf '%s\n' "$$output" | grep -q 'KSJ_GDB_BREAK_OK'

clean:
	rm -f $(TARGET) $(ALIASES) core core.*
