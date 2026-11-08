LAB_DIRS := \
	day1/00-foundations \
	day1/01-fsb-read \
	day1/02-fsb-write \
	day1/03-bof-ret2win \
	day1/04-stack-shellcode \
	day1/05-fake-rbp \
	day2/00-heap-basics \
	day2/01-heap-overflow \
	day2/02-use-after-free \
	day3/00-protections \
	day3/01-canary-leak \
	day3/02-got-overwrite \
	day3/03-rop-ret2libc

.PHONY: all clean verify list $(LAB_DIRS)

all: $(LAB_DIRS)

$(LAB_DIRS):
	$(MAKE) -C $@ all

verify: all
	@set -eu; \
	for lab in $(LAB_DIRS); do \
		echo "[VERIFY] $$lab"; \
		$(MAKE) -C "$$lab" test gdb-check; \
	done
	@echo "[PASS] 모든 KSJ 실습의 기준 풀이와 GDB 실행 검증 완료"

list:
	@printf '%s\n' $(LAB_DIRS)

clean:
	@set -eu; \
	for lab in $(LAB_DIRS); do \
		$(MAKE) -C "$$lab" clean; \
	done
